<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreIbuRequest;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IbuController extends Controller
{
    public function index(Request $request): View
    {
        $ibus = User::query()
            ->role(RoleEnum::Ibu->value)
            ->with('ibu.wilayahKerja')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';

                $query->where(fn ($q) => $q
                    ->where('name', 'like', $search)
                    ->orWhereHas('ibu', fn ($i) => $i->where('nik', 'like', $search)));
            })
            ->when($request->filled('wilayah_kerja_id'), fn ($query) => $query
                ->whereHas('ibu', fn ($i) => $i
                    ->where('wilayah_kerja_id', $request->integer('wilayah_kerja_id'))))
            ->when($request->filled('status'), fn ($query) => $query
                ->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.ibu.index', [
            'ibus' => $ibus,
            'wilayahs' => WilayahKerja::query()
                ->orderBy('kecamatan')
                ->orderBy('desa')
                ->get(),
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

        DB::transaction(function () use ($data, $hamil) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'status' => UserStatusEnum::Active->value,
            ]);

            $user->assignRole(RoleEnum::Ibu->value);

            $user->ibu()->create([
                'wilayah_kerja_id' => $data['wilayah_kerja_id'],
                'nik' => $data['nik'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'status_kehamilan' => $data['status_kehamilan'],
                'hpl' => $hamil ? ($data['hpl'] ?? null) : null,
                'usia_kehamilan' => $hamil ? ($data['usia_kehamilan'] ?? null) : null,
            ]);
        });

        return redirect()
            ->route('admin.ibu.index')
            ->with('success', 'Data ibu berhasil didaftarkan.');
    }

    public function edit(User $ibu): View
    {
        abort_unless($ibu->hasRole(RoleEnum::Ibu->value), 404);

        $ibu->load('ibu');

        return view('admin.ibu.edit', [
            'ibu' => $ibu,
            ...$this->formData(),
        ]);
    }

    public function update(Request $request, User $ibu): RedirectResponse
    {
        abort_unless($ibu->hasRole(RoleEnum::Ibu->value), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($ibu->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'wilayah_kerja_id' => ['required', 'exists:wilayah_kerja,id'],
            'nik' => ['required', 'string', 'max:16'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'status_kehamilan' => [
                'required',
                Rule::enum(StatusKehamilanEnum::class),
            ],
            'hpl' => ['nullable', 'date'],
            'usia_kehamilan' => ['nullable', 'integer', 'min:1', 'max:45'],
        ]);

        $hamil = $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value;

        DB::transaction(function () use ($ibu, $data, $hamil) {
            $ibu->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            $ibu->ibu()->updateOrCreate(
                ['user_id' => $ibu->id],
                [
                    'wilayah_kerja_id' => $data['wilayah_kerja_id'],
                    'nik' => $data['nik'],
                    'tempat_lahir' => $data['tempat_lahir'],
                    'tanggal_lahir' => $data['tanggal_lahir'],
                    'status_kehamilan' => $data['status_kehamilan'],
                    'hpl' => $hamil ? ($data['hpl'] ?? null) : null,
                    'usia_kehamilan' => $hamil ? ($data['usia_kehamilan'] ?? null) : null,
                ]
            );
        });

        return redirect()
            ->route('admin.ibu.index')
            ->with('success', 'Data ibu berhasil diperbarui.');
    }

    public function toggleStatus(User $ibu): RedirectResponse
    {
        abort_unless($ibu->hasRole(RoleEnum::Ibu->value), 404);

        if ($ibu->id === auth()->id()) {
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