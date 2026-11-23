<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends DatabaseNotification
{
    protected $table = 'notifikasi';

    protected $fillable = [
        'id',
        'notifiable_type',
        'notifiable_id',
        'type',
        'data',
        'id_jadwal',
        'jenis',
        'judul',
        'isi',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(
            Jadwal::class,
            'id_jadwal',
            'id_jadwal'
        );
    }
}
