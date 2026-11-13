<?php

namespace App\Http\Controllers\SuperAdmin;

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
    // Menampilkan daftar role
    public function index(): View
    {
        $roles = Role::with('permissions')
            ->orderBy('name')
            ->get();

        return view('superadmin.roles.index', compact('roles'));
    }

    // Form tambah role (Super Admin)
    public function create(): View
    {
        return view('superadmin.roles.create', [
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

   
    // Menyimpan role baru (Super Admin)
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(
            [
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
            ],
            [
                'name.required' => 'Nama role wajib diisi.',
                'name.string' => 'Nama role harus berupa teks.',
                'name.max' => 'Nama role maksimal 100 karakter.',
                'name.alpha_dash' => 'Nama role hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
                'name.unique' => 'Nama role sudah digunakan.',
                'permissions.array' => 'Format hak akses tidak valid.',
                'permissions.*.exists' => 'Hak akses yang dipilih tidak ditemukan.',
            ]
        );

        DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => trim($data['name']),
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
        return view('superadmin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }




    // Memperbarui permission role
public function update(Request $request, Role $role): RedirectResponse
{
    $data = $request->validate([
        'name' => [
            'required',
            'string',
            'max:100',
            'alpha_dash',
            Rule::unique('roles', 'name')
                ->where('guard_name', 'web')
                ->ignore($role->id),
        ],
        'permissions' => ['nullable', 'array'],
        'permissions.*' => [
            'string',
            Rule::exists('permissions', 'name'),
        ],
    ], [
        'name.required' => 'Nama role wajib diisi.',
        'name.alpha_dash' => 'Nama role hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
        'name.unique' => 'Nama role sudah digunakan.',
    ]);

    $systemRoles = [
        'super_admin',
        'admin',
        'bidan',
        'kader',
        'ibu',
    ];

    $newName = trim($data['name']);

    if (in_array($role->name, $systemRoles, true) && $newName !== $role->name) {
        return back()
            ->withInput()
            ->with('error', 'Nama role bawaan sistem tidak boleh diubah.');
    }

    $names = collect($data['permissions'] ?? []);

    if ($role->name === 'super_admin') {
        $names = collect(PermissionEnum::values());
    }

    if ($role->name === 'admin') {
        $names = $names->merge(PermissionEnum::adminValues());
    }

    DB::transaction(function () use ($role, $newName, $names) {
        $role->name = $newName;
        $role->save();

        $role->syncPermissions(
            $names->unique()->values()->all()
        );
    });

    app(\Spatie\Permission\PermissionRegistrar::class)
        ->forgetCachedPermissions();

    return redirect()
        ->route('superadmin.roles.index')
        ->with('success', 'Nama role dan hak akses berhasil diperbarui.');
}





    // Menghapus role (Super Admin)
    public function destroy(Role $role): RedirectResponse
    {
        // Super Admin tidak boleh dihapus.
        if ($role->name === 'super_admin') {
            return redirect()
                ->route('superadmin.roles.index')
                ->with('error', 'Role Super Admin tidak boleh dihapus.');
        }

        // Jangan hapus role yang masih digunakan user.
        if ($role->users()->exists()) {
            return redirect()
                ->route('superadmin.roles.index')
                ->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh user.');
        }

        $role->delete();

        return redirect()
            ->route('superadmin.roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }
}