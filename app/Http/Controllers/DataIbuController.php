<?php

namespace App\Http\Controllers;

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

        // Role Ibu: tampilan kartu sesuai wireframe 5.14
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
            'wilayahs' => $this->wilayahs(),
            'statuses' => StatusKehamilanEnum::cases(),
            'base' => $this->base($request),
        ]);
    }

    public function show(Request $request, Ibu $ibu): View
    {
        $user = $request->user();

        // Kader hanya boleh mencari, tidak boleh membuka detail
        abort_if($user->hasRole(RoleEnum::Kader->value), 403, 'Akses ditolak.');

        abort_unless(
            Ibu::visibleTo($user)->whereKey($ibu->id)->exists(),
            403,
            'Akses ditolak.'
        );

        if ($user->isOrangTua()) {
            return $this->tampilanIbuSendiri($user);
        }

        return view('data-ibu.show', [
            'ibu' => $ibu->load(['user', 'wilayahKerja', 'anak']),
            'base' => $this->base($request),
        ]);
    }

    public function edit(Ibu $ibu): View
    {
        return view('data-ibu.edit', [
            'ibu' => $ibu->load('user'),
            'wilayahs' => $this->wilayahs(),
            'statuses' => StatusKehamilanEnum::cases(),
        ]);
    }

    public function update(UpdateIbuRequest $request, Ibu $ibu): RedirectResponse
    {
        $data = $request->validated();
        $hamil = $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value;

        DB::transaction(function () use ($ibu, $data, $hamil) {
            $ibu->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            $ibu->update([
                'wilayah_kerja_id' => $data['wilayah_kerja_id'],
                'nik' => $data['nik'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'status_kehamilan' => $data['status_kehamilan'],
                // Melahirkan: usia kehamilan dan HPL dikosongkan
                'usia_kehamilan' => $hamil ? $data['usia_kehamilan'] : null,
                'hpl' => $hamil ? $data['hpl'] : null,
            ]);
        });

        return redirect()
            ->route('admin.data-ibu.show', $ibu)
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

    private function wilayahs()
    {
        return WilayahKerja::query()->orderBy('kecamatan')->orderBy('desa')->get();
    }
}