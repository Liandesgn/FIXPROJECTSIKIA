<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\KegiatanPosyandu;
use App\Models\PemeriksaanPosyandu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('wilayahKerja');

        $wilayah = $user->wilayahKerja;
        $wilayahId = $wilayah?->id;

        $stats = [
            'posyandu' => 0,
            'pengukuran' => 0,
        ];

        $jadwalTerdekat = collect();
        $notifikasiTerbaru = collect();

        if ($wilayahId) {
            // Menghitung jumlah kegiatan Posyandu di wilayah Kader.
            $stats['posyandu'] = KegiatanPosyandu::query()
                ->where('id_wilayah', $wilayahId)
                ->count();

            // Menghitung jumlah pemeriksaan anak di wilayah Kader.
            $stats['pengukuran'] = PemeriksaanPosyandu::query()
                ->whereHas('kegiatan', function ($query) use ($wilayahId) {
                    $query->where('id_wilayah', $wilayahId);
                })
                ->count();

            // Mengambil maksimal 5 jadwal Posyandu mendatang.
            $jadwalTerdekat = KegiatanPosyandu::query()
                ->with('wilayah')
                ->where('id_wilayah', $wilayahId)
                ->whereDate('tanggal', '>=', today())
                ->orderBy('tanggal')
                ->orderBy('waktu')
                ->limit(5)
                ->get();


             // Notifikasi dari pemeriksaan anak yang perlu perhatian.
            $notifikasiTerbaru = PemeriksaanPosyandu::query()
                ->with(['anak.ibu.user', 'kegiatan'])
                ->where('status_kesehatan', PemeriksaanPosyandu::PERLU_PERHATIAN)
                ->whereHas('anak.ibu', function ($query) use ($wilayahId) {
                    $query->where('wilayah_kerja_id', $wilayahId);
                })
                ->orderByDesc('id')
                ->limit(5)
                ->get();
                    }

        return view('kader.dashboard', [
            'user' => $user,
            'wilayah' => $wilayah,
            'stats' => $stats,
            'jadwalTerdekat' => $jadwalTerdekat,
            'notifikasiTerbaru' => $notifikasiTerbaru,
        ]);
    }
}
