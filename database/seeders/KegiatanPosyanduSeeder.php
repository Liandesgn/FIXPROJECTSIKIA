<?php

namespace Database\Seeders;

use App\Models\KegiatanPosyandu;
use App\Models\WilayahKerja;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class KegiatanPosyanduSeeder extends Seeder
{
    public function run(): void
    {
        $wilayahs = WilayahKerja::with([
            'kelurahanDesa',
            'puskesmas',
        ])
            ->whereHas('kelurahanDesa', function ($query) {
                $query->whereIn('nama_kelurahan_desa', [
                    'Kroman',
                    'Prambangan',
                ]);
            })
            ->whereHas('puskesmas', function ($query) {
                $query->whereIn('nama_puskesmas', [
                    'Puskesmas Alun-Alun',
                    'Puskesmas Gending',
                ]);
            })
            ->get();

        foreach ($wilayahs as $wilayah) {
            $namaWilayah = $wilayah->kelurahanDesa->nama_kelurahan_desa;

            foreach ([7, 14] as $hari) {
                $tanggal = Carbon::today()->addDays($hari);

                KegiatanPosyandu::updateOrCreate(
                    [
                        'id_wilayah' => $wilayah->id,
                        'tanggal' => $tanggal->toDateString(),
                        'lokasi' => 'Posyandu ' . $namaWilayah,
                    ],
                    [
                        'jenis_kegiatan' => 'Pemeriksaan Ibu dan Anak',
                        'waktu' => '08:00:00',
                    ]
                );
            }
        }
    }
}
