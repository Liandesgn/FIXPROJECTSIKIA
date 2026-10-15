<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        foreach (PermissionEnum::values() as $permission) {
            Permission::findOrCreate($permission, $guard);
        }

        $admin = Role::findOrCreate(RoleEnum::Admin->value, $guard);
        $admin->syncPermissions(PermissionEnum::values());

        foreach ([RoleEnum::OrangTua, RoleEnum::Bidan, RoleEnum::Kader] as $role) {
            Role::findOrCreate($role->value, $guard);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
