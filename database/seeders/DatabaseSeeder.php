<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Seeder hak akses
            RolePermissionSeeder::class,

            // Seeder master wilayah (referensi dropdown)
            KecamatanSeeder::class,
            PuskesmasSeeder::class,
            KelurahanDesaSeeder::class,
            RwSeeder::class,
            RtSeeder::class,

            // Seeder pengguna
            AdminUserSeeder::class,

            // Seeder Modul 3
            KegiatanPosyanduSeeder::class,
        ]);
    }
}