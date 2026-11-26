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
        $user = $request->user()->load([
            'kader.wilayahKerja',
            'wilayahKerja',
        ]);

        // Wilayah kerja Kader.
        $wilayah = $user->kader?->wilayahKerja
            ?? $user->wilayahKerja;

        // Cakupan utama Kader adalah kelurahan/desa.
        $kelurahanDesaId = $wilayah?->kelurahan_desa_id;

        $stats = [
            'posyandu' => 0,
            'pengukuran' => 0,
        ];

        $jadwalTerdekat = collect();
        $notifikasiTerbaru = collect();

        if ($kelurahanDesaId) {

            // Jumlah seluruh kegiatan Posyandu
            // dalam kelurahan/desa Kader.
            $stats['posyandu'] = KegiatanPosyandu::query()
                ->whereHas('rw', function ($query) use ($kelurahanDesaId) {
                    $query->where(
                        'kelurahan_desa_id',
                        $kelurahanDesaId
                    );
                })
                ->count();

            // Jumlah pengukuran anak berdasarkan
            // kegiatan Posyandu dalam kelurahan/desa Kader.
            $stats['pengukuran'] = PemeriksaanPosyandu::query()
                ->whereHas('kegiatan.rw', function ($query) use ($kelurahanDesaId) {
                    $query->where(
                        'kelurahan_desa_id',
                        $kelurahanDesaId
                    );
                })
                ->count();

            // Jadwal Posyandu mendatang.
            $jadwalTerdekat = KegiatanPosyandu::query()
                ->with(['rw'])
                ->whereHas('rw', function ($query) use ($kelurahanDesaId) {
                    $query->where(
                        'kelurahan_desa_id',
                        $kelurahanDesaId
                    );
                })
                ->whereDate('tanggal', '>=', today())
                ->orderBy('tanggal')
                ->orderBy('waktu')
                ->limit(5)
                ->get();

            // Notifikasi pemeriksaan anak yang perlu perhatian.
                $notifikasiTerbaru = PemeriksaanPosyandu::query()
                    ->with([
                        'anak.ibu.user',
                        'kegiatan',
                    ])
                    ->whereIn('tingkat_risiko', ['sedang', 'tinggi'])
                    ->whereHas('kegiatan.rw', function ($query) use ($kelurahanDesaId) {
                        $query->where(
                            'kelurahan_desa_id',
                            $kelurahanDesaId
                        );
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
