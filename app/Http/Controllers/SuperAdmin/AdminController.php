<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Admin as AdminProfile;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $admins = AdminProfile::query()
            ->with(['user', 'wilayahKerja'])
            ->latest()
            ->paginate(10);

        return view('superadmin.admins.index', compact('admins'));
    }

    public function create(): View
    {
        return view('superadmin.admins.create', [
            'wilayahs' => $this->wilayahs(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'regex:/^[0-9]+$/', 'min:10', 'max:13'],
            'id_wilayah' => [
                'required',
                'integer',
                Rule::exists('wilayah_kerja', 'id'),
                Rule::unique('admin', 'id_wilayah'),
            ],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'status' => UserStatusEnum::Active->value,
            ]);

            $user->assignRole(RoleEnum::Admin->value);
            $user->admin()->create(['id_wilayah' => $data['id_wilayah']]);
        });

        return redirect()
            ->route('superadmin.admins.index')
            ->with('success', 'Admin wilayah berhasil ditambahkan.');
    }

    public function edit(AdminProfile $admin): View
    {
        return view('superadmin.admins.edit', [
            'admin' => $admin->load(['user', 'wilayahKerja']),
            'wilayahs' => $this->wilayahs($admin->id_wilayah),
        ]);
    }

    public function update(Request $request, AdminProfile $admin): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($admin->user_id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'regex:/^[0-9]+$/', 'min:10', 'max:13'],
            'id_wilayah' => [
                'required',
                'integer',
                Rule::exists('wilayah_kerja', 'id'),
                Rule::unique('admin', 'id_wilayah')->ignore($admin->id),
            ],
        ]);

        DB::transaction(function () use ($admin, $data) {
            $admin->user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            if (! empty($data['password'])) {
                $admin->user->password = $data['password'];
            }

            $admin->user->save();
            $admin->update(['id_wilayah' => $data['id_wilayah']]);
        });

        return redirect()
            ->route('superadmin.admins.index')
            ->with('success', 'Admin wilayah berhasil diperbarui.');
    }

    public function destroy(AdminProfile $admin): RedirectResponse
    {
        DB::transaction(function () use ($admin) {
            $admin->user->tokens()->delete();
            $admin->user->delete();
        });

        return redirect()
            ->route('superadmin.admins.index')
            ->with('success', 'Admin wilayah berhasil dihapus.');
    }

    private function wilayahs(?int $includeId = null)
    {
        return WilayahKerja::query()
            ->where(fn ($query) => $query
                ->doesntHave('admins')
                ->when($includeId, fn ($q) => $q->orWhere('id', $includeId)))
            ->orderBy('nama_wilayah')
            ->get();
    }
}
