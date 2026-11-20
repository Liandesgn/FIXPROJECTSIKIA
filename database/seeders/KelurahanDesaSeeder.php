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
            // =====================================
            // KECAMATAN GRESIK - DATA LAMA
            // =====================================
            [
                'kecamatan' => 'Kecamatan Gresik',
                'puskesmas' => 'Puskesmas Alun-Alun',
                'wilayah' => [
                    ['nama' => 'Kelurahan Kroman', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Kemuteran', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Kebungson', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Pekelingan', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Bedilan', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Pekauman', 'jenis' => 'kelurahan'],
                    ['nama' => 'Desa Tlogobendung', 'jenis' => 'desa'],
                    ['nama' => 'Desa Gapurosukolilo', 'jenis' => 'desa'],
                    ['nama' => 'Desa Pulopancikan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Sidorukun', 'jenis' => 'desa'],
                    ['nama' => 'Desa Kramatinggil', 'jenis' => 'desa'],
                ],
            ],
            [
                'kecamatan' => 'Kecamatan Gresik',
                'puskesmas' => 'Puskesmas Nelayan',
                'wilayah' => [
                    ['nama' => 'Kelurahan Tlogopojok', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Sukodono', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Lumpur', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Karangturi', 'jenis' => 'kelurahan'],
                ],
            ],
            [
                'kecamatan' => 'Kecamatan Gresik',
                'puskesmas' => 'Puskesmas Industri',
                'wilayah' => [
                    ['nama' => 'Kelurahan Sidokumpul', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Sukorame', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Tlogopatut', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Trate', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Ngipik', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Karangpoh', 'jenis' => 'kelurahan'],
                ],
            ],

            // =====================================
            // KECAMATAN KEBOMAS - DATA LAMA
            // =====================================
            [
                'kecamatan' => 'Kecamatan Kebomas',
                'puskesmas' => 'Puskesmas Kebomas',
                'wilayah' => [
                    ['nama' => 'Kelurahan Kebomas', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Kawisanyar', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Ngargosari', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Sidomukti', 'jenis' => 'kelurahan'],
                    ['nama' => 'Desa Giri', 'jenis' => 'desa'],
                    ['nama' => 'Desa Klangonan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Sekarkurung', 'jenis' => 'desa'],
                    ['nama' => 'Desa Kedanyang', 'jenis' => 'desa'],
                    ['nama' => 'Desa Kembangan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Randuagung', 'jenis' => 'desa'],
                    ['nama' => 'Desa Dahanrejo', 'jenis' => 'desa'],
                ],
            ],
            [
                'kecamatan' => 'Kecamatan Kebomas',
                'puskesmas' => 'Puskesmas Gending',
                'wilayah' => [
                    ['nama' => 'Kelurahan Prambangan', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Gulomantung', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Sukorejo', 'jenis' => 'kelurahan'],
                    ['nama' => 'Desa Segoromadu', 'jenis' => 'desa'],
                    ['nama' => 'Kelurahan Tenggulunan', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Karangkiring', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Indro', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Singosari', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Sidomoro', 'jenis' => 'kelurahan'],
                    ['nama' => 'Kelurahan Gending', 'jenis' => 'kelurahan'],
                ],
            ],

            // =====================================
            // KECAMATAN SIDAYU
            // =====================================
            [
                'kecamatan' => 'Kecamatan Sidayu',
                'puskesmas' => 'Puskesmas Sidayu',
                'wilayah' => [
                    ['nama' => 'Desa Asempapak', 'jenis' => 'desa'],
                    ['nama' => 'Desa Bunderan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Gedangan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Golokan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Kauman', 'jenis' => 'desa'],
                    ['nama' => 'Desa Kertosono', 'jenis' => 'desa'],
                    ['nama' => 'Desa Lasem', 'jenis' => 'desa'],
                    ['nama' => 'Desa Mriyunan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Ngawen', 'jenis' => 'desa'],
                    ['nama' => 'Desa Pengulu', 'jenis' => 'desa'],
                    ['nama' => 'Desa Purwodadi', 'jenis' => 'desa'],
                    ['nama' => 'Desa Raci Kulon', 'jenis' => 'desa'],
                    ['nama' => 'Desa Raci Tengah', 'jenis' => 'desa'],
                    ['nama' => 'Desa Randuboto', 'jenis' => 'desa'],
                    ['nama' => 'Desa Sambi Pondok', 'jenis' => 'desa'],
                    ['nama' => 'Desa Sedagaran', 'jenis' => 'desa'],
                    ['nama' => 'Desa Sidomulyo', 'jenis' => 'desa'],
                    ['nama' => 'Desa Srowo', 'jenis' => 'desa'],
                    ['nama' => 'Desa Sukorejo', 'jenis' => 'desa'],
                    ['nama' => 'Desa Wadeng', 'jenis' => 'desa'],
                    ['nama' => 'Desa Mojo Asem', 'jenis' => 'desa'],
                ],
            ],

            // =====================================
            // KECAMATAN MENGANTI
            // =====================================
            [
                'kecamatan' => 'Kecamatan Menganti',
                'puskesmas' => 'Puskesmas Menganti',
                'wilayah' => [
                    ['nama' => 'Desa Beton', 'jenis' => 'desa'],
                    ['nama' => 'Desa Boboh', 'jenis' => 'desa'],
                    ['nama' => 'Desa Boteng', 'jenis' => 'desa'],
                    ['nama' => 'Desa Bringkang', 'jenis' => 'desa'],
                    ['nama' => 'Desa Domas', 'jenis' => 'desa'],
                    ['nama' => 'Desa Drancang', 'jenis' => 'desa'],
                    ['nama' => 'Desa Gadingwatu', 'jenis' => 'desa'],
                    ['nama' => 'Desa Gempolkurung', 'jenis' => 'desa'],
                    ['nama' => 'Desa Hendrosari', 'jenis' => 'desa'],
                    ['nama' => 'Desa Hulaan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Menganti', 'jenis' => 'desa'],
                    ['nama' => 'Desa Mojotengah', 'jenis' => 'desa'],
                ],
            ],
            [
                'kecamatan' => 'Kecamatan Menganti',
                'puskesmas' => 'Puskesmas Kepatihan',
                'wilayah' => [
                    ['nama' => 'Desa Kepatihan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Laban', 'jenis' => 'desa'],
                    ['nama' => 'Desa Pelemwatu', 'jenis' => 'desa'],
                    ['nama' => 'Desa Pengalangan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Pranti', 'jenis' => 'desa'],
                    ['nama' => 'Desa Putat Lor', 'jenis' => 'desa'],
                    ['nama' => 'Desa Randupadangan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Setro', 'jenis' => 'desa'],
                    ['nama' => 'Desa Sidojangkung', 'jenis' => 'desa'],
                    ['nama' => 'Desa Sidowungu', 'jenis' => 'desa'],
                ],
            ],

            // =====================================
            // KECAMATAN CERME
            // =====================================
            [
                'kecamatan' => 'Kecamatan Cerme',
                'puskesmas' => 'Puskesmas Cerme',
                'wilayah' => [
                    ['nama' => 'Desa Banjarsari', 'jenis' => 'desa'],
                    ['nama' => 'Desa Betiting', 'jenis' => 'desa'],
                    ['nama' => 'Desa Cagakagung', 'jenis' => 'desa'],
                    ['nama' => 'Desa Cermekidul', 'jenis' => 'desa'],
                    ['nama' => 'Desa Cermelor', 'jenis' => 'desa'],
                    ['nama' => 'Desa Dadapkuning', 'jenis' => 'desa'],
                    ['nama' => 'Desa Dampaan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Dooro', 'jenis' => 'desa'],
                    ['nama' => 'Desa Dungus', 'jenis' => 'desa'],
                    ['nama' => 'Desa Gedangkulut', 'jenis' => 'desa'],
                    ['nama' => 'Desa Guranganyar', 'jenis' => 'desa'],
                    ['nama' => 'Desa Iker-ikergeger', 'jenis' => 'desa'],
                    ['nama' => 'Desa Jono', 'jenis' => 'desa'],
                ],
            ],
            [
                'kecamatan' => 'Kecamatan Cerme',
                'puskesmas' => 'Puskesmas Dadap Kuning',
                'wilayah' => [
                    ['nama' => 'Desa Kambingan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Kandangan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Lengkong', 'jenis' => 'desa'],
                    ['nama' => 'Desa Morowudi', 'jenis' => 'desa'],
                    ['nama' => 'Desa Ngabetan', 'jenis' => 'desa'],
                    ['nama' => 'Desa Ngembung', 'jenis' => 'desa'],
                    ['nama' => 'Desa Padeg', 'jenis' => 'desa'],
                    ['nama' => 'Desa Pandu', 'jenis' => 'desa'],
                    ['nama' => 'Desa Semampir', 'jenis' => 'desa'],
                    ['nama' => 'Desa Sukoanyar', 'jenis' => 'desa'],
                    ['nama' => 'Desa Tambakberas', 'jenis' => 'desa'],
                    ['nama' => 'Desa Wedani', 'jenis' => 'desa'],
                ],
            ],
        ];

        DB::transaction(function () use ($dataWilayah) {
            foreach ($dataWilayah as $data) {
                $kecamatan = Kecamatan::where(
                    'nama_kecamatan',
                    $data['kecamatan']
                )->firstOrFail();

                $puskesmas = Puskesmas::where(
                    'nama_puskesmas',
                    $data['puskesmas']
                )
                    ->where('kecamatan_id', $kecamatan->id)
                    ->first();

                if (! $puskesmas) {
                    throw new RuntimeException(
                        "Puskesmas {$data['puskesmas']} tidak ditemukan."
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