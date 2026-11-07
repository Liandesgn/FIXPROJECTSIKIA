<?php

namespace App\Http\Controllers;

use App\Models\PemeriksaanIbu;
use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Http\Requests\Admin\UpdateIbuRequest;
use App\Models\Ibu;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DataIbuController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

     
        if ($user->isOrangTua()) {
            return $this->tampilanIbuSendiri($user);
        }

        $ibus = Ibu::query()
            ->visibleTo($user)
            ->with(['user', 'wilayahKerja'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($q) => $q
                    ->where('nik', 'like', $search)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $search)));
            })
            ->when($request->filled('wilayah_kerja_id'), fn ($q) => $q
                ->where('wilayah_kerja_id', $request->integer('wilayah_kerja_id')))
            ->when($request->filled('status_kehamilan'), fn ($q) => $q
                ->where('status_kehamilan', $request->string('status_kehamilan')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('data-ibu.index', [
            'ibus' => $ibus,
            'wilayahs' => $this->wilayahs($user),
            'statuses' => StatusKehamilanEnum::cases(),
            'base' => $this->base($request),
        ]);
    }

    public function show(Request $request, Ibu $ibu): View
{
    $user = $request->user();

    // Kader tidak memiliki akses ke detail ibu.
    abort_if(
        $user->hasRole(RoleEnum::Kader->value),
        403,
        'Akses ditolak.'
    );

    // Memastikan ibu termasuk dalam cakupan wilayah pengguna.
    abort_unless(
        Ibu::visibleTo($user)->whereKey($ibu->id)->exists(),
        403,
        'Akses ditolak.'
    );

    // Ibu hanya boleh melihat data dirinya sendiri.
    if ($user->isOrangTua()) {
        abort_unless(
            (int) $ibu->user_id === (int) $user->id,
            403,
            'Akses ditolak.'
        );
    }

    // Mengambil data akun, wilayah kerja, dan anak.
    $ibu->load([
        'user',
        'wilayahKerja',
        'anak',
    ]);

    // Mengambil seluruh riwayat pemeriksaan kehamilan.
    $riwayatPemeriksaanIbu = PemeriksaanIbu::query()
        ->where('ibu_id', $ibu->id)
        ->orderByDesc('tgl_pemeriksaan')
        ->get();

    // Mengambil pemeriksaan paling baru.
    $pemeriksaanIbuTerakhir = $riwayatPemeriksaanIbu->first();

    return view('data-ibu.show', [
        'ibu' => $ibu,
        'base' => $this->base($request),
        'riwayatPemeriksaanIbu' => $riwayatPemeriksaanIbu,
        'pemeriksaanIbuTerakhir' => $pemeriksaanIbuTerakhir,
    ]);
}

    public function edit(Request $request, Ibu $ibu): View
    {
        $user = $request->user();

        abort_unless(
            Ibu::visibleTo($user)->whereKey($ibu->id)->exists(),
            403,
            'Akses ditolak.'
        );

        return view('data-ibu.edit', [
            'ibu' => $ibu->load('user'),
            'wilayahs' => $this->wilayahs($user),
            'statuses' => StatusKehamilanEnum::cases(),
        ]);
    }

    public function update(UpdateIbuRequest $request, Ibu $ibu): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            Ibu::visibleTo($user)->whereKey($ibu->id)->exists(),
            403,
            'Akses ditolak.'
        );

        $data = $request->validated();
        $hamil = $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value;
        $wilayahId = $user->isAdmin()
            ? $user->loadMissing('admin')->managedWilayahId()
            : $ibu->wilayah_kerja_id;

        abort_unless($wilayahId, 403);

        DB::transaction(function () use ($ibu, $data, $hamil, $wilayahId) {
            $ibu->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            $ibu->update([
                'wilayah_kerja_id' => $wilayahId,
                'nik' => $data['nik'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tgl_lahir' => $data['tgl_lahir'],
                'status_kehamilan' => $data['status_kehamilan'],
                // Melahirkan: usia kehamilan dan HPL dikosongkan
                'usia_kehamilan' => $hamil ? $data['usia_kehamilan'] : null,
                'hpl' => $hamil ? $data['hpl'] : null,
            ]);
        });

        return redirect()
            ->route($this->base($request).'.data-ibu.show', $ibu)
            ->with('success', 'Data ibu berhasil diperbarui.');
    }

    /** Tampilan khusus role Ibu: data dirinya sendiri + anaknya. */
    private function tampilanIbuSendiri(User $user): View
    {
        $ibu = Ibu::with(['user', 'wilayahKerja', 'anak'])
            ->where('user_id', $user->id)
            ->first();

        return view('data-ibu.ibu-sendiri', ['ibu' => $ibu]);
    }

    /** Awalan nama route sesuai role: admin, bidan, kader, atau ibu. */
    private function base(Request $request): string
    {
        return Str::before($request->route()->getName(), '.');
    }

    private function wilayahs(User $user)
    {
        $wilayahId = $user->isAdmin()
            ? $user->loadMissing('admin')->managedWilayahId()
            : ($user->bidan?->wilayah_kerja_id ?? $user->kader?->wilayah_kerja_id);

        return WilayahKerja::query()
            ->when($wilayahId, fn ($q) => $q->where('id', $wilayahId))
            ->when(! $wilayahId, fn ($q) => $q->whereRaw('1 = 0'))
            ->orderBy('kecamatan')
            ->orderBy('kelurahan')
            ->get();
    }
}