<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    // Dipakai middleware di routes/web.php. Nama tidak boleh diubah/dihapus,
    // dan admin tidak boleh kehilangan izin ini.
    private const PROTECTED = [
        'user.view', 'user.create', 'user.update', 'user.delete', 'user.activate',
    ];

public function index(Request $request)
{
    $urutan = ['admin', 'bidan', 'kader', 'ibu'];

    $roles = Role::with('permissions')->get()
        ->sortBy(fn ($r) => array_search($r->name, $urutan, true) === false
            ? 99
            : array_search($r->name, $urutan, true))
        ->values();

    $permissions = Permission::with('roles')
        ->when($request->filled('search'), function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->search . '%');
        })
        ->when($request->filled('role'), function ($query) use ($request) {
            if ($request->role === '-') {
                $query->doesntHave('roles');
            } else {
                $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
            }
        })
        ->orderBy('name')
        ->get();

    return view('admin.permissions.index', compact('roles', 'permissions'));
}

    public function create()
    {
        return view('admin.permissions.create', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());

        $permission = Permission::create(['name' => $data['name'], 'guard_name' => 'web']);
        $this->syncRoles($permission, $data['roles'] ?? []);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil ditambahkan.');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', [
            'permission' => $permission->load('roles'),
            'roles'      => Role::orderBy('name')->get(),
            'locked'     => in_array($permission->name, self::PROTECTED, true),
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $locked = in_array($permission->name, self::PROTECTED, true);

        $data = $request->validate($this->rules($permission->id), $this->messages());

        // Nama permission inti tidak boleh berubah
        if (! $locked) {
            $permission->update(['name' => $data['name']]);
        }

        $this->syncRoles($permission, $data['roles'] ?? []);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Hak akses berhasil diperbarui.');
    }

    public function destroy(Permission $permission)
    {
        if (in_array($permission->name, self::PROTECTED, true)) {
            return back()->with('error', 'Permission inti tidak boleh dihapus.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil dihapus.');
    }

    
        //view untuk mengedit permission role
        public function editRole(Role $role)
{
    return view('admin.permissions.role-edit', [
        'role'        => $role->load('permissions'),
        'permissions' => Permission::orderBy('name')->get(),
    ]);
}

public function updateRole(Request $request, Role $role)
{
    $data = $request->validate([
        'permissions'   => ['array'],
        'permissions.*' => ['exists:permissions,name'],
    ]);

    $names = $data['permissions'] ?? [];

    // Admin tidak boleh kehilangan permission inti (supaya tidak terkunci)
    if ($role->name === 'admin') {
        $names = array_unique(array_merge($names, self::PROTECTED));
    }

    $role->syncPermissions($names);

    return redirect()->route('admin.permissions.index')
        ->with('success', 'Hak akses ' . ucfirst($role->name) . ' berhasil diperbarui.');
}




    /**
     * Beri permission ke role yang dicentang, cabut dari yang tidak.
     */
    private function syncRoles(Permission $permission, array $roleNames): void
    {
        // Admin tidak boleh kehilangan permission inti (supaya tidak terkunci)
        if (in_array($permission->name, self::PROTECTED, true) && ! in_array('admin', $roleNames, true)) {
            $roleNames[] = 'admin';
        }

        foreach (Role::all() as $role) {
            if (in_array($role->name, $roleNames, true)) {
                $role->givePermissionTo($permission);
            } else {
                $role->revokePermissionTo($permission);
            }
        }
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                'regex:/^[a-z0-9_-]+(\.[a-z0-9_-]+)*$/',
                'unique:permissions,name' . ($ignoreId ? ',' . $ignoreId : ''),
            ],
            'roles'   => ['array'],
            'roles.*' => ['exists:roles,name'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Nama permission wajib diisi.',
            'name.regex'    => 'Format: huruf kecil, dipisah titik. Contoh: ibu.view',
            'name.unique'   => 'Nama permission sudah dipakai.',
        ];
    }
}