<?php

namespace App\Models;

use App\Models\Rt;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'id_wilayah',
    'rw_id',
    'jenis_kegiatan',
    'tanggal',
    'waktu',
    'lokasi',
    'rt_id',
])]
class KegiatanPosyandu extends Model
{
    protected $table = 'kegiatan_posyandu';

    protected function casts(): array
    {
        return [
            'id_wilayah' => 'integer',
            'rw_id' => 'integer',
            'tanggal' => 'date',
            'lokasi' => 'string',
            'jenis_kegiatan' => 'string',
        ];
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(
            WilayahKerja::class,
            'id_wilayah'
        );
    }

    public function rw(): BelongsTo
    {
        return $this->belongsTo(
            Rw::class,
            'rw_id'
        );
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function pemeriksaan(): HasMany
    {
        return $this->hasMany(
            PemeriksaanPosyandu::class,
            'kegiatan_posyandu_id'
        );
    }

    /**
     * Kegiatan hari ini atau setelahnya.
     */
    public function scopeMendatang(Builder $query): Builder
    {
        return $query->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('waktu');
    }

    /**
     * Kegiatan pada wilayah tertentu.
     */
    public function scopeDiWilayah(
        Builder $query,
        int $wilayahId
    ): Builder {
        return $query->where('id_wilayah', $wilayahId);
    }
}
