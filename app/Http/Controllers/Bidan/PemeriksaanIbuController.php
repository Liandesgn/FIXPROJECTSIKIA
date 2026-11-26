<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Ibu;
use App\Models\PemeriksaanIbu;
use App\Models\LogPerubahanPemeriksaan;
use App\Notifications\SikiaNotification;
use App\Services\PemeriksaanIbuService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PemeriksaanIbuController extends Controller
{
    public function __construct(
        protected PemeriksaanIbuService $pemeriksaanIbuService
    ) {}

    /**
     * Daftar ibu hamil di wilayah kerja bidan.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $bidan = $user->bidan;

        abort_unless($bidan, 403, 'Akun bidan tidak ditemukan.');

        $query = Ibu::query()
            ->with(['user', 'wilayahKerja'])
            ->where('wilayah_kerja_id', $bidan->wilayah_kerja_id)
            ->where('status_kehamilan', 'hamil');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('risiko')) {
            $query->whereHas('pemeriksaanIbu', function ($q) use ($request) {
                $q->where('tingkat_risiko', $request->risiko)
                    ->whereRaw('tgl_pemeriksaan = (
                        SELECT MAX(pi2.tgl_pemeriksaan)
                        FROM pemeriksaan_ibu AS pi2
                        WHERE pi2.ibu_id = ibu.id
                    )');
            });
        }

        $ibu = $query
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('bidan.pemeriksaan-ibu.index', compact('ibu'));
    }

    /**
     * Detail ibu hamil khusus halaman Bidan.
     */
public function show(Ibu $ibu)
{
    $this->pastikanWilayahBidan($ibu);

    $ibu->load([
        'user',
        'wilayahKerja',
        'anak',
    ]);

    // Ambil seluruh riwayat pemeriksaan satu kali.
    $riwayatPemeriksaanIbu = PemeriksaanIbu::where('ibu_id', $ibu->id)
        ->orderByDesc('tgl_pemeriksaan')
        ->orderByDesc('id')
        ->get();

    // Pemeriksaan pertama dalam koleksi adalah pemeriksaan terbaru.
    $pemeriksaanTerakhir = $riwayatPemeriksaanIbu->first();

    return view('bidan.pemeriksaan-ibu.show', compact(
        'ibu',
        'pemeriksaanTerakhir',
        'riwayatPemeriksaanIbu'
    ));
}

    /**
     * Detail status risiko kehamilan khusus halaman Bidan.
     */
    public function status(Ibu $ibu)
    {
        $this->pastikanWilayahBidan($ibu);

        $ibu->load('user');

        $pemeriksaan = PemeriksaanIbu::where('ibu_id', $ibu->id)
            ->orderByDesc('tgl_pemeriksaan')
            ->orderByDesc('id')
            ->first();

        if (!$pemeriksaan) {
            return redirect()
                ->route('bidan.pemeriksaan-ibu.show', $ibu->id)
                ->with('warning', 'Belum ada data pemeriksaan ibu hamil.');
        }

        return view('bidan.pemeriksaan-ibu.status', compact(
            'ibu',
            'pemeriksaan'
        ));
    }

    /**
     * Form pemeriksaan baru.
     */
    public function create(Ibu $ibu)
    {
        $this->pastikanWilayahBidan($ibu);

        abort_unless($ibu->sedangHamil(), 422, 'Ibu tidak sedang hamil.');

        $tanggalPemeriksaan = now();

        $usiaKehamilan = $this->pemeriksaanIbuService
            ->hitungUsiaKehamilan($ibu, $tanggalPemeriksaan);

        return view('bidan.pemeriksaan-ibu.form', [
            'ibu' => $ibu->load('user'),
            'pemeriksaan' => null,
            'tanggalPemeriksaan' => $tanggalPemeriksaan,
            'usiaKehamilan' => $usiaKehamilan,
        ]);
    }

    /**
     * Simpan pemeriksaan baru.
     */
    public function store(Request $request, Ibu $ibu)
    {
        $this->pastikanWilayahBidan($ibu);

        abort_unless($ibu->sedangHamil(), 422, 'Ibu tidak sedang hamil.');

        $data = $this->validasi($request);

        $tanggal = now()->startOfDay();

        $data['tgl_pemeriksaan'] = $tanggal->toDateString();

        $usiaKehamilan = $this->pemeriksaanIbuService
            ->hitungUsiaKehamilan($ibu, $tanggal);

        $data['usia_kehamilan'] = $usiaKehamilan;

        $risiko = $this->pemeriksaanIbuService
            ->hitungRisiko($ibu, $data);

        $hpl = $this->pemeriksaanIbuService
            ->hitungHpl($usiaKehamilan, $tanggal);

        DB::transaction(function () use (
            $ibu,
            $data,
            $risiko,
            $hpl
        ) {
            $pemeriksaan = PemeriksaanIbu::create([
                ...$data,
                'ibu_id' => $ibu->id,
                'id_bidan' => Auth::user()->bidan->id,
                'usia_kehamilan' => $data['usia_kehamilan'],
                'skor_risiko' => $risiko['skor_risiko'],
                'tingkat_risiko' => $risiko['tingkat_risiko'],
                'faktor_risiko' => implode('; ', $risiko['faktor_risiko']),
                'status_kesehatan' => $risiko['tingkat_risiko'] === 'tinggi'
                    ? PemeriksaanIbu::PERLU_PERHATIAN
                    : PemeriksaanIbu::NORMAL,
            ]);

            DB::table('ibu')
                ->where('id', $ibu->id)
                ->update([
                    'hpl' => $hpl->toDateString(),
                    'updated_at' => now(),
                ]);

            $this->catatLog(
                $pemeriksaan,
                'buat',
                null,
                $pemeriksaan->only([
                    'tgl_pemeriksaan',
                    'usia_kehamilan',
                    'berat_badan',
                    'tinggi_badan',
                    'lila',
                    'hb',
                    'tekanan_sistolik',
                    'tekanan_diastolik',
                    'proteinuria',
                    'gejala_berat',
                    'keluhan',
                    'kondisi_kehamilan',
                    'skor_risiko',
                    'tingkat_risiko',
                    'faktor_risiko',
                ])
            );
        });

        $this->kirimNotifikasiJikaRisikoTinggi($ibu, $risiko);

        return redirect()
            ->route('bidan.pemeriksaan-ibu.index')
            ->with('success', 'Pemeriksaan ibu hamil berhasil disimpan.');
    }

    /**
     * Form edit pemeriksaan.
     */
    public function edit(Ibu $ibu, PemeriksaanIbu $pemeriksaan)
    {
        $this->pastikanWilayahBidan($ibu);
        $this->pastikanPemeriksaanMilikIbu($ibu, $pemeriksaan);

        $tanggalPemeriksaan = Carbon::parse($pemeriksaan->tgl_pemeriksaan);

        $usiaKehamilan = $this->pemeriksaanIbuService
            ->hitungUsiaKehamilan($ibu, $tanggalPemeriksaan);

        return view('bidan.pemeriksaan-ibu.form', [
            'ibu' => $ibu->load('user'),
            'pemeriksaan' => $pemeriksaan,
            'tanggalPemeriksaan' => $tanggalPemeriksaan,
            'usiaKehamilan' => $usiaKehamilan,
        ]);
    }

    /**
     * Update pemeriksaan dan simpan log perubahan.
     */
    public function update(
        Request $request,
        Ibu $ibu,
        PemeriksaanIbu $pemeriksaan
    ) {
        $this->pastikanWilayahBidan($ibu);
        $this->pastikanPemeriksaanMilikIbu($ibu, $pemeriksaan);

        $data = $this->validasi($request);

        $tanggal = Carbon::parse($pemeriksaan->tgl_pemeriksaan);

        $data['tgl_pemeriksaan'] = $tanggal->toDateString();

        $usiaKehamilan = $this->pemeriksaanIbuService
            ->hitungUsiaKehamilan($ibu, $tanggal);

        $data['usia_kehamilan'] = $usiaKehamilan;

        $risiko = $this->pemeriksaanIbuService
            ->hitungRisiko($ibu, $data);

        $hpl = $this->pemeriksaanIbuService
            ->hitungHpl($usiaKehamilan, $tanggal);

        $kolom = [
            'tgl_pemeriksaan',
            'usia_kehamilan',
            'berat_badan',
            'tinggi_badan',
            'lila',
            'hb',
            'tekanan_sistolik',
            'tekanan_diastolik',
            'proteinuria',
            'gejala_berat',
            'keluhan',
            'kondisi_kehamilan',
            'skor_risiko',
            'tingkat_risiko',
            'faktor_risiko',
            'status_kesehatan',
        ];

        $dataBaru = [
            ...$data,
            'skor_risiko' => $risiko['skor_risiko'],
            'tingkat_risiko' => $risiko['tingkat_risiko'],
            'faktor_risiko' => implode('; ', $risiko['faktor_risiko']),
            'status_kesehatan' => $risiko['tingkat_risiko'] === 'tinggi'
                ? PemeriksaanIbu::PERLU_PERHATIAN
                : PemeriksaanIbu::NORMAL,
        ];

        DB::transaction(function () use (
            $pemeriksaan,
            $ibu,
            $dataBaru,
            $kolom,
            $hpl
        ) {
            $dataLama = $pemeriksaan->only($kolom);

            $pemeriksaan->update($dataBaru);

            DB::table('ibu')
                ->where('id', $ibu->id)
                ->update([
                    'hpl' => $hpl->toDateString(),
                    'updated_at' => now(),
                ]);

            $this->catatLog(
                $pemeriksaan,
                'ubah',
                $dataLama,
                $pemeriksaan->fresh()->only($kolom)
            );
        });

        $this->kirimNotifikasiJikaRisikoTinggi($ibu, $risiko);

        return redirect()
            ->route('bidan.pemeriksaan-ibu.index')
            ->with('success', 'Pemeriksaan ibu hamil berhasil diperbarui.');
    }

    /**
     * Validasi input pemeriksaan.
     */
    private function validasi(Request $request): array
    {
        return $request->validate([
            'berat_badan' => ['required', 'numeric', 'between:20,250'],
            'tinggi_badan' => ['required', 'numeric', 'between:100,220'],
            'lila' => ['required', 'numeric', 'between:10,60'],
            'hb' => ['required', 'numeric', 'between:3,25'],
            'tekanan_sistolik' => ['required', 'integer', 'between:50,250'],
            'tekanan_diastolik' => ['required', 'integer', 'between:30,160'],
            'proteinuria' => ['required', 'in:negatif,+,1+,2+,3+,4+'],
            'gejala_berat' => ['required', 'boolean'],
            'keluhan' => ['nullable', 'string', 'max:2000'],
            'kondisi_kehamilan' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /**
     * Pastikan bidan hanya mengakses ibu dalam wilayah kerjanya.
     */
    private function pastikanWilayahBidan(Ibu $ibu): void
    {
        $bidan = Auth::user()->bidan;

        abort_unless(
            $bidan &&
            (int) $ibu->wilayah_kerja_id === (int) $bidan->wilayah_kerja_id,
            403,
            'Ibu tidak berada dalam wilayah kerja Anda.'
        );
    }

    /**
     * Pastikan pemeriksaan benar-benar milik ibu tersebut.
     */
    private function pastikanPemeriksaanMilikIbu(
        Ibu $ibu,
        PemeriksaanIbu $pemeriksaan
    ): void {
        abort_unless(
            (int) $pemeriksaan->ibu_id === (int) $ibu->id,
            404
        );
    }

    /**
     * Simpan riwayat perubahan.
     */
    private function catatLog(
        PemeriksaanIbu $pemeriksaan,
        string $aksi,
        ?array $dataLama,
        ?array $dataBaru
    ): void {
        LogPerubahanPemeriksaan::create([
            'pemeriksaan_ibu_id' => $pemeriksaan->id,
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'data_lama' => $dataLama,
            'data_baru' => $dataBaru,
        ]);
    }

    /**
     * Kirim notifikasi kepada bidan jika risiko tinggi.
     */
    private function kirimNotifikasiJikaRisikoTinggi(
        Ibu $ibu,
        array $risiko
    ): void {
        if ($risiko['tingkat_risiko'] !== 'tinggi') {
            return;
        }

        $namaIbu = $ibu->user?->name ?? 'Ibu hamil';

        $judul = 'Peringatan Risiko Kehamilan Tinggi';

        $isi = "Pemeriksaan {$namaIbu} menunjukkan risiko kehamilan tinggi. "
            . 'Skor risiko: ' . $risiko['skor_risiko'] . '. '
            . 'Faktor: ' . implode(', ', $risiko['faktor_risiko']);

        Auth::user()->notify(new SikiaNotification(
            'risiko_kehamilan',
            $judul,
            $isi,
            [
                'ibu_id' => $ibu->id,
                'tingkat_risiko' => $risiko['tingkat_risiko'],
                'skor_risiko' => $risiko['skor_risiko'],
                'perlu_rujukan_segera' => $risiko['perlu_rujukan_segera'],
            ]
        ));
    }
}
