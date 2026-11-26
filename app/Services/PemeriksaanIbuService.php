<?php

namespace App\Services;

use App\Models\Ibu;
use Carbon\Carbon;

class PemeriksaanIbuService
{
    /**
     * Menghitung usia kehamilan berdasarkan usia awal
     * dan tanggal pemeriksaan awal.
     */

    public function hitungUsiaKehamilan(
        Ibu $ibu,
        Carbon|string|null $tanggalPemeriksaan = null
    ): int {
        if (
            !$ibu->sedangHamil() ||
            $ibu->usia_kehamilan === null ||
            !$ibu->tanggal_pemeriksaan_awal
        ) {
            throw new \InvalidArgumentException(
                'Data awal kehamilan ibu belum lengkap.'
            );
        }

        $tanggalAwal = Carbon::parse(
            $ibu->tanggal_pemeriksaan_awal
        )->startOfDay();

        $tanggalAcuan = $tanggalPemeriksaan
            ? Carbon::parse($tanggalPemeriksaan)->startOfDay()
            : now()->startOfDay();

        if ($tanggalAcuan->lt($tanggalAwal)) {
            throw new \InvalidArgumentException(
                'Tanggal pemeriksaan tidak boleh sebelum tanggal pemeriksaan awal.'
            );
        }

        $selisihHari = (int) $tanggalAwal->diffInDays($tanggalAcuan);

        return (int) $ibu->usia_kehamilan
            + (int) floor($selisihHari / 7);
    }


    /**
     * Menghitung perkiraan hari lahir berdasarkan
     * usia kehamilan dan tanggal pemeriksaan.
     */
    public function hitungHpl(
        int $usiaKehamilan,
        Carbon|string $tanggalPemeriksaan
    ): Carbon {
    if ($usiaKehamilan >= 40 && $usiaKehamilan < 42) {
        $skor += 4;
        $faktor[] = 'Usia kehamilan mencapai 40 minggu';
    }

        $tanggal = Carbon::parse($tanggalPemeriksaan);

        $sisaHari = (40 * 7) - ($usiaKehamilan * 7);

        return $tanggal->copy()->addDays($sisaHari);
    }

    /**
     * Menghitung skor dan tingkat risiko sesuai aturan tugas.
     *
     * Catatan: ini implementasi aturan aplikasi,
     * bukan pengganti penilaian klinis bidan.
     */
    public function hitungRisiko(
        Ibu $ibu,
        array $data
    ): array {
        $usiaKehamilan = (int) $data['usia_kehamilan'];

        $skor = 2;
        $faktor = [];

        $usiaIbu = $ibu->tgl_lahir
            ? Carbon::parse($ibu->tgl_lahir)->age
            : null;

        // Faktor usia ibu.
        if ($usiaIbu !== null && ($usiaIbu < 20 || $usiaIbu > 35)) {
            $skor += 4;
            $faktor[] = 'Usia ibu kurang dari 20 tahun atau lebih dari 35 tahun';
        }

        // Faktor jumlah anak.
        $jumlahAnak = $ibu->anak()->count();

        if ($jumlahAnak >= 4) {
            $skor += 4;
            $faktor[] = 'Jumlah anak 4 atau lebih';
        }

        // Faktor jarak kehamilan.
        $anakTerakhir = $ibu->anak()
            ->whereNotNull('tgl_lahir')
            ->orderByDesc('tgl_lahir')
            ->first();

        if ($anakTerakhir && $anakTerakhir->tgl_lahir) {
            $tanggalKonsepsi = Carbon::parse(
                $data['tgl_pemeriksaan']
            )->subWeeks($usiaKehamilan);

            $jarakBulan = Carbon::parse(
                $anakTerakhir->tgl_lahir
            )->diffInMonths($tanggalKonsepsi, false);

            if ($jarakBulan >= 0 && $jarakBulan < 24) {
                $skor += 4;
                $faktor[] = 'Jarak kehamilan kurang dari 24 bulan';
            }
        }

        // Faktor tinggi badan ibu.
        if ((float) $data['tinggi_badan'] < 145) {
            $skor += 4;
            $faktor[] = 'Tinggi badan ibu kurang dari 145 cm';
        }

        // Faktor LILA.
        if ((float) $data['lila'] < 23.5) {
            $skor += 4;
            $faktor[] = 'LILA kurang dari 23,5 cm';
        }

        // Faktor Hb.
        if ((float) $data['hb'] < 11) {
            $skor += 4;
            $faktor[] = 'Hemoglobin kurang dari 11 g/dL';
        }

        $sistolik = (int) $data['tekanan_sistolik'];
        $diastolik = (int) $data['tekanan_diastolik'];

        $proteinuria = (string) $data['proteinuria'];

        $hipertensi = $sistolik >= 140 || $diastolik >= 90;

        $proteinPositif = in_array(
            strtolower($proteinuria),
            ['+', 'positif', 'positive', '1+', '2+', '3+', '4+'],
            true
        );

        // Preeklamsia: hipertensi dan proteinuria positif
        // pada usia kehamilan minimal 20 minggu.
        $preeklamsia = $usiaKehamilan >= 20
            && $hipertensi
            && $proteinPositif;

        // Hipertensi tanpa proteinuria positif.
        if (
            $usiaKehamilan >= 20 &&
            $hipertensi &&
            !$proteinPositif
        ) {
            $skor += 4;
            $faktor[] = 'Hipertensi dalam kehamilan';
        }

        if ($preeklamsia) {
            $faktor[] = 'Indikasi preeklamsia';
        }

        // Gejala berat atau tekanan darah berat.
        $hipertensiBerat = $sistolik >= 160 || $diastolik >= 110;

        $risikoTinggiLangsung = $hipertensiBerat
            || ($preeklamsia && (bool) $data['gejala_berat']);

        if ($hipertensiBerat) {
            $faktor[] = 'Tekanan darah mencapai kategori berat';
        }

        if ($preeklamsia && (bool) $data['gejala_berat']) {
            $faktor[] = 'Preeklamsia disertai gejala berat';
        }

        // Kehamilan lewat waktu.
        if ($usiaKehamilan >= 40) {
            $skor += 4;
            $faktor[] = 'Usia kehamilan mencapai 40 minggu atau lebih';
        }

        $posttermBerat = $usiaKehamilan >= 42;

        if ($posttermBerat) {
            $faktor[] = 'Kehamilan mencapai 42 minggu atau lebih';
        }

        // Menentukan tingkat risiko.
        if (
            $risikoTinggiLangsung ||
            $posttermBerat ||
            $preeklamsia ||
            $skor >= 12
        ) {
            $tingkatRisiko = 'tinggi';
        } elseif ($skor === 2) {
            $tingkatRisiko = 'rendah';
        } else {
            $tingkatRisiko = 'sedang';
        }

        return [
            'skor_risiko' => $skor,
            'tingkat_risiko' => $tingkatRisiko,
            'faktor_risiko' => $faktor,
            'perlu_rujukan_segera' => $risikoTinggiLangsung
                || $posttermBerat,
        ];
    }
}
