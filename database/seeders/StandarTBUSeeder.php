<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
/**
 * Seeder standar Tinggi/Panjang Badan menurut Umur (TB/U - PB/U) WHO 2006.
 *
 * Sumber : WHO Child Growth Standards - Length/height-for-age (z-scores)
 *          lhfa_boys / lhfa_girls, 0-2 tahun dan 2-5 tahun.
 * Cakupan: umur 0-60 bulan, laki-laki (L) dan perempuan (P) = 122 baris.
 * Nilai  : Median (SD0), -1 SD (SD1neg), +1 SD (SD1) dalam cm, sesuai tabel WHO.
 *
 * Aturan umur 24 bulan: umur 0-23 bulan memakai panjang badan (berbaring),
 * umur 24-60 bulan memakai tinggi badan (berdiri), sesuai konvensi WHO.
 *
 * Aman dijalankan berulang: memakai updateOrInsert per (jenis_kelamin, umur_bulan),
 * TIDAK memakai truncate/delete, sehingga tabel lain dan data lain tidak tersentuh.
 */

class StandarTBUSeeder extends Seeder
{
    /** Nama tabel. */
    private const TABLE = 'standar_tbu';

    /**
     * Pemetaan kolom. Ubah nilai di sebelah kanan jika nama kolom
     * di tabel standar_tbu Anda berbeda.
     */
    private const COL = [
        'jenis_kelamin' => 'jenis_kelamin',
        'umur'          => 'umur_bulan',
        'median'        => 'median',
        'min_1sd'       => 'sd_minus_1',
        'plus_1sd'      => 'sd_plus_1',
    ];

    /** Kode jenis kelamin yang disimpan di database. */
    private const GENDER = ['laki' => 'L', 'perempuan' => 'P'];
    
