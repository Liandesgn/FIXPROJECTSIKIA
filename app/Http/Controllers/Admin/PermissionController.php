<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return view('admin.permissions.index', compact('roles', 'permissions'));
    }

    public function update(Request $request)
    {
        $matrix = $request->input('permissions', []);

        foreach (Role::all() as $role) {
            $rolePermissions = $matrix[$role->id] ?? [];
            $role->syncPermissions($rolePermissions);
        }

        return back()->with('success', 'Permission berhasil diperbarui');
    }
}