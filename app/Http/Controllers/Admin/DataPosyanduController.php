<?php

namespace App\Http\Controllers\Admin;

use App\Models\JadwalImunisasi;
use App\Models\JadwalVitamin;
use App\Models\ImunisasiAnak;
use App\Models\Jadwal;
use App\Models\Anak;
use App\Models\PemeriksaanPosyandu;
use App\Services\StuntingCalculator;
use Illuminate\Validation\ValidationException;
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
    $base = $this->base($request);
    
    $wilayahId = $this->wilayahId($request);


        // Ambil jadwal Posyandu hari ini sesuai kelurahan/desa Kader.
    $kader = $request->user()
        ->kader()
        ->with('wilayahKerja.kelurahanDesa')
        ->first();

    $kelurahanDesaId = $kader?->wilayahKerja?->kelurahan_desa_id;

    $jadwalHariIni = null;

    if ($kelurahanDesaId) {
        $jadwalHariIni = Jadwal::with([
            'jadwalPosyandu.kegiatan.rw.kelurahanDesa',
            'jadwalPosyandu.bidan.user',
            'jadwalPosyandu.kader.user',
        ])
            ->whereDate('tanggal', today())
            ->whereHas(
                'jadwalPosyandu.kegiatan.rw',
                function ($q) use ($kelurahanDesaId) {
                    $q->where('kelurahan_desa_id', $kelurahanDesaId);
                }
            )
            ->orderBy('waktu')
            ->first();
    }

   
