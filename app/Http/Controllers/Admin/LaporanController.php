<?php

namespace App\Http\Controllers\Admin;


use App\Enums\StatusKehamilanEnum;
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
            'wilayah_id' => ['nullable', 'exists:wilayah_kerja,id'],
        ]);

        $wilayahId = $request->user()->loadMissing('admin')->managedWilayahId();

        abort_unless($wilayahId, 403, 'Akun Admin belum memiliki wilayah kerja.');

        $request->merge(['wilayah_id' => $wilayahId]);

        $periode = $request->input('periode', now()->format('Y-m'));

        $tanggalMulai = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
        $tanggalAkhir = Carbon::createFromFormat('Y-m', $periode)->endOfMonth();

        $wilayah = WilayahKerja::whereKey($wilayahId)->get();

        $daftarWilayah = WilayahKerja::query()
            ->when(
                $request->filled('wilayah_id'),
                fn ($query) => $query->where('id', $request->wilayah_id)
            )
            ->orderBy('nama_wilayah')
            ->get();

        $rekapWilayah = $daftarWilayah->map(function ($wilayahItem) use (
            $tanggalMulai,
            $tanggalAkhir
        ) {
            // Jumlah ibu hamil di wilayah
            $jumlahIbuHamil = Ibu::where(
                'wilayah_kerja_id',
                $wilayahItem->id
            )
                ->where(
                    'status_kehamilan',
                    StatusKehamilanEnum::Hamil->value
                )
                ->count();

            // Jumlah anak terdaftar di wilayah
            $jumlahAnak = Anak::whereHas('ibu', function ($query) use ($wilayahItem) {
                $query->where('wilayah_kerja_id', $wilayahItem->id);
            })->count();

            // Ibu yang diperiksa pada periode terpilih
            $ibuDiperiksa = PemeriksaanIbu::whereBetween(
                'tgl_pemeriksaan',
                [$tanggalMulai, $tanggalAkhir]
            )
                ->whereHas('ibu', function ($query) use ($wilayahItem) {
                    $query->where('wilayah_kerja_id', $wilayahItem->id);
                })
                ->distinct()
                ->count('ibu_id');

            // Anak yang diperiksa pada periode terpilih
            $anakDiperiksa = PemeriksaanPosyandu::whereHas(
                'kegiatan',
                function ($query) use ($tanggalMulai, $tanggalAkhir) {
                    $query->whereBetween(
                        'tanggal',
                        [$tanggalMulai, $tanggalAkhir]
                    );
                }
            )
                ->whereHas('anak.ibu', function ($query) use ($wilayahItem) {
                    $query->where('wilayah_kerja_id', $wilayahItem->id);
                })
                ->distinct()
                ->count('anak_id');

            // Ibu dengan status perlu perhatian
            $ibuPerluPerhatian = PemeriksaanIbu::whereBetween(
                'tgl_pemeriksaan',
                [$tanggalMulai, $tanggalAkhir]
            )
                ->where('status_kesehatan', PemeriksaanIbu::PERLU_PERHATIAN)
                ->whereHas('ibu', function ($query) use ($wilayahItem) {
                    $query->where('wilayah_kerja_id', $wilayahItem->id);
                })
                ->distinct()
                ->count('ibu_id');

            // Anak dengan status perlu perhatian
            $anakPerluPerhatian = PemeriksaanPosyandu::where(
                'status_kesehatan',
                PemeriksaanPosyandu::PERLU_PERHATIAN
            )
                ->whereHas('kegiatan', function ($query) use (
                    $tanggalMulai,
                    $tanggalAkhir
                ) {
                    $query->whereBetween(
                        'tanggal',
                        [$tanggalMulai, $tanggalAkhir]
                    );
                })
                ->whereHas('anak.ibu', function ($query) use ($wilayahItem) {
                    $query->where('wilayah_kerja_id', $wilayahItem->id);
                })
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

        // Laporan kegiatan Posyandu
        $kegiatanPosyandu = KegiatanPosyandu::with('wilayah')
            ->withCount('pemeriksaan')
            ->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
            ->when(
                $request->filled('wilayah_id'),
                fn ($query) => $query->where('id_wilayah', $request->wilayah_id)
            )
            ->orderBy('tanggal')
            ->get();

        // Data risiko ibu: hanya pemeriksaan terakhir setiap ibu
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
            ->whereHas('ibu', function ($query) use ($request) {
                $query->when(
                    $request->filled('wilayah_id'),
                    fn ($ibuQuery) => $ibuQuery->where(
                        'wilayah_kerja_id',
                        $request->wilayah_id
                    )
                );
            })
            ->get();

        // Data risiko anak: hanya pemeriksaan terakhir setiap anak
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
            ->whereHas('anak.ibu', function ($query) use ($request) {
                $query->when(
                    $request->filled('wilayah_id'),
                    fn ($ibuQuery) => $ibuQuery->where(
                        'wilayah_kerja_id',
                        $request->wilayah_id
                    )
                );
            })
            ->get();

        // Gabungkan data risiko ibu dan anak
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


                // Rekap pemeriksaan berdasarkan bulan
        $rekapPemeriksaan = collect();

        $jumlahPemeriksaanIbu = PemeriksaanIbu::whereBetween(
            'tgl_pemeriksaan',
            [$tanggalMulai, $tanggalAkhir]
        )
            ->whereHas('ibu', function ($query) use ($request) {
                $query->when(
                    $request->filled('wilayah_id'),
                    fn ($ibuQuery) => $ibuQuery->where(
                        'wilayah_kerja_id',
                        $request->wilayah_id
                    )
                );
            })
            ->count();

        $jumlahPemeriksaanAnak = PemeriksaanPosyandu::whereHas(
            'kegiatan',
            function ($query) use ($tanggalMulai, $tanggalAkhir, $request) {
                $query->whereBetween('tanggal', [
                    $tanggalMulai->toDateString(),
                    $tanggalAkhir->toDateString(),
                ]);

                $query->when(
                    $request->filled('wilayah_id'),
                    fn ($kegiatan) => $kegiatan->where(
                        'id_wilayah',
                        $request->wilayah_id
                    )
                );
            }
        )->count();

        $jumlahKegiatan = $kegiatanPosyandu->count();

        $rekapPemeriksaan->push((object) [
            'bulan' => $tanggalMulai->translatedFormat('F Y'),
            'pemeriksaan_ibu' => $jumlahPemeriksaanIbu,
            'pemeriksaan_anak' => $jumlahPemeriksaanAnak,
            'kegiatan_posyandu' => $jumlahKegiatan,
        ]);



        $ringkasan = [
            'ibu_hamil' => $rekapWilayah->sum('ibu_hamil'),
            'anak' => $rekapWilayah->sum('anak'),
            'perlu_perhatian' => $rekapWilayah->sum('perlu_perhatian'),
        ];

        return view('admin.laporan.index', compact(
            'wilayah',
            'rekapWilayah',
            'ringkasan',
            'periode',
            'kegiatanPosyandu',
            'dataRisiko',
            'rekapPemeriksaan'
        ));
    }
}
