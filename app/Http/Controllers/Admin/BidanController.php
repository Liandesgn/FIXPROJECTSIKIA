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
use Illuminate\View\View;

class BidanController extends Controller
{
    public function index(Request $request): View
    {
        $bidans = User::query()
            ->role(RoleEnum::Bidan->value)
            ->with('wilayahKerja')
            ->when($request->filled('search'), fn ($query) => $query
                ->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('wilayah_kerja_id'), fn ($query) => $query
                ->where('wilayah_kerja_id', $request->integer('wilayah_kerja_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.bidan.index', [
            'bidans' => $bidans,
            'wilayahs' => $this->wilayahs(),
        ]);
    }

    public function create(): View
    {
        return view('admin.bidan.create', [
            'wilayahs' => $this->wilayahs(),
        ]);
    }

    public function store(StorePetugasRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $bidan = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'wilayah_kerja_id' => $data['wilayah_kerja_id'],
            'status' => UserStatusEnum::Active->value,
        ]);

        $bidan->assignRole(RoleEnum::Bidan->value);

        return redirect()
            ->route('admin.bidan.index')
            ->with('success', 'Data bidan berhasil ditambahkan.');
    }

    public function edit(User $bidan): View
    {
        abort_unless($bidan->hasRole(RoleEnum::Bidan->value), 404);

        return view('admin.bidan.edit', [
            'bidan' => $bidan->load('wilayahKerja'),
            'wilayahs' => $this->wilayahs(),
        ]);
    }

    public function update(UpdatePetugasRequest $request, User $bidan): RedirectResponse
    {
        abort_unless($bidan->hasRole(RoleEnum::Bidan->value), 404);

        $data = $request->validated();

        $bidan->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'wilayah_kerja_id' => $data['wilayah_kerja_id'],
        ]);

        if (! empty($data['password'])) {
            $bidan->password = $data['password'];
        }

        $bidan->save();

        return redirect()
            ->route('admin.bidan.index')
            ->with('success', 'Data bidan berhasil diperbarui.');
    }

    public function destroy(User $bidan): RedirectResponse
    {
        abort_unless($bidan->hasRole(RoleEnum::Bidan->value), 404);

        if ($bidan->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $bidan->tokens()->delete();
        $bidan->delete();

        return redirect()
            ->route('admin.bidan.index')
            ->with('success', 'Data bidan berhasil dihapus.');
    }

    private function wilayahs()
    {
        return WilayahKerja::query()
            ->orderBy('kecamatan')
            ->orderBy('desa')
            ->get();
    }
}
