<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_ibu',
    'status_kehamilan',
    'tgl_status',
])]
class RiwayatStatusKehamilan extends Model
{
    protected $table = 'riwayat_status_kehamilan';

    protected $primaryKey = 'id_riwayat';

    protected function casts(): array
    {
        return [
            'tgl_status' => 'date',
        ];
    }

    public function ibu(): BelongsTo
    {
        return $this->belongsTo(
            Ibu::class,
            'id_ibu',
            'id'
        );
    }
}
