<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['posyandu_id', 'tanggal', 'waktu', 'lokasi'])]
class KegiatanPosyandu extends Model
{
    protected $table = 'kegiatan_posyandu';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function posyandu(): BelongsTo
    {
        return $this->belongsTo(Posyandu::class);
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

    /** Kegiatan pada Posyandu di wilayah tertentu. */
    public function scopeDiWilayah(Builder $query, int $wilayahId): Builder
    {
        return $query->whereHas('posyandu', fn (Builder $p) => $p->where('wilayah_kerja_id', $wilayahId));
    }
}