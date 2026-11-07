<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePetugasRequest;
use App\Http\Requests\Admin\UpdatePetugasRequest;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BidanController extends Controller
{
    public function index(Request $request): View
    {
        $wilayahId = $request->user()->loadMissing('admin')->managedWilayahId();

        $bidans = User::query()
            ->role(RoleEnum::Bidan->value)
            ->with('bidan.wilayahKerja')
            ->when($wilayahId, fn ($query) => $query
                ->whereHas('bidan', fn ($b) => $b->where('wilayah_kerja_id', $wilayahId)))
            ->when(! $wilayahId, fn ($query) => $query->whereRaw('1 = 0'))
            ->when($request->filled('search'), fn ($query) => $query
                ->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('wilayah_kerja_id'), fn ($query) => $query
                ->whereHas('bidan', fn ($b) => $b
                    ->where('wilayah_kerja_id', $request->integer('wilayah_kerja_id'))))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.bidan.index', [
            'bidans' => $bidans,
            'wilayahs' => $this->wilayahs($wilayahId),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.bidan.create', [
            'wilayahs' => $this->wilayahs($request->user()->loadMissing('admin')->managedWilayahId()),
        ]);
    }

    public function store(StorePetugasRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $bidan = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'status' => UserStatusEnum::Active->value,
            ]);

            $bidan->assignRole(RoleEnum::Bidan->value);
            $bidan->assignWilayah((int) $data['wilayah_kerja_id']);
        });

        return redirect()
            ->route('admin.bidan.index')
            ->with('success', 'Data bidan berhasil ditambahkan.');
    }

    public function edit(Request $request, User $bidan): View
    {
        abort_unless($bidan->hasRole(RoleEnum::Bidan->value), 404);

        $wilayahId = $request->user()->loadMissing('admin')->managedWilayahId();
        abort_unless($bidan->bidan?->wilayah_kerja_id === $wilayahId, 403);

       return view('admin.bidan.index', [
        'bidans' => $bidans,
        'wilayahs' => WilayahKerja::orderBy('kecamatan')
            ->orderBy('kelurahan')
            ->get(),
    ]);
    }

    public function update(UpdatePetugasRequest $request, User $bidan): RedirectResponse
    {
        abort_unless($bidan->hasRole(RoleEnum::Bidan->value), 404);
        abort_unless($bidan->bidan?->wilayah_kerja_id === $request->user()->loadMissing('admin')->managedWilayahId(), 403);

        $data = $request->validated();

        DB::transaction(function () use ($data, $bidan) {
            $bidan->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            if (! empty($data['password'])) {
                $bidan->password = $data['password'];
            }

            $bidan->save();
            $bidan->assignWilayah((int) $data['wilayah_kerja_id']);
        });

        return redirect()
            ->route('admin.bidan.index')
            ->with('success', 'Data bidan berhasil diperbarui.');
    }

    public function destroy(User $bidan): RedirectResponse
    {
        abort_unless($bidan->hasRole(RoleEnum::Bidan->value), 404);
        abort_unless($bidan->bidan?->wilayah_kerja_id === auth()->user()->loadMissing('admin')->managedWilayahId(), 403);

        if ($bidan->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $bidan->tokens()->delete();
        $bidan->delete();

        return redirect()
            ->route('admin.bidan.index')
            ->with('success', 'Data bidan berhasil dihapus.');
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