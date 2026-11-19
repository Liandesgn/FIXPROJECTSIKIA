<?php

namespace Database\Seeders;

use App\Models\KelurahanDesa;
use App\Models\Rw;
use Illuminate\Database\Seeder;

class RwSeeder extends Seeder
{
    public function run(): void
    {
        $kelurahanDesa = KelurahanDesa::all();

        foreach ($kelurahanDesa as $wilayah) {
            foreach (range(1, 5) as $nomor) {
                Rw::firstOrCreate([
                    'kelurahan_desa_id' => $wilayah->id,
                    'nomor_rw' => str_pad(
                        (string) $nomor,
                        3,
                        '0',
                        STR_PAD_LEFT
                    ),
                ]);
            }
        }
    }
}
