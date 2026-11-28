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
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'periode' => ['nullable', 'date_format:Y-m'],
        ]);

        /*
         * 1. Identitas Puskesmas Admin yang login.
         */
        $admin = $request->user()
            ->loadMissing('admin')
            ->admin;

        abort_unless(
            $admin && $admin->puskesmas_id,
            403,
            'Akun Admin belum terhubung dengan Puskesmas.'
        );

        $puskesmasId = (int) $admin->puskesmas_id;

        /*
         * 2. Ambil seluruh wilayah kerja Puskesmas Admin.
         */
        $wilayah = WilayahKerja::where(
            'puskesmas_id',
            $puskesmasId
        )->get();

        $daftarWilayah = $wilayah;
        $wilayahIds = $daftarWilayah->pluck('id')->all();

        $namaPuskesmas = DB::table('puskesmas')
            ->where('id', $puskesmasId)
            ->value('nama_puskesmas');

        $namaWilayah = $namaPuskesmas ?? 'Puskesmas';

        /*
         * 3. Periode laporan.
         */
        $periode = $request->input(
            'periode',
            now()->format('Y-m')
        );

        $tanggalMulai = Carbon::createFromFormat(
            '!Y-m',
            $periode
        )->startOfMonth();

        $tanggalAkhir = $tanggalMulai->copy()->endOfMonth();

        /*
         * 4. Filter kegiatan berdasarkan Puskesmas.
         *
         * Jalur pertama:
         * kegiatan_posyandu.id_wilayah
         *
         * Jalur kedua:
         * kegiatan_posyandu.rw_id
         * -> rw.kelurahan_desa_id
         * -> kelurahan_desa.puskesmas_id
         */
        $filterKegiatanPuskesmas = function ($query) use (
            $puskesmasId,
            $wilayahIds
        ) {
            $query->where(function ($scope) use (
                $puskesmasId,
                $wilayahIds
            ) {
                $scope->whereIn('id_wilayah', $wilayahIds)
                    ->orWhereHas(
                        'rw.kelurahanDesa',
                        function ($kelurahan) use ($puskesmasId) {
                            $kelurahan->where(
                                'puskesmas_id',
                                $puskesmasId
                            );
                        }
                    );
            });
        };

        /*
         * 5. Rekap kesehatan setiap wilayah kerja.
         */
        $rekapWilayah = $daftarWilayah->map(
            function ($wilayahItem) use (
                $tanggalMulai,
                $tanggalAkhir
            ) {
                $jumlahIbuHamil = Ibu::where(
                    'wilayah_kerja_id',
                    $wilayahItem->id
                )
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
                        $query->where(
                            'wilayah_kerja_id',
                            $wilayahItem->id
                        );
                    })
                    ->count();

                $ibuDiperiksa = PemeriksaanIbu::whereBetween(
                    'tgl_pemeriksaan',
                    [
                        $tanggalMulai->toDateString(),
                        $tanggalAkhir->toDateString(),
                    ]
                )
                    ->whereHas('ibu', function ($query) use ($wilayahItem) {
                        $query->where(
                            'wilayah_kerja_id',
                            $wilayahItem->id
                        );
                    })
                    ->distinct()
                    ->count('ibu_id');

                $anakDiperiksa = PemeriksaanPosyandu::whereHas(
                    'kegiatan',
                    function ($query) use (
                        $tanggalMulai,
                        $tanggalAkhir,
                        $wilayahItem
                    ) {
                        $query->whereBetween('tanggal', [
                            $tanggalMulai->toDateString(),
                            $tanggalAkhir->toDateString(),
                        ])->where(
                            'id_wilayah',
                            $wilayahItem->id
                        );
                    }
                )
                    ->distinct()
                    ->count('anak_id');

                $ibuPerluPerhatian = PemeriksaanIbu::whereBetween(
                    'tgl_pemeriksaan',
                    [
                        $tanggalMulai->toDateString(),
                        $tanggalAkhir->toDateString(),
                    ]
                )
                    ->where(
                        'status_kesehatan',
                        PemeriksaanIbu::PERLU_PERHATIAN
                    )
                    ->whereHas('ibu', function ($query) use ($wilayahItem) {
                        $query->where(
                            'wilayah_kerja_id',
                            $wilayahItem->id
                        );
                    })
                    ->distinct()
                    ->count('ibu_id');

