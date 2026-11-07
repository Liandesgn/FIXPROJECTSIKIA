<?php

namespace Database\Seeders;

use App\Models\WilayahKerja;
use Illuminate\Database\Seeder;

class WilayahKerjaSeeder extends Seeder
{
    /**
     * 5 wilayah kerja awal (A-E).
     */
    public function run(): void
    {
        foreach (range('A', 'E') as $huruf) {
            WilayahKerja::updateOrCreate(
                ['kelurahan' => 'Kelurahan '.$huruf, 'kecamatan' => 'Kecamatan '.$huruf],
                [
                    'kabupaten' => 'Gresik',
                    'nama_wilayah' => 'Wilayah '.$huruf,
                    'deskripsi' => 'Wilayah kerja '.$huruf.', Kabupaten Gresik.',
                ]
            );
        }
    }
}
