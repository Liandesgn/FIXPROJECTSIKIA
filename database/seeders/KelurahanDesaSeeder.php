<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\KelurahanDesa;
use App\Models\Puskesmas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class KelurahanDesaSeeder extends Seeder
{
    public function run(): void
    {
        $dataWilayah = [
            [
                'kecamatan' => 'Kecamatan Gresik',
                'puskesmas' => 'Puskesmas Alun-Alun',
                'wilayah' => [
                    ['nama' => 'Kroman', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kemuteran', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kebungson', 'jenis' => 'kelurahan'],
                    ['nama' => 'Pekelingan', 'jenis' => 'kelurahan'],
                    ['nama' => 'Bedilan', 'jenis' => 'kelurahan'],
                    ['nama' => 'Pekauman', 'jenis' => 'kelurahan'],
                    ['nama' => 'Tlogobendung', 'jenis' => 'desa'],
                    ['nama' => 'Gapurosukolilo', 'jenis' => 'desa'],
                    ['nama' => 'Pulopancikan', 'jenis' => 'desa'],
                    ['nama' => 'Sidorukun', 'jenis' => 'desa'],
                    ['nama' => 'Kramatinggil', 'jenis' => 'desa'],
                ],
            ],

            [
                'kecamatan' => 'Kecamatan Gresik',
                'puskesmas' => 'Puskesmas Nelayan',
                'wilayah' => [
                    ['nama' => 'Tlogopojok', 'jenis' => 'kelurahan'],
                    ['nama' => 'Sukodono', 'jenis' => 'kelurahan'],
                    ['nama' => 'Lumpur', 'jenis' => 'kelurahan'],
                    ['nama' => 'Karangturi', 'jenis' => 'kelurahan'],
                ],
            ],

            [
                'kecamatan' => 'Kecamatan Gresik',
                'puskesmas' => 'Puskesmas Industri',
                'wilayah' => [
                    ['nama' => 'Sidokumpul', 'jenis' => 'kelurahan'],
                    ['nama' => 'Sukorame', 'jenis' => 'kelurahan'],
                    ['nama' => 'Tlogopatut', 'jenis' => 'kelurahan'],
                    ['nama' => 'Trate', 'jenis' => 'kelurahan'],
                    ['nama' => 'Ngipik', 'jenis' => 'kelurahan'],
                    ['nama' => 'Karangpoh', 'jenis' => 'kelurahan'],
                ],
            ],

            [
                'kecamatan' => 'Kecamatan Kebomas',
                'puskesmas' => 'Puskesmas Kebomas',
                'wilayah' => [
                    ['nama' => 'Kebomas', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kawisanyar', 'jenis' => 'kelurahan'],
                    ['nama' => 'Ngargosari', 'jenis' => 'kelurahan'],
                    ['nama' => 'Sidomukti', 'jenis' => 'kelurahan'],
                    ['nama' => 'Giri', 'jenis' => 'desa'],
                    ['nama' => 'Klangonan', 'jenis' => 'desa'],
                    ['nama' => 'Sekarkurung', 'jenis' => 'desa'],
                    ['nama' => 'Kedanyang', 'jenis' => 'desa'],
                    ['nama' => 'Kembangan', 'jenis' => 'desa'],
                    ['nama' => 'Randuagung', 'jenis' => 'desa'],
                    ['nama' => 'Dahanrejo', 'jenis' => 'desa'],
                ],
            ],

            [
                'kecamatan' => 'Kecamatan Kebomas',
                'puskesmas' => 'Puskesmas Gending',
                'wilayah' => [
                    ['nama' => 'Prambangan', 'jenis' => 'desa'],
                    ['nama' => 'Gulomantung', 'jenis' => 'desa'],
                    ['nama' => 'Sukorejo', 'jenis' => 'desa'],
                    ['nama' => 'Segoromadu', 'jenis' => 'desa'],
                    ['nama' => 'Tenggulunan', 'jenis' => 'kelurahan'],
                    ['nama' => 'Karangkiring', 'jenis' => 'kelurahan'],
                    ['nama' => 'Indro', 'jenis' => 'kelurahan'],
                    ['nama' => 'Singosari', 'jenis' => 'kelurahan'],
                    ['nama' => 'Sidomoro', 'jenis' => 'kelurahan'],
                    ['nama' => 'Gending', 'jenis' => 'kelurahan'],
                ],
            ],
        ];

        DB::transaction(function () use ($dataWilayah) {
            foreach ($dataWilayah as $data) {
                $kecamatan = Kecamatan::where(
                    'nama_kecamatan',
                    $data['kecamatan']
                )->first();

                if (! $kecamatan) {
                    throw new RuntimeException(
                        "Kecamatan {$data['kecamatan']} tidak ditemukan."
                    );
                }

                $puskesmas = Puskesmas::where(
                    'nama_puskesmas',
                    $data['puskesmas']
                )
                    ->where('kecamatan_id', $kecamatan->id)
                    ->first();

                if (! $puskesmas) {
                    throw new RuntimeException(
                        "Puskesmas {$data['puskesmas']} tidak ditemukan "
                        . "atau bukan bagian dari {$data['kecamatan']}."
                    );
                }

                foreach ($data['wilayah'] as $wilayah) {
                    KelurahanDesa::updateOrCreate(
                        [
                            'kecamatan_id' => $kecamatan->id,
                            'puskesmas_id' => $puskesmas->id,
                            'nama_kelurahan_desa' => $wilayah['nama'],
                        ],
                        [
                            'jenis' => $wilayah['jenis'],
                        ]
                    );
                }
            }
        });
    }
}