$anakPerluPerhatian = PemeriksaanPosyandu::whereIn(
    'tingkat_risiko',
    [
        PemeriksaanPosyandu::RISIKO_SEDANG,
        PemeriksaanPosyandu::RISIKO_TINGGI,
    ]
)
    ->whereHas(
        'kegiatan',
        function ($query) use (
            $tanggalMulai,
            $tanggalAkhir,
            $wilayahItem
        ) {
            $query->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])->where(
                'id_wilayah',
                $wilayahItem->id
            );
        }
    )
    ->distinct()
    ->count('anak_id');

                $diperiksa = $ibuDiperiksa + $anakDiperiksa;
                $perluPerhatian = $ibuPerluPerhatian
                    + $anakPerluPerhatian;

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
            }
        );

        /*
         * 6. Grafik status pemeriksaan Ibu dan Anak.
         */
        $grafikStatus = [
            'ibu_normal' => PemeriksaanIbu::whereBetween(
                'tgl_pemeriksaan',
                [
                    $tanggalMulai->toDateString(),
                    $tanggalAkhir->toDateString(),
                ]
            )
                ->where(
                    'status_kesehatan',
                    PemeriksaanIbu::NORMAL
                )
                ->whereHas('ibu', function ($query) use ($wilayahIds) {
                    $query->whereIn('wilayah_kerja_id', $wilayahIds);
                })
                ->count(),

            'ibu_perhatian' => PemeriksaanIbu::whereBetween(
                'tgl_pemeriksaan',
                [
                    $tanggalMulai->toDateString(),
                    $tanggalAkhir->toDateString(),
                ]
            )
                ->where(
                    'status_kesehatan',
                    PemeriksaanIbu::PERLU_PERHATIAN
                )
                ->whereHas('ibu', function ($query) use ($wilayahIds) {
                    $query->whereIn('wilayah_kerja_id', $wilayahIds);
                })
                ->count(),

'anak_normal' => PemeriksaanPosyandu::where(
    'tingkat_risiko',
    PemeriksaanPosyandu::RISIKO_RENDAH
)
    ->whereHas(
        'kegiatan',
        function ($query) use (
            $tanggalMulai,
            $tanggalAkhir,
            $filterKegiatanPuskesmas
        ) {
            $query->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ]);

            $filterKegiatanPuskesmas($query);
        }
    )
    ->count(),

'anak_perhatian' => PemeriksaanPosyandu::whereIn(
    'tingkat_risiko',
    [
        PemeriksaanPosyandu::RISIKO_SEDANG,
        PemeriksaanPosyandu::RISIKO_TINGGI,
    ]
)
    ->whereHas(
        'kegiatan',
        function ($query) use (
            $tanggalMulai,
            $tanggalAkhir,
            $filterKegiatanPuskesmas
        ) {
            $query->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ]);

            $filterKegiatanPuskesmas($query);
        }
    )
    ->count(),

'anak_perhatian' => PemeriksaanPosyandu::whereIn(
    'tingkat_risiko',
    [
        PemeriksaanPosyandu::RISIKO_SEDANG,
        PemeriksaanPosyandu::RISIKO_TINGGI,
    ]
)
    ->whereHas(
        'kegiatan',
        function ($query) use (
            $tanggalMulai,
            $tanggalAkhir,
            $filterKegiatanPuskesmas
        ) {
            $query->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ]);

            $filterKegiatanPuskesmas($query);
        }
    )
    ->count(),
        ];

        /*
         * 7. Laporan kegiatan Posyandu.
         */
        $kegiatanPosyandu = KegiatanPosyandu::query()
            ->with([
                'wilayah',
                'rw.kelurahanDesa',
            ])
            ->addSelect([
                'anak_diperiksa' => PemeriksaanPosyandu::query()
                    ->selectRaw(
                        'COUNT(DISTINCT pemeriksaan_posyandu.anak_id)'
                    )
                    ->whereColumn(
                        'pemeriksaan_posyandu.kegiatan_posyandu_id',
                        'kegiatan_posyandu.id'
                    ),
            ])
            ->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
            ->where($filterKegiatanPuskesmas)
            ->orderBy('tanggal')
            ->get();

        /*
         * 8. Data risiko Ibu.
         */
        $risikoIbu = PemeriksaanIbu::with([
            'ibu.user',
            'ibu.wilayahKerja',
        ])
            ->terakhirPerIbu()
            ->where(
                'status_kesehatan',
                PemeriksaanIbu::PERLU_PERHATIAN
            )
            ->whereBetween('tgl_pemeriksaan', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ])
            ->whereHas('ibu', function ($query) use ($wilayahIds) {
                $query->whereIn('wilayah_kerja_id', $wilayahIds);
            })
            ->get();

        /*
         * 9. Data risiko Anak.
         */
