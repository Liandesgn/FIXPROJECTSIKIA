<?php

namespace Database\Seeders;

use App\Models\KelurahanDesa;
use App\Models\WilayahKerja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WilayahKerjaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $dataWilayah = KelurahanDesa::with([
                'kecamatan',
                'puskesmas',
            ])->get();

            foreach ($dataWilayah as $wilayah) {
                if (!$wilayah->kecamatan || !$wilayah->puskesmas) {
                    throw new RuntimeException(
                        "Data Kecamatan atau Puskesmas tidak ditemukan untuk "
                        . $wilayah->nama_kelurahan_desa
                    );
                }

                WilayahKerja::updateOrCreate(
                    [
                        'kelurahan_desa_id' => $wilayah->id,
                    ],
                    [
                        'kecamatan_id' => $wilayah->kecamatan_id,
                        'puskesmas_id' => $wilayah->puskesmas_id,

                        // Kolom lama dipertahankan sementara
                        'nama_wilayah' => $wilayah->nama_kelurahan_desa,
                        'kabupaten' => 'Gresik',
                        'kecamatan' => $wilayah->kecamatan->nama_kecamatan,
                        'kelurahan' => $wilayah->nama_kelurahan_desa,
                        'deskripsi' => 'Wilayah kerja '
                            . $wilayah->nama_kelurahan_desa
                            . ' - '
                            . $wilayah->puskesmas->nama_puskesmas,
                    ]
                );
            }
        });
    }
}