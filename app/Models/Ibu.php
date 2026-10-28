<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\StatusKehamilanEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'wilayah_kerja_id',
    'nik',
    'tempat_lahir',
    'tanggal_lahir',
    'status_kehamilan',
    'usia_kehamilan',
    'hpl',
])]
class Ibu extends Model
{
    protected $table = 'ibu';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'hpl' => 'date',
            'status_kehamilan' => StatusKehamilanEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wilayahKerja(): BelongsTo
    {
        return $this->belongsTo(WilayahKerja::class);
    }

    /**
 * Satu ibu dapat memiliki banyak anak.
 */
public function anak(): HasMany
{
    return $this->hasMany(Anak::class, 'ibu_id');
}

    public function sedangHamil(): bool
    {
        return $this->status_kehamilan === StatusKehamilanEnum::Hamil;
    }

        /**
     * Tempat dan tanggal lahir (TTL).
     */
    public function ttl(): ?string
    {
        if (! $this->tempat_lahir && ! $this->tanggal_lahir) {
            return null;
        }

        return trim(($this->tempat_lahir ?? '').', '.($this->tanggal_lahir?->format('d-m-Y') ?? ''), ', ');
    }


        /**
     * Batasi data sesuai role: admin semua, bidan/kader wilayahnya, ibu dirinya sendiri.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isOrangTua()) {
            return $query->where('user_id', $user->id);
        }

        $wilayahId = ($user->bidan ?? $user->kader)?->wilayah_kerja_id;

        return $wilayahId
            ? $query->where('wilayah_kerja_id', $wilayahId)
            : $query->whereRaw('1 = 0');
    }


}