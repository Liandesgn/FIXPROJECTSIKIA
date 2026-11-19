<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Http\Requests\Admin\UpdateIbuRequest;
use App\Models\Ibu;
use App\Models\PemeriksaanIbu;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Database\Eloquent\Builder;
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
            $ibu = Ibu::where('user_id', $user->id)->firstOrFail();

            return $this->show($request, $ibu);
        }

        $ibus = Ibu::query()
            ->visibleTo($user)
            ->with(['user', 'wilayahKerja'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->string('search') . '%';

                $query->where(fn ($q) => $q
                    ->where('nik', 'like', $search)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $search))
                );
            })
            ->when(
                $request->filled('wilayah_kerja_id'),
                fn ($q) => $q->where(
                    'wilayah_kerja_id',
                    $request->integer('wilayah_kerja_id')
                )
            )
            ->when(
                $request->filled('status_kehamilan'),
                fn ($q) => $q->where(
                    'status_kehamilan',
                    $request->string('status_kehamilan')
                )
            )
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

        abort_if(
            $user->hasRole(RoleEnum::Kader->value),
            403,
            'Akses ditolak.'
        );

        abort_unless(
            Ibu::visibleTo($user)->whereKey($ibu->id)->exists(),
            403,
            'Akses ditolak.'
        );

        if ($user->isOrangTua()) {
            abort_unless(
                (int) $ibu->user_id === (int) $user->id,
                403,
                'Akses ditolak.'
            );
        }

        $ibu->load(['user', 'wilayahKerja', 'anak']);

        $riwayatPemeriksaanIbu = PemeriksaanIbu::query()
            ->where('ibu_id', $ibu->id)
            ->orderByDesc('tgl_pemeriksaan')
            ->get();

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
            'ibu' => $ibu->load(['user', 'wilayahKerja']),
            'wilayahs' => $this->wilayahs($user),
            'statuses' => StatusKehamilanEnum::cases(),
        ]);
    }

    public function update(
        UpdateIbuRequest $request,
        Ibu $ibu
    ): RedirectResponse {
        $user = $request->user();

        // Pastikan ibu memang berada dalam cakupan akses pengguna.
        abort_unless(
            Ibu::visibleTo($user)->whereKey($ibu->id)->exists(),
            403,
            'Akses ditolak.'
        );

        $data = $request->validated();

        // Pastikan wilayah yang dikirim memang boleh dipilih pengguna.
        $wilayah = $this->wilayahQuery($user)
            ->whereKey($data['wilayah_kerja_id'])
            ->first();

        abort_unless(
            $wilayah,
            403,
            'Wilayah kerja yang dipilih bukan wilayah yang dapat dikelola.'
        );

        $hamil = $data['status_kehamilan']
            === StatusKehamilanEnum::Hamil->value;

        // HPL dihitung ulang di server dengan patokan kehamilan 40 minggu.
        $hpl = $hamil
            ? now()->startOfDay()
                ->addDays((40 - (int) $data['usia_kehamilan']) * 7)
                ->toDateString()
            : null;

        DB::transaction(function () use (
            $ibu,
            $data,
            $wilayah,
            $hamil,
            $hpl
        ) {
            $ibu->user()->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            $ibu->update([
                'wilayah_kerja_id' => $wilayah->id,
                'nik' => $data['nik'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tgl_lahir' => $data['tgl_lahir'],
                'status_kehamilan' => $data['status_kehamilan'],
                'usia_kehamilan' => $hamil
                    ? (int) $data['usia_kehamilan']
                    : null,
                'hpl' => $hpl,
            ]);
        });

        return redirect()
            ->route($this->base($request) . '.data-ibu.show', $ibu)
            ->with('success', 'Data ibu berhasil diperbarui.');
    }

    /**
     * Query wilayah yang boleh diakses pengguna.
     *
     * Admin: semua wilayah dalam Puskesmas miliknya.
     * Superadmin: semua wilayah.
     * Bidan/Kader: hanya wilayah penugasannya.
     */
    private function wilayahQuery(User $user): Builder
    {
        $query = WilayahKerja::query();

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAdmin()) {
            $user->loadMissing('admin');

            $puskesmasId = $user->admin?->puskesmas_id;

            return $puskesmasId
                ? $query->where('puskesmas_id', $puskesmasId)
                : $query->whereRaw('1 = 0');
        }

        $wilayahId = $user->bidan?->wilayah_kerja_id
            ?? $user->kader?->wilayah_kerja_id
            ?? $user->wilayah_kerja_id;

        return $wilayahId
            ? $query->where('id', $wilayahId)
            : $query->whereRaw('1 = 0');
    }

    private function wilayahs(User $user)
    {
        return $this->wilayahQuery($user)
            ->orderBy('kecamatan')
            ->orderBy('kelurahan')
            ->get();
    }

    /** Awalan nama route sesuai role. */
    private function base(Request $request): string
    {
        return Str::before($request->route()->getName(), '.');
    }
}
