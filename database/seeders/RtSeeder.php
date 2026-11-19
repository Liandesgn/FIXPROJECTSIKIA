<?php

namespace Database\Seeders;

use App\Models\Rw;
use App\Models\Rt;
use Illuminate\Database\Seeder;

class RtSeeder extends Seeder
{
    public function run(): void
    {
        $dataRw = Rw::all();

        foreach ($dataRw as $rw) {
            foreach (range(1, 5) as $nomor) {
                Rt::firstOrCreate([
                    'rw_id' => $rw->id,
                    'nomor_rt' => str_pad(
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
