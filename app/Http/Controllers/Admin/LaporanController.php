<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Ibu;
use App\Models\KegiatanPosyandu;
use App\Models\PemeriksaanIbu;
use App\Models\PemeriksaanPosyandu;
use App\Models\WilayahKerja;
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

        // Admin hanya dapat melihat wilayah yang menjadi tanggung jawabnya.
        $wilayahId = $request->user()
            ->loadMissing('admin')
            ->managedWilayahId();

        abort_unless(
            $wilayahId,
            403,
            'Akun Admin belum memiliki wilayah kerja.'
        );

        // Paksa filter wilayah sesuai wilayah Admin yang login.
        $request->merge(['wilayah_id' => $wilayahId]);

        $periode = $request->input('periode', now()->format('Y-m'));

        $tanggalMulai = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
        $tanggalAkhir = Carbon::createFromFormat('Y-m', $periode)->endOfMonth();

        $wilayah = WilayahKerja::whereKey($wilayahId)->get();

        $daftarWilayah = WilayahKerja::whereKey($wilayahId)->get();

        // Rekap kesehatan per wilayah.
        $rekapWilayah = $daftarWilayah->map(function ($wilayahItem) use (
            $tanggalMulai,
            $tanggalAkhir
        ) {
            $jumlahIbuHamil = Ibu::where('wilayah_kerja_id', $wilayahItem->id)
            ->whereBetween('tanggal_pemeriksaan_awal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
            ->count();

            $jumlahAnak = Anak::whereBetween('created_at', [
            $tanggalMulai->copy()->startOfDay(),
            $tanggalAkhir->copy()->endOfDay(),
        ])
            ->whereHas('ibu', function ($query) use ($wilayahItem) {
                $query->where('wilayah_kerja_id', $wilayahItem->id);
            })
            ->count();

            // Jumlah orang unik yang diperiksa.
            $ibuDiperiksa = PemeriksaanIbu::whereBetween('tgl_pemeriksaan', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
                ->whereHas('ibu', function ($query) use ($wilayahItem) {
                    $query->where('wilayah_kerja_id', $wilayahItem->id);
                })
                ->distinct()
                ->count('ibu_id');

            $anakDiperiksa = PemeriksaanPosyandu::whereHas(
                'kegiatan',
                function ($query) use ($tanggalMulai, $tanggalAkhir, $wilayahItem) {
                    $query->whereBetween('tanggal', [
                        $tanggalMulai->toDateString(),
                        $tanggalAkhir->toDateString(),
                    ])->where('id_wilayah', $wilayahItem->id);
                }
            )
                ->distinct()
                ->count('anak_id');

            // Jumlah orang unik yang perlu perhatian.
            $ibuPerluPerhatian = PemeriksaanIbu::whereBetween('tgl_pemeriksaan', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
                ->where('status_kesehatan', PemeriksaanIbu::PERLU_PERHATIAN)
                ->whereHas('ibu', function ($query) use ($wilayahItem) {
                    $query->where('wilayah_kerja_id', $wilayahItem->id);
                })
                ->distinct()
                ->count('ibu_id');

            $anakPerluPerhatian = PemeriksaanPosyandu::where(
                'status_kesehatan',
                PemeriksaanPosyandu::PERLU_PERHATIAN
            )
                ->whereHas(
                    'kegiatan',
                    function ($query) use ($tanggalMulai, $tanggalAkhir, $wilayahItem) {
                        $query->whereBetween('tanggal', [
                            $tanggalMulai->toDateString(),
                            $tanggalAkhir->toDateString(),
                        ])->where('id_wilayah', $wilayahItem->id);
                    }
                )
                ->distinct()
                ->count('anak_id');

            $diperiksa = $ibuDiperiksa + $anakDiperiksa;
            $perluPerhatian = $ibuPerluPerhatian + $anakPerluPerhatian;

            $persentase = $diperiksa > 0
                ? round(($perluPerhatian / $diperiksa) * 100, 1)
                : 0;

            return (object) [
                'nama_wilayah' => $wilayahItem->nama_wilayah,
                'ibu_hamil' => $jumlahIbuHamil,
                'anak' => $jumlahAnak,
                'diperiksa' => $diperiksa,
                'perlu_perhatian' => $perluPerhatian,
                'persentase' => $persentase,
            ];
        });

        // Data grafik status pemeriksaan Ibu.
        $grafikStatus = [
            'ibu_normal' => PemeriksaanIbu::whereBetween('tgl_pemeriksaan', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
                ->where('status_kesehatan', PemeriksaanIbu::NORMAL)
                ->whereHas('ibu', function ($query) use ($wilayahId) {
                    $query->where('wilayah_kerja_id', $wilayahId);
                })
                ->count(),

            'ibu_perhatian' => PemeriksaanIbu::whereBetween('tgl_pemeriksaan', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
                ->where('status_kesehatan', PemeriksaanIbu::PERLU_PERHATIAN)
                ->whereHas('ibu', function ($query) use ($wilayahId) {
                    $query->where('wilayah_kerja_id', $wilayahId);
                })
                ->count(),

            // Data grafik status pemeriksaan Anak.
            'anak_normal' => PemeriksaanPosyandu::where(
                'status_kesehatan',
                PemeriksaanPosyandu::NORMAL
            )
                ->whereHas('kegiatan', function ($query) use (
                    $tanggalMulai,
                    $tanggalAkhir,
                    $wilayahId
                ) {
                    $query->whereBetween('tanggal', [
                        $tanggalMulai->toDateString(),
                        $tanggalAkhir->toDateString(),
                    ])->where('id_wilayah', $wilayahId);
                })
                ->count(),

            'anak_perhatian' => PemeriksaanPosyandu::where(
                'status_kesehatan',
                PemeriksaanPosyandu::PERLU_PERHATIAN
            )
                ->whereHas('kegiatan', function ($query) use (
                    $tanggalMulai,
                    $tanggalAkhir,
                    $wilayahId
                ) {
                    $query->whereBetween('tanggal', [
                        $tanggalMulai->toDateString(),
                        $tanggalAkhir->toDateString(),
                    ])->where('id_wilayah', $wilayahId);
                })
                ->count(),
        ];

      $periode = $request->input('periode', now()->format('Y-m'));

        try {
            $tanggalPeriode = \Carbon\Carbon::createFromFormat('Y-m', $periode);
        } catch (\Throwable $e) {
            $tanggalPeriode = now();
        }

        $bulan = $tanggalPeriode->month;
        $tahun = $tanggalPeriode->year;

        // Laporan kegiatan Posyandu.
        $kegiatanPosyandu = KegiatanPosyandu::query()
            ->with('wilayah')
            ->addSelect([
                'anak_diperiksa' => PemeriksaanPosyandu::query()
                    ->selectRaw('COUNT(DISTINCT pemeriksaan_posyandu.anak_id)')
                    ->whereColumn(
                        'pemeriksaan_posyandu.kegiatan_posyandu_id',
                        'kegiatan_posyandu.id'
                    ),
            ])
            ->where('id_wilayah', $wilayahId)
            ->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
            ->orderBy('tanggal')
            ->get();
    

        // Data risiko Ibu: pemeriksaan terakhir setiap Ibu.
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
            ->whereHas('ibu', function ($query) use ($wilayahId) {
                $query->where('wilayah_kerja_id', $wilayahId);
            })
            ->get();

        // Data risiko Anak: pemeriksaan terakhir setiap Anak.
        $risikoAnak = PemeriksaanPosyandu::with([
            'anak.ibu.wilayahKerja',
            'kegiatan',
        ])
            ->terakhirPerAnak()
            ->where('status_kesehatan', PemeriksaanPosyandu::PERLU_PERHATIAN)
            ->whereHas('kegiatan', function ($query) use ($tanggalMulai, $tanggalAkhir) {
                $query->whereBetween('tanggal', [
                    $tanggalMulai->toDateString(),
                    $tanggalAkhir->toDateString(),
                ]);
            })
            ->whereHas('anak.ibu', function ($query) use ($wilayahId) {
                $query->where('wilayah_kerja_id', $wilayahId);
            })
            ->get();

        // Gabungkan data risiko Ibu dan Anak.
        $dataRisiko = collect();

        foreach ($risikoAnak as $pemeriksaan) {
            $dataRisiko->push((object) [
                'nama' => $pemeriksaan->anak?->nama_anak ?? '-',
                'jenis' => 'Anak',
                'wilayah' => $pemeriksaan->anak?->ibu?->wilayahKerja?->nama_wilayah ?? '-',
                'tanggal' => $pemeriksaan->kegiatan?->tanggal,
                'indikator' => 'BB: '.($pemeriksaan->berat_badan ?? '-').' kg, TB: '.($pemeriksaan->tinggi_badan ?? '-').' cm',
                'penanda' => 'Perlu Perhatian',
            ]);
        }

        foreach ($risikoIbu as $pemeriksaan) {
            $dataRisiko->push((object) [
                'nama' => $pemeriksaan->ibu?->user?->name ?? '-',
                'jenis' => 'Ibu',
                'wilayah' => $pemeriksaan->ibu?->wilayahKerja?->nama_wilayah ?? '-',
                'tanggal' => $pemeriksaan->tgl_pemeriksaan,
                'indikator' => 'Status pemeriksaan: Perlu Perhatian',
                'penanda' => 'Perlu Perhatian',
            ]);
        }

        // Rekap jumlah catatan pemeriksaan pada periode terpilih.
        $jumlahPemeriksaanIbu = PemeriksaanIbu::whereBetween('tgl_pemeriksaan', [
            $tanggalMulai->toDateString(),
            $tanggalAkhir->toDateString(),
        ])
            ->whereHas('ibu', function ($query) use ($wilayahId) {
                $query->where('wilayah_kerja_id', $wilayahId);
            })
            ->count();

        $jumlahPemeriksaanAnak = PemeriksaanPosyandu::whereHas(
            'kegiatan',
            function ($query) use ($tanggalMulai, $tanggalAkhir, $wilayahId) {
                $query->whereBetween('tanggal', [
                    $tanggalMulai->toDateString(),
                    $tanggalAkhir->toDateString(),
                ])->where('id_wilayah', $wilayahId);
            }
        )->count();

        $rekapPemeriksaan = collect([
            (object) [
                'bulan' => $tanggalMulai->translatedFormat('F Y'),
                'pemeriksaan_ibu' => $jumlahPemeriksaanIbu,
                'pemeriksaan_anak' => $jumlahPemeriksaanAnak,
                'kegiatan_posyandu' => $kegiatanPosyandu->count(),
            ],
        ]);

        $ringkasan = [
            'ibu_hamil' => $rekapWilayah->sum('ibu_hamil'),
            'anak' => $rekapWilayah->sum('anak'),
            'perlu_perhatian' => $rekapWilayah->sum('perlu_perhatian'),
        ];


        $namaWilayah = $wilayah->first()?->nama_wilayah ?? 'Wilayah belum ditetapkan';

        $ringkasanKegiatan = [
            'jumlah_kegiatan' => $kegiatanPosyandu->count(),
            'anak_diperiksa' => $kegiatanPosyandu->sum('anak_diperiksa'),
        ];

       return view('admin.laporan.index', compact(
            'wilayah',
            'namaWilayah',
            'rekapWilayah',
            'ringkasan',
            'ringkasanKegiatan',
            'periode',
            'grafikStatus',
            'kegiatanPosyandu',
            'dataRisiko',
            'rekapPemeriksaan'
        ))->with('base', 'admin');
    }
}