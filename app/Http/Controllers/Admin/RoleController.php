<?php

namespace App\Http\Controllers\Admin;


use App\Enums\PermissionEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private function routePrefix(): string
    {
        return request()->routeIs('admin.*') ? 'admin' : 'superadmin';
    }

    // Menampilkan daftar role
    public function index(): View
    {
        $roles = Role::with('permissions')
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    // Form tambah role (Super Admin)
    public function create(): View
    {
        return view('admin.roles.create', [
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    // Menyimpan role baru (Super Admin)
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('roles', 'name')->where('guard_name', 'web'),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name'),
            ],
        ]);

        DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => strtolower($data['name']),
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($data['permissions'] ?? []);
        });

        return redirect()
            ->route('superadmin.roles.index')
            ->with('success', 'Role berhasil ditambahkan.');
    }

    // Form edit role
    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    // Memperbarui permission role
    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name'),
            ],
        ]);

        $names = collect($data['permissions'] ?? []);

        // Super Admin selalu memiliki seluruh permission.
        if ($role->name === 'super_admin') {
            $names = collect(PermissionEnum::values());
        }

        // Permission dasar Admin tetap dipertahankan.
        if ($role->name === 'admin') {
            $names = $names->merge(PermissionEnum::adminValues());
        }

        $role->syncPermissions(
            $names->unique()->values()->all()
        );

        return redirect()
            ->route($this->routePrefix() . '.roles.index')
            ->with(
                'success',
                'Hak akses role ' . ucfirst($role->name) . ' berhasil diperbarui.'
            );
    }

    // Menghapus role (Super Admin)
    public function destroy(Role $role): RedirectResponse
{
    // Super Admin tidak boleh dihapus.
    if ($role->name === 'super_admin') {
        return redirect()
            ->route($this->routePrefix() . '.roles.index')
            ->with('error', 'Role Super Admin tidak boleh dihapus.');
    }

    // Jangan hapus role yang masih digunakan user.
    if ($role->users()->exists()) {
        return redirect()
            ->route($this->routePrefix() . '.roles.index')
            ->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh user.');
    }

    $role->delete();

    return redirect()
        ->route($this->routePrefix() . '.roles.index')
        ->with('success', 'Role berhasil dihapus.');
}
}