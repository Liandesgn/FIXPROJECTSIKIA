<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WilayahKerja extends Model
{
    use HasFactory;

    protected $table = 'wilayah_kerja';

protected $fillable = [
    'nama_wilayah',
    'kabupaten',
    'kecamatan_id',
    'puskesmas_id',
    'kelurahan_desa_id',
    'rw_id',
    'rt_id',
    'deskripsi',
];

    // Relasi master wilayah.

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class);
    }

    public function kelurahanDesa(): BelongsTo
    {
        return $this->belongsTo(KelurahanDesa::class);
    }

    // Relasi pengguna.
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'wilayah_kerja_id');
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'wilayah_kerja_id');
    }

    public function bidan(): HasOne
    {
        return $this->hasOne(Bidan::class, 'wilayah_kerja_id');
    }

    public function bidans(): HasMany
    {
        return $this->hasMany(Bidan::class, 'wilayah_kerja_id');
    }

    public function kader(): HasOne
    {
        return $this->hasOne(Kader::class, 'wilayah_kerja_id');
    }

    public function kaders(): HasMany
    {
        return $this->hasMany(Kader::class, 'wilayah_kerja_id');
    }

    public function ibus(): HasMany
    {
        return $this->hasMany(Ibu::class, 'wilayah_kerja_id');
    }

    // Relasi Modul 2 tetap dipertahankan.

    public function kegiatanPosyandu(): HasMany
    {
        return $this->hasMany(KegiatanPosyandu::class, 'id_wilayah');
    }

    public function rw(): BelongsTo
    {
        return $this->belongsTo(Rw::class, 'rw_id');
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    // Label wilayah dari relasi master.

    public function label(): string
    {
        return implode(' / ', array_filter([
            $this->kecamatan?->nama_kecamatan,
            $this->puskesmas?->nama_puskesmas,
            $this->kelurahanDesa?->nama_kelurahan_desa,
        ]));
    }
}
