<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_pemeriksaan',
    'id_jadwal_imunisasi',
])]
class ImunisasiAnak extends Model
{
    protected $table = 'imunisasi_anak';

    protected $primaryKey = 'id_imunisasi';

    public function pemeriksaan(): BelongsTo
    {
        return $this->belongsTo(
            PemeriksaanPosyandu::class,
            'id_pemeriksaan',
            'id'
        );
    }

    public function jadwalImunisasi(): BelongsTo
    {
        return $this->belongsTo(
            JadwalImunisasi::class,
            'id_jadwal_imunisasi',
            'id_jadwal_imunisasi'
        );
    }
}
