<?php

namespace Database\Seeders;

use App\Models\JadwalImunisasi;
use Illuminate\Database\Seeder;

class JadwalImunisasiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Hepatitis B-0: maksimal 24 jam setelah lahir
            [
                'jenis_vaksin' => 'Hepatitis B-0',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 0,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => 'Diberikan maksimal 24 jam setelah lahir',
            ],

            // BCG: 1 bulan
            [
                'jenis_vaksin' => 'BCG',
                'dosis' => '0,05 ml, intrakutan',
                'usia_min_bulan' => 1,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],

            // Polio tetes (bOPV): 0–1 bulan
            [
                'jenis_vaksin' => 'Polio Tetes (bOPV)',
                'dosis' => '2 tetes, oral',
                'usia_min_bulan' => 0,
                'usia_maks_bulan' => 1,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],

            // Polio tetes (bOPV): 2, 3, dan 4 bulan
            [
                'jenis_vaksin' => 'Polio Tetes (bOPV)',
                'dosis' => '2 tetes, oral',
                'usia_min_bulan' => 2,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],
            [
                'jenis_vaksin' => 'Polio Tetes (bOPV)',
                'dosis' => '2 tetes, oral',
                'usia_min_bulan' => 3,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],
            [
                'jenis_vaksin' => 'Polio Tetes (bOPV)',
                'dosis' => '2 tetes, oral',
                'usia_min_bulan' => 4,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],

            // DPT-HB-Hib: 2, 3, dan 4 bulan
            [
                'jenis_vaksin' => 'DPT-HB-Hib',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 2,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],
            [
                'jenis_vaksin' => 'DPT-HB-Hib',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 3,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],
            [
                'jenis_vaksin' => 'DPT-HB-Hib',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 4,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],

            // DPT-HB-Hib lanjutan: 18 bulan
            [
                'jenis_vaksin' => 'DPT-HB-Hib',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 18,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => 'Dosis lanjutan',
            ],

            // PCV: 2 dan 3 bulan
            [
                'jenis_vaksin' => 'PCV',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 2,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],
            [
                'jenis_vaksin' => 'PCV',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 3,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],

            // PCV lanjutan: 12 bulan
            [
                'jenis_vaksin' => 'PCV',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 12,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => 'Dosis lanjutan',
            ],

            // Rotavirus: 2, 3, dan 4 bulan
            [
                'jenis_vaksin' => 'Rotavirus (RV)',
                'dosis' => '5 tetes, oral',
                'usia_min_bulan' => 2,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],
            [
                'jenis_vaksin' => 'Rotavirus (RV)',
                'dosis' => '5 tetes, oral',
                'usia_min_bulan' => 3,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],
            [
                'jenis_vaksin' => 'Rotavirus (RV)',
                'dosis' => '5 tetes, oral',
                'usia_min_bulan' => 4,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],

            // Polio suntik (IPV): 4 dan 9 bulan
            [
                'jenis_vaksin' => 'Polio Suntik (IPV)',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 4,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],
            [
                'jenis_vaksin' => 'Polio Suntik (IPV)',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 9,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],

            // Campak Rubela (MR): 9 bulan
            [
                'jenis_vaksin' => 'Campak Rubela (MR)',
                'dosis' => '0,5 ml, subkutan',
                'usia_min_bulan' => 9,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => null,
            ],

            // JE: 10 bulan, khusus daerah tertentu
            [
                'jenis_vaksin' => 'JE',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 10,
                'usia_maks_bulan' => null,
                'wajib_nasional' => false,
                'keterangan' => 'Hanya untuk daerah tertentu',
            ],

            // PCV lanjutan sudah dicatat pada usia 12 bulan

            // Campak Rubela (MR) lanjutan: 18 bulan
            [
                'jenis_vaksin' => 'Campak Rubela (MR)',
                'dosis' => '0,5 ml, subkutan',
                'usia_min_bulan' => 18,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => 'Dosis lanjutan',
            ],

            // Imunisasi kelas 1 SD: 7 tahun = 84 bulan
            [
                'jenis_vaksin' => 'MR Kelas 1 SD',
                'dosis' => '0,5 ml, subkutan',
                'usia_min_bulan' => 84,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => 'Diberikan pada bulan Agustus',
            ],
            [
                'jenis_vaksin' => 'DT Kelas 1 SD',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 84,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => 'Diberikan pada bulan November',
            ],

            // Td kelas 2 SD: 8 tahun = 96 bulan
            [
                'jenis_vaksin' => 'Td Kelas 2 SD',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 96,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => 'Diberikan pada bulan November',
            ],

            // Td kelas 5 SD: 11 tahun = 132 bulan
            [
                'jenis_vaksin' => 'Td Kelas 5 SD',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 132,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => 'Diberikan pada bulan November',
            ],

            // HPV kelas 5 SD: 11 tahun = 132 bulan
            [
                'jenis_vaksin' => 'HPV Kelas 5 SD',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 132,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => 'Bulan Agustus, khusus anak perempuan',
            ],

            // HPV kelas 6 SD: 12 tahun = 144 bulan
            [
                'jenis_vaksin' => 'HPV Kelas 6 SD',
                'dosis' => '0,5 ml, intramuskular',
                'usia_min_bulan' => 144,
                'usia_maks_bulan' => null,
                'wajib_nasional' => true,
                'keterangan' => 'Bulan Agustus, khusus anak perempuan',
            ],
        ];

        foreach ($data as $item) {
            JadwalImunisasi::updateOrCreate(
                [
                    'jenis_vaksin' => $item['jenis_vaksin'],
                    'usia_min_bulan' => $item['usia_min_bulan'],
                ],
                [
                    'dosis' => $item['dosis'],
                    'usia_maks_bulan' => $item['usia_maks_bulan'],
                    'wajib_nasional' => $item['wajib_nasional'],
                    'keterangan' => $item['keterangan'],
                ]
            );
        }
    }
}
