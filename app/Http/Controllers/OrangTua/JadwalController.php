<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    /**
     * Menampilkan jadwal sesuai wilayah tempat tinggal Ibu.
     */
    public function index(Request $request): View
    {
        // Mengambil profil Ibu yang sedang login.
        $ibu = $request->user()
            ->ibu()
            ->with(['rt.rw.kelurahanDesa'])
            ->first();

        // Default data kosong jika profil Ibu belum tersedia.
        $jadwalPemeriksaan = collect();
        $jadwalPosyandu = collect();

        if ($ibu) {
            // Jadwal Pemeriksaan berdasarkan wilayah kerja Ibu.
            if ($ibu->wilayah_kerja_id) {
                $jadwalPemeriksaan = Jadwal::query()
                    ->whereDate('tanggal', '>=', today())
                    ->whereHas('jadwalPemeriksaan', function ($query) use ($ibu) {
                        $query->where(
                            'id_wilayah_kerja',
                            $ibu->wilayah_kerja_id
                        );
                    })
                    ->with([
                        'jadwalPemeriksaan.wilayahKerja.kelurahanDesa',
                        'jadwalPemeriksaan.bidan.user',
                    ])
                    ->orderBy('tanggal')
                    ->orderBy('waktu')
                    ->get();
            }

            // Mengambil RW berdasarkan RT tempat tinggal Ibu.
            $rwId = $ibu->rt?->rw_id;

            // Jadwal Posyandu berdasarkan RW Ibu.
            if ($rwId) {
                $jadwalPosyandu = Jadwal::query()
                    ->whereDate('tanggal', '>=', today())
                    ->whereHas(
                        'jadwalPosyandu.kegiatan',
                        function ($query) use ($rwId) {
                            $query->where('rw_id', $rwId);
                        }
                    )
                    ->with([
                        'jadwalPosyandu.kegiatan.rw.kelurahanDesa',
                        'jadwalPosyandu.bidan.user',
                        'jadwalPosyandu.kader.user',
                    ])
                    ->orderBy('tanggal')
                    ->orderBy('waktu')
                    ->get();
            }
        }

        return view('orang-tua.jadwal.index', [
            'ibu' => $ibu,
            'jadwalPemeriksaan' => $jadwalPemeriksaan,
            'jadwalPosyandu' => $jadwalPosyandu,
        ]);
    }
}

