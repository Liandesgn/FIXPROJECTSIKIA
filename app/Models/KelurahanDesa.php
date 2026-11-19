<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KelurahanDesa extends Model
{
    protected $table = 'kelurahan_desa';

    protected $fillable = [
        'puskesmas_id',
        'kecamatan_id',
        'nama_kelurahan_desa',
        'jenis',
    ];

    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function rw(): HasMany
    {
        return $this->hasMany(Rw::class, 'kelurahan_desa_id');
    }

    public function wilayahKerja(): HasOne
    {
        return $this->hasOne(WilayahKerja::class, 'kelurahan_desa_id');
    }
}