$risikoAnak = PemeriksaanPosyandu::with([
    'anak.ibu.wilayahKerja',
    'kegiatan.rw.kelurahanDesa',
])
    ->terakhirPerAnak()
    ->whereIn(
        'tingkat_risiko',
        [
            PemeriksaanPosyandu::RISIKO_SEDANG,
            PemeriksaanPosyandu::RISIKO_TINGGI,
        ]
    )
    ->whereHas(
        'kegiatan',
        function ($query) use (
            $tanggalMulai,
            $tanggalAkhir,
            $filterKegiatanPuskesmas
        ) {
            $query->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ]);

            $filterKegiatanPuskesmas($query);
        }
    )
    ->get();

        /*
         * 10. Gabungkan data risiko Ibu dan Anak.
         */
        $dataRisiko = collect();

        foreach ($risikoAnak as $pemeriksaan) {
            $dataRisiko->push((object) [
                'nama' => $pemeriksaan->anak?->nama_anak ?? '-',
                'jenis' => 'Anak',
                'wilayah' => $pemeriksaan->anak?->ibu?->wilayahKerja?->nama_wilayah
                    ?? $pemeriksaan->kegiatan?->rw?->kelurahanDesa?->nama_kelurahan_desa
                    ?? '-',
                'tanggal' => $pemeriksaan->kegiatan?->tanggal,
                'indikator' => 'BB: '
                    . ($pemeriksaan->berat_badan ?? '-')
                    . ' kg, TB: '
                    . ($pemeriksaan->tinggi_badan ?? '-')
                    . ' cm',
                'penanda' => 'Perlu Perhatian',
            ]);
        }

        foreach ($risikoIbu as $pemeriksaan) {
            $dataRisiko->push((object) [
                'nama' => $pemeriksaan->ibu?->user?->name ?? '-',
                'jenis' => 'Ibu',
                'wilayah' => $pemeriksaan->ibu?->wilayahKerja?->nama_wilayah
                    ?? '-',
                'tanggal' => $pemeriksaan->tgl_pemeriksaan,
                'indikator' => 'Status pemeriksaan: Perlu Perhatian',
                'penanda' => 'Perlu Perhatian',
            ]);
        }

        /*
         * 11. Rekap jumlah pemeriksaan.
         */
        $jumlahPemeriksaanIbu = PemeriksaanIbu::whereBetween(
            'tgl_pemeriksaan',
            [
                $tanggalMulai->toDateString(),
                $tanggalAkhir->toDateString(),
            ]
        )
            ->whereHas('ibu', function ($query) use ($wilayahIds) {
                $query->whereIn('wilayah_kerja_id', $wilayahIds);
            })
            ->count();

        $jumlahPemeriksaanAnak = PemeriksaanPosyandu::whereHas(
            'kegiatan',
            function ($query) use (
                $tanggalMulai,
                $tanggalAkhir,
                $filterKegiatanPuskesmas
            ) {
                $query->whereBetween('tanggal', [
                    $tanggalMulai->toDateString(),
                    $tanggalAkhir->toDateString(),
                ]);

                $filterKegiatanPuskesmas($query);
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

        /*
         * 12. Ringkasan dashboard laporan.
         */
        $ringkasan = [
            'ibu_hamil' => $rekapWilayah->sum('ibu_hamil'),
            'anak' => $rekapWilayah->sum('anak'),
            'perlu_perhatian' => $rekapWilayah->sum('perlu_perhatian'),
        ];

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