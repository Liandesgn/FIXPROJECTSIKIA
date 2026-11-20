<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class KecamatanSeeder extends Seeder
{
    public function run(): void
    {
        $dataKecamatan = [
            'Kecamatan Gresik',
            'Kecamatan Kebomas',
            'Kecamatan Sidayu',
            'Kecamatan Menganti',
            'Kecamatan Cerme',
        ];

        foreach ($dataKecamatan as $nama) {
            Kecamatan::firstOrCreate([
                'nama_kecamatan' => $nama,
            ]);
        }
    }
}