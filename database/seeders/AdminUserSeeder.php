<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@sikia'],
            [
                'name' => 'Super Administrator',
                'password' => 'password',
                'phone' => '081000000000',
                'status' => UserStatusEnum::Active->value,
            ],
        );

        if (! $superAdmin->hasRole(RoleEnum::SuperAdmin->value)) {
            $superAdmin->syncRoles([RoleEnum::SuperAdmin->value]);
        }
    }
}
