<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kecamatan;


class KecamatanSeeder extends Seeder
{
    public function run(): void
    {
        $dataKecamatan = [
            'Kecamatan Gresik',
            'Kecamatan Kebomas',
        ];

        foreach ($dataKecamatan as $nama) {
            Kecamatan::firstOrCreate([
                'nama_kecamatan' => $nama,
            ]);
        }
    }
}
