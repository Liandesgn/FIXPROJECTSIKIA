<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['posyandu_id', 'tanggal', 'waktu', 'lokasi'])]
class KegiatanPosyandu extends Model
{
    protected $table = 'kegiatan_posyandu';

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function posyandu(): BelongsTo
    {
        return $this->belongsTo(Posyandu::class);
    }
}