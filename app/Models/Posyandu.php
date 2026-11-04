<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'wilayah_kerja_id',
    'nama_posyandu',
    'alamat',
])]
class Posyandu extends Model
{
    protected $table = 'posyandu';

    public function wilayahKerja(): BelongsTo
    {
        return $this->belongsTo(WilayahKerja::class);
    }

    /**
     * Kader Posyandu = kader yang bertugas di wilayah Posyandu ini.
     */
    public function kaders(): HasMany
    {
        return $this->hasMany(Kader::class, 'wilayah_kerja_id', 'wilayah_kerja_id');
    }

    public function kegiatan(): HasMany
    {
        return $this->hasMany(KegiatanPosyandu::class);
    }
}