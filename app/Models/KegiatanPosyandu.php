<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id_wilayah', 'jenis_kegiatan', 'tanggal', 'waktu', 'lokasi'])]
class KegiatanPosyandu extends Model
{
    protected $table = 'kegiatan_posyandu';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id_wilayah' => 'integer',
            'tanggal' => 'date',
            'waktu' => 'time',
            'lokasi' => 'string',
            'jenis_kegiatan' => 'string',
        ];
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(WilayahKerja::class, 'id_wilayah');
    }

    public function pemeriksaan(): HasMany
    {
        return $this->hasMany(PemeriksaanPosyandu::class, 'kegiatan_posyandu_id');
    }

    /** Kegiatan hari ini atau setelahnya, yang terdekat di atas. */
    public function scopeMendatang(Builder $query): Builder
    {
        return $query->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('waktu');
    }

    /** Kegiatan pada wilayah tertentu. */
    public function scopeDiWilayah(Builder $query, int $wilayahId): Builder
    {
        return $query->where('id_wilayah', $wilayahId);
    }
}