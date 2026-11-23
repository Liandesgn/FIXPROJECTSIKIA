<?php

namespace App\Models;

use App\Models\Rw;
use App\Models\KegiatanPosyandu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rt extends Model
{
    protected $table = 'rt';

    protected $fillable = [
        'rw_id',
        'nomor_rt',
    ];

    public function rw(): BelongsTo
    {
        return $this->belongsTo(Rw::class, 'rw_id');
    }

    public function kegiatanPosyandu()
    {
        return $this->hasMany(KegiatanPosyandu::class, 'rt_id');
    }

    public function ibu(): HasMany
    {
        return $this->hasMany(Ibu::class, 'rt_id');
    }
}
