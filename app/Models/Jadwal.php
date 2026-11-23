<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jadwal extends Model
{
    protected $table = 'jadwal';

    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'jenis_kegiatan',
        'tanggal',
        'waktu',
        'lokasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function jadwalPosyandu(): HasOne
    {
        return $this->hasOne(
            JadwalPosyandu::class,
            'id_jadwal',
            'id_jadwal'
        );
    }

    public function jadwalPemeriksaan(): HasOne
    {
        return $this->hasOne(
            JadwalPemeriksaan::class,
            'id_jadwal',
            'id_jadwal'
        );
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(
            Notifikasi::class,
            'id_jadwal',
            'id_jadwal'
        );
    }
}
