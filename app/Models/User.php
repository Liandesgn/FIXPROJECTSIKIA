<?php

namespace App\Models;

use App\Enums\StatusKehamilanEnum;
use App\Enums\RoleEnum;
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
             'last_login_at' => 'datetime', 
        ];
    }

    public function wilayahKerja(): BelongsTo
    {
        return $this->belongsTo(WilayahKerja::class);
    }


        public function ibu(): HasOne
    {
        return $this->hasOne(Ibu::class);
    }

    public function bidan(): HasOne
    {
        return $this->hasOne(Bidan::class);
    }

    public function kader(): HasOne
    {
        return $this->hasOne(Kader::class);
    }

        /**
     * Tetapkan wilayah untuk bidan/kader.
     * Tabel bidan/kader adalah sumber utama; kolom users.wilayah_kerja_id
     * ikut diisi sebagai JEMBATAN dan dihapus di tahap C.
     */
    public function assignWilayah(int $wilayahId): void
    {
        $profil = $this->hasRole(RoleEnum::Bidan->value) ? $this->bidan() : $this->kader();
        $profil->updateOrCreate([], ['wilayah_kerja_id' => $wilayahId]);

        $this->forceFill(['wilayah_kerja_id' => $wilayahId])->save();
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
        return $this->hasRole(RoleEnum::Ibu->value);
    }

    public function sedangHamil(): bool
{
    return $this->ibu?->sedangHamil() ?? false;
}

    /**
     * Tempat dan tanggal lahir (TTL).
     */
    public function ttl(): ?string
{
    return $this->ibu?->ttl();
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

        /**
     * Lepas bidan/kader dari wilayahnya (akun tidak dihapus).
     */
    public function lepasWilayah(): void
    {
        $profil = $this->hasRole(RoleEnum::Bidan->value) ? $this->bidan() : $this->kader();
        $profil->update(['wilayah_kerja_id' => null]);

        // JEMBATAN: dihapus di tahap C
        $this->forceFill(['wilayah_kerja_id' => null])->save();
    }

}
