<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'kecamatan_id',
    'nama_puskesmas',
])]
class Puskesmas extends Model
{
    protected $table = 'puskesmas';

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function kelurahanDesa(): HasMany
    {
        return $this->hasMany(KelurahanDesa::class, 'puskesmas_id');
    }

    public function wilayahKerja(): HasMany
    {
        return $this->hasMany(WilayahKerja::class, 'puskesmas_id');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'puskesmas_id');
    }
}
