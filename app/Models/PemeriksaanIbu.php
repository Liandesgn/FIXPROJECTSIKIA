<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ibu_id', 'tgl_pemeriksaan', 'status_kesehatan'])]
class PemeriksaanIbu extends Model
{
    public const NORMAL = 'normal';
    public const PERLU_PERHATIAN = 'perlu_perhatian';

    protected $table = 'pemeriksaan_ibu';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tgl_pemeriksaan' => 'date',
        ];
    }

    public function ibu(): BelongsTo
    {
        return $this->belongsTo(Ibu::class);
    }

    /** Hanya pemeriksaan terakhir tiap ibu. */
    public function scopeTerakhirPerIbu(Builder $query): Builder
    {
        return $query->whereRaw(
            'pemeriksaan_ibu.tgl_pemeriksaan = (select max(p2.tgl_pemeriksaan) from pemeriksaan_ibu as p2 where p2.ibu_id = pemeriksaan_ibu.ibu_id)'
        );
    }
}