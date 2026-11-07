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
        $wilayahId = $request->user()->loadMissing('admin')->managedWilayahId();

        $ibus = User::query()
            ->role(RoleEnum::Ibu->value)
            ->with('ibu.wilayahKerja')
            ->when($wilayahId, fn ($query) => $query
                ->whereHas('ibu', fn ($i) => $i->where('wilayah_kerja_id', $wilayahId)))
            ->when(! $wilayahId, fn ($query) => $query->whereRaw('1 = 0'))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';

                $query->where(fn ($q) => $q
                    ->where('name', 'like', $search)
                    ->orWhereHas('ibu', fn ($i) => $i->where('nik', 'like', $search)));
            })
            ->when($request->filled('status'), fn ($query) => $query
                ->where('status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

            return view('admin.ibu.index', [
            'ibus' => $ibus,
            'wilayahs' => WilayahKerja::orderBy('kecamatan')
                ->orderBy('kelurahan')
                ->get(),
            'statuses' => UserStatusEnum::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.ibu.create', $this->formData(
            $request->user()->loadMissing('admin')->managedWilayahId()
        ));
    }

    public function store(StoreIbuRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $wilayahId = $request->user()->loadMissing('admin')->managedWilayahId();

        abort_unless($wilayahId, 403, 'Admin belum memiliki wilayah kerja.');
        abort_unless((int) $data['wilayah_kerja_id'] === $wilayahId, 403);

        $hamil = $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value;

        DB::transaction(function () use ($data, $hamil, $wilayahId) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'status' => UserStatusEnum::Active->value,
            ]);

            $user->assignRole(RoleEnum::Ibu->value);

            $user->ibu()->create([
                'wilayah_kerja_id' => $wilayahId,
                'nik' => $data['nik'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tgl_lahir' => $data['tgl_lahir'],
                'status_kehamilan' => $data['status_kehamilan'],
                'hpl' => $hamil ? ($data['hpl'] ?? null) : null,
                'usia_kehamilan' => $hamil ? ($data['usia_kehamilan'] ?? null) : null,
            ]);
        });

        return redirect()
            ->route('admin.ibu.index')
            ->with('success', 'Data ibu berhasil didaftarkan.');
    }

    public function edit(Request $request, User $ibu): View
    {
        abort_unless($ibu->hasRole(RoleEnum::Ibu->value), 404);

        $wilayahId = $request->user()->loadMissing('admin')->managedWilayahId();
        abort_unless($ibu->ibu?->wilayah_kerja_id === $wilayahId, 403);

        $ibu->load('ibu');

        return view('admin.ibu.edit', [
            'ibu' => $ibu,
            ...$this->formData($wilayahId),
        ]);
    }

    public function update(Request $request, User $ibu): RedirectResponse
    {
        abort_unless($ibu->hasRole(RoleEnum::Ibu->value), 404);

        $wilayahId = $request->user()->loadMissing('admin')->managedWilayahId();
        abort_unless($ibu->ibu?->wilayah_kerja_id === $wilayahId, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($ibu->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'nik' => ['required', 'string', 'max:16'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tgl_lahir' => ['required', 'date'],
            'status_kehamilan' => [
                'required',
                Rule::enum(StatusKehamilanEnum::class),
            ],
            'hpl' => ['nullable', 'date'],
            'usia_kehamilan' => ['nullable', 'integer', 'min:1', 'max:45'],
        ]);

        $hamil = $data['status_kehamilan'] === StatusKehamilanEnum::Hamil->value;

        DB::transaction(function () use ($ibu, $data, $hamil, $wilayahId) {
            $ibu->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            $ibu->ibu()->updateOrCreate(
                ['user_id' => $ibu->id],
                [
                    'wilayah_kerja_id' => $wilayahId,
                    'nik' => $data['nik'],
                    'tempat_lahir' => $data['tempat_lahir'],
                    'tgl_lahir' => $data['tgl_lahir'],
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

    public function toggleStatus(Request $request, User $ibu): RedirectResponse
    {
        abort_unless($ibu->hasRole(RoleEnum::Ibu->value), 404);
        abort_unless(
            $ibu->ibu?->wilayah_kerja_id === $request->user()->loadMissing('admin')->managedWilayahId(),
            403
        );

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
    private function formData(?int $wilayahId): array
    {
        $wilayahs = $this->wilayahs($wilayahId);

        return [
            'wilayahs' => $wilayahs,
            'kabupaten' => $wilayahs->pluck('kabupaten')->filter()->unique()->first() ?? 'Gresik',
            'statuses' => StatusKehamilanEnum::cases(),
        ];
    }

    private function wilayahs(?int $wilayahId)
    {
        return WilayahKerja::query()
            ->when($wilayahId, fn ($q) => $q->where('id', $wilayahId))
            ->when(! $wilayahId, fn ($q) => $q->whereRaw('1 = 0'))
            ->orderBy('kecamatan')
            ->orderBy('kelurahan')
            ->get();
    }
}