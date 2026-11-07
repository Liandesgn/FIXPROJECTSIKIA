<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WilayahKerja extends Model
{
    use HasFactory;

    protected $table = 'wilayah_kerja';

    protected $fillable = [
        'nama_wilayah',
        'kabupaten',
        'kecamatan',
        'kelurahan',
        'deskripsi',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'id_wilayah');
    }

    public function kegiatanPosyandu(): HasMany
    {
        return $this->hasMany(KegiatanPosyandu::class, 'id_wilayah');
    }

    /**
     * Alias lama untuk tampilan yang belum diperbarui.
     */
    public function getDesaAttribute(): ?string
    {
        return $this->kelurahan;
    }

    /**
     * Bidan penanggung jawab wilayah ini (satu per wilayah).
     */
    public function bidan(): HasOne
    {
        return $this->hasOne(User::class)->role(RoleEnum::Bidan->value);
    }

    /**
     * Kader yang bertugas di wilayah ini (satu kader, dipertahankan agar kode lama tidak rusak).
     */
    public function kader(): HasOne
    {
        return $this->hasOne(User::class)->role(RoleEnum::Kader->value);
    }

    /**
     * Semua kader di wilayah ini (banyak per wilayah).
     */
    public function kaders(): HasMany
    {
        return $this->hasMany(User::class)->role(RoleEnum::Kader->value);
    }

    public function orangTua(): HasMany
    {
        return $this->hasMany(User::class)->role(RoleEnum::Ibu->value);
    }

    /**
     * Label lengkap wilayah (kabupaten/kecamatan/desa).
     */
    public function label(): string
    {
        $parts = array_filter([$this->kabupaten, $this->kecamatan, $this->kelurahan]);

        return $parts ? implode(' / ', $parts) : (string) $this->nama_wilayah;
    }
}