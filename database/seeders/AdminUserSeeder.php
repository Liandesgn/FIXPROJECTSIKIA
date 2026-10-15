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
        $admin = User::firstOrCreate(
            ['email' => 'admin@sikia.test'],
            [
                'name' => 'Administrator',
                'password' => 'password',
                'phone' => '081000000000',
                'status' => UserStatusEnum::Active->value,
            ],
        );

        if (! $admin->hasRole(RoleEnum::Admin->value)) {
            $admin->assignRole(RoleEnum::Admin->value);
        }
    }
}