// Khusus Kader dan Bidan: tampilkan daftar anak berdasarkan kelurahan/desa tugas.
if (in_array($base, ['kader', 'bidan'], true)) {

    $user = $request->user()->loadMissing([
        'kader.wilayahKerja',
        'bidan.wilayahKerja',
    ]);

    // Ambil kelurahan/desa sesuai profil pengguna.
    $kelurahanDesaId = $base === 'kader'
        ? $user->kader?->wilayahKerja?->kelurahan_desa_id
        : $user->bidan?->wilayahKerja?->kelurahan_desa_id;

    // Ambil jadwal Posyandu hari ini di kelurahan/desa tersebut.
    $jadwalHariIni = null;

    if ($kelurahanDesaId) {
        $jadwalHariIni = Jadwal::with([
            'jadwalPosyandu.kegiatan.rw.kelurahanDesa',
            'jadwalPosyandu.bidan.user',
            'jadwalPosyandu.kader.user',
        ])
            ->whereDate('tanggal', today())
            ->whereHas(
                'jadwalPosyandu.kegiatan.rw',
                function ($q) use ($kelurahanDesaId) {
                    $q->where('kelurahan_desa_id', $kelurahanDesaId);
                }
            )
            ->orderBy('waktu')
            ->first();
    }

    // Ambil pemeriksaan terakhir setiap anak.
    $pemeriksaanTerakhir = DB::table('pemeriksaan_posyandu')
        ->selectRaw('anak_id, MAX(id) as pemeriksaan_id')
        ->groupBy('anak_id');

    $query = DB::table('anak as a')
        ->join('ibu as i', 'i.id', '=', 'a.ibu_id')
        ->join('wilayah_kerja as wk', 'wk.id', '=', 'i.wilayah_kerja_id')
        ->where('wk.kelurahan_desa_id', $kelurahanDesaId)
        ->when(
            ! $kelurahanDesaId,
            fn ($q) => $q->whereRaw('1 = 0')
        )
        ->leftJoinSub(
            $pemeriksaanTerakhir,
            'latest_pp',
            fn ($join) => $join->on(
                'latest_pp.anak_id',
                '=',
                'a.id'
            )
        )
        ->leftJoin(
            'pemeriksaan_posyandu as pp',
            'pp.id',
            '=',
            'latest_pp.pemeriksaan_id'
        )
        ->select([
            'a.id as anak_id',
            'a.nama_anak',
            'a.nik_anak',
            'a.tgl_lahir',
            'a.jenis_kelamin',
            'pp.id as pemeriksaan_id',
            'pp.kegiatan_posyandu_id',
            'pp.kader_id',
            'pp.tgl_pemeriksaan',
            'pp.berat_badan',
            'pp.tinggi_badan',
            'pp.status_stunting',
            'pp.tingkat_risiko',
            'pp.jadwal_vitamin',
        ]);

    // Pencarian nama anak atau NIK.
    $query->when($request->filled('search'), function ($q) use ($request) {
        $search = '%' . trim($request->input('search')) . '%';

        $q->where(function ($sub) use ($search) {
            $sub->where('a.nama_anak', 'like', $search)
                ->orWhere('a.nik_anak', 'like', $search);
        });
    });

    // Filter status pemeriksaan.
    $query->when($request->filled('status'), function ($q) use ($request) {
        $status = $request->input('status');

        if ($status === 'belum_diperiksa') {
            $q->whereNull('pp.id');
        } else {
            $q->where('pp.status_stunting', $status);
        }
    });

    // Filter periode pemeriksaan.
    $periode = $this->periode($request->input('periode'));

    if ($periode) {
        $query->whereYear('pp.tgl_pemeriksaan', $periode[0])
            ->whereMonth('pp.tgl_pemeriksaan', $periode[1]);
    }

    $anakList = $query
        ->orderBy('a.nama_anak')
        ->paginate(10)
        ->withQueryString();

    // Hitung seluruh anak di kelurahan/desa tugas.
    $jumlahAnak = DB::table('anak as a')
        ->join('ibu as i', 'i.id', '=', 'a.ibu_id')
        ->join('wilayah_kerja as wk', 'wk.id', '=', 'i.wilayah_kerja_id')
        ->where('wk.kelurahan_desa_id', $kelurahanDesaId)
        ->when(
            ! $kelurahanDesaId,
            fn ($q) => $q->whereRaw('1 = 0')
        )
        ->count();

    // Hitung anak yang sudah diperiksa.
    $jumlahSudahDiperiksa = DB::table('anak as a')
        ->join('ibu as i', 'i.id', '=', 'a.ibu_id')
        ->join('wilayah_kerja as wk', 'wk.id', '=', 'i.wilayah_kerja_id')
        ->join('pemeriksaan_posyandu as pp', 'pp.anak_id', '=', 'a.id')
        ->where('wk.kelurahan_desa_id', $kelurahanDesaId)
        ->when(
            ! $kelurahanDesaId,
            fn ($q) => $q->whereRaw('1 = 0')
        )
        ->distinct()
        ->count('a.id');

    $jumlahBelumDiperiksa = max(
        0,
        $jumlahAnak - $jumlahSudahDiperiksa
    );


    return view('kader.data-posyandu.index', [
        'anakList' => $anakList,
        'jumlahAnak' => $jumlahAnak,
        'jumlahSudahDiperiksa' => $jumlahSudahDiperiksa,
        'jumlahBelumDiperiksa' => $jumlahBelumDiperiksa,
        'jadwalHariIni' => $jadwalHariIni,
        'base' => $base,
    ]);
}

    // Admin dan Bidan tetap menggunakan alur lama.
    $periode = $this->periode($request->input('periode'));

    $queryDasar = KegiatanPosyandu::query()
        ->with('wilayah')
        ->when(
            $wilayahId,
            fn ($q) => $q->where('id_wilayah', $wilayahId)
        )
        ->when(
            ! $wilayahId,
            fn ($q) => $q->whereRaw('1 = 0')
        )
        ->when(
            $periode,
            fn ($q, $p) => $q
                ->whereYear('tanggal', $p[0])
                ->whereMonth('tanggal', $p[1])
        );

    $periodes = $this->daftarPeriode($wilayahId);

    $jumlahAnak = DB::table('anak as a')
        ->join('ibu as i', 'i.id', '=', 'a.ibu_id')
        ->where('i.wilayah_kerja_id', $wilayahId)
        ->count();

    $jumlahKegiatan = (clone $queryDasar)->count();

    $idsKegiatan = (clone $queryDasar)->pluck('id');

    $jumlahAnakDiperiksa = $this->jumlahAnakDiperiksa($idsKegiatan);

    $posyanduAktif = (clone $queryDasar)
        ->whereNotNull('lokasi')
        ->where('lokasi', '<>', '')
        ->distinct()
        ->count('lokasi');

    $kegiatans = (clone $queryDasar)
        ->when($request->filled('search'), function ($q) use ($request) {
            $search = '%' . $request->input('search') . '%';

            $q->where(function ($sub) use ($search) {
                $sub->where('jenis_kegiatan', 'like', $search)
                    ->orWhere('lokasi', 'like', $search);
            });
        })
        ->orderByDesc('tanggal')
        ->orderByDesc('waktu')
        ->paginate(10)
        ->withQueryString();

