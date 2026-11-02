<?php

namespace App\Http\Controllers\Bidan;

use App\Enums\StatusKehamilanEnum;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Ibu;
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

        $stats = [
            'orang_tua' => 0,
            'ibu_hamil' => 0,
            'anak' => 0,
            'pemeriksaan' => 0,
        ];

        $notifikasiTerbaru = collect();

        // Jadwal belum tersedia karena tabel kegiatan_posyandu belum dibuat.
        $jadwalTerdekat = collect();

        if ($wilayahId) {
            $ibuQuery = Ibu::query()
                ->where('wilayah_kerja_id', $wilayahId);

            $stats['orang_tua'] = (clone $ibuQuery)->count();

            $stats['ibu_hamil'] = (clone $ibuQuery)
                ->where(
                    'status_kehamilan',
                    StatusKehamilanEnum::Hamil->value
                )
                ->count();

            $stats['anak'] = Anak::query()
                ->whereHas('ibu', function ($query) use ($wilayahId) {
                    $query->where('wilayah_kerja_id', $wilayahId);
                })
                ->count();

            $stats['pemeriksaan'] = PemeriksaanIbu::query()
                ->whereHas('ibu', function ($query) use ($wilayahId) {
                    $query->where('wilayah_kerja_id', $wilayahId);
                })
                ->count();

            // Notifikasi dari pemeriksaan ibu yang perlu perhatian.
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