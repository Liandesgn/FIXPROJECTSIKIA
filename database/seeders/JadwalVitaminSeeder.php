<?php

namespace Database\Seeders;

use App\Models\JadwalVitamin;
use Illuminate\Database\Seeder;

class JadwalVitaminSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'jenis_vitamin' => 'Vitamin A Biru',
                'dosis' => 'Kapsul biru, 100.000 IU',
                'frekuensi' => '1 kali setahun, Februari atau Agustus',
                'usia_min_bulan' => 6,
                'usia_maks_bulan' => 11,
            ],
            [
                'jenis_vitamin' => 'Vitamin A Merah',
                'dosis' => 'Kapsul merah, 200.000 IU',
                'frekuensi' => '2 kali setahun, Februari dan Agustus',
                'usia_min_bulan' => 12,
                'usia_maks_bulan' => 59,
            ],
        ];

        foreach ($data as $item) {
            JadwalVitamin::updateOrCreate(
                [
                    'jenis_vitamin' => $item['jenis_vitamin'],
                    'usia_min_bulan' => $item['usia_min_bulan'],
                ],
                [
                    'dosis' => $item['dosis'],
                    'frekuensi' => $item['frekuensi'],
                    'usia_maks_bulan' => $item['usia_maks_bulan'],
                ]
            );
        }
    }
}
