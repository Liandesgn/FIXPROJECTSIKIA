<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogPerubahanPemeriksaan extends Model
{
    protected $table = 'log_perubahan_pemeriksaan';

    protected $fillable = [
        'pemeriksaan_ibu_id',
        'user_id',
        'aksi',
        'data_lama',
        'data_baru',
    ];

    protected function casts(): array
    {
        return [
            'data_lama' => 'array',
            'data_baru' => 'array',
        ];
    }

    public function pemeriksaanIbu(): BelongsTo
    {
        return $this->belongsTo(
            PemeriksaanIbu::class,
            'pemeriksaan_ibu_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
