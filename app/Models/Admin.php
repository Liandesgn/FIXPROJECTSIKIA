<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'id_wilayah'])]
class Admin extends Model
{
    protected $table = 'admin';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wilayahKerja(): BelongsTo
    {
        return $this->belongsTo(WilayahKerja::class, 'id_wilayah');
    }
}