<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ComingSoonController extends Controller
{
    /**
     * Judul dan deskripsi modul yang belum diimplementasikan.
     *
     * @var array<string, array{0: string, 1: string}>
     */
public const MODULES = [
    'kelola-wilayah' => ['Data Wilayah Kerja', 'Kelola master data wilayah kerja posyandu.'],
    'wilayah-kerja' => ['Data Wilayah Kerja', 'Kelola master data wilayah kerja posyandu.'],
        'kelola-jadwal' => ['Kelola Jadwal', 'Atur jadwal kegiatan posyandu dan pemeriksaan.'],
        'laporan' => ['Laporan', 'Unduh laporan sistem dan rekapitulasi data.'],
        'data-ibu' => ['Data Ibu', 'Data induk ibu hamil dan ibu menyusui.'],
        'data-anak' => ['Data Anak', 'Data induk anak dan balita.'],
        'jadwal' => ['Jadwal', 'Jadwal pemeriksaan dan kegiatan posyandu.'],
        'data-posyandu' => ['Data Posyandu', 'Data induk posyandu dan wilayah pelayanannya.'],
        'pemeriksaan-ibu' => ['Pemeriksaan Ibu Hamil', 'Catat hasil pemeriksaan kesehatan ibu hamil.'],
        'pemeriksaan-anak' => ['Pemeriksaan Anak & Balita', 'Catat hasil pemeriksaan anak dan balita.'],
        'riwayat-pemeriksaan' => ['Riwayat Pemeriksaan', 'Lihat riwayat pemeriksaan pasien.'],
        'monitoring-status' => ['Monitoring Status Kesehatan', 'Pantau status kesehatan ibu dan anak.'],
        'grafik-perkembangan' => ['Grafik Perkembangan', 'Pantau grafik tumbuh kembang anak.'],
        'konsultasi' => ['Konsultasi Bidan', 'Ruang tanya jawab antara orang tua dan bidan.'],
        'jadwal-pemeriksaan' => ['Jadwal Pemeriksaan', 'Lihat jadwal pemeriksaan kesehatan.'],
         'jadwal' => ['Jadwal', 'Lihat jadwal.'],
        'jadwal-posyandu' => ['Jadwal Posyandu', 'Lihat jadwal kegiatan posyandu.'],
        'laporan-wilayah' => ['Laporan Kesehatan Wilayah', 'Unduh laporan kesehatan per wilayah kerja.'],
        'pendataan-posyandu' => ['Pendataan Kegiatan Posyandu', 'Catat kehadiran dan kegiatan posyandu.'],
        'cari-ibu' => ['Cari Data Ibu', 'Cari data ibu berdasarkan nama atau identitas.'],
        'edukasi' => ['Edukasi Kesehatan', 'Artikel dan materi edukasi kesehatan ibu dan anak.'],
    ];

    public function show(string $module): View
    {
        abort_unless(isset(self::MODULES[$module]), 404);

        [$title, $description] = self::MODULES[$module];

        return view('panel.coming-soon', [
            'title' => $title,
            'description' => $description,
        ]);
    }
}
