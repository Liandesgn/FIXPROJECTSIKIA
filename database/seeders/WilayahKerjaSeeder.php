<?php

namespace Database\Seeders;

use App\Models\WilayahKerja;
use Illuminate\Database\Seeder;

class WilayahKerjaSeeder extends Seeder
{
    /**
     * 20 desa contoh yang tersebar pada 18 kecamatan di Kabupaten Gresik.
     * Beberapa kecamatan memiliki lebih dari satu desa.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    public const DESA = [
        ['Wringinanom', 'Sumberame'],
        ['Wringinanom', 'Lebanisuko'],
        ['Driyorejo', 'Karangandong'],
        ['Driyorejo', 'Bambe'],
        ['Kedamean', 'Slempit'],
        ['Menganti', 'Hendrosari'],
        ['Cerme', 'Cerme Lor'],
        ['Benjeng', 'Bulurejo'],
        ['Balongpanggang', 'Karangsemanding'],
        ['Duduksampeyan', 'Duduksampeyan'],
        ['Kebomas', 'Randuagung'],
        ['Gresik', 'Tumapel'],
        ['Manyar', 'Manyarsidomukti'],
        ['Bungah', 'Bungah'],
        ['Sidayu', 'Sidomulyo'],
        ['Dukun', 'Sembungankidul'],
        ['Panceng', 'Prupuh'],
        ['Ujungpangkah', 'Banyuurip'],
        ['Sangkapura', 'Sungaiteluk'],
        ['Tambak', 'Tambak'],
    ];

    public function run(): void
    {
        WilayahKerja::query()->delete();

        foreach (self::DESA as [$kecamatan, $desa]) {
            WilayahKerja::create([
                'kabupaten' => 'Gresik',
                'kecamatan' => $kecamatan,
                'desa' => $desa,
                'nama' => 'Desa '.$desa.', Kec. '.$kecamatan,
                'deskripsi' => 'Wilayah kerja Desa '.$desa.', Kecamatan '.$kecamatan.', Kabupaten Gresik.',
            ]);
        }
    }
}
