<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $wilayahs = WilayahKerja::with([
            'kelurahanDesa',
            'puskesmas',
        ])
            ->whereHas('puskesmas', function ($query) {
                $query->whereIn('nama_puskesmas', [
                    'Puskesmas Alun-Alun',
                    'Puskesmas Gending',
                ]);
            })
            ->whereHas('kelurahanDesa', function ($query) {
                $query->whereIn('nama_kelurahan_desa', [
                    'Kroman',
                    'Prambangan',
                ]);
            })
            ->get();

        foreach ($wilayahs as $wilayah) {
            $namaWilayah = $wilayah->kelurahanDesa->nama_kelurahan_desa;

            $kode = \Illuminate\Support\Str::slug(
            $wilayah->puskesmas->nama_puskesmas . '-' . $namaWilayah
        );

            // 1 Bidan untuk setiap desa
            $bidan = $this->createUser(
                name: 'Bidan ' . $namaWilayah,
                email: 'bidan.' . $namaWilayah . '@sikia',
                role: RoleEnum::Bidan,
            );

            $bidan->assignWilayah($wilayah->id);

            // 5 Kader untuk setiap desa
            for ($i = 1; $i <= 5; $i++) {
                $kader = $this->createUser(
                    name: 'Kader ' . $i . ' ' . $namaWilayah,
                    email: 'kader.' . $namaWilayah . '@sikia',
                    role: RoleEnum::Kader,
                );

                $kader->assignWilayah($wilayah->id);
            }
        }
    }

    private function createUser(
        string $name,
        string $email,
        RoleEnum $role
    ): User {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => '12345678',
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
