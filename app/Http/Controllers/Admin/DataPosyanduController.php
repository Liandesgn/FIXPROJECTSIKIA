<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KegiatanPosyandu;
use App\Models\WilayahKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DataPosyanduController extends Controller
{
    public function index(Request $request): View
    {
        $wilayahId = $this->wilayahId($request);

        $kegiatans = KegiatanPosyandu::query()
            ->with('wilayah')
            ->when($wilayahId, fn ($q) => $q->where('id_wilayah', $wilayahId))
            ->when(! $wilayahId, fn ($q) => $q->whereRaw('1 = 0'))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%'.$request->string('search').'%';
                $q->where(fn ($sub) => $sub
                    ->where('jenis_kegiatan', 'like', $search)
                    ->orWhere('lokasi', 'like', $search)
                    ->orWhereHas('wilayah', fn ($w) => $w
                        ->where('nama_wilayah', 'like', $search)
                        ->orWhere('kelurahan', 'like', $search)));
            })
            ->when($this->periode($request->input('periode')), fn ($q, $p) => $q
                ->whereYear('tanggal', $p[0])
                ->whereMonth('tanggal', $p[1]))
            ->orderByDesc('tanggal')
            ->paginate(10)
            ->withQueryString();

        return view('admin.data-posyandu.index', [
            'kegiatans' => $kegiatans,
            'periodes' => $this->daftarPeriode($wilayahId),
            'jumlahAnak' => $this->anakPerKegiatan($kegiatans->pluck('id')),
            'base' => $this->base($request),
        ]);
    }

    public function show(Request $request, KegiatanPosyandu $kegiatan): View
    {
        $wilayahId = $this->wilayahId($request);
        abort_unless($wilayahId && $kegiatan->id_wilayah === $wilayahId, 403);

        $kegiatan->load('wilayah');

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
            'base' => $this->base($request),
        ]);
    }

    public function rekap(Request $request): View
    {
        $wilayahId = $this->wilayahId($request);
        $periode = $this->periode($request->input('periode')) ?? [now()->year, now()->month];

        $kegiatans = KegiatanPosyandu::query()
            ->with('wilayah')
            ->when($wilayahId, fn ($q) => $q->where('id_wilayah', $wilayahId))
            ->when(! $wilayahId, fn ($q) => $q->whereRaw('1 = 0'))
            ->whereYear('tanggal', $periode[0])
            ->whereMonth('tanggal', $periode[1])
            ->when($request->filled('id_wilayah'), fn ($q) => $q
                ->where('id_wilayah', $request->integer('id_wilayah')))
            ->get();

        $jumlahAnak = $this->anakPerKegiatan($kegiatans->pluck('id'));

        $rekap = $kegiatans->groupBy('id_wilayah')
            ->map(fn ($grup) => [
                'wilayah' => $grup->first()->wilayah,
                'kegiatan' => $grup->count(),
                'anak' => $grup->sum(fn ($k) => $jumlahAnak[$k->id] ?? 0),
            ])
            ->sortBy(fn ($baris) => $baris['wilayah']?->nama_wilayah)
            ->values();

        return view('admin.data-posyandu.rekap', [
            'rekap' => $rekap,
            'ringkasan' => [
                'kegiatan' => $kegiatans->count(),
                'anak' => $rekap->sum('anak'),
                'wilayah' => $rekap->count(),
            ],
            'periodeValue' => sprintf('%04d-%02d', $periode[0], $periode[1]),
            'wilayahs' => WilayahKerja::query()
                ->when($wilayahId, fn ($q) => $q->where('id', $wilayahId))
                ->orderBy('nama_wilayah')
                ->get(),
            'base' => $this->base($request),
        ]);
    }

    private function base(Request $request): string
    {
        return $request->route()?->getName()
            ? Str::before($request->route()->getName(), '.')
            : 'admin';
    }

    private function wilayahId(Request $request): ?int
    {
        $user = $request->user()->loadMissing(['admin', 'bidan', 'kader']);

        return $user->managedWilayahId()
            ?? $user->bidan?->wilayah_kerja_id
            ?? $user->kader?->wilayah_kerja_id;
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

    private function daftarPeriode(?int $wilayahId)
    {
        return KegiatanPosyandu::query()
            ->when($wilayahId, fn ($q) => $q->where('id_wilayah', $wilayahId))
            ->pluck('tanggal')
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