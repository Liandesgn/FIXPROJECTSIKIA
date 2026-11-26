<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ibu_id',
    'id_bidan',
    'tgl_pemeriksaan',
    'usia_kehamilan',
    'berat_badan',
    'tinggi_badan',
    'lila',
    'hb',
    'tekanan_sistolik',
    'tekanan_diastolik',
    'proteinuria',
    'gejala_berat',
    'keluhan',
    'kondisi_kehamilan',
    'skor_risiko',
    'tingkat_risiko',
    'faktor_risiko',
    'status_kesehatan',
])]
class PemeriksaanIbu extends Model
{
    public const NORMAL = 'normal';
    public const PERLU_PERHATIAN = 'perlu_perhatian';

    public const RISIKO_RENDAH = 'rendah';
    public const RISIKO_SEDANG = 'sedang';
    public const RISIKO_TINGGI = 'tinggi';

    protected $table = 'pemeriksaan_ibu';

    protected function casts(): array
    {
        return [
            'tgl_pemeriksaan' => 'date',
            'gejala_berat' => 'boolean',
            'berat_badan' => 'decimal:2',
            'tinggi_badan' => 'decimal:2',
            'lila' => 'decimal:2',
            'hb' => 'decimal:2',
        ];
    }

    public function ibu(): BelongsTo
    {
        return $this->belongsTo(Ibu::class, 'ibu_id');
    }

    public function bidan(): BelongsTo
    {
        return $this->belongsTo(Bidan::class, 'id_bidan');
    }

    /**
     * Hanya pemeriksaan terakhir tiap ibu.
     */
    public function scopeTerakhirPerIbu(Builder $query): Builder
    {
        return $query->whereRaw(
            'pemeriksaan_ibu.tgl_pemeriksaan = (
                select max(p2.tgl_pemeriksaan)
                from pemeriksaan_ibu as p2
                where p2.ibu_id = pemeriksaan_ibu.ibu_id
            )'
        );
    }
}
