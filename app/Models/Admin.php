<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'wilayah_kerja_id',
    'puskesmas_id',
])]
class Admin extends Model
{
    protected $table = 'admin';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id');
    }

    // Sementara dipertahankan untuk kompatibilitas kode lama.
    public function wilayahKerja(): BelongsTo
    {
        return $this->belongsTo(WilayahKerja::class, 'wilayah_kerja_id');
    }
 }

