<?php

namespace App\Http\Controllers\Bidan;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\ImunisasiAnak;
use App\Models\JadwalImunisasi;
use App\Models\JadwalVitamin;
use App\Models\KegiatanPosyandu;
use App\Models\PemeriksaanPosyandu;
use App\Services\StuntingCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PemeriksaanAnakController extends Controller
{
    /**
     * Menampilkan form edit pemeriksaan anak oleh Bidan.
     */
    public function edit(
        Request $request,
        KegiatanPosyandu $kegiatan,
        Anak $anak,
        PemeriksaanPosyandu $pemeriksaan
    ): View {
        $wilayahId = $request->user()
            ->loadMissing('bidan')
            ->bidan?->wilayah_kerja_id;

        abort_unless(
            $wilayahId && (int) $kegiatan->id_wilayah === (int) $wilayahId,
            403,
            'Kegiatan berada di luar wilayah kerja Anda.'
        );

        $this->pastikanAnakDalamWilayah($anak, $wilayahId);

        abort_unless(
            (int) $pemeriksaan->anak_id === (int) $anak->id
            && (int) $pemeriksaan->kegiatan_posyandu_id === (int) $kegiatan->id,
            404
        );

        $anak->loadMissing('ibu');

        $pemeriksaan->load([
            'imunisasiAnak.jadwalImunisasi',
            'jadwalVitamin',
        ]);

        $tanggalPemeriksaan = Carbon::parse($pemeriksaan->tgl_pemeriksaan);
        $tanggalLahir = Carbon::parse($anak->tgl_lahir);

        abort_if(
            $tanggalPemeriksaan->lt($tanggalLahir),
            422,
            'Tanggal pemeriksaan tidak boleh sebelum tanggal lahir anak.'
        );

        $umurBulan = (int) $tanggalLahir->diffInMonths($tanggalPemeriksaan);

        $vaksinTerpilih = $pemeriksaan->imunisasiAnak
            ->pluck('id_jadwal_imunisasi')
            ->map(fn ($id) => (int) $id)
            ->all();

        $vitaminTerpilih = $pemeriksaan->id_jadwal_vitamin;

        $jadwalImunisasi = $this->jadwalImunisasiTersedia(
            $anak,
            $umurBulan,
            $pemeriksaan,
            $vaksinTerpilih
        );

        $jadwalVitamin = $this->jadwalVitaminTersedia(
            $umurBulan,
            $vitaminTerpilih
        )->get();

        return view('bidan.pemeriksaan-anak.edit', [
            'kegiatan' => $kegiatan,
            'anak' => $anak,
            'pemeriksaan' => $pemeriksaan,
            'umurBulan' => $umurBulan,
            'jadwalImunisasi' => $jadwalImunisasi,
            'jadwalVitamin' => $jadwalVitamin,
            'vaksinTerpilih' => $vaksinTerpilih,
            'vitaminTerpilih' => $vitaminTerpilih,
        ]);
    }

    /**
     * Memperbarui pemeriksaan, pilihan vaksin, dan vitamin.
     */
    public function update(
        Request $request,
        KegiatanPosyandu $kegiatan,
        Anak $anak,
        PemeriksaanPosyandu $pemeriksaan
    ) {
        $user = $request->user()->loadMissing('bidan');
        $wilayahId = $user->bidan?->wilayah_kerja_id;

        abort_unless(
            $wilayahId && (int) $kegiatan->id_wilayah === (int) $wilayahId,
            403,
            'Kegiatan berada di luar wilayah kerja Anda.'
        );

        $this->pastikanAnakDalamWilayah($anak, $wilayahId);

        abort_unless(
            (int) $pemeriksaan->anak_id === (int) $anak->id
            && (int) $pemeriksaan->kegiatan_posyandu_id === (int) $kegiatan->id,
            404
        );

        $data = $request->validate([
            'tgl_pemeriksaan' => ['required', 'date'],
            'berat_badan' => ['required', 'numeric', 'min:0.1', 'max:100'],
            'tinggi_badan' => ['required', 'numeric', 'min:1', 'max:200'],
            'lingkar_kepala' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'lila' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:5000'],

            'vaksin' => ['nullable', 'array'],
            'vaksin.*' => [
                'required',
                'integer',
                'distinct',
                'exists:jadwal_imunisasi,id_jadwal_imunisasi',
            ],

            'id_jadwal_vitamin' => [
                'nullable',
                'integer',
                'exists:jadwal_vitamin,id_jadwal_vitamin',
            ],
        ]);

        if (! $anak->tgl_lahir) {
            throw ValidationException::withMessages([
                'tgl_pemeriksaan' => 'Tanggal lahir anak belum tersedia.',
            ]);
        }

        $tanggalLahir = Carbon::parse($anak->tgl_lahir);
        $tanggalPemeriksaan = Carbon::parse($data['tgl_pemeriksaan']);

        if ($tanggalPemeriksaan->lt($tanggalLahir)) {
            throw ValidationException::withMessages([
                'tgl_pemeriksaan' => 'Tanggal pemeriksaan tidak boleh sebelum tanggal lahir anak.',
            ]);
        }

        $umurBulan = (int) $tanggalLahir->diffInMonths($tanggalPemeriksaan);

        $vaksinIds = collect($data['vaksin'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $vaksinLama = $pemeriksaan->imunisasiAnak()
            ->pluck('id_jadwal_imunisasi')
            ->map(fn ($id) => (int) $id)
            ->all();

        $vaksinTersedia = $this->jadwalImunisasiTersedia(
            $anak,
            $umurBulan,
            $pemeriksaan,
            $vaksinLama
        )->pluck('id_jadwal_imunisasi')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (array_diff($vaksinIds, $vaksinTersedia)) {
            throw ValidationException::withMessages([
                'vaksin' => 'Ada vaksin yang tidak sesuai jadwal umur atau sudah pernah diberikan.',
            ]);
        }

        $vitaminId = $data['id_jadwal_vitamin'] ?? null;

        if ($vitaminId) {
            $vitaminTersedia = $this->jadwalVitaminTersedia(
                $umurBulan,
                $pemeriksaan->id_jadwal_vitamin
            )->where('id_jadwal_vitamin', $vitaminId)
                ->exists();

            if (! $vitaminTersedia) {
                throw ValidationException::withMessages([
                    'id_jadwal_vitamin' => 'Vitamin tidak sesuai dengan jadwal umur anak.',
                ]);
            }
        }

        $hasil = (new StuntingCalculator())->hitung(
            $anak->jenis_kelamin,
            $anak->tgl_lahir,
            $data['tgl_pemeriksaan'],
            (float) $data['tinggi_badan']
        );

        $vitamin = $vitaminId
            ? JadwalVitamin::findOrFail($vitaminId)
            : null;

        DB::transaction(function () use (
            $pemeriksaan,
            $data,
            $hasil,
            $user,
            $vaksinIds,
            $vitaminId,
            $vitamin
        ) {
            $pemeriksaan->update([
                'tgl_pemeriksaan' => $data['tgl_pemeriksaan'],
                'berat_badan' => $data['berat_badan'],
                'tinggi_badan' => $data['tinggi_badan'],
                'lingkar_kepala' => $data['lingkar_kepala'] ?? null,
                'lila' => $data['lila'] ?? null,

                'id_jadwal_vitamin' => $vitaminId,
                'jadwal_vitamin' => $vitamin?->jenis_vitamin,

                'catatan' => $data['catatan'] ?? null,

                'umur_bulan' => $hasil['umur_bulan'],
                'z_score_tbu' => $hasil['z_score_tbu'],
                'median_tbu' => $hasil['median_tbu'],
                'sd_minus_1_tbu' => $hasil['sd_minus_1_tbu'],
                'sd_plus_1_tbu' => $hasil['sd_plus_1_tbu'],
                'status_stunting' => $hasil['status_stunting'],
                'tingkat_risiko' => $hasil['tingkat_risiko'],

                'bidan_id' => $user->bidan->id,
                'pengubah_user_id' => $user->id,
            ]);

            $pemeriksaan->imunisasiAnak()->delete();

            foreach ($vaksinIds as $vaksinId) {
                ImunisasiAnak::create([
                    'id_pemeriksaan' => $pemeriksaan->id,
                    'id_jadwal_imunisasi' => $vaksinId,
                ]);
            }
        });

        return redirect()
            ->route('bidan.data-posyandu.show', $kegiatan->id)
            ->with('success', 'Pemeriksaan anak berhasil diperbarui.');
    }

  private function jadwalImunisasiTersedia(
    Anak $anak,
    int $umurBulan,
    PemeriksaanPosyandu $pemeriksaan,
    array $vaksinTerpilih = []
): Collection {
    $pemeriksaanId = $pemeriksaan->getKey();
    $anakId = $anak->getKey();

    $jadwal = JadwalImunisasi::query()
        ->where('usia_min_bulan', '<=', $umurBulan)
        ->where(function ($query) use ($umurBulan) {
            $query->where(function ($q) use ($umurBulan) {
                // Jadwal tanpa usia maksimal hanya berlaku
                // tepat pada usia minimalnya.
                $q->whereNull('usia_maks_bulan')
                    ->where('usia_min_bulan', $umurBulan);
            })->orWhere(function ($q) use ($umurBulan) {
                $q->whereNotNull('usia_maks_bulan')
                    ->where('usia_maks_bulan', '>=', $umurBulan);
            });
        })
        ->where(function ($query) use (
            $anakId,
            $pemeriksaanId,
            $vaksinTerpilih
        ) {
            $query->whereIn(
                'id_jadwal_imunisasi',
                $vaksinTerpilih
            )->orWhereNotExists(function ($subQuery) use (
                $anakId,
                $pemeriksaanId
            ) {
                $subQuery->select(DB::raw(1))
                    ->from('imunisasi_anak as ia')
                    ->join(
                        'pemeriksaan_posyandu as pp',
                        'pp.id',
                        '=',
                        'ia.id_pemeriksaan'
                    )
                    ->whereColumn(
                        'ia.id_jadwal_imunisasi',
                        'jadwal_imunisasi.id_jadwal_imunisasi'
                    )
                    ->where('pp.anak_id', $anakId)
                    ->where('pp.id', '!=', $pemeriksaanId);
            });
        })
        ->orderBy('usia_min_bulan')
        ->get();

    // HPV hanya untuk anak perempuan (P).
    $jadwal = $jadwal->reject(function ($item) use ($anak) {
        return Str::contains(
            Str::lower($item->jenis_vaksin),
            'hpv'
        ) && strtoupper($anak->jenis_kelamin) !== 'P';
    });

    // JE hanya untuk anak di Kecamatan Gresik
    // sebagai konfigurasi simulasi project.
    $kecamatanAnak = DB::table('anak as a')
        ->join('ibu as i', 'i.id', '=', 'a.ibu_id')
        ->join('wilayah_kerja as wk', 'wk.id', '=', 'i.wilayah_kerja_id')
        ->join('kelurahan_desa as kd', 'kd.id', '=', 'wk.kelurahan_desa_id')
        ->join('kecamatan as k', 'k.id', '=', 'kd.kecamatan_id')
        ->where('a.id', $anakId)
        ->value('k.nama_kecamatan');

    if (mb_strtolower((string) $kecamatanAnak) !== 'gresik') {
        $jadwal = $jadwal->reject(function ($item) {
            return Str::lower($item->jenis_vaksin) === 'je';
        });
    }

    // Hepatitis B-0:
    // Menggunakan created_at pemeriksaan sebagai pendekatan waktu
    // pencatatan, karena tabel anak tidak memiliki jam lahir.
    // Ini bukan validasi akurat waktu pemberian vaksin.
    $batasHepatitisB0 = Carbon::parse($anak->tgl_lahir)
        ->startOfDay()
        ->addHours(24);

    $waktuPencatatan = $pemeriksaan->created_at;

    if (
        $waktuPencatatan
        && $waktuPencatatan->greaterThan($batasHepatitisB0)
    ) {
        $jadwal = $jadwal->reject(function ($item) use ($vaksinTerpilih) {
            $isHepatitisB0 = Str::contains(
                Str::lower($item->jenis_vaksin),
                'hepatitis b-0'
            );

            $sudahDipilih = in_array(
                (int) $item->id_jadwal_imunisasi,
                $vaksinTerpilih,
                true
            );

            return $isHepatitisB0 && ! $sudahDipilih;
        });
    }

    return $jadwal->values();
}

private function jadwalVitaminTersedia(
    int $umurBulan,
    ?int $vitaminTerpilih = null
) {
    return JadwalVitamin::query()
        ->where(function ($query) use ($umurBulan, $vitaminTerpilih) {
            $query->where(function ($usia) use ($umurBulan) {
                $usia->where('usia_min_bulan', '<=', $umurBulan)
                    ->where(function ($maksimal) use ($umurBulan) {
                        $maksimal->whereNull('usia_maks_bulan')
                            ->orWhere('usia_maks_bulan', '>=', $umurBulan);
                    });
            });

            if ($vitaminTerpilih) {
                $query->orWhere(
                    'id_jadwal_vitamin',
                    $vitaminTerpilih
                );
            }
        });
}


    private function pastikanAnakDalamWilayah(
        Anak $anak,
        int $wilayahId
    ): void {
        $terdaftar = DB::table('anak as a')
            ->join('ibu as i', 'i.id', '=', 'a.ibu_id')
            ->where('a.id', $anak->id)
            ->where('i.wilayah_kerja_id', $wilayahId)
            ->exists();

        abort_unless(
            $terdaftar,
            403,
            'Anak berada di luar wilayah kerja Anda.'
        );
    }
}