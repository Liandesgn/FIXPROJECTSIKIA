<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
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

        $jadwalPemeriksaan = collect();
        $jadwalPosyandu = collect();
        $riwayatPosyandu = collect();
        $daftarRw = collect();

        if ($kelurahanDesaId) {
            // Daftar RW hanya dari kelurahan/desa wilayah Bidan.
            $daftarRw = Rw::where('kelurahan_desa_id', $kelurahanDesaId)
                ->orderBy('nomor_rw')
                ->get();

            // Jadwal Pemeriksaan Ibu Hamil.
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

            // Semua jadwal Posyandu, termasuk yang sudah lewat.
            $jadwalPosyandu = Jadwal::with([
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
                ->orderBy('tanggal')
                ->orderBy('waktu')
                ->get();

            // Tetap disediakan jika masih digunakan bagian lain pada Blade.
            $riwayatPosyandu = $jadwalPosyandu
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
        }

        return view('bidan.jadwal.index', [
            'bidan' => $bidan,
            'wilayah' => $wilayah,
            'jadwalPemeriksaan' => $jadwalPemeriksaan,
            'jadwalPosyandu' => $jadwalPosyandu,
            'riwayatPosyandu' => $riwayatPosyandu,
            'daftarRw' => $daftarRw,
        ]);
    }
}
