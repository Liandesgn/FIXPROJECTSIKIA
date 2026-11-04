<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['anak_id', 'kegiatan_posyandu_id', 'tinggi_badan', 'berat_badan', 'status_kesehatan'])]
class PemeriksaanPosyandu extends Model
{
    public const NORMAL = 'normal';
    public const PERLU_PERHATIAN = 'perlu_perhatian';

    protected $table = 'pemeriksaan_posyandu';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tinggi_badan' => 'decimal:2',
            'berat_badan' => 'decimal:2',
        ];
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(KegiatanPosyandu::class, 'kegiatan_posyandu_id');
    }

    /** Hanya pemeriksaan terakhir tiap anak (berdasarkan tanggal kegiatan). */
    public function scopeTerakhirPerAnak(Builder $query): Builder
    {
        return $query->whereRaw(
            '(select k.tanggal from kegiatan_posyandu as k where k.id = pemeriksaan_posyandu.kegiatan_posyandu_id) = '
            .'(select max(k2.tanggal) from pemeriksaan_posyandu as p2 '
            .'join kegiatan_posyandu as k2 on k2.id = p2.kegiatan_posyandu_id '
            .'where p2.anak_id = pemeriksaan_posyandu.anak_id)'
        );
    }
}