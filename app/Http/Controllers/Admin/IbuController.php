<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreIbuRequest;
use App\Http\Requests\Admin\UpdateIbuRequest;
use App\Models\KelurahanDesa;
use App\Models\Puskesmas;
use App\Models\Rt;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class IbuController extends Controller
{
    public function index(Request $request): View
    {
        $puskesmasId = $this->managedPuskesmasId($request->user());

        $ibus = User::query()
            ->role(RoleEnum::Ibu->value)
            ->with('ibu.wilayahKerja.kelurahanDesa')
            ->when(
                $puskesmasId,
                fn ($query) => $query->whereHas(
                    'ibu.wilayahKerja',
                    fn ($wilayah) => $wilayah->where('puskesmas_id', $puskesmasId)
                ),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->input('search') . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)
                        ->orWhereHas(
                            'ibu',
                            fn ($i) => $i->where('nik', 'like', $search)
                        );
                });
            })
            ->when($request->filled('status'), fn ($query) => $query
                ->where('status', $request->input('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.ibu.index', [
            'ibus' => $ibus,
            'wilayahs' => WilayahKerja::query()
                ->where('puskesmas_id', $puskesmasId)
                ->with('kelurahanDesa')
                ->orderBy('kelurahan')
                ->get(),
            'statuses' => UserStatusEnum::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $puskesmasId = $this->managedPuskesmasId($request->user());

        abort_unless($puskesmasId, 403, 'Admin belum memiliki Puskesmas.');

        return view('admin.ibu.create', $this->formData($puskesmasId));
    }

    public function store(StoreIbuRequest $request): RedirectResponse
    {
        $puskesmasId = $this->managedPuskesmasId($request->user());

        abort_unless($puskesmasId, 403, 'Admin belum memiliki Puskesmas.');

        $data = $request->validated();

        $alamat = $this->resolveAddress(
            (int) $data['rt_id'],
            $puskesmasId
        );

        $hamil = $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value;
        $hpl = $hamil
        ? now()->startOfDay()
            ->addWeeks(40 - (int) $data['usia_kehamilan'])
            ->toDateString()
        : null;

        DB::transaction(function () use ($data, $alamat, $hamil, $hpl) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'],
                'status' => UserStatusEnum::Active->value,
            ]);

            $user->assignRole(RoleEnum::Ibu->value);

            $user->ibu()->create([
                'wilayah_kerja_id' => $alamat['wilayah']->id,
                'rt_id' => $alamat['rt']->id,
                'nik' => $data['nik'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tgl_lahir' => $data['tgl_lahir'],
                'alamat_lengkap' => $data['alamat_lengkap'] ?? null,
                'status_kehamilan' => $data['status_kehamilan'],
                'hpl' => $hpl,
                'usia_kehamilan' => $hamil ? ($data['usia_kehamilan'] ?? null) : null,
            ]);
        });

        return redirect()
            ->route('admin.ibu.index')
            ->with('success', 'Registrasi berhasil.');
    }

    public function edit(Request $request, User $ibu): View
    {
        abort_unless($ibu->hasRole(RoleEnum::Ibu->value), 404);

        $puskesmasId = $this->managedPuskesmasId($request->user());

        abort_unless($puskesmasId, 403);
        $this->assertUserInPuskesmas($ibu, $puskesmasId);

        $ibu->load('ibu.rt.rw.kelurahanDesa');

        return view('admin.ibu.edit', [
            'ibu' => $ibu,
            ...$this->formData($puskesmasId, $ibu),
        ]);
    }

    public function update(UpdateIbuRequest $request, User $ibu): RedirectResponse
    {
        abort_unless($ibu->hasRole(RoleEnum::Ibu->value), 404);

        $puskesmasId = $this->managedPuskesmasId($request->user());

        abort_unless($puskesmasId, 403);
        $this->assertUserInPuskesmas($ibu, $puskesmasId);

        $data = $request->validated();

        $alamat = $this->resolveAddress(
            (int) $data['rt_id'],
            $puskesmasId
        );

        $hamil = $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value;
        $hpl = $hamil
        ? now()->startOfDay()
            ->addWeeks(40 - (int) $data['usia_kehamilan'])
            ->toDateString()
        : null;
        
        DB::transaction(function () use ($ibu, $data, $alamat, $hamil, $hpl) {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ];

            if (! empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $ibu->update($userData);

            $ibu->ibu()->updateOrCreate(
                ['user_id' => $ibu->id],
                [
                    'wilayah_kerja_id' => $alamat['wilayah']->id,
                    'rt_id' => $alamat['rt']->id,
                    'nik' => $data['nik'],
                    'tempat_lahir' => $data['tempat_lahir'],
                    'tgl_lahir' => $data['tgl_lahir'],
                    'alamat_lengkap' => $data['alamat_lengkap'] ?? null,
                    'status_kehamilan' => $data['status_kehamilan'],
                    'hpl' => $hpl,
                    'usia_kehamilan' => $hamil ? ($data['usia_kehamilan'] ?? null) : null,
                ]
            );
        });

        return redirect()
            ->route('admin.ibu.index')
            ->with('success', 'Data ibu berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, User $ibu): RedirectResponse
    {
        abort_unless($ibu->hasRole(RoleEnum::Ibu->value), 404);

        $puskesmasId = $this->managedPuskesmasId($request->user());

        abort_unless($puskesmasId, 403);
        $this->assertUserInPuskesmas($ibu, $puskesmasId);

        if ($ibu->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $ibu->update([
            'status' => $ibu->isActive()
                ? UserStatusEnum::Inactive->value
                : UserStatusEnum::Active->value,
        ]);

        if (! $ibu->isActive()) {
            $ibu->tokens()->delete();
        }

        return back()->with('success', 'Status akun berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | Endpoint Dropdown Alamat
    |--------------------------------------------------------------------------
    */

    public function getKelurahan(Request $request): JsonResponse
    {
        $this->authorizeAddressAccess();

        $puskesmasId = $this->managedPuskesmasId($request->user());

        abort_unless($puskesmasId, 403);

        $data = KelurahanDesa::query()
            ->where('puskesmas_id', $puskesmasId)
            ->orderBy('nama_kelurahan_desa')
            ->get(['id', 'nama_kelurahan_desa', 'jenis']);

        return response()->json($data);
    }

    public function getRw(Request $request): JsonResponse
    {
        $this->authorizeAddressAccess();

        $puskesmasId = $this->managedPuskesmasId($request->user());

        abort_unless($puskesmasId, 403);

        $request->validate([
            'kelurahan_desa_id' => ['required', 'integer'],
        ]);

        $data = \App\Models\Rw::query()
            ->where('kelurahan_desa_id', $request->integer('kelurahan_desa_id'))
            ->whereHas(
                'kelurahanDesa',
                fn ($query) => $query->where('puskesmas_id', $puskesmasId)
            )
            ->orderBy('nomor_rw')
            ->get(['id', 'nomor_rw']);

        return response()->json($data);
    }

    public function getRt(Request $request): JsonResponse
    {
        $this->authorizeAddressAccess();

        $puskesmasId = $this->managedPuskesmasId($request->user());

        abort_unless($puskesmasId, 403);

        $request->validate([
            'rw_id' => ['required', 'integer'],
        ]);

        $data = Rt::query()
            ->where('rw_id', $request->integer('rw_id'))
            ->whereHas(
                'rw.kelurahanDesa',
                fn ($query) => $query->where('puskesmas_id', $puskesmasId)
            )
            ->orderBy('nomor_rt')
            ->get(['id', 'nomor_rt']);

        return response()->json($data);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    private function managedPuskesmasId(User $user): ?int
    {
        $user->loadMissing('admin');

        return $user->admin?->puskesmas_id
            ? (int) $user->admin->puskesmas_id
            : null;
    }

    private function authorizeAddressAccess(): void
    {
        abort_unless(
            Gate::allows('user.create') || Gate::allows('user.update'),
            403
        );
    }

    private function assertUserInPuskesmas(User $ibu, int $puskesmasId): void
    {
        abort_unless(
            $ibu->ibu()
                ->whereHas(
                    'wilayahKerja',
                    fn ($query) => $query->where('puskesmas_id', $puskesmasId)
                )
                ->exists(),
            403
        );
    }

    private function resolveAddress(int $rtId, int $puskesmasId): array
    {
        $rt = Rt::query()
            ->with('rw.kelurahanDesa')
            ->findOrFail($rtId);

        $kelurahan = $rt->rw?->kelurahanDesa;

        abort_unless(
            $kelurahan && (int) $kelurahan->puskesmas_id === $puskesmasId,
            403,
            'RT yang dipilih bukan bagian dari wilayah Puskesmas Anda.'
        );

        $wilayah = WilayahKerja::query()
            ->where('kelurahan_desa_id', $kelurahan->id)
            ->where('puskesmas_id', $puskesmasId)
            ->firstOrFail();

        return [
            'rt' => $rt,
            'wilayah' => $wilayah,
        ];
    }

    private function formData(int $puskesmasId, ?User $ibu = null): array
    {
        $puskesmas = Puskesmas::with('kecamatan')->findOrFail($puskesmasId);

        $profil = $ibu?->ibu;

        $selectedKelurahanId = old(
            'kelurahan_desa_id',
            $profil?->rt?->rw?->kelurahan_desa_id
        );

        $selectedRwId = old('rw_id', $profil?->rt?->rw_id);
        $selectedRtId = old('rt_id', $profil?->rt_id);

        return [
            'puskesmas' => $puskesmas,
            'kecamatan' => $puskesmas->kecamatan,
            'kelurahans' => KelurahanDesa::query()
                ->where('puskesmas_id', $puskesmasId)
                ->orderBy('nama_kelurahan_desa')
                ->get(),
            'selectedKelurahanId' => $selectedKelurahanId,
            'selectedRwId' => $selectedRwId,
            'selectedRtId' => $selectedRtId,
            'statuses' => StatusKehamilanEnum::cases(),
        ];
    }
}