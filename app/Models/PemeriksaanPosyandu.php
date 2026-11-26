<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'anak_id',
    'kegiatan_posyandu_id',
    'kader_id',
    'bidan_id',
    'tgl_pemeriksaan',
    'jadwal_vitamin',
    'id_jadwal_vitamin',
    'tinggi_badan',
    'berat_badan',
    'lingkar_kepala',
    'lila',
    'umur_bulan',
    'z_score_tbu',
    'median_tbu',
    'sd_minus_1_tbu',
    'sd_plus_1_tbu',
    'status_stunting',
    'tingkat_risiko',
    'catatan',
    'pencatat_user_id',
    'pengubah_user_id',
])]
class PemeriksaanPosyandu extends Model
{
    public const NORMAL = 'normal';
    public const PERLU_PERHATIAN = 'perlu_perhatian';

    public const STUNTING_NORMAL = 'normal';
    public const STUNTING_PENDEK = 'pendek';
    public const STUNTING_SANGAT_PENDEK = 'sangat_pendek';

    public const RISIKO_RENDAH = 'rendah';
    public const RISIKO_SEDANG = 'sedang';
    public const RISIKO_TINGGI = 'tinggi';

    protected $table = 'pemeriksaan_posyandu';

    /**
     * Konversi tipe data otomatis.
     */
    protected function casts(): array
    {
        return [
            'tgl_pemeriksaan' => 'date',
            'tinggi_badan' => 'decimal:2',
            'berat_badan' => 'decimal:2',
            'lingkar_kepala' => 'decimal:2',
            'lila' => 'decimal:2',
            'umur_bulan' => 'integer',
            'z_score_tbu' => 'decimal:3',
            'median_tbu' => 'decimal:2',
            'sd_minus_1_tbu' => 'decimal:2',
            'sd_plus_1_tbu' => 'decimal:2',
        ];
    }

    /**
     * Relasi ke data anak.
     */
    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class, 'anak_id');
    }

    /**
     * Relasi ke kegiatan Posyandu.
     */
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(
            KegiatanPosyandu::class,
            'kegiatan_posyandu_id'
        );
    }

    /**
     * Kader yang melakukan pemeriksaan.
     */
    public function kader(): BelongsTo
    {
        return $this->belongsTo(Kader::class, 'kader_id');
    }

    /**
     * Bidan yang melakukan pemeriksaan.
     */
    public function bidan(): BelongsTo
    {
        return $this->belongsTo(Bidan::class, 'bidan_id');
    }

    /**
     * Akun yang pertama kali mencatat pemeriksaan.
     */
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pencatat_user_id');
    }

    /**
     * Akun yang terakhir mengubah pemeriksaan.
     */
    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengubah_user_id');
    }

    /**
     * Relasi ke master jadwal vitamin.
     */
    public function jadwalVitamin(): BelongsTo
    {
        return $this->belongsTo(
            JadwalVitamin::class,
            'id_jadwal_vitamin',
            'id_jadwal_vitamin'
        );
    }

    /**
     * Relasi ke data imunisasi anak.
     */
    public function imunisasiAnak(): HasMany
    {
        return $this->hasMany(
            ImunisasiAnak::class,
            'id_pemeriksaan',
            'id'
        );
    }

    /**
     * Hanya pemeriksaan terakhir tiap anak
     * berdasarkan tanggal kegiatan Posyandu.
     */
    public function scopeTerakhirPerAnak(Builder $query): Builder
    {
        return $query->whereRaw(
            '(select k.tanggal
                from kegiatan_posyandu as k
                where k.id = pemeriksaan_posyandu.kegiatan_posyandu_id) = '
            . '(select max(k2.tanggal)
                from pemeriksaan_posyandu as p2
                join kegiatan_posyandu as k2
                    on k2.id = p2.kegiatan_posyandu_id
                where p2.anak_id = pemeriksaan_posyandu.anak_id)'
        );
    }
}
