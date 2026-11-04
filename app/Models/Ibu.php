<?php

namespace App\Models;

use App\Enums\StatusKehamilanEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'wilayah_kerja_id',
    'nik',
    'tempat_lahir',
    'tanggal_lahir',
    'status_kehamilan',
    'usia_kehamilan',
    'tanggal_pemeriksaan_awal',
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
            'tanggal_pemeriksaan_awal' => 'date',
            'hpl' => 'date',
            'status_kehamilan' => StatusKehamilanEnum::class,
        ];
    }

    /**
     * Tanggal pemeriksaan awal diisi otomatis:
     * - data baru berstatus Hamil  : hari ini
     * - usia kehamilan diubah      : hari ini (usia yang diinput menjadi usia awal)
     * - bukan hamil / usia kosong  : dikosongkan
     */
    protected static function booted(): void
    {
        static::saving(function (Ibu $ibu) {
            if ($ibu->status_kehamilan !== StatusKehamilanEnum::Hamil || $ibu->usia_kehamilan === null) {
                $ibu->tanggal_pemeriksaan_awal = null;

                return;
            }

            if (! $ibu->exists || $ibu->isDirty('usia_kehamilan') || $ibu->tanggal_pemeriksaan_awal === null) {
                $ibu->tanggal_pemeriksaan_awal = now()->toDateString();
            }
        });
    }

    /**
     * Usia kehamilan saat ini (minggu): usia saat pemeriksaan awal
     * ditambah selisih minggu sejak tanggal pemeriksaan awal.
     */
    public function getUsiaKehamilanSekarangAttribute(): ?int
    {
        if (! $this->sedangHamil() || $this->usia_kehamilan === null) {
            return null;
        }

        if (! $this->tanggal_pemeriksaan_awal) {
            return (int) $this->usia_kehamilan;
        }

        $hari = $this->tanggal_pemeriksaan_awal->copy()->startOfDay()
            ->diffInDays(now()->startOfDay());

        return (int) $this->usia_kehamilan + max(0, (int) floor($hari / 7));
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

        $wilayahId = $user->bidan?->wilayah_kerja_id
            ?? $user->kader?->wilayah_kerja_id
            ?? $user->wilayah_kerja_id;

        return $wilayahId
            ? $query->where('wilayah_kerja_id', $wilayahId)
            : $query->whereRaw('1 = 0');
    }
}