<?php

namespace App\Http\Controllers\SuperAdmin;

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
        $data = $this->buildData($request);

        return view('superadmin.laporan.index', $data);
    }

    /**
     * Menyiapkan data laporan untuk halaman dan PDF.
     */
    public function buildData(Request $request): array
    {
        $validated = $request->validate([
            'periode' => ['nullable', 'date_format:Y-m'],
            'wilayah_id' => ['nullable', 'integer', 'exists:wilayah_kerja,id'],
            'tab' => [
                'nullable',
                'in:rekap-kesehatan,kegiatan-posyandu,data-risiko,rekap-pemeriksaan',
            ],
        ]);

        $periode = $validated['periode'] ?? now()->format('Y-m');
        $wilayahId = $validated['wilayah_id'] ?? null;
        $tab = $validated['tab'] ?? 'rekap-kesehatan';

        $tanggalMulai = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
        $tanggalAkhir = Carbon::createFromFormat('Y-m', $periode)->endOfMonth();

        // Super Admin dapat melihat semua wilayah atau satu wilayah.
        $wilayah = WilayahKerja::query()
            ->orderBy('nama_wilayah')
            ->get();

        $daftarWilayah = WilayahKerja::query()
            ->when($wilayahId, fn ($q) => $q->whereKey($wilayahId))
            ->orderBy('nama_wilayah')
            ->get();

        // 1. Rekap kesehatan setiap wilayah.
        $rekapWilayah = $daftarWilayah->map(function ($item) use (
            $tanggalMulai,
            $tanggalAkhir
        ) {
                $jumlahIbuHamil = Ibu::where('wilayah_kerja_id', $item->id)
                ->whereBetween('tanggal_pemeriksaan_awal', [
                    $tanggalMulai->toDateString(),
                    $tanggalAkhir->toDateString(),
                ])
                ->count();

            $jumlahAnak = Anak::whereBetween('created_at', [
                $tanggalMulai->copy()->startOfDay(),
                $tanggalAkhir->copy()->endOfDay(),
            ])
                ->whereHas(
                    'ibu',
                    fn ($q) => $q->where('wilayah_kerja_id', $item->id)
                )
                ->count();

            $ibuDiperiksa = PemeriksaanIbu::whereBetween(
                'tgl_pemeriksaan',
                [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()]
            )
                ->whereHas('ibu', fn ($q) => $q->where('wilayah_kerja_id', $item->id))
                ->distinct()
                ->count('ibu_id');

            $anakDiperiksa = PemeriksaanPosyandu::whereHas(
                'kegiatan',
                fn ($q) => $q->where('id_wilayah', $item->id)
                    ->whereBetween('tanggal', [
                        $tanggalMulai->toDateString(),
                        $tanggalAkhir->toDateString(),
                    ])
            )
                ->whereHas(
                    'anak.ibu',
                    fn ($q) => $q->where('wilayah_kerja_id', $item->id)
                )
                ->distinct()
                ->count('anak_id');

            $ibuRisiko = PemeriksaanIbu::whereBetween(
                'tgl_pemeriksaan',
                [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()]
            )
                ->where('status_kesehatan', PemeriksaanIbu::PERLU_PERHATIAN)
                ->whereHas('ibu', fn ($q) => $q->where('wilayah_kerja_id', $item->id))
                ->distinct()
                ->count('ibu_id');

            $anakRisiko = PemeriksaanPosyandu::where(
                'status_kesehatan',
                PemeriksaanPosyandu::PERLU_PERHATIAN
            )
                ->whereHas(
                    'kegiatan',
                    fn ($q) => $q->where('id_wilayah', $item->id)
                        ->whereBetween('tanggal', [
                            $tanggalMulai->toDateString(),
                            $tanggalAkhir->toDateString(),
                        ])
                )
                ->whereHas(
                    'anak.ibu',
                    fn ($q) => $q->where('wilayah_kerja_id', $item->id)
                )
                ->distinct()
                ->count('anak_id');

            $diperiksa = $ibuDiperiksa + $anakDiperiksa;
            $perluPerhatian = $ibuRisiko + $anakRisiko;

            return (object) [
                'nama_wilayah' => $item->nama_wilayah,
                'ibu_hamil' => $jumlahIbuHamil,
                'anak' => $jumlahAnak,
                'diperiksa' => $diperiksa,
                'perlu_perhatian' => $perluPerhatian,
                'persentase' => $diperiksa > 0
                    ? round(($perluPerhatian / $diperiksa) * 100, 1)
                    : 0,
            ];
        });

        // 2. Laporan kegiatan Posyandu.
        $kegiatanPosyandu = KegiatanPosyandu::with('wilayah')
            ->withCount('pemeriksaan')
            ->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
            ->when(
                $wilayahId,
                fn ($q) => $q->where('id_wilayah', $wilayahId)
            )
            ->orderBy('tanggal')
            ->get();

        // 3. Data risiko ibu berdasarkan pemeriksaan terakhir.
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
            ->whereHas('ibu', function ($q) use ($wilayahId) {
                $q->when(
                    $wilayahId,
                    fn ($ibuQuery) => $ibuQuery->where(
                        'wilayah_kerja_id',
                        $wilayahId
                    )
                );
            })
            ->get();

        // 4. Data risiko anak berdasarkan pemeriksaan terakhir.
        $risikoAnak = PemeriksaanPosyandu::with([
            'anak.ibu.wilayahKerja',
            'kegiatan',
        ])
            ->terakhirPerAnak()
            ->where('status_kesehatan', PemeriksaanPosyandu::PERLU_PERHATIAN)
            ->whereHas('kegiatan', function ($q) use (
                $tanggalMulai,
                $tanggalAkhir,
                $wilayahId
            ) {
                $q->whereBetween('tanggal', [
                    $tanggalMulai->toDateString(),
                    $tanggalAkhir->toDateString(),
                ])->when(
                    $wilayahId,
                    fn ($kegiatan) => $kegiatan->where('id_wilayah', $wilayahId)
                );
            })
            ->whereHas('anak.ibu', function ($q) use ($wilayahId) {
                $q->when(
                    $wilayahId,
                    fn ($ibuQuery) => $ibuQuery->where(
                        'wilayah_kerja_id',
                        $wilayahId
                    )
                );
            })
            ->get();

        $dataRisiko = collect();

        foreach ($risikoAnak as $pemeriksaan) {
            $dataRisiko->push((object) [
                'nama' => $pemeriksaan->anak?->nama_anak ?? '-',
                'jenis' => 'Anak',
                'wilayah' => $pemeriksaan->anak?->ibu?->wilayahKerja?->nama_wilayah ?? '-',
                'tanggal' => $pemeriksaan->kegiatan?->tanggal,
                'indikator' => 'BB: '.($pemeriksaan->berat_badan ?? '-')
                    .' kg, TB: '.($pemeriksaan->tinggi_badan ?? '-').' cm',
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

        // 5. Rekap pemeriksaan dan kegiatan pada bulan terpilih.
        $jumlahPemeriksaanIbu = PemeriksaanIbu::whereBetween(
            'tgl_pemeriksaan',
            [$tanggalMulai->toDateString(), $tanggalAkhir->toDateString()]
        )
            ->whereHas('ibu', function ($q) use ($wilayahId) {
                $q->when(
                    $wilayahId,
                    fn ($ibuQuery) => $ibuQuery->where(
                        'wilayah_kerja_id',
                        $wilayahId
                    )
                );
            })
            ->count();

        $jumlahPemeriksaanAnak = PemeriksaanPosyandu::whereHas(
            'kegiatan',
            function ($q) use ($tanggalMulai, $tanggalAkhir, $wilayahId) {
                $q->whereBetween('tanggal', [
                    $tanggalMulai->toDateString(),
                    $tanggalAkhir->toDateString(),
                ])->when(
                    $wilayahId,
                    fn ($kegiatan) => $kegiatan->where('id_wilayah', $wilayahId)
                );
            }
        )->count();

        $rekapPemeriksaan = collect([
            (object) [
                'bulan' => $tanggalMulai->translatedFormat('F Y'),
                'pemeriksaan_ibu' => $jumlahPemeriksaanIbu,
                'pemeriksaan_anak' => $jumlahPemeriksaanAnak,
                'kegiatan_posyandu' => $kegiatanPosyandu->count(),
                'total' => $jumlahPemeriksaanIbu + $jumlahPemeriksaanAnak,
            ],
        ]);

        $ringkasan = [
            'ibu_hamil' => $rekapWilayah->sum('ibu_hamil'),
            'anak' => $rekapWilayah->sum('anak'),
            'perlu_perhatian' => $rekapWilayah->sum('perlu_perhatian'),
        ];

        $namaWilayah = $wilayahId
            ? ($wilayah->firstWhere('id', $wilayahId)?->nama_wilayah ?? '-')
            : 'Semua Wilayah';

        // Pemeriksaan apakah laporan pada tab aktif memiliki data.
        $kosong = match ($tab) {
            'rekap-kesehatan' => (
                $rekapWilayah->sum('ibu_hamil')
                + $rekapWilayah->sum('anak')
                + $rekapWilayah->sum('diperiksa')
                + $rekapWilayah->sum('perlu_perhatian')
            ) === 0,

            'kegiatan-posyandu' => $kegiatanPosyandu->isEmpty(),

            'data-risiko' => $dataRisiko->isEmpty(),

            'rekap-pemeriksaan' => (
                $rekapPemeriksaan->sum('pemeriksaan_ibu')
                + $rekapPemeriksaan->sum('pemeriksaan_anak')
                + $rekapPemeriksaan->sum('kegiatan_posyandu')
            ) === 0,

            default => true,
        };

        $judulTab = [
            'rekap-kesehatan' => 'Rekap Kesehatan Wilayah',
            'kegiatan-posyandu' => 'Laporan Kegiatan Posyandu',
            'data-risiko' => 'Data Risiko',
            'rekap-pemeriksaan' => 'Rekap Pemeriksaan',
        ];

        return compact(
            'wilayah',
            'wilayahId',
            'periode',
            'tab',
            'namaWilayah',
            'judulTab',
            'rekapWilayah',
            'kegiatanPosyandu',
            'dataRisiko',
            'rekapPemeriksaan',
            'ringkasan',
            'kosong'
        );
    }
}