$view = 'admin.data-posyandu.index';

    return view($view, [
        'kegiatans' => $kegiatans,
        'periodes' => $periodes,
        'jumlahAnak' => $jumlahAnak,
        'jumlahKegiatan' => $jumlahKegiatan,
        'jumlahAnakDiperiksa' => $jumlahAnakDiperiksa,
        'posyanduAktif' => $posyanduAktif,
        'base' => $base,
    ]);
}

            
            public function show(
                Request $request,
                KegiatanPosyandu $kegiatan
            ): View {
                $wilayahId = $this->wilayahId($request);
                $base = $this->base($request);

// Pastikan kegiatan berada di wilayah kerja pengguna.
$this->pastikanKegiatanDalamWilayah($request, $kegiatan);

                // Kader hanya boleh membuka kegiatan yang masih aktif.
                if ($base === 'kader') {
                    abort_unless($this->kegiatanMasihAktif($kegiatan), 403);
                }

                $kegiatan->load('wilayah');

                $tersedia = $this->tabelPemeriksaanAda();

                // Ambil seluruh anak yang ibunya berada di wilayah kerja.
$query = DB::table('anak as a')
    ->join('ibu as i', 'i.id', '=', 'a.ibu_id');

if ($base === 'kader') {
    $kader = $request->user()
        ->loadMissing('kader.wilayahKerja')
        ->kader;

    $kelurahanDesaId = $kader?->wilayahKerja?->kelurahan_desa_id;

    $query->join('wilayah_kerja as wk', 'wk.id', '=', 'i.wilayah_kerja_id')
        ->where('wk.kelurahan_desa_id', $kelurahanDesaId);
} else {
    $query->where('i.wilayah_kerja_id', $wilayahId);
}

                if ($tersedia) {
                    // LEFT JOIN agar anak yang belum diperiksa tetap ditampilkan.
                    $query->leftJoin('pemeriksaan_posyandu as pp', function ($join) use ($kegiatan) {
                        $join->on('pp.anak_id', '=', 'a.id')
                            ->where('pp.kegiatan_posyandu_id', '=', $kegiatan->id);
                    });

                    $pemeriksaan = $query
                        ->select([
                            'a.id as anak_id',
                            'a.nama_anak',
                            'a.nik_anak',
                            'a.tgl_lahir',
                            'a.jenis_kelamin',

                            'pp.id as pemeriksaan_id',
                            'pp.tgl_pemeriksaan',
                            'pp.berat_badan',
                            'pp.tinggi_badan',
                            'pp.lingkar_kepala',
                            'pp.lila',
                            'pp.umur_bulan',
                            'pp.z_score_tbu',
                            'pp.median_tbu',
                            'pp.sd_minus_1_tbu',
                            'pp.sd_plus_1_tbu',
                            'pp.status_stunting',
                            'pp.tingkat_risiko',
                            'pp.jadwal_vitamin',
                            'pp.catatan',
                            'pp.kader_id',
                            'pp.bidan_id',
                            'pp.pencatat_user_id',
                            'pp.pengubah_user_id',
                        ])
                        ->orderBy('a.nama_anak')
                        ->get();
                } else {
                    $pemeriksaan = $query
                        ->select([
                            'a.id as anak_id',
                            'a.nama_anak',
                            'a.nik_anak',
                            'a.tgl_lahir',
                            'a.jenis_kelamin',
                        ])
                        ->orderBy('a.nama_anak')
                        ->get();
                }
return view('admin.data-posyandu.show', [
    'kegiatan' => $kegiatan,
    'pemeriksaan' => $pemeriksaan,
    'tersedia' => $tersedia,
    'base' => $base,
]);
        }

     /**
 * Menampilkan form input pemeriksaan anak.
 */