    public function run(): void
    {
        $now = now();
        $records = [];

        foreach ($this->data() as $gender => $rows) {
            foreach ($rows as [$umur, $median, $min1, $plus1]) {
                $records[] = [
                    'jenis_kelamin' => $gender,
                    'umur_bulan' => $umur,
                    'median' => $median,
                    'sd_minus_1' => $min1,
                    'sd_plus_1' => $plus1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(function () use ($records) {
            DB::table(self::TABLE)->upsert(
                $records,
                ['jenis_kelamin', 'umur_bulan'],
                ['median', 'sd_minus_1', 'sd_plus_1', 'updated_at']
            );
        });

        $this->command?->info(
            count($records) . ' baris standar TB/U berhasil diproses.'
        );
    }

    /**
     * Format baris: [umur_bulan, median, -1 SD, +1 SD]
     *
     * @return array<string, array<int, array{0:int,1:float,2:float,3:float}>>
     */
    private function data(): array
    {
        return [
            self::GENDER['laki'] => [
                [ 0,  49.9,  48.0,  51.8],
                [ 1,  54.7,  52.8,  56.7],
                [ 2,  58.4,  56.4,  60.4],
                [ 3,  61.4,  59.4,  63.5],
                [ 4,  63.9,  61.8,  66.0],
                [ 5,  65.9,  63.8,  68.0],
                [ 6,  67.6,  65.5,  69.8],
                [ 7,  69.2,  67.0,  71.3],
                [ 8,  70.6,  68.4,  72.8],
                [ 9,  72.0,  69.7,  74.2],
                [10,  73.3,  71.0,  75.6],
                [11,  74.5,  72.2,  76.9],
                [12,  75.7,  73.4,  78.1],
                [13,  76.9,  74.5,  79.3],
                [14,  78.0,  75.6,  80.5],
                [15,  79.1,  76.6,  81.7],
                [16,  80.2,  77.6,  82.8],
                [17,  81.2,  78.6,  83.9],
                [18,  82.3,  79.6,  85.0],
                [19,  83.2,  80.5,  86.0],
                [20,  84.2,  81.4,  87.0],
                [21,  85.1,  82.3,  88.0],
                [22,  86.0,  83.1,  89.0],
                [23,  86.9,  83.9,  89.9],
                [24,  87.1,  84.1,  90.2],
                [25,  88.0,  84.9,  91.1],
                [26,  88.8,  85.6,  92.0],
                [27,  89.6,  86.4,  92.9],
                [28,  90.4,  87.1,  93.7],
                [29,  91.2,  87.8,  94.5],
                [30,  91.9,  88.5,  95.3],
                [31,  92.7,  89.2,  96.1],
                [32,  93.4,  89.9,  96.9],
                [33,  94.1,  90.5,  97.6],
                [34,  94.8,  91.1,  98.4],
                [35,  95.4,  91.8,  99.1],
                [36,  96.1,  92.4,  99.8],
                [37,  96.7,  93.0, 100.5],
                [38,  97.4,  93.6, 101.2],
                [39,  98.0,  94.2, 101.8],
                [40,  98.6,  94.7, 102.5],
                [41,  99.2,  95.3, 103.2],
                [42,  99.9,  95.9, 103.8],
                [43, 100.4,  96.4, 104.5],
                [44, 101.0,  97.0, 105.1],
                [45, 101.6,  97.5, 105.7],
                [46, 102.2,  98.1, 106.3],
                [47, 102.8,  98.6, 106.9],
                [48, 103.3,  99.1, 107.5],
                [49, 103.9,  99.7, 108.1],
                [50, 104.4, 100.2, 108.7],
                [51, 105.0, 100.7, 109.3],
                [52, 105.6, 101.2, 109.9],
                [53, 106.1, 101.7, 110.5],
                [54, 106.7, 102.3, 111.1],
                [55, 107.2, 102.8, 111.7],
                [56, 107.8, 103.3, 112.3],
                [57, 108.3, 103.8, 112.8],
                [58, 108.9, 104.3, 113.4],
                [59, 109.4, 104.8, 114.0],
                [60, 110.0, 105.3, 114.6],
            ],
            self::GENDER['perempuan'] => [
                [ 0,  49.1,  47.3,  51.0],
                [ 1,  53.7,  51.7,  55.6],
                [ 2,  57.1,  55.0,  59.1],
                [ 3,  59.8,  57.7,  61.9],
                [ 4,  62.1,  59.9,  64.3],
                [ 5,  64.0,  61.8,  66.2],
                [ 6,  65.7,  63.5,  68.0],
                [ 7,  67.3,  65.0,  69.6],
                [ 8,  68.7,  66.4,  71.1],
                [ 9,  70.1,  67.7,  72.6],
                [10,  71.5,  69.0,  73.9],
                [11,  72.8,  70.3,  75.3],
                [12,  74.0,  71.4,  76.6],
                [13,  75.2,  72.6,  77.8],
                [14,  76.4,  73.7,  79.1],
                [15,  77.5,  74.8,  80.2],
                [16,  78.6,  75.8,  81.4],
                [17,  79.7,  76.8,  82.5],
                [18,  80.7,  77.8,  83.6],
                [19,  81.7,  78.8,  84.7],
                [20,  82.7,  79.7,  85.7],
                [21,  83.7,  80.6,  86.7],
                [22,  84.6,  81.5,  87.7],
                [23,  85.5,  82.3,  88.7],
                [24,  85.7,  82.5,  88.9],
                [25,  86.6,  83.3,  89.9],
                [26,  87.4,  84.1,  90.8],
                [27,  88.3,  84.9,  91.7],
                [28,  89.1,  85.7,  92.5],
                [29,  89.9,  86.4,  93.4],
                [30,  90.7,  87.1,  94.2],
                [31,  91.4,  87.9,  95.0],
                [32,  92.2,  88.6,  95.8],
                [33,  92.9,  89.3,  96.6],
                [34,  93.6,  89.9,  97.4],
                [35,  94.4,  90.6,  98.1],
                [36,  95.1,  91.2,  98.9],
                [37,  95.7,  91.9,  99.6],
                [38,  96.4,  92.5, 100.3],
                [39,  97.1,  93.1, 101.0],
                [40,  97.7,  93.8, 101.7],
                [41,  98.4,  94.4, 102.4],
                [42,  99.0,  95.0, 103.1],
                [43,  99.7,  95.6, 103.8],
                [44, 100.3,  96.2, 104.5],
                [45, 100.9,  96.7, 105.1],
                [46, 101.5,  97.3, 105.8],
                [47, 102.1,  97.9, 106.4],
                [48, 102.7,  98.4, 107.0],
                [49, 103.3,  99.0, 107.7],
                [50, 103.9,  99.5, 108.3],
                [51, 104.5, 100.1, 108.9],
                [52, 105.0, 100.6, 109.5],
                [53, 105.6, 101.1, 110.1],
                [54, 106.2, 101.6, 110.7],
                [55, 106.7, 102.2, 111.3],
                [56, 107.3, 102.7, 111.9],
                [57, 107.8, 103.2, 112.5],
                [58, 108.4, 103.7, 113.0],
                [59, 108.9, 104.2, 113.6],
                [60, 109.4, 104.7, 114.2],
            ],
        ];
    }
}
