<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KegiatanPosyandu;
use App\Models\Posyandu;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DataPosyanduController extends Controller
{
    public function index(Request $request): View
    {
        $kegiatans = KegiatanPosyandu::query()
            ->with('posyandu.wilayahKerja')
            ->when($request->filled('search'), fn ($q) => $q
                ->whereHas('posyandu', fn ($p) => $p
                    ->where('nama_posyandu', 'like', '%'.$request->string('search').'%')))
            ->when($this->periode($request->input('periode')), fn ($q, $p) => $q
                ->whereYear('tanggal', $p[0])
                ->whereMonth('tanggal', $p[1]))
            ->orderByDesc('tanggal')
            ->paginate(10)
            ->withQueryString();

        return view('admin.data-posyandu.index', [
            'kegiatans' => $kegiatans,
            'periodes' => $this->daftarPeriode(),
            'jumlahAnak' => $this->anakPerKegiatan($kegiatans->pluck('id')),
        ]);
    }

    public function show(KegiatanPosyandu $kegiatan): View
    {
        $kegiatan->load('posyandu.wilayahKerja');

        $tersedia = $this->tabelPemeriksaanAda();
        $pemeriksaan = collect();

        if ($tersedia) {
            $pemeriksaan = DB::table('pemeriksaan_posyandu as pp')
                ->join('anak as a', 'a.id', '=', 'pp.anak_id')
                ->where('pp.kegiatan_posyandu_id', $kegiatan->id)
                ->select('a.nama_anak', 'a.nik_anak', 'pp.*')
                ->get();
        }

        return view('admin.data-posyandu.show', [
            'kegiatan' => $kegiatan,
            'pemeriksaan' => $pemeriksaan,
            'tersedia' => $tersedia,
        ]);
    }

    public function rekap(Request $request): View
    {
        $periode = $this->periode($request->input('periode')) ?? [now()->year, now()->month];

        $kegiatans = KegiatanPosyandu::query()
            ->with('posyandu.wilayahKerja')
            ->whereYear('tanggal', $periode[0])
            ->whereMonth('tanggal', $periode[1])
            ->when($request->filled('posyandu_id'), fn ($q) => $q
                ->where('posyandu_id', $request->integer('posyandu_id')))
            ->get();

        $jumlahAnak = $this->anakPerKegiatan($kegiatans->pluck('id'));

        $rekap = $kegiatans->groupBy('posyandu_id')
            ->map(fn ($grup) => [
                'posyandu' => $grup->first()->posyandu,
                'kegiatan' => $grup->count(),
                'anak' => $grup->sum(fn ($k) => $jumlahAnak[$k->id] ?? 0),
            ])
            ->sortBy(fn ($baris) => $baris['posyandu']?->nama_posyandu)
            ->values();

        return view('admin.data-posyandu.rekap', [
            'rekap' => $rekap,
            'ringkasan' => [
                'kegiatan' => $kegiatans->count(),
                'anak' => $rekap->sum('anak'),
                'posyandu' => $rekap->count(),
            ],
            'periodeValue' => sprintf('%04d-%02d', $periode[0], $periode[1]),
            'posyandus' => Posyandu::orderBy('nama_posyandu')->get(),
        ]);
    }

    /**
     * "2026-10" menjadi [2026, 10]; format salah menjadi null.
     *
     * @return array{0:int,1:int}|null
     */
    private function periode(?string $nilai): ?array
    {
        return preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', (string) $nilai, $m)
            ? [(int) $m[1], (int) $m[2]]
            : null;
    }

    private function daftarPeriode()
    {
        return KegiatanPosyandu::query()->pluck('tanggal')
            ->map(fn ($t) => Carbon::parse($t)->format('Y-m'))
            ->unique()
            ->sortDesc()
            ->values();
    }

    /**
     * Tabel pemeriksaan milik modul 3. Sebelum ada, jumlahnya dianggap kosong.
     */
    private function tabelPemeriksaanAda(): bool
    {
        return Schema::hasTable('pemeriksaan_posyandu')
            && Schema::hasColumn('pemeriksaan_posyandu', 'kegiatan_posyandu_id')
            && Schema::hasColumn('pemeriksaan_posyandu', 'anak_id');
    }

    /**
     * @return array<int, int>  [id kegiatan => jumlah anak diperiksa]
     */
    private function anakPerKegiatan($ids): array
    {
        if (! $this->tabelPemeriksaanAda()) {
            return [];
        }

        return DB::table('pemeriksaan_posyandu')
            ->whereIn('kegiatan_posyandu_id', $ids)
            ->selectRaw('kegiatan_posyandu_id, count(*) as n')
            ->groupBy('kegiatan_posyandu_id')
            ->pluck('n', 'kegiatan_posyandu_id')
            ->all();
    }
}