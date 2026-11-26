<?php

namespace App\Services;

use App\Models\StandarTBU;
use Carbon\Carbon;
use InvalidArgumentException;

class StuntingCalculator
{
    public function hitung(
        string $jenisKelamin,
        string $tanggalLahir,
        string $tanggalPemeriksaan,
        float $tinggiBadan
    ): array {
        if (!in_array($jenisKelamin, ['L', 'P'], true)) {
            throw new InvalidArgumentException('Jenis kelamin harus L atau P.');
        }

        if ($tinggiBadan <= 0) {
            throw new InvalidArgumentException('Tinggi badan harus lebih dari 0.');
        }

        $lahir = Carbon::parse($tanggalLahir)->startOfDay();
        $pemeriksaan = Carbon::parse($tanggalPemeriksaan)->startOfDay();

        if ($pemeriksaan->lt($lahir)) {
            throw new InvalidArgumentException(
                'Tanggal pemeriksaan tidak boleh sebelum tanggal lahir.'
            );
        }

        // Menghitung umur dalam bulan penuh.
        $umurBulan = (int) $lahir->diffInMonths($pemeriksaan);

        // Ambil standar WHO sesuai jenis kelamin dan umur.
        $standar = StandarTBU::query()
            ->where('jenis_kelamin', $jenisKelamin)
            ->where('umur_bulan', $umurBulan)
            ->first();

        if (!$standar) {
            throw new InvalidArgumentException(
                "Standar TB/U untuk umur {$umurBulan} bulan tidak ditemukan."
            );
        }

        $median = (float) $standar->median;
        $sdMinus1 = (float) $standar->sd_minus_1;
        $sdPlus1 = (float) $standar->sd_plus_1;

        // Rumus Z-score TB/U.
        if ($tinggiBadan < $median) {
            $zScore = ($tinggiBadan - $median) / ($median - $sdMinus1);
        } else {
            $zScore = ($tinggiBadan - $median) / ($sdPlus1 - $median);
        }

        $zScore = round($zScore, 3);

        // Menentukan status stunting dan tingkat risiko.
        if ($zScore < -3) {
            $statusStunting = 'sangat_pendek';
            $tingkatRisiko = 'tinggi';
        } elseif ($zScore < -2) {
            $statusStunting = 'pendek';
            $tingkatRisiko = 'tinggi';
        } elseif ($zScore < -1) {
            $statusStunting = 'normal';
            $tingkatRisiko = 'sedang';
        } else {
            $statusStunting = 'normal';
            $tingkatRisiko = 'rendah';
        }

        return [
            'umur_bulan' => $umurBulan,
            'median_tbu' => $median,
            'sd_minus_1_tbu' => $sdMinus1,
            'sd_plus_1_tbu' => $sdPlus1,
            'z_score_tbu' => $zScore,
            'status_stunting' => $statusStunting,
            'tingkat_risiko' => $tingkatRisiko,
        ];
    }
}
