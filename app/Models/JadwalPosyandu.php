<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPosyandu extends Model
{
    protected $table = 'jadwal_posyandu';

    protected $primaryKey = 'id_jadwal_posyandu';

    protected $fillable = [
        'id_jadwal',
        'id_kegiatan',
        'id_bidan',
        'id_kader',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(
            Jadwal::class,
            'id_jadwal',
            'id_jadwal'
        );
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(
            KegiatanPosyandu::class,
            'id_kegiatan'
        );
    }

    public function bidan(): BelongsTo
    {
        return $this->belongsTo(Bidan::class, 'id_bidan');
    }

    public function kader(): BelongsTo
    {
        return $this->belongsTo(Kader::class, 'id_kader');
    }
}
