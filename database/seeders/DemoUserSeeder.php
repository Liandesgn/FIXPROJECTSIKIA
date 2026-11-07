<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $wilayahs = WilayahKerja::query()
            ->whereIn('nama_wilayah', ['Wilayah A', 'Wilayah B'])
            ->orderBy('nama_wilayah')
            ->get();

        foreach ($wilayahs as $wilayah) {
            $kode = Str::lower(Str::afterLast($wilayah->nama_wilayah, ' '));

            $bidan = $this->createUser(
                name: 'Bidan '.$wilayah->nama_wilayah,
                email: 'bidan.'.$kode.'@sikia',
                role: RoleEnum::Bidan,
            );
            $bidan->assignWilayah($wilayah->id);

            for ($i = 1; $i <= 3; $i++) {
                $kader = $this->createUser(
                    name: 'Kader '.$i.' '.$wilayah->nama_wilayah,
                    email: 'kader.'.$kode.'.'.$i.'@sikia',
                    role: RoleEnum::Kader,
                );
                $kader->assignWilayah($wilayah->id);
            }
        }
    }

    private function createUser(string $name, string $email, RoleEnum $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'phone' => '081000000000',
                'status' => UserStatusEnum::Active->value,
            ],
        );

        if (! $user->hasRole($role->value)) {
            $user->syncRoles([$role->value]);
        }

        return $user;
    }
}
