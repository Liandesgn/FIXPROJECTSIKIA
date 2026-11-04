<?php

namespace Database\Seeders;

use App\Models\WilayahKerja;
use Illuminate\Database\Seeder;

class WilayahKerjaSeeder extends Seeder
{
    /**
     * 2 wilayah kerja awal (A-B).
     */
    public function run(): void
    {
        foreach (range('A', 'B') as $huruf) {
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
