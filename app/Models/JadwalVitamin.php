<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'jenis_vitamin',
    'dosis',
    'frekuensi',
    'usia_min_bulan',
    'usia_maks_bulan',
])]
class JadwalVitamin extends Model
{
    protected $table = 'jadwal_vitamin';

    protected $primaryKey = 'id_jadwal_vitamin';

    protected function casts(): array
    {
        return [
            'usia_min_bulan' => 'integer',
            'usia_maks_bulan' => 'integer',
        ];
    }

    /**
     * Relasi ke seluruh pemeriksaan yang menggunakan vitamin ini.
     */
    public function pemeriksaanPosyandu(): HasMany
    {
        return $this->hasMany(
            PemeriksaanPosyandu::class,
            'id_jadwal_vitamin',
            'id_jadwal_vitamin'
        );
    }
}
