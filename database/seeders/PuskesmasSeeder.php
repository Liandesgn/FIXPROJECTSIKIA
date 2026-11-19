<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\Puskesmas;
use Illuminate\Database\Seeder;

class PuskesmasSeeder extends Seeder
{
    public function run(): void
    {
        $dataPuskesmas = [
            // Kecamatan Gresik
            [
                'kecamatan' => 'Kecamatan Gresik',
                'nama_puskesmas' => 'Puskesmas Alun-Alun',
            ],
            [
                'kecamatan' => 'Kecamatan Gresik',
                'nama_puskesmas' => 'Puskesmas Nelayan',
            ],
            [
                'kecamatan' => 'Kecamatan Gresik',
                'nama_puskesmas' => 'Puskesmas Industri',
            ],

            // Kecamatan Kebomas
            [
                'kecamatan' => 'Kecamatan Kebomas',
                'nama_puskesmas' => 'Puskesmas Kebomas',
            ],
            [
                'kecamatan' => 'Kecamatan Kebomas',
                'nama_puskesmas' => 'Puskesmas Gending',
            ],
        ];

        foreach ($dataPuskesmas as $data) {
            $kecamatan = Kecamatan::where(
                'nama_kecamatan',
                $data['kecamatan']
            )->firstOrFail();

            Puskesmas::firstOrCreate(
                [
                    'kecamatan_id' => $kecamatan->id,
                    'nama_puskesmas' => $data['nama_puskesmas'],
                ]
            );
        }
    }
}
