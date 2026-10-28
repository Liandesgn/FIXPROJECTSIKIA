<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anak extends Model
{
    protected $table = 'anak';

    protected $fillable = [
        'ibu_id',
        'nik_anak',
        'nama_anak',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'nama_ayah',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    /**
     * Setiap anak terhubung dengan satu data ibu.
     */
    public function ibu(): BelongsTo
    {
        return $this->belongsTo(Ibu::class);
    }

    /**
     * Anak mengikuti cakupan akses data ibunya.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas(
            'ibu',
            fn (Builder $ibuQuery) => $ibuQuery->visibleTo($user)
        );
    }
}