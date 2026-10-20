<?php

namespace App\Models;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Enums\UserStatusEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'password',
    'phone',
    'nik',
    'nama_ibu',
    'tempat_lahir',
    'tanggal_lahir',
    'status_kehamilan',
    'hpl',
    'usia_kehamilan',
    'status',
    'wilayah_kerja_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatusEnum::class,
            'tanggal_lahir' => 'date',
            'hpl' => 'date',
            'status_kehamilan' => StatusKehamilanEnum::class,
             'last_login_at' => 'datetime', 
        ];
    }

    public function wilayahKerja(): BelongsTo
    {
        return $this->belongsTo(WilayahKerja::class);
    }

    /**
     * Bidan yang bertugas pada desa/wilayah yang sama.
     */
    public function bidanWilayah(): HasOne
    {
        return $this->hasOne(User::class, 'wilayah_kerja_id', 'wilayah_kerja_id')
            ->role(RoleEnum::Bidan->value);
    }

    /**
     * Kader yang bertugas pada desa/wilayah yang sama.
     */
    public function kaderWilayah(): HasOne
    {
        return $this->hasOne(User::class, 'wilayah_kerja_id', 'wilayah_kerja_id')
            ->role(RoleEnum::Kader->value);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleEnum::Admin->value);
    }

    public function isOrangTua(): bool
    {
        return $this->hasRole(RoleEnum::OrangTua->value);
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

    public function isActive(): bool
    {
        return $this->status === UserStatusEnum::Active;
    }

    /**
     * Role utama user (satu per user pada sistem SI-KIA).
     */
    public function primaryRole(): ?RoleEnum
    {
        $name = $this->getRoleNames()->first();

        return $name ? RoleEnum::tryFrom($name) : null;
    }
}
