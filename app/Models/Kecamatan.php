<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';

    protected $fillable = [
        'nama_kecamatan',
    ];

    public function puskesmas(): HasMany
    {
        return $this->hasMany(Puskesmas::class, 'kecamatan_id');
    }

    public function kelurahanDesa(): HasMany
    {
        return $this->hasMany(KelurahanDesa::class, 'kecamatan_id');
    }

    public function wilayahKerja(): HasMany
    {
        return $this->hasMany(WilayahKerja::class, 'kecamatan_id');
    }
}
