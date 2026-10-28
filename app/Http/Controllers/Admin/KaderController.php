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

class KaderController extends Controller
{
    public function index(Request $request): View
    {
        $kaders = User::query()
            ->role(RoleEnum::Kader->value)
            ->with('kader.wilayahKerja')
            ->when($request->filled('search'), fn ($query) => $query
                ->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('wilayah_kerja_id'), fn ($query) => $query
                ->whereHas('kader', fn ($k) => $k
                    ->where('wilayah_kerja_id', $request->integer('wilayah_kerja_id'))))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.kader.index', [
            'kaders' => $kaders,
            'wilayahs' => $this->wilayahs(),
        ]);
    }

    public function create(): View
    {
        return view('admin.kader.create', [
            'wilayahs' => $this->wilayahs(),
        ]);
    }

    public function store(StorePetugasRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $kader = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'status' => UserStatusEnum::Active->value,
            ]);

            $kader->assignRole(RoleEnum::Kader->value);
            $kader->assignWilayah((int) $data['wilayah_kerja_id']);
        });

        return redirect()
            ->route('admin.kader.index')
            ->with('success', 'Data kader berhasil ditambahkan.');
    }

    public function edit(User $kader): View
    {
        abort_unless($kader->hasRole(RoleEnum::Kader->value), 404);

        return view('admin.kader.edit', [
            'kader' => $kader->load('kader.wilayahKerja'),
            'wilayahs' => $this->wilayahs(),
        ]);
    }

    public function update(UpdatePetugasRequest $request, User $kader): RedirectResponse
    {
        abort_unless($kader->hasRole(RoleEnum::Kader->value), 404);

        $data = $request->validated();

        DB::transaction(function () use ($data, $kader) {
            $kader->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            if (! empty($data['password'])) {
                $kader->password = $data['password'];
            }

            $kader->save();
            $kader->assignWilayah((int) $data['wilayah_kerja_id']);
        });

        return redirect()
            ->route('admin.kader.index')
            ->with('success', 'Data kader berhasil diperbarui.');
    }

    public function destroy(User $kader): RedirectResponse
    {
        abort_unless($kader->hasRole(RoleEnum::Kader->value), 404);

        if ($kader->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $kader->tokens()->delete();
        $kader->delete();

        return redirect()
            ->route('admin.kader.index')
            ->with('success', 'Data kader berhasil dihapus.');
    }

    private function wilayahs()
    {
        return WilayahKerja::query()
            ->orderBy('kecamatan')
            ->orderBy('desa')
            ->get();
    }
}