public function createPemeriksaan(
    Request $request,
    KegiatanPosyandu $kegiatan,
    Anak $anak
): View {
    $base = $this->base($request);

    abort_unless(
        in_array($base, ['kader', 'bidan'], true),
        403,
        'Role tidak diizinkan menginput pemeriksaan.'
    );

    $this->pastikanKegiatanDalamWilayah($request, $kegiatan);

    if ($base === 'kader') {
        abort_unless(
            $this->kegiatanMasihAktif($kegiatan),
            403,
            'Kegiatan Posyandu sudah tidak aktif.'
        );
    }

    $this->pastikanAnakDalamWilayah($request, $anak);

    $sudahAda = PemeriksaanPosyandu::query()
        ->where('anak_id', $anak->id)
        ->where('kegiatan_posyandu_id', $kegiatan->id)
        ->exists();

    abort_if(
        $sudahAda,
        409,
        'Anak sudah memiliki pemeriksaan pada kegiatan ini.'
    );

    return view('kader.data-posyandu.create-pemeriksaan', [
        'kegiatan' => $kegiatan,
        'anak' => $anak,
        'base' => $base,
    ]);
}

/**
 * Menyimpan pemeriksaan baru.
 */
public function storePemeriksaan(
    Request $request,
    KegiatanPosyandu $kegiatan,
    Anak $anak
) {
    $wilayahId = $this->wilayahId($request);
    $base = $this->base($request);

    abort_unless(in_array($base, ['kader', 'bidan'], true), 403);

$this->pastikanKegiatanDalamWilayah($request, $kegiatan);

    if ($base === 'kader') {
        abort_unless($this->kegiatanMasihAktif($kegiatan), 403);
    }

    $this->pastikanAnakDalamWilayah($request, $anak);

    $sudahAda = PemeriksaanPosyandu::query()
        ->where('anak_id', $anak->id)
        ->where('kegiatan_posyandu_id', $kegiatan->id)
        ->exists();

    abort_if($sudahAda, 409, 'Pemeriksaan anak pada kegiatan ini sudah tersedia.');

    $data = $request->validate([
        'tgl_pemeriksaan' => ['required', 'date'],
        'berat_badan' => ['required', 'numeric', 'min:0.1', 'max:100'],
        'tinggi_badan' => ['required', 'numeric', 'min:1', 'max:200'],
        'lingkar_kepala' => ['nullable', 'numeric', 'min:1', 'max:100'],
        'lila' => ['nullable', 'numeric', 'min:1', 'max:100'],
        'jadwal_vitamin' => ['nullable', 'string', 'max:255'],
        'catatan' => ['nullable', 'string', 'max:5000'],
    ]);

    if (! $anak->tgl_lahir) {
        throw ValidationException::withMessages([
            'tgl_pemeriksaan' => 'Tanggal lahir anak belum tersedia.',
        ]);
    }

    if (
        \Carbon\Carbon::parse($data['tgl_pemeriksaan'])
            ->lt(\Carbon\Carbon::parse($anak->tgl_lahir))
    ) {
        throw ValidationException::withMessages([
            'tgl_pemeriksaan' => 'Tanggal pemeriksaan tidak boleh sebelum tanggal lahir anak.',
        ]);
    }

    // Hitung umur, Z-score TB/U, status stunting, dan tingkat risiko.
    $calculator = new StuntingCalculator();

    $hasil = $calculator->hitung(
        $anak->jenis_kelamin,
        $anak->tgl_lahir,
        $data['tgl_pemeriksaan'],
        (float) $data['tinggi_badan']
    );

    $user = $request->user()->loadMissing(['kader', 'bidan']);

    $kaderId = $base === 'kader'
        ? $user->kader?->id
        : null;

    $bidanId = $base === 'bidan'
        ? $user->bidan?->id
        : null;

    if ($base === 'kader' && ! $kaderId) {
        abort(403, 'Profil Kader tidak ditemukan.');
    }

    if ($base === 'bidan' && ! $bidanId) {
        abort(403, 'Profil Bidan tidak ditemukan.');
    }

    DB::transaction(function () use (
        $data,
        $hasil,
        $anak,
        $kegiatan,
        $user,
        $kaderId,
        $bidanId
    ) {
        PemeriksaanPosyandu::create([
            'anak_id' => $anak->id,
            'kegiatan_posyandu_id' => $kegiatan->id,

            'kader_id' => $kaderId,
            'bidan_id' => $bidanId,

            'tgl_pemeriksaan' => $data['tgl_pemeriksaan'],
            'berat_badan' => $data['berat_badan'],
            'tinggi_badan' => $data['tinggi_badan'],
            'lingkar_kepala' => $data['lingkar_kepala'] ?? null,
            'lila' => $data['lila'] ?? null,

            'jadwal_vitamin' => $data['jadwal_vitamin'] ?? null,
            'catatan' => $data['catatan'] ?? null,

            'umur_bulan' => $hasil['umur_bulan'],
            'z_score_tbu' => $hasil['z_score_tbu'],
            'median_tbu' => $hasil['median_tbu'],
            'sd_minus_1_tbu' => $hasil['sd_minus_1_tbu'],
            'sd_plus_1_tbu' => $hasil['sd_plus_1_tbu'],
            'status_stunting' => $hasil['status_stunting'],
            'tingkat_risiko' => $hasil['tingkat_risiko'],

            'pencatat_user_id' => $user->id,
            'pengubah_user_id' => null,
        ]);
    });

    return redirect()
        ->route($base . '.data-posyandu.show', $kegiatan->id)
        ->with('success', 'Pemeriksaan anak berhasil disimpan.');
}

