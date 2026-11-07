<?php

namespace App\Http\Controllers\Bidan;

use App\Enums\StatusKehamilanEnum;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Ibu;
use App\Models\KegiatanPosyandu;
use App\Models\PemeriksaanIbu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('bidan.wilayahKerja');

        $wilayah = $user->bidan?->wilayahKerja;
        $wilayahId = $wilayah?->id;

        // Nilai awal statistik dashboard.
        $stats = [
            'ibu_melahirkan' => 0,
            'ibu_hamil' => 0,
            'anak' => 0,
            'pemeriksaan_hari_ini' => 0,
            'perlu_perhatian' => 0,
            'kegiatan_posyandu' => 0,
            'konsultasi_menunggu' => 0,
        ];

        $notifikasiTerbaru = collect();
        $jadwalTerdekat = collect();

        // Mengambil data sesuai wilayah tugas Bidan.
        if ($wilayahId) {
            $ibuQuery = Ibu::query()
                ->where('wilayah_kerja_id', $wilayahId);

            // Jumlah ibu yang sudah melahirkan.
            $stats['ibu_melahirkan'] = (clone $ibuQuery)
                ->where(
                    'status_kehamilan',
                    StatusKehamilanEnum::Melahirkan->value
                )
                ->count();

            // Jumlah ibu yang masih hamil.
            $stats['ibu_hamil'] = (clone $ibuQuery)
                ->where(
                    'status_kehamilan',
                    StatusKehamilanEnum::Hamil->value
                )
                ->count();

            // Jumlah anak di wilayah tugas Bidan.
            $stats['anak'] = Anak::query()
                ->whereHas('ibu', function ($query) use ($wilayahId) {
                    $query->where('wilayah_kerja_id', $wilayahId);
                })
                ->count();

            // Jumlah pemeriksaan ibu pada hari ini.
            $stats['pemeriksaan_hari_ini'] = PemeriksaanIbu::query()
                ->whereDate('tgl_pemeriksaan', today())
                ->whereHas('ibu', function ($query) use ($wilayahId) {
                    $query->where('wilayah_kerja_id', $wilayahId);
                })
                ->count();

            // Jumlah pemeriksaan ibu yang perlu perhatian.
            $stats['perlu_perhatian'] = PemeriksaanIbu::query()
                ->where(
                    'status_kesehatan',
                    PemeriksaanIbu::PERLU_PERHATIAN
                )
                ->whereHas('ibu', function ($query) use ($wilayahId) {
                    $query->where('wilayah_kerja_id', $wilayahId);
                })
                ->count();

            // Jumlah seluruh kegiatan Posyandu di wilayah Bidan.
            $stats['kegiatan_posyandu'] = KegiatanPosyandu::query()
                ->where('id_wilayah', $wilayahId)
                ->count();

            // Sementara 0 karena fitur konsultasi belum tersedia.
            $stats['konsultasi_menunggu'] = 0;

            // Jadwal Posyandu terdekat.
            $jadwalTerdekat = KegiatanPosyandu::query()
                ->with('wilayah')
                ->where('id_wilayah', $wilayahId)
                ->whereDate('tanggal', '>=', today())
                ->orderBy('tanggal')
                ->orderBy('waktu')
                ->limit(5)
                ->get();

            // Notifikasi pemeriksaan ibu yang perlu perhatian.
            $notifikasiTerbaru = PemeriksaanIbu::query()
                ->with(['ibu.user'])
                ->where(
                    'status_kesehatan',
                    PemeriksaanIbu::PERLU_PERHATIAN
                )
                ->whereHas('ibu', function ($query) use ($wilayahId) {
                    $query->where('wilayah_kerja_id', $wilayahId);
                })
                ->orderByDesc('tgl_pemeriksaan')
                ->limit(5)
                ->get();
        }

        return view('bidan.dashboard', [
            'user' => $user,
            'wilayah' => $wilayah,
            'stats' => $stats,
            'jadwalTerdekat' => $jadwalTerdekat,
            'notifikasiTerbaru' => $notifikasiTerbaru,
        ]);
    }
}
