<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'wilayah_kerja_id',
    'kader_id',
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

    public function kader(): BelongsTo
    {
        return $this->belongsTo(Kader::class);
    }

    public function kegiatan(): HasMany
    {
        return $this->hasMany(KegiatanPosyandu::class);
    }
}