<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Rt;
use App\Models\Rw;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    /**
     * Menampilkan jadwal pemeriksaan ibu hamil dan jadwal Posyandu
     * sesuai kelurahan/desa wilayah kerja Bidan.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $bidan = $user->bidan()
            ->with('wilayahKerja.kelurahanDesa')
            ->firstOrFail();

        $wilayah = $bidan->wilayahKerja;
        $kelurahanDesaId = $wilayah?->kelurahan_desa_id;

        // Jika Bidan belum memiliki wilayah kerja.
        if (!$kelurahanDesaId) {
            return view('bidan.jadwal.index', [
                'bidan' => $bidan,
                'wilayah' => $wilayah,
                'jadwalPemeriksaan' => collect(),
                'jadwalPosyandu' => collect(),
                'riwayatPosyandu' => collect(),
                'daftarRw' => collect(),
                'daftarRt' => collect(),
            ]);
        }

        // ==========================================
        // DAFTAR RW DAN RT
        // ==========================================

        $daftarRw = Rw::where('kelurahan_desa_id', $kelurahanDesaId)
            ->orderBy('nomor_rw')
            ->get();

        $daftarRt = Rt::whereIn('rw_id', $daftarRw->pluck('id'))
            ->orderBy('nomor_rt')
            ->get();

        // ==========================================
        // 1. JADWAL PEMERIKSAAN IBU HAMIL
        // ==========================================

        $jadwalPemeriksaan = Jadwal::with([
            'jadwalPemeriksaan.bidan.user',
            'jadwalPemeriksaan.wilayahKerja.kelurahanDesa',
        ])
            ->whereHas(
                'jadwalPemeriksaan.wilayahKerja',
                function ($query) use ($kelurahanDesaId) {
                    $query->where(
                        'kelurahan_desa_id',
                        $kelurahanDesaId
                    );
                }
            )
            ->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('waktu')
            ->get();

        // ==========================================
        // 2. SEMUA JADWAL POSYANDU
        // ==========================================

        $semuaJadwalPosyandu = Jadwal::with([
            'jadwalPosyandu.kegiatan.rw.kelurahanDesa',
            'jadwalPosyandu.kegiatan.rt',
            'jadwalPosyandu.bidan.user',
            'jadwalPosyandu.kader.user',
        ])
            ->whereHas(
                'jadwalPosyandu.kegiatan.rw',
                function ($query) use ($kelurahanDesaId) {
                    $query->where(
                        'kelurahan_desa_id',
                        $kelurahanDesaId
                    );
                }
            )
            ->get();

        // ==========================================
        // 3. JADWAL POSYANDU MENDATANG
        // ==========================================

        $jadwalPosyandu = $semuaJadwalPosyandu
            ->filter(function ($jadwal) {
                return $jadwal->tanggal
                    && $jadwal->tanggal->toDateString()
                        >= today()->toDateString();
            })
            ->sortBy(function ($jadwal) {
                return $jadwal->tanggal->format('Y-m-d')
                    . ' ' . ($jadwal->waktu ?? '00:00:00');
            })
            ->values();

        // ==========================================
        // 4. RIWAYAT JADWAL POSYANDU
        // ==========================================

        $riwayatPosyandu = $semuaJadwalPosyandu
            ->filter(function ($jadwal) {
                return $jadwal->tanggal
                    && $jadwal->tanggal->toDateString()
                        < today()->toDateString();
            })
            ->sortByDesc(function ($jadwal) {
                return $jadwal->tanggal->format('Y-m-d')
                    . ' ' . ($jadwal->waktu ?? '00:00:00');
            })
            ->values();

        return view('bidan.jadwal.index', [
            'bidan' => $bidan,
            'wilayah' => $wilayah,
            'jadwalPemeriksaan' => $jadwalPemeriksaan,
            'jadwalPosyandu' => $jadwalPosyandu,
            'riwayatPosyandu' => $riwayatPosyandu,
            'daftarRw' => $daftarRw,
            'daftarRt' => $daftarRt,
        ]);
    }
}
