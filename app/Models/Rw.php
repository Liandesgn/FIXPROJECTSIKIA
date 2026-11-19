<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rw extends Model
{
    protected $table = 'rw';

    protected $fillable = [
        'kelurahan_desa_id',
        'nomor_rw',
    ];

    public function kelurahanDesa(): BelongsTo
    {
        return $this->belongsTo(KelurahanDesa::class, 'kelurahan_desa_id');
    }

    public function rt(): HasMany
    {
        return $this->hasMany(Rt::class, 'rw_id');
    }
}
