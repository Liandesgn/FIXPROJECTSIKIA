<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'wilayah_kerja_id'])]
class Bidan extends Model
{
    protected $table = 'bidan';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wilayahKerja(): BelongsTo
    {
        return $this->belongsTo(WilayahKerja::class);
    }
}