/**
 * Menampilkan form edit pemeriksaan.
 */
public function editPemeriksaan(
    Request $request,
    KegiatanPosyandu $kegiatan,
    Anak $anak,
    PemeriksaanPosyandu $pemeriksaan
): View {
    $wilayahId = $this->wilayahId($request);
    $base = $this->base($request);

    abort_unless(in_array($base, ['kader', 'bidan'], true), 403);

$this->pastikanKegiatanDalamWilayah($request, $kegiatan);

    if ($base === 'kader') {
        abort_unless($this->kegiatanMasihAktif($kegiatan), 403);
    }

    $this->pastikanAnakDalamWilayah($request, $anak);

    abort_unless(
        (int) $pemeriksaan->anak_id === (int) $anak->id
        && (int) $pemeriksaan->kegiatan_posyandu_id === (int) $kegiatan->id,
        404
    );

    $this->pastikanBolehMengubahPemeriksaan(
        $request,
        $pemeriksaan,
        $base
    );

    return view('kader.data-posyandu.edit-pemeriksaan', [
        'kegiatan' => $kegiatan,
        'anak' => $anak,
        'pemeriksaan' => $pemeriksaan,
    ]);
}

/**
 * Memperbarui pemeriksaan yang sudah ada.
 */
public function updatePemeriksaan(
    Request $request,
    KegiatanPosyandu $kegiatan,
    Anak $anak,
    PemeriksaanPosyandu $pemeriksaan
) {
    $wilayahId = $this->wilayahId($request);
    $base = $this->base($request);

    abort_unless(in_array($base, ['kader', 'bidan'], true), 403);

$this->pastikanKegiatanDalamWilayah($request, $kegiatan);

    if ($base === 'kader') {
        abort_unless($this->kegiatanMasihAktif($kegiatan), 403);
    }

    $this->pastikanAnakDalamWilayah($request, $anak);

    abort_unless(
        (int) $pemeriksaan->anak_id === (int) $anak->id
        && (int) $pemeriksaan->kegiatan_posyandu_id === (int) $kegiatan->id,
        404
    );

    $this->pastikanBolehMengubahPemeriksaan(
        $request,
        $pemeriksaan,
        $base
    );

    $data = $request->validate([
        'tgl_pemeriksaan' => ['required', 'date'],
        'berat_badan' => ['required', 'numeric', 'min:0.1', 'max:100'],
        'tinggi_badan' => ['required', 'numeric', 'min:1', 'max:200'],
        'lingkar_kepala' => ['nullable', 'numeric', 'min:1', 'max:100'],
        'lila' => ['nullable', 'numeric', 'min:1', 'max:100'],
        'jadwal_vitamin' => ['nullable', 'string', 'max:255'],
        'catatan' => ['nullable', 'string', 'max:5000'],
    ]);

    if (! $anak->tgl_lahir) {
        throw ValidationException::withMessages([
            'tgl_pemeriksaan' => 'Tanggal lahir anak belum tersedia.',
        ]);
    }

    if (
        \Carbon\Carbon::parse($data['tgl_pemeriksaan'])
            ->lt(\Carbon\Carbon::parse($anak->tgl_lahir))
    ) {
        throw ValidationException::withMessages([
            'tgl_pemeriksaan' => 'Tanggal pemeriksaan tidak boleh sebelum tanggal lahir anak.',
        ]);
    }

    $calculator = new StuntingCalculator();

    $hasil = $calculator->hitung(
        $anak->jenis_kelamin,
        $anak->tgl_lahir,
        $data['tgl_pemeriksaan'],
        (float) $data['tinggi_badan']
    );

    $user = $request->user()->loadMissing(['kader', 'bidan']);

    if ($base === 'kader' && ! $user->kader) {
        abort(403, 'Profil Kader tidak ditemukan.');
    }

    if ($base === 'bidan' && ! $user->bidan) {
        abort(403, 'Profil Bidan tidak ditemukan.');
    }

    DB::transaction(function () use (
        $pemeriksaan,
        $data,
        $hasil,
        $user,
        $base
    ) {
        $pemeriksaan->update([
            'tgl_pemeriksaan' => $data['tgl_pemeriksaan'],
            'berat_badan' => $data['berat_badan'],
            'tinggi_badan' => $data['tinggi_badan'],
            'lingkar_kepala' => $data['lingkar_kepala'] ?? null,
            'lila' => $data['lila'] ?? null,

            'jadwal_vitamin' => $data['jadwal_vitamin'] ?? null,
            'catatan' => $data['catatan'] ?? null,

            'umur_bulan' => $hasil['umur_bulan'],
            'z_score_tbu' => $hasil['z_score_tbu'],
            'median_tbu' => $hasil['median_tbu'],
            'sd_minus_1_tbu' => $hasil['sd_minus_1_tbu'],
            'sd_plus_1_tbu' => $hasil['sd_plus_1_tbu'],
            'status_stunting' => $hasil['status_stunting'],
            'tingkat_risiko' => $hasil['tingkat_risiko'],

            'bidan_id' => $base === 'bidan'
                ? $user->bidan->id
                : $pemeriksaan->bidan_id,

            'pengubah_user_id' => $user->id,
        ]);
    });

    return redirect()
        ->route($base . '.data-posyandu.show', $kegiatan->id)
        ->with('success', 'Pemeriksaan anak berhasil diperbarui.');
}

    public function rekap(Request $request): View
    {
        $wilayahId = $this->wilayahId($request);

        $periode = $this->periode($request->input('periode'))
            ?? [now()->year, now()->month];

        $kegiatans = KegiatanPosyandu::query()
            ->with('wilayah')
            ->when(
                $wilayahId,
                fn ($q) => $q->where('id_wilayah', $wilayahId)
            )
            ->when(
                ! $wilayahId,
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->whereYear('tanggal', $periode[0])
            ->whereMonth('tanggal', $periode[1])
            ->when(
                $request->filled('id_wilayah'),
                fn ($q) => $q->where(
                    'id_wilayah',
                    $request->integer('id_wilayah')
                )
            )
            ->get();

        $jumlahAnak = $this->anakPerKegiatan($kegiatans->pluck('id'));

        $rekap = $kegiatans->groupBy('id_wilayah')
            ->map(fn ($grup) => [
                'wilayah' => $grup->first()->wilayah,
                'kegiatan' => $grup->count(),
                'anak' => $grup->sum(
                    fn ($k) => $jumlahAnak[$k->id] ?? 0
                ),
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
            'periodeValue' => sprintf(
                '%04d-%02d',
                $periode[0],
                $periode[1]
            ),
            'wilayahs' => WilayahKerja::query()
                ->when(
                    $wilayahId,
                    fn ($q) => $q->where('id', $wilayahId)
                )
                ->orderBy('nama_wilayah')
                ->get(),
            'base' => $this->base($request),
        ]);
    }

    /**
 * Memastikan kegiatan berada dalam cakupan wilayah pengguna.
 * Kader menggunakan cakupan kelurahan/desa.
 * Bidan tetap menggunakan wilayah kerja yang ditugaskan.
 */
private function pastikanKegiatanDalamWilayah(
    Request $request,
    KegiatanPosyandu $kegiatan
): void {
    $base = $this->base($request);

    if ($base === 'kader') {
        $kader = $request->user()
            ->loadMissing('kader.wilayahKerja')
            ->kader;

        $kelurahanDesaId = $kader?->wilayahKerja?->kelurahan_desa_id;

        $kegiatan->loadMissing('rw');

        $kelurahanKegiatanId = $kegiatan->rw?->kelurahan_desa_id;

        abort_unless(
            $kelurahanDesaId
            && $kelurahanKegiatanId
            && (int) $kelurahanDesaId === (int) $kelurahanKegiatanId,
            403,
            'Kegiatan berada di luar kelurahan/desa wilayah kerja Kader.'
        );

        return;
    }

    $wilayahId = $this->wilayahId($request);

    abort_unless(
        $wilayahId
        && (int) $kegiatan->id_wilayah === (int) $wilayahId,
        403,
        'Kegiatan berada di luar wilayah kerja.'
    );
}

/**
 * Memastikan anak berasal dari wilayah yang boleh diakses pengguna.
 * Kader menggunakan cakupan kelurahan/desa.
 * Bidan tetap menggunakan wilayah kerja yang ditugaskan.
 */
private function pastikanAnakDalamWilayah(
    Request $request,
    Anak $anak
): void {
    $base = $this->base($request);
    $wilayahId = $this->wilayahId($request);

    $query = DB::table('anak as a')
        ->join('ibu as i', 'i.id', '=', 'a.ibu_id')
        ->where('a.id', $anak->id);

    if ($base === 'kader') {
        $kader = $request->user()
            ->loadMissing('kader.wilayahKerja')
            ->kader;

        $kelurahanDesaId = $kader?->wilayahKerja?->kelurahan_desa_id;

        $terdaftar = $kelurahanDesaId
            ? $query
                ->join('wilayah_kerja as wk', 'wk.id', '=', 'i.wilayah_kerja_id')
                ->where('wk.kelurahan_desa_id', $kelurahanDesaId)
                ->exists()
            : false;
    } else {
        $terdaftar = $wilayahId
            ? $query
                ->where('i.wilayah_kerja_id', $wilayahId)
                ->exists()
            : false;
    }

    abort_unless(
        $terdaftar,
        403,
        'Anak berada di luar wilayah kerja Anda.'
    );
}

    /**
     * Kader hanya boleh mengubah pemeriksaan yang dicatat olehnya.
     * Bidan dapat mengubah pemeriksaan dalam wilayah kerjanya.
     */
    private function pastikanBolehMengubahPemeriksaan(
        Request $request,
        PemeriksaanPosyandu $pemeriksaan,
        string $base
    ): void {
        if ($base === 'kader') {
            $kaderId = $request->user()
                ->loadMissing('kader')
                ->kader?->id;

            abort_unless(
                $kaderId
                && (int) $pemeriksaan->kader_id === (int) $kaderId,
                403
            );
        }
    }

    private function base(Request $request): string
    {
        return $request->route()?->getName()
            ? Str::before($request->route()->getName(), '.')
            : 'admin';
    }

    private function wilayahId(Request $request): ?int
    {
        $user = $request->user()->loadMissing([
            'admin',
            'bidan',
            'kader',
        ]);

        return $user->managedWilayahId()
            ?? $user->bidan?->wilayah_kerja_id
            ?? $user->kader?->wilayah_kerja_id;
    }

    /**
     * "2026-10" menjadi [2026, 10].
     *
     * @return array{0:int,1:int}|null
     */
    private function periode(?string $nilai): ?array
    {
        return preg_match(
            '/^(\d{4})-(0[1-9]|1[0-2])$/',
            (string) $nilai,
            $m
        )
            ? [(int) $m[1], (int) $m[2]]
            : null;
    }

    private function daftarPeriode(?int $wilayahId)
    {
        return KegiatanPosyandu::query()
            ->when(
                $wilayahId,
                fn ($q) => $q->where('id_wilayah', $wilayahId)
            )
            ->when(
                ! $wilayahId,
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->pluck('tanggal')
            ->map(
                fn ($tanggal) => Carbon::parse($tanggal)->format('Y-m')
            )
            ->unique()
            ->sortDesc()
            ->values();
    }



private function kegiatanMasihAktif(KegiatanPosyandu $kegiatan): bool
{
    if (! $kegiatan->tanggal || ! $kegiatan->waktu) {
        return false;
    }

    $tanggal = Carbon::parse($kegiatan->tanggal)->format('Y-m-d');
    $waktu = Carbon::parse($kegiatan->waktu)->format('H:i:s');

    $waktuMulai = Carbon::parse($tanggal . ' ' . $waktu);

    return $waktuMulai->between(
        now()->subHours(24),
        now()
    );
}



    private function tabelPemeriksaanAda(): bool
    {
        return Schema::hasTable('pemeriksaan_posyandu')
            && Schema::hasColumn(
                'pemeriksaan_posyandu',
                'kegiatan_posyandu_id'
            )
            && Schema::hasColumn(
                'pemeriksaan_posyandu',
                'anak_id'
            );
    }

    private function jumlahAnakDiperiksa($ids): int
    {
        if (! $this->tabelPemeriksaanAda() || $ids->isEmpty()) {
            return 0;
        }

        return (int) DB::table('pemeriksaan_posyandu')
            ->whereIn('kegiatan_posyandu_id', $ids)
            ->distinct('anak_id')
            ->count('anak_id');
    }

    /**
     * @return array<int, int>
     */
    private function anakPerKegiatan($ids): array
    {
        if (! $this->tabelPemeriksaanAda() || $ids->isEmpty()) {
            return [];
        }

        return DB::table('pemeriksaan_posyandu')
            ->whereIn('kegiatan_posyandu_id', $ids)
            ->selectRaw(
                'kegiatan_posyandu_id, count(*) as n'
            )
            ->groupBy('kegiatan_posyandu_id')
            ->pluck('n', 'kegiatan_posyandu_id')
            ->all();
    }
}
