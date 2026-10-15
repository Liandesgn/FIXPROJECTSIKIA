<?php

namespace App\Http\Controllers\Panel;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrangTua\StoreOrangTuaRequest;
use App\Http\Requests\OrangTua\UpdateOrangTuaRequest;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelolaOrangTuaController extends Controller
{
    public function index(Request $request): View
    {
        $orangTuaQuery = User::query()
            ->role(RoleEnum::OrangTua->value)
            ->with(['wilayahKerja', 'bidanWilayah', 'kaderWilayah'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(fn ($query) => $query
                    ->where('name', 'like', $search)
                    ->orWhere('nama_ibu', 'like', $search)
                    ->orWhere('nik', 'like', $search));
            })
            ->when($request->filled('kecamatan'), fn ($query) => $query
                ->whereHas('wilayahKerja', fn ($query) => $query->where('kecamatan', $request->string('kecamatan'))))
            ->latest();

        $wilayahs = WilayahKerja::query()
            ->with(['bidan', 'kader'])
            ->withCount('orangTua')
            ->orderBy('kecamatan')
            ->orderBy('desa')
            ->get();

        $stats = [
            'orang_tua' => User::role(RoleEnum::OrangTua->value)->count(),
            'hamil' => User::role(RoleEnum::OrangTua->value)
                ->where('status_kehamilan', StatusKehamilanEnum::Hamil->value)
                ->count(),
            'desa' => $wilayahs->count(),
            'kecamatan' => $wilayahs->pluck('kecamatan')->filter()->unique()->count(),
        ];

        return view('panel.kelola-orang-tua.index', [
            'orangTua' => $orangTuaQuery->paginate(10)->withQueryString(),
            'wilayahs' => $wilayahs->groupBy('kecamatan'),
            'kecamatans' => $wilayahs->pluck('kecamatan')->filter()->unique()->sort()->values(),
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('panel.kelola-orang-tua.create', $this->formData());
    }

    public function store(StoreOrangTuaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'nik' => $data['nik'],
            'nama_ibu' => $data['nama_ibu'],
            'tempat_lahir' => $data['tempat_lahir'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'status_kehamilan' => $data['status_kehamilan'],
            'hpl' => $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value ? ($data['hpl'] ?? null) : null,
            'usia_kehamilan' => $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value ? ($data['usia_kehamilan'] ?? null) : null,
            'wilayah_kerja_id' => $data['wilayah_kerja_id'],
            'status' => UserStatusEnum::Active->value,
        ]);

        $user->assignRole(RoleEnum::OrangTua->value);

        return redirect()
            ->route('panel.kelola-orang-tua.index')
            ->with('success', 'Data orang tua berhasil didaftarkan.');
    }

    public function show(User $orangTua): View
    {
        abort_unless($orangTua->isOrangTua(), 404);

        $orangTua->load(['wilayahKerja', 'bidanWilayah', 'kaderWilayah']);

        return view('panel.kelola-orang-tua.show', compact('orangTua'));
    }

    public function edit(User $orangTua): View
    {
        abort_unless($orangTua->isOrangTua(), 404);

        return view('panel.kelola-orang-tua.edit', [
            'orangTua' => $orangTua->load('wilayahKerja'),
        ] + $this->formData());
    }

    public function update(UpdateOrangTuaRequest $request, User $orangTua): RedirectResponse
    {
        abort_unless($orangTua->isOrangTua(), 404);

        $data = $request->validated();

        $orangTua->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'nik' => $data['nik'],
            'nama_ibu' => $data['nama_ibu'],
            'tempat_lahir' => $data['tempat_lahir'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'status_kehamilan' => $data['status_kehamilan'],
            'hpl' => $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value ? ($data['hpl'] ?? null) : null,
            'usia_kehamilan' => $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value ? ($data['usia_kehamilan'] ?? null) : null,
            'wilayah_kerja_id' => $data['wilayah_kerja_id'],
        ]);

        if (! empty($data['password'])) {
            $orangTua->password = $data['password'];
        }

        $orangTua->save();

        return redirect()
            ->route('panel.kelola-orang-tua.show', $orangTua)
            ->with('success', 'Data orang tua berhasil diperbarui.');
    }

    public function destroy(User $orangTua): RedirectResponse
    {
        abort_unless($orangTua->isOrangTua(), 404);

        $orangTua->tokens()->delete();
        $orangTua->delete();

        return redirect()
            ->route('panel.kelola-orang-tua.index')
            ->with('success', 'Data orang tua berhasil dihapus.');
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
            'kecamatans' => $wilayahs->pluck('kecamatan')->filter()->unique()->sort()->values(),
            'kabupaten' => $wilayahs->pluck('kabupaten')->filter()->unique()->first() ?? 'Gresik',
            'statuses' => StatusKehamilanEnum::cases(),
        ];
    }
}
