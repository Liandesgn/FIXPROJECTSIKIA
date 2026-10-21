<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreIbuRequest;
use App\Http\Requests\Admin\UpdateIbuRequest;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IbuController extends Controller
{
    public function index(Request $request): View
    {
        $ibus = User::query()
            ->role(RoleEnum::Ibu->value)
            ->with('wilayahKerja')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($query) => $query
                    ->where('name', 'like', $search)
                    ->orWhere('nik', 'like', $search));
            })
            ->when($request->filled('wilayah_kerja_id'), fn ($query) => $query
                ->where('wilayah_kerja_id', $request->integer('wilayah_kerja_id')))
            ->when($request->filled('status'), fn ($query) => $query
                ->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.ibu.index', [
            'ibus' => $ibus,
            'wilayahs' => WilayahKerja::query()->orderBy('kecamatan')->orderBy('desa')->get(),
            'statuses' => UserStatusEnum::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.ibu.create', $this->formData());
    }

    public function store(StoreIbuRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $hamil = $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value;

        $ibu = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'nik' => $data['nik'],
            'tempat_lahir' => $data['tempat_lahir'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'status_kehamilan' => $data['status_kehamilan'],
            'hpl' => $hamil ? ($data['hpl'] ?? null) : null,
            'usia_kehamilan' => $hamil ? ($data['usia_kehamilan'] ?? null) : null,
            'wilayah_kerja_id' => $data['wilayah_kerja_id'],
            'status' => UserStatusEnum::Active->value,
        ]);

        $ibu->assignRole(RoleEnum::OrangTua->value);

        return redirect()
            ->route('admin.ibu.index')
            ->with('success', 'Data ibu berhasil didaftarkan.');
    }

    public function edit(User $ibu): View
    {
        abort_unless($ibu->hasRole(RoleEnum::OrangTua->value), 404);

        return view('admin.ibu.edit', [
            'ibu' => $ibu->load('wilayahKerja'),
        ] + $this->formData());
    }

    public function update(UpdateIbuRequest $request, User $ibu): RedirectResponse
    {
        abort_unless($ibu->hasRole(RoleEnum::OrangTua->value), 404);

        $data = $request->validated();
        $hamil = $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value;

        $ibu->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'nik' => $data['nik'],
            'tempat_lahir' => $data['tempat_lahir'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'status_kehamilan' => $data['status_kehamilan'],
            'hpl' => $hamil ? ($data['hpl'] ?? null) : null,
            'usia_kehamilan' => $hamil ? ($data['usia_kehamilan'] ?? null) : null,
            'wilayah_kerja_id' => $data['wilayah_kerja_id'],
        ]);

        if (! empty($data['password'])) {
            $ibu->password = $data['password'];
        }

        $ibu->save();

        return redirect()
            ->route('admin.ibu.index')
            ->with('success', 'Data ibu berhasil diperbarui.');
    }

    public function toggleStatus(User $ibu): RedirectResponse
    {
        abort_unless($ibu->hasRole(RoleEnum::OrangTua->value), 404);

        if ($ibu->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $ibu->update([
            'status' => $ibu->isActive() ? UserStatusEnum::Inactive->value : UserStatusEnum::Active->value,
        ]);

        if (! $ibu->isActive()) {
            $ibu->tokens()->delete();
        }

        return back()->with('success', 'Status akun berhasil diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $wilayahs = WilayahKerja::query()
            ->orderBy('kecamatan')
            ->orderBy('desa')
            ->get();

        return [
            'wilayahs' => $wilayahs,
            'kabupaten' => $wilayahs->pluck('kabupaten')->filter()->unique()->first() ?? 'Gresik',
            'statuses' => StatusKehamilanEnum::cases(),
        ];
    }
}
