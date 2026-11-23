<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Rw;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    /**
     * Menampilkan jadwal Posyandu sesuai wilayah kerja Kader.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Mengambil data Kader beserta wilayah kerjanya.
        $kader = $user->kader()
            ->with('wilayahKerja.kelurahanDesa')
            ->firstOrFail();

        $wilayah = $kader->wilayahKerja;
        $kelurahanDesaId = $wilayah?->kelurahan_desa_id;

        // Mengambil daftar RW dan RT dalam wilayah kerja Kader.
        $daftarRw = $kelurahanDesaId
            ? Rw::with('rt')
                ->where('kelurahan_desa_id', $kelurahanDesaId)
                ->orderBy('nomor_rw')
                ->get()
            : collect();

        // Mengambil nilai filter dari halaman.
        $cari = $request->input('cari');
        $rwRt = $request->input('rw_rt');
        $bulan = $request->input('bulan');

        // Query dasar: jadwal Posyandu di kelurahan/desa Kader.
        $query = Jadwal::with([
            'jadwalPosyandu.kegiatan.rw.kelurahanDesa',
            'jadwalPosyandu.bidan.user',
            'jadwalPosyandu.kader.user',
        ]);

        if ($kelurahanDesaId) {
            $query->whereHas(
                'jadwalPosyandu.kegiatan.rw',
                function ($q) use ($kelurahanDesaId) {
                    $q->where('kelurahan_desa_id', $kelurahanDesaId);
                }
            );
        } else {
            $query->whereRaw('1 = 0');
        }

        // Filter pencarian nama/lokasi Posyandu.
        if (!empty($cari)) {
            $query->where(function ($q) use ($cari) {
                $q->where('lokasi', 'like', "%{$cari}%")
                    ->orWhereHas(
                        'jadwalPosyandu.kegiatan',
                        function ($kegiatan) use ($cari) {
                            $kegiatan->where('lokasi', 'like', "%{$cari}%")
                                ->orWhere('jenis_kegiatan', 'like', "%{$cari}%");
                        }
                    );
            });
        }

        // Filter RW/RT gabungan.
        // Format nilai dropdown: ID_RW:ID_RT.
        // Jadwal tetap difilter berdasarkan RW karena tersimpan per RW.
        if (!empty($rwRt)) {
            $bagian = explode(':', $rwRt);

            if (
                count($bagian) === 2
                && ctype_digit($bagian[0])
                && ctype_digit($bagian[1])
            ) {
                $rwId = (int) $bagian[0];

                $query->whereHas(
                    'jadwalPosyandu.kegiatan',
                    function ($q) use ($rwId) {
                        $q->where('rw_id', $rwId);
                    }
                );
            }
        }

        // Filter bulan.
        if (!empty($bulan) && ctype_digit((string) $bulan)) {
            $bulanAngka = (int) $bulan;

            if ($bulanAngka >= 1 && $bulanAngka <= 12) {
                $query->whereMonth('tanggal', $bulanAngka);
            }
        }

        // Mengambil jadwal sesuai filter.
        $jadwals = $query->get();

        // Jadwal hari ini dan mendatang.
        $jadwalMendatang = $jadwals
            ->filter(function ($jadwal) {
                return $jadwal->tanggal
                    && $jadwal->tanggal->toDateString() >= today()->toDateString();
            })
            ->sortBy(function ($jadwal) {
                return $jadwal->tanggal->format('Y-m-d')
                    . ' ' . ($jadwal->waktu ?? '00:00:00');
            })
            ->values();

        // Riwayat jadwal yang sudah lewat.
        $riwayatJadwal = $jadwals
            ->filter(function ($jadwal) {
                return $jadwal->tanggal
                    && $jadwal->tanggal->toDateString() < today()->toDateString();
            })
            ->sortByDesc(function ($jadwal) {
                return $jadwal->tanggal->format('Y-m-d')
                    . ' ' . ($jadwal->waktu ?? '00:00:00');
            })
            ->values();

        return view('kader.jadwal.index', [
            'kader' => $kader,
            'wilayah' => $wilayah,
            'daftarRw' => $daftarRw,
            'jadwalMendatang' => $jadwalMendatang,
            'riwayatJadwal' => $riwayatJadwal,
            'cari' => $cari,
            'rwRt' => $rwRt,
            'bulan' => $bulan,
        ]);
    }
}
