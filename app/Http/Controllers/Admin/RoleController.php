<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PermissionEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::with('permissions')->orderBy('name')->get();

        return view('admin.roles.index', ['roles' => $roles]);
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $nama = collect($data['permissions'] ?? []);

       
        if ($role->name === 'super_admin') {
            $nama = collect(PermissionEnum::values());
        }

        if ($role->name === 'admin') {
            $nama = $nama->merge(PermissionEnum::adminValues());
        }


        $role->syncPermissions($nama->unique()->values()->all());

        return redirect()
            ->route('superadmin.roles.index')
            ->with('success', 'Hak akses role ' . ucfirst($role->name) . ' berhasil diperbarui.');
    }
}