<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPemeriksaan extends Model
{
    protected $table = 'jadwal_pemeriksaan';

    protected $primaryKey = 'id_jadwal_pemeriksaan';

    protected $fillable = [
        'id_jadwal',
        'id_wilayah_kerja',
        'id_bidan',
        'catatan',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(
            Jadwal::class,
            'id_jadwal',
            'id_jadwal'
        );
    }

    public function wilayahKerja(): BelongsTo
    {
        return $this->belongsTo(
            WilayahKerja::class,
            'id_wilayah_kerja'
        );
    }

    public function bidan(): BelongsTo
    {
        return $this->belongsTo(Bidan::class, 'id_bidan');
    }
}

