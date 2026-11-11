<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Ibu;
use App\Models\KegiatanPosyandu;
use App\Models\PemeriksaanIbu;
use App\Models\PemeriksaanPosyandu;
use App\Models\WilayahKerja;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanPdfController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'tab' => ['required', 'in:rekap-kesehatan,kegiatan-posyandu,data-risiko,rekap-pemeriksaan'],
            'periode' => ['nullable', 'date_format:Y-m'],
            'wilayah_id' => ['nullable', 'exists:wilayah_kerja,id'],
        ]);

        // DITAMBAH: Bidan memakai wilayah dari data bidan; Admin tetap memakai managedWilayahId()
        $user = $request->user();

        $wilayahId = $user->hasRole('bidan')
            ? $user->loadMissing('bidan')->bidan?->wilayah_kerja_id
            : $user->loadMissing('admin')->managedWilayahId();

        abort_unless($wilayahId, 403, 'Akun belum memiliki wilayah kerja.');

        $request->merge(['wilayah_id' => $wilayahId]);

        $tab = $request->input('tab');
        $periode = $request->input('periode', now()->format('Y-m'));

        $mulai = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
        $akhir = Carbon::createFromFormat('Y-m', $periode)->endOfMonth();

        $namaWilayah = $request->filled('wilayah_id')
            ? WilayahKerja::find($request->wilayah_id)?->nama_wilayah
            : 'Semua Wilayah';

        $judul = match ($tab) {
            'rekap-kesehatan' => 'Rekap Kesehatan',
            'kegiatan-posyandu' => 'Kegiatan Posyandu',
            'data-risiko' => 'Data Risiko',
            'rekap-pemeriksaan' => 'Rekap Pemeriksaan',
        };

        $data = collect();
        $grafikStatus = null; // DITAMBAH: hanya diisi untuk tab rekap-kesehatan

        if ($tab === 'rekap-kesehatan') {
            $data = WilayahKerja::query()
                ->when($request->filled('wilayah_id'),
                    fn ($q) => $q->where('id', $request->wilayah_id))
                ->orderBy('nama_wilayah')
                ->get()
                ->map(function ($wilayah) use ($mulai, $akhir) {
                    $ibuHamil = Ibu::where('wilayah_kerja_id', $wilayah->id)
                        ->where('status_kehamilan', \App\Enums\StatusKehamilanEnum::Hamil->value)
                        ->count();

                    $anak = Anak::whereHas('ibu', fn ($q) =>
                        $q->where('wilayah_kerja_id', $wilayah->id)
                    )->count();

                    $ibuDiperiksa = PemeriksaanIbu::whereBetween(
                        'tgl_pemeriksaan', [$mulai, $akhir]
                    )->whereHas('ibu', fn ($q) =>
                        $q->where('wilayah_kerja_id', $wilayah->id)
                    )->distinct()->count('ibu_id');

                    $anakDiperiksa = PemeriksaanPosyandu::whereHas('kegiatan', fn ($q) =>
                        $q->whereBetween('tanggal', [
                            $mulai->toDateString(), $akhir->toDateString()
                        ])
                    )->whereHas('anak.ibu', fn ($q) =>
                        $q->where('wilayah_kerja_id', $wilayah->id)
                    )->distinct()->count('anak_id');

                    $ibuRisiko = PemeriksaanIbu::whereBetween(
                        'tgl_pemeriksaan', [$mulai, $akhir]
                    )->where('status_kesehatan', PemeriksaanIbu::PERLU_PERHATIAN)
                    ->whereHas('ibu', fn ($q) =>
                        $q->where('wilayah_kerja_id', $wilayah->id)
                    )->distinct()->count('ibu_id');

                    $anakRisiko = PemeriksaanPosyandu::where(
                        'status_kesehatan', PemeriksaanPosyandu::PERLU_PERHATIAN
                    )->whereHas('kegiatan', fn ($q) =>
                        $q->whereBetween('tanggal', [
                            $mulai->toDateString(), $akhir->toDateString()
                        ])
                    )->whereHas('anak.ibu', fn ($q) =>
                        $q->where('wilayah_kerja_id', $wilayah->id)
                    )->distinct()->count('anak_id');

                    $diperiksa = $ibuDiperiksa + $anakDiperiksa;
                    $risiko = $ibuRisiko + $anakRisiko;

                    return (object) [
                        'wilayah' => $wilayah->nama_wilayah,
                        'ibu_hamil' => $ibuHamil,
                        'anak' => $anak,
                        'diperiksa' => $diperiksa,
                        'risiko' => $risiko,
                        'persentase' => $diperiksa > 0
                            ? round(($risiko / $diperiksa) * 100, 1) : 0,

                        // DITAMBAH: angka rinci untuk tabel status Normal / Perlu Perhatian
                        'ibu_diperiksa' => $ibuDiperiksa,
                        'ibu_risiko' => $ibuRisiko,
                        'anak_diperiksa' => $anakDiperiksa,
                        'anak_risiko' => $anakRisiko,
                    ];
                });

            // DITAMBAH: status Normal / Perlu Perhatian untuk Ibu dan Anak
            $grafikStatus = [
                'ibu_normal' => max($data->sum('ibu_diperiksa') - $data->sum('ibu_risiko'), 0),
                'ibu_perhatian' => $data->sum('ibu_risiko'),
                'anak_normal' => max($data->sum('anak_diperiksa') - $data->sum('anak_risiko'), 0),
                'anak_perhatian' => $data->sum('anak_risiko'),
            ];
        }

        if ($tab === 'kegiatan-posyandu') {
            $data = KegiatanPosyandu::with('wilayah')
                ->withCount('pemeriksaan')
                ->whereBetween('tanggal', [
                    $mulai->toDateString(), $akhir->toDateString()
                ])
                ->when($request->filled('wilayah_id'), fn ($q) =>
                    $q->where('id_wilayah', $request->wilayah_id)
                )
                ->orderBy('tanggal')
                ->get();
        }

        if ($tab === 'data-risiko') {
            $ibu = PemeriksaanIbu::with(['ibu.user', 'ibu.wilayahKerja'])
                ->terakhirPerIbu()
                ->where('status_kesehatan', PemeriksaanIbu::PERLU_PERHATIAN)
                ->whereBetween('tgl_pemeriksaan', [$mulai, $akhir])
                ->whereHas('ibu', fn ($q) =>
                    $q->when($request->filled('wilayah_id'),
                        fn ($w) => $w->where('wilayah_kerja_id', $request->wilayah_id))
                )
                ->get();

            foreach ($ibu as $item) {
                $data->push((object) [
                    'nama' => $item->ibu?->user?->name ?? '-',
                    'jenis' => 'Ibu',
                    'wilayah' => $item->ibu?->wilayahKerja?->nama_wilayah ?? '-',
                    'tanggal' => $item->tgl_pemeriksaan,
                    'indikator' => 'Status pemeriksaan: Perlu Perhatian',
                    'penanda' => ['Perlu Perhatian'], // DITAMBAH
                ]);
            }

            $anak = PemeriksaanPosyandu::with(['anak.ibu.wilayahKerja', 'kegiatan'])
                ->terakhirPerAnak()
                ->where('status_kesehatan', PemeriksaanPosyandu::PERLU_PERHATIAN)
                ->whereHas('kegiatan', fn ($q) =>
                    $q->whereBetween('tanggal', [
                        $mulai->toDateString(), $akhir->toDateString()
                    ])
                )
                ->whereHas('anak.ibu', fn ($q) =>
                    $q->when($request->filled('wilayah_id'),
                        fn ($w) => $w->where('wilayah_kerja_id', $request->wilayah_id))
                )
                ->get();

            foreach ($anak as $item) {
                $data->push((object) [
                    'nama' => $item->anak?->nama_anak ?? '-',
                    'jenis' => 'Anak',
                    'wilayah' => $item->anak?->ibu?->wilayahKerja?->nama_wilayah ?? '-',
                    'tanggal' => $item->kegiatan?->tanggal,
                    'indikator' => 'BB: '.($item->berat_badan ?? '-').' kg, TB: '.($item->tinggi_badan ?? '-').' cm',
                    'penanda' => ['Perlu Perhatian'], // DITAMBAH
                ]);
            }
        }

        if ($tab === 'rekap-pemeriksaan') {
            $ibu = PemeriksaanIbu::whereBetween('tgl_pemeriksaan', [$mulai, $akhir])
                ->whereHas('ibu', fn ($q) =>
                    $q->when($request->filled('wilayah_id'),
                        fn ($w) => $w->where('wilayah_kerja_id', $request->wilayah_id))
                )->count();

            $anak = PemeriksaanPosyandu::whereHas('kegiatan', fn ($q) =>
                $q->whereBetween('tanggal', [
                    $mulai->toDateString(), $akhir->toDateString()
                ])->when($request->filled('wilayah_id'), fn ($k) =>
                    $k->where('id_wilayah', $request->wilayah_id)
                )
            )->count();

            $kegiatan = KegiatanPosyandu::whereBetween('tanggal', [
                $mulai->toDateString(), $akhir->toDateString()
            ])->when($request->filled('wilayah_id'), fn ($q) =>
                $q->where('id_wilayah', $request->wilayah_id)
            )->count();

            $data->push((object) [
                'bulan' => $mulai->translatedFormat('F Y'),
                'ibu' => $ibu,
                'anak' => $anak,
                'kegiatan' => $kegiatan,
                'total' => $ibu + $anak,
            ]);
        }

        $pdf = Pdf::loadView('admin.laporan.pdf', compact(
            'data', 'judul', 'periode', 'namaWilayah', 'tab', 'grafikStatus' // DITAMBAH: grafikStatus
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-'.$tab.'-'.$periode.'.pdf');
    }
}