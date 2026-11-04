<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Enums\StatusKehamilanEnum;
use App\Models\Bidan;
use App\Models\Ibu;
use App\Models\Anak;
use App\Models\KegiatanPosyandu;
use App\Models\PemeriksaanIbu;
use App\Models\PemeriksaanPosyandu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'periode' => ['nullable', 'date_format:Y-m'],
        ]);

        // Ambil wilayah kerja Bidan yang sedang login
        $bidan = Bidan::with('wilayahKerja')
            ->where('user_id', auth()->id())
            ->firstOrFail();

        abort_if(
            ! $bidan->wilayah_kerja_id,
            403,
            'Akun Bidan belum memiliki wilayah kerja.'
        );

        $wilayahId = $bidan->wilayah_kerja_id;

        $periode = $request->input('periode', now()->format('Y-m'));

        $tanggalMulai = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
        $tanggalAkhir = Carbon::createFromFormat('Y-m', $periode)->endOfMonth();

        // Bidan hanya melihat wilayah kerjanya sendiri
        $wilayah = collect([$bidan->wilayahKerja]);

        // Rekap kesehatan wilayah
        $jumlahIbuHamil = Ibu::where('wilayah_kerja_id', $wilayahId)
            ->where('status_kehamilan', StatusKehamilanEnum::Hamil->value)
            ->count();

        $jumlahAnak = Anak::whereHas('ibu', function ($query) use ($wilayahId) {
            $query->where('wilayah_kerja_id', $wilayahId);
        })->count();

        $ibuDiperiksa = PemeriksaanIbu::whereBetween('tgl_pemeriksaan', [
            $tanggalMulai->toDateString(),
            $tanggalAkhir->toDateString(),
        ])
            ->whereHas('ibu', fn ($query) =>
                $query->where('wilayah_kerja_id', $wilayahId)
            )
            ->distinct()
            ->count('ibu_id');

        $anakDiperiksa = PemeriksaanPosyandu::whereHas('kegiatan', function ($query) use (
            $tanggalMulai, $tanggalAkhir, $wilayahId
        ) {
            $query->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])->where('id_wilayah', $wilayahId);
        })
            ->distinct()
            ->count('anak_id');

        $ibuPerhatian = PemeriksaanIbu::whereBetween('tgl_pemeriksaan', [
            $tanggalMulai->toDateString(),
            $tanggalAkhir->toDateString(),
        ])
            ->where('status_kesehatan', PemeriksaanIbu::PERLU_PERHATIAN)
            ->whereHas('ibu', fn ($query) =>
                $query->where('wilayah_kerja_id', $wilayahId)
            )
            ->distinct()
            ->count('ibu_id');

        $anakPerhatian = PemeriksaanPosyandu::where(
            'status_kesehatan',
            PemeriksaanPosyandu::PERLU_PERHATIAN
        )
            ->whereHas('kegiatan', function ($query) use (
                $tanggalMulai, $tanggalAkhir, $wilayahId
            ) {
                $query->whereBetween('tanggal', [
                    $tanggalMulai->toDateString(),
                    $tanggalAkhir->toDateString(),
                ])->where('id_wilayah', $wilayahId);
            })
            ->distinct()
            ->count('anak_id');

        $diperiksa = $ibuDiperiksa + $anakDiperiksa;
        $perluPerhatian = $ibuPerhatian + $anakPerhatian;

        $persentase = $diperiksa > 0
            ? round(($perluPerhatian / $diperiksa) * 100, 1)
            : 0;

        $rekapWilayah = collect([
            (object) [
                'nama_wilayah' => $bidan->wilayahKerja->nama_wilayah,
                'ibu_hamil' => $jumlahIbuHamil,
                'anak' => $jumlahAnak,
                'diperiksa' => $diperiksa,
                'perlu_perhatian' => $perluPerhatian,
                'persentase' => $persentase,
            ],
        ]);

        $ringkasan = [
            'ibu_hamil' => $jumlahIbuHamil,
            'anak' => $jumlahAnak,
            'perlu_perhatian' => $perluPerhatian,
        ];

        // Kegiatan Posyandu
        $kegiatanPosyandu = KegiatanPosyandu::with('wilayah')
            ->withCount('pemeriksaan')
            ->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
            ->diWilayah($wilayahId)
            ->orderBy('tanggal')
            ->get();

        // Data risiko ibu
        $risikoIbu = PemeriksaanIbu::with([
            'ibu.user',
            'ibu.wilayahKerja',
        ])
            ->terakhirPerIbu()
            ->where('status_kesehatan', PemeriksaanIbu::PERLU_PERHATIAN)
            ->whereBetween('tgl_pemeriksaan', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
            ->whereHas('ibu', fn ($query) =>
                $query->where('wilayah_kerja_id', $wilayahId)
            )
            ->get();

        // Data risiko anak
        $risikoAnak = PemeriksaanPosyandu::with([
            'anak.ibu.wilayahKerja',
            'kegiatan',
        ])
            ->terakhirPerAnak()
            ->where('status_kesehatan', PemeriksaanPosyandu::PERLU_PERHATIAN)
            ->whereHas('kegiatan', fn ($query) =>
                $query->whereBetween('tanggal', [
                    $tanggalMulai->toDateString(),
                    $tanggalAkhir->toDateString(),
                ])
            )
            ->whereHas('anak.ibu', fn ($query) =>
                $query->where('wilayah_kerja_id', $wilayahId)
            )
            ->get();

        $dataRisiko = collect();

        foreach ($risikoAnak as $item) {
            $dataRisiko->push((object) [
                'nama' => $item->anak?->nama_anak ?? '-',
                'jenis' => 'Anak',
                'wilayah' => $item->anak?->ibu?->wilayahKerja?->nama_wilayah ?? '-',
                'tanggal' => $item->kegiatan?->tanggal,
                'indikator' => 'BB: '.($item->berat_badan ?? '-').' kg, TB: '.($item->tinggi_badan ?? '-').' cm',
                'penanda' => 'Perlu Perhatian',
            ]);
        }

        foreach ($risikoIbu as $item) {
            $dataRisiko->push((object) [
                'nama' => $item->ibu?->user?->name ?? '-',
                'jenis' => 'Ibu',
                'wilayah' => $item->ibu?->wilayahKerja?->nama_wilayah ?? '-',
                'tanggal' => $item->tgl_pemeriksaan,
                'indikator' => 'Status pemeriksaan: Perlu Perhatian',
                'penanda' => 'Perlu Perhatian',
            ]);
        }

        // Rekap pemeriksaan
        $jumlahPemeriksaanIbu = PemeriksaanIbu::whereBetween(
            'tgl_pemeriksaan',
            [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()]
        )
            ->whereHas('ibu', fn ($query) =>
                $query->where('wilayah_kerja_id', $wilayahId)
            )
            ->count();

        $jumlahPemeriksaanAnak = PemeriksaanPosyandu::whereHas(
            'kegiatan',
            fn ($query) => $query->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])->where('id_wilayah', $wilayahId)
        )->count();

        $rekapPemeriksaan = collect([
            (object) [
                'bulan' => $tanggalMulai->translatedFormat('F Y'),
                'pemeriksaan_ibu' => $jumlahPemeriksaanIbu,
                'pemeriksaan_anak' => $jumlahPemeriksaanAnak,
                'kegiatan_posyandu' => $kegiatanPosyandu->count(),
            ],
        ]);

        return view('admin.laporan.index', compact(
            'wilayah',
            'rekapWilayah',
            'ringkasan',
            'periode',
            'kegiatanPosyandu',
            'dataRisiko',
            'rekapPemeriksaan'
        ))->with('base', 'bidan');
    }
}