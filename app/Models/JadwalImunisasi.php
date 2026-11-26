<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'jenis_vaksin',
    'dosis',
    'usia_min_bulan',
    'usia_maks_bulan',
    'wajib_nasional',
    'keterangan',
])]
class JadwalImunisasi extends Model
{
    protected $table = 'jadwal_imunisasi';

    protected $primaryKey = 'id_jadwal_imunisasi';

protected function casts(): array
{
    return [
        'usia_min_bulan' => 'integer',
        'usia_maks_bulan' => 'integer',
        'wajib_nasional' => 'boolean',
    ];
}

    public function imunisasiAnak(): HasMany
    {
        return $this->hasMany(
            ImunisasiAnak::class,
            'id_jadwal_imunisasi',
            'id_jadwal_imunisasi'
        );
    }
}

