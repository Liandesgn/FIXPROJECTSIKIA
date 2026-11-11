<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\PemeriksaanIbu;
use App\Models\PemeriksaanPosyandu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load([
            'ibu.wilayahKerja',
            'ibu.anak',
            'wilayahKerja',
        ]);

        $profil = $user->ibu;

        $wilayah = $profil?->wilayahKerja
            ?? $user->wilayahKerja;

        $bidanWilayah = $wilayah?->bidan;
        $kaderWilayah = $wilayah?->kaders()->first();

        // Data anak milik orang tua.
        $anakList = $profil?->anak ?? collect();

        // Pemeriksaan ibu terakhir.
        $pemeriksaanIbuTerakhir = $profil
            ? PemeriksaanIbu::where('ibu_id', $profil->id)
                ->orderByDesc('tgl_pemeriksaan')
                ->orderByDesc('id')
                ->first()
            : null;

        // Pemeriksaan Posyandu terakhir untuk setiap anak.
        $pemeriksaanAnakTerakhir = collect();

        if ($anakList->isNotEmpty()) {
            $pemeriksaanAnakTerakhir = PemeriksaanPosyandu::with('kegiatan')
                ->whereIn('anak_id', $anakList->pluck('id'))
                ->get()
                ->sortByDesc(function ($pemeriksaan) {
                    $tanggal = $pemeriksaan->kegiatan?->tanggal;

                    return ($tanggal?->format('Y-m-d') ?? '')
                        . '-'
                        . str_pad((string) $pemeriksaan->id, 12, '0', STR_PAD_LEFT);
                })
                ->groupBy('anak_id')
                ->map(fn ($riwayat) => $riwayat->first());
        }

        // Jadwal Posyandu di wilayah orang tua.
        $jadwalPosyandu = $wilayah
            ? $wilayah->kegiatanPosyandu()
                ->mendatang()
                ->get()
            : collect();

        return view('orang-tua.dashboard', [
            'user' => $user,
            'bidanWilayah' => $bidanWilayah,
            'kaderWilayah' => $kaderWilayah,
            'anakList' => $anakList,
            'jadwalPosyandu' => $jadwalPosyandu,
            'pemeriksaanIbuTerakhir' => $pemeriksaanIbuTerakhir,
            'pemeriksaanAnakTerakhir' => $pemeriksaanAnakTerakhir,
        ]);
    }
}
