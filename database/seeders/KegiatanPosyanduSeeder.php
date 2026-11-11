<?php

namespace Database\Seeders;

use App\Models\KegiatanPosyandu;
use App\Models\WilayahKerja;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class KegiatanPosyanduSeeder extends Seeder
{
    public function run(): void
    {
        $wilayahs = WilayahKerja::whereIn(
            'nama_wilayah',
            [
                'Wilayah A',
                'Wilayah B',
                'Wilayah C',
                'Wilayah D',
                'Wilayah E',
            ]
        )->get();

        foreach ($wilayahs as $wilayah) {
            foreach ([7, 14] as $hari) {
                $tanggal = Carbon::today()->addDays($hari);

                KegiatanPosyandu::updateOrCreate(
                    [
                        'id_wilayah' => $wilayah->id,
                        'tanggal' => $tanggal->toDateString(),
                        'lokasi' => 'Posyandu '.$wilayah->nama_wilayah,
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