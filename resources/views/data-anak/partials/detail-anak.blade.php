
@php
    $ibu = $anak->ibu;

    $riwayatPemeriksaan = $riwayatPemeriksaan ?? collect();
    $pemeriksaanTerakhir = $pemeriksaanTerakhir ?? null;

    $formatTanggal = fn ($t) => $t
        ? \Carbon\Carbon::parse($t)->format('d/m/Y')
        : '-';

    $angka = fn ($v) => ($v === null || $v === '')
        ? '-'
        : number_format((float) $v, 1, ',', '.');

    // Mengambil kolom secara aman.
    $ambil = function ($model, array $kolom) {
        if (! $model) {
            return null;
        }

        $atribut = $model->getAttributes();

        foreach ($kolom as $k) {
            if (
                array_key_exists($k, $atribut)
                && $atribut[$k] !== null
                && $atribut[$k] !== ''
            ) {
                return $atribut[$k];
            }
        }

        return null;
    };

    // Alamat ibu.
    $alamatDari = function ($w) {
        if (! $w) {
            return '-';
        }

        $relasi = function (string $nama) use ($w) {
            if ($w->relationLoaded($nama)) {
                return $w->getRelation($nama);
            }

            return method_exists($w, $nama)
                ? $w->{$nama}()->first()
                : null;
        };

        $kec = $relasi('kecamatan');
        $desa = $relasi('kelurahanDesa');
        $rw = $relasi('rw');
        $rt = $relasi('rt');

        $kolomKec = $w->getAttribute('kecamatan');
        $kolomDesa = $w->getAttribute('kelurahan');

        $namaKec = $kec?->nama_kecamatan
            ?? (is_string($kolomKec) && $kolomKec !== '' ? $kolomKec : null);

        $bagian = [];

        if ($namaKec) {
            $bagian[] = 'Kec. ' . $namaKec;
        }

        if ($desa?->nama_kelurahan_desa) {
            $bagian[] = (
                strtolower((string) $desa->jenis) === 'kelurahan'
                    ? 'Kel. '
                    : 'Desa '
            ) . $desa->nama_kelurahan_desa;
        } elseif (is_string($kolomDesa) && $kolomDesa !== '') {
            $bagian[] = 'Desa ' . $kolomDesa;
        }

        $nomor = array_filter([
            $rt?->nomor_rt ? 'RT ' . $rt->nomor_rt : null,
            $rw?->nomor_rw ? 'RW ' . $rw->nomor_rw : null,
        ]);

        if ($nomor) {
            $bagian[] = implode('/', $nomor);
        }

        return $bagian
            ? implode(', ', $bagian)
            : ($w->getAttribute('nama_wilayah') ?: '-');
    };

    $warnaStatus = fn ($s) => match ($s) {
        'normal' => '#16a34a',
        'perlu_perhatian' => '#dc2626',
        default => '#94a3b8',
    };

    $labelStatus = fn ($s) => match ($s) {
        'normal' => 'Normal',
        'perlu_perhatian' => 'Perlu Perhatian',
        default => 'Belum ada status',
    };

    // Usia anak saat ini.
    $tanggalLahirAnak = $ambil($anak, [
        'tanggal_lahir',
        'tgl_lahir',
    ]);

    $tglLahir = $tanggalLahirAnak
        ? \Carbon\Carbon::parse($tanggalLahirAnak)
        : null;

    $usiaAnak = $tglLahir
        ? (int) $tglLahir->diffInMonths(now())
        : null;

    $labelUsia = $usiaAnak !== null
        ? $usiaAnak . ' bulan'
        : '-';

    // Data pemeriksaan terakhir.
    $statusTerakhir = $pemeriksaanTerakhir?->status_kesehatan;
    $beratTerakhir = $pemeriksaanTerakhir?->berat_badan;
    $tinggiTerakhir = $pemeriksaanTerakhir?->tinggi_badan;
    $tanggalTerakhir = $pemeriksaanTerakhir?->kegiatan?->tanggal;

    // Siapkan data grafik berat badan dan tinggi badan.
    $kolomGrafik = [
        'berat_badan' => [
            'label' => 'Berat Badan',
            'satuan' => 'kg',
            'warna' => '#14A89B',
        ],
        'tinggi_badan' => [
            'label' => 'Tinggi Badan',
            'satuan' => 'cm',
            'warna' => '#C9517E',
        ],
    ];

    // Periksa apakah setidaknya ada satu hasil pengukuran.
    $adaPengukuran = $riwayatPemeriksaan->contains(
        fn ($item) =>
            $item->berat_badan !== null
            || $item->tinggi_badan !== null
    );

    $dataGrafik = [];

    foreach ($kolomGrafik as $kolom => $konfigurasi) {
        $data = $riwayatPemeriksaan
            ->filter(fn ($item) => $item->{$kolom} !== null)
            ->values()
            ->take(-12)
            ->values();

        $jumlah = $data->count();
        $minimum = $jumlah > 0 ? (float) $data->min($kolom) : 0;
        $maksimum = $jumlah > 0 ? (float) $data->max($kolom) : 0;
        $rentang = max($maksimum - $minimum, 1);

        $xKiri = 70;
        $xKanan = 570;
        $yAtas = 40;
        $yBawah = 170;

        $points = [];

        foreach ($data as $index => $item) {
            $x = $jumlah > 1
                ? $xKiri + ($index * (($xKanan - $xKiri) / ($jumlah - 1)))
                : ($xKiri + $xKanan) / 2;

            $nilai = (float) $item->{$kolom};

            $y = $maksimum === $minimum
                ? ($yAtas + $yBawah) / 2
                : $yBawah - (
                    (($nilai - $minimum) / $rentang)
                    * ($yBawah - $yAtas)
                );

            $tanggal = $item->kegiatan?->tanggal;

            $points[] = [
                'x' => round($x, 1),
                'y' => round($y, 1),
                'nilai' => $nilai,
                'tanggal' => $tanggal,
                'status' => $item->status_kesehatan,
            ];
        }

        $dataGrafik[$kolom] = [
            'label' => $konfigurasi['label'],
            'satuan' => $konfigurasi['satuan'],
            'warna' => $konfigurasi['warna'],
            'jumlah' => $jumlah,
            'minimum' => $minimum,
            'maksimum' => $maksimum,
            'points' => $points,
        ];
    }

    // Riwayat pemeriksaan terbaru.
    $riwayatTerbaru = $riwayatPemeriksaan
        ->sortByDesc(fn ($item) => $item->kegiatan?->tanggal)
        ->values();

    $batasRiwayat = 4;
    $adaLebih = $riwayatTerbaru->count() > $batasRiwayat;

    // Filter periode untuk tampilan gabungan role Ibu.
    $filterPeriode = ! empty($tampilanGabungan);

    $daftarPeriode = $riwayatTerbaru
        ->map(fn ($item) => $item->kegiatan?->tanggal
            ? \Carbon\Carbon::parse($item->kegiatan->tanggal)->format('Y-m')
            : null
        )
        ->filter()
        ->unique()
        ->values();

    // Data identitas anak.
    $nikAnak = $ambil($anak, ['nik_anak', 'nik']);
    $tempatLahirAnak = $ambil($anak, ['tempat_lahir']);
    $jenisKelaminAnak = $ambil($anak, ['jenis_kelamin', 'jk']);

    $jenisKelaminAnak = match (strtoupper((string) $jenisKelaminAnak)) {
        'L', 'LAKI-LAKI' => 'Laki-laki',
        'P', 'PEREMPUAN' => 'Perempuan',
        default => $jenisKelaminAnak ?: '-',
    };

    // Data identitas ibu.
    $nikIbu = $ambil($ibu, ['nik']);
    $tanggalLahirIbu = $ambil($ibu, ['tanggal_lahir']);

    $nomorHp = $ambil($ibu, [
        'nomor_hp',
        'no_hp',
        'no_telepon',
        'telepon',
        'phone',
    ]) ?? $ambil($ibu?->user, [
        'nomor_hp',
        'no_hp',
        'no_telepon',
        'telepon',
        'phone',
    ]);

    $wilayahIbu = $alamatDari($ibu?->wilayahKerja);

    // Kelas tampilan.
    $kartu = 'rounded-xl border border-[#D9D9D9] bg-white shadow-sm';
    $judulBagian = 'text-sm font-semibold text-[#333333]';
    $labelData = 'text-sm text-[#666666]';
    $nilaiData = 'text-sm font-medium text-[#333333]';
@endphp

<div class="mx-auto w-full max-w-5xl space-y-5">

    {{-- Judul halaman --}}
    @if (empty($tampilanGabungan))
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#222222] sm:text-3xl">
                Detail {{ $anak->nama_anak }}
            </h1>

            <p class="mt-1 text-xs text-[#777777] sm:text-sm">
                {{ $base === 'admin' ? 'Data Master / ' : '' }}
                Pendataan Posyandu / Detail
            </p>
        </div>
    @elseif (($jumlahAnak ?? 1) > 1)
        <div>
            <h2 class="text-xl font-bold text-[#222222]">
                Detail {{ $anak->nama_anak }}
            </h2>
        </div>
    @endif

    {{-- Data Anak, Status Gizi, dan Data Ibu --}}
    <div class="space-y-4">

        {{-- Data Anak dan Status Gizi --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

            {{-- Data Anak --}}
            <section class="{{ $kartu }} p-4 sm:p-5">
                <h2 class="{{ $judulBagian }} mb-4">
                    Data Anak
                </h2>

                <dl class="space-y-3">
                    <div class="grid grid-cols-[105px_1fr] gap-3">
                        <dt class="{{ $labelData }}">NIK</dt>
                        <dd class="{{ $nilaiData }}">
                            {{ $nikAnak ?: '-' }}
                        </dd>
                    </div>

                    <div class="grid grid-cols-[105px_1fr] gap-3">
                        <dt class="{{ $labelData }}">Nama</dt>
                        <dd class="{{ $nilaiData }}">
                            {{ $anak->nama_anak ?: '-' }}
                        </dd>
                    </div>

                    <div class="grid grid-cols-[105px_1fr] gap-3">
                        <dt class="{{ $labelData }}">Tanggal Lahir</dt>
                        <dd class="{{ $nilaiData }}">
                            {{ $formatTanggal($tanggalLahirAnak) }}
                        </dd>
                    </div>

                    <div class="grid grid-cols-[105px_1fr] gap-3">
                        <dt class="{{ $labelData }}">Tempat Lahir</dt>
                        <dd class="{{ $nilaiData }}">
                            {{ $tempatLahirAnak ?: '-' }}
                        </dd>
                    </div>

                    <div class="grid grid-cols-[105px_1fr] gap-3">
                        <dt class="{{ $labelData }}">JK</dt>
                        <dd class="{{ $nilaiData }}">
                            {{ $jenisKelaminAnak }}
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Status Gizi --}}
            <section class="{{ $kartu }} p-4 sm:p-5">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <h2 class="{{ $judulBagian }}">
                        Status Gizi
                    </h2>

                    <a
                        href="{{ route($base . '.data-anak.detail-status', $anak) }}"
                        class="inline-flex items-center rounded-lg border border-[#D9D9D9] bg-white px-3 py-2 text-xs font-semibold text-[#C9517E] transition hover:bg-[#FFF5F8]"
                    >
                        Lihat Detail Status
                    </a>
                </div>

                <dl class="space-y-3">
                    <div class="grid grid-cols-[130px_1fr] gap-3">
                        <dt class="{{ $labelData }}">Berat Badan</dt>
                        <dd class="{{ $nilaiData }}">
                            {{ $angka($beratTerakhir) }}{{ $beratTerakhir !== null ? ' kg' : '' }}
                        </dd>
                    </div>

                    <div class="grid grid-cols-[130px_1fr] gap-3">
                        <dt class="{{ $labelData }}">Tinggi Badan</dt>
                        <dd class="{{ $nilaiData }}">
                            {{ $angka($tinggiTerakhir) }}{{ $tinggiTerakhir !== null ? ' cm' : '' }}
                        </dd>
                    </div>

                    <div class="grid grid-cols-[130px_1fr] gap-3">
                        <dt class="{{ $labelData }}">Pengukuran Terakhir</dt>
                        <dd class="{{ $nilaiData }}">
                            {{ $formatTanggal($tanggalTerakhir) }}
                        </dd>
                    </div>
                </dl>
            </section>
        </div>

        {{-- Data Ibu --}}
        <section class="{{ $kartu }} p-4 sm:p-5">
            <h2 class="{{ $judulBagian }} mb-4">
                Data Ibu
            </h2>

            <dl class="space-y-3">
                <div class="grid grid-cols-[105px_1fr] gap-3">
                    <dt class="{{ $labelData }}">NIK</dt>
                    <dd class="{{ $nilaiData }}">
                        {{ $nikIbu ?: '-' }}
                    </dd>
                </div>

                <div class="grid grid-cols-[105px_1fr] gap-3">
                    <dt class="{{ $labelData }}">Nama</dt>
                    <dd class="{{ $nilaiData }}">
                        {{ $ibu?->user?->name ?? '-' }}
                    </dd>
                </div>

                <div class="grid grid-cols-[105px_1fr] gap-3">
                    <dt class="{{ $labelData }}">Tanggal Lahir</dt>
                    <dd class="{{ $nilaiData }}">
                        {{ $formatTanggal($tanggalLahirIbu) }}
                    </dd>
                </div>

                <div class="grid grid-cols-[105px_1fr] gap-3">
                    <dt class="{{ $labelData }}">Nomor HP</dt>
                    <dd class="{{ $nilaiData }}">
                        {{ $nomorHp ?: '-' }}
                    </dd>
                </div>

                <div class="grid grid-cols-[105px_1fr] gap-3">
                    <dt class="{{ $labelData }}">Wilayah</dt>
                    <dd class="{{ $nilaiData }}">
                        {{ $wilayahIbu ?: '-' }}
                    </dd>
                </div>
            </dl>
        </section>
    </div>

    {{-- Grafik pertumbuhan --}}
    <section class="{{ $kartu }} overflow-hidden">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#EEEEEE] px-4 py-3 sm:px-5">
            <h2 class="text-sm font-semibold text-[#333333]">
                Grafik Pertumbuhan
            </h2>

            <select
                id="pilihan-grafik"
                aria-label="Pilih indikator grafik"
                class="rounded-lg border border-[#D9E0E8] bg-white px-3 py-2 text-xs text-[#475569] outline-none focus:border-[#C9517E] focus:ring-2 focus:ring-[#E99AB7]/30"
            >
                <option value="berat_badan">Berat Badan (kg)</option>
                <option value="tinggi_badan">Tinggi Badan (cm)</option>
            </select>
        </div>

        <div class="p-4 sm:px-5 sm:py-4">

            @if (! $adaPengukuran)
                <div class="flex min-h-40 flex-col items-center justify-center rounded-lg bg-[#F8FAFC] px-4 text-center">
                    <p class="text-sm font-medium text-[#64748B]">
                        Belum ada riwayat pengukuran
                    </p>
                    <p class="mt-1 text-xs text-[#94A3B8]">
                        Grafik akan tampil setelah data pengukuran tersimpan.
                    </p>
                </div>
            @else
                @foreach ($dataGrafik as $kolom => $grafik)
                    <div
                        data-grafik="{{ $kolom }}"
                        class="{{ $kolom === 'tinggi_badan' ? 'hidden' : '' }}"
                    >
                        @if ($grafik['jumlah'] < 2)
                            <div class="flex min-h-40 flex-col items-center justify-center rounded-lg bg-[#F8FAFC] px-4 text-center">
                                <p class="text-sm font-medium text-[#64748B]">
                                    Data belum cukup untuk grafik
                                </p>
                                <p class="mt-1 text-xs text-[#94A3B8]">
                                    Diperlukan minimal 2 hasil pengukuran {{ strtolower($grafik['label']) }}.
                                </p>
                            </div>
                        @else
                            <div class="w-full overflow-x-auto">
                                <svg
                                    viewBox="0 0 600 222"
                                    class="w-full min-w-[480px]"
                                    role="img"
                                    aria-label="Grafik {{ strtolower($grafik['label']) }} anak"
                                >
                                    {{-- Garis bantu --}}
                                    @foreach ([40, 82, 126, 170] as $gy)
                                        <line
                                            x1="60"
                                            y1="{{ $gy }}"
                                            x2="580"
                                            y2="{{ $gy }}"
                                            stroke="#E8EDF2"
                                            stroke-width="1"
                                        />
                                    @endforeach

                                    {{-- Garis penghubung --}}
                                    @foreach ($grafik['points'] as $i => $p)
                                        @if ($i > 0)
                                            <line
                                                x1="{{ $grafik['points'][$i - 1]['x'] }}"
                                                y1="{{ $grafik['points'][$i - 1]['y'] }}"
                                                x2="{{ $p['x'] }}"
                                                y2="{{ $p['y'] }}"
                                                stroke="{{ $grafik['warna'] }}"
                                                stroke-width="2.5"
                                                stroke-linecap="round"
                                            />
                                        @endif
                                    @endforeach

                                    {{-- Titik hasil pemeriksaan --}}
                                    @foreach ($grafik['points'] as $i => $p)
                                        <circle
                                            cx="{{ $p['x'] }}"
                                            cy="{{ $p['y'] }}"
                                            r="4.5"
                                            fill="{{ $grafik['warna'] }}"
                                            stroke="white"
                                            stroke-width="2"
                                        >
                                            <title>
                                                {{ $formatTanggal($p['tanggal']) }}
                                                · {{ $p['nilai'] }} {{ $grafik['satuan'] }}
                                                · {{ $labelStatus($p['status']) }}
                                            </title>
                                        </circle>

                                        @if ($grafik['jumlah'] <= 8)
                                            <text
                                                x="{{ $p['x'] }}"
                                                y="{{ $p['y'] - 12 }}"
                                                font-size="11"
                                                font-weight="600"
                                                text-anchor="middle"
                                                fill="#475569"
                                            >
                                                {{ $angka($p['nilai']) }}
                                            </text>
                                        @endif

                                        @if ($grafik['jumlah'] <= 8 || $i % 2 === 0)
                                            <text
                                                x="{{ $p['x'] }}"
                                                y="192"
                                                font-size="10"
                                                text-anchor="middle"
                                                fill="#64748B"
                                            >
                                                {{ $p['tanggal']
                                                    ? \Carbon\Carbon::parse($p['tanggal'])->translatedFormat('M')
                                                    : ''
                                                }}
                                            </text>
                                        @endif
                                    @endforeach

                                    <text x="4" y="20" font-size="11" font-weight="600" fill="#64748B">
                                        {{ $grafik['label'] }} ({{ $grafik['satuan'] }})
                                    </text>

                                    <text
                                        x="54"
                                        y="44"
                                        font-size="11"
                                        text-anchor="end"
                                        fill="#64748B"
                                    >
                                        {{ $angka($grafik['maksimum']) }}
                                    </text>

                                    <text
                                        x="54"
                                        y="174"
                                        font-size="11"
                                        text-anchor="end"
                                        fill="#64748B"
                                    >
                                        {{ $angka($grafik['minimum']) }}
                                    </text>
                                </svg>
                            </div>

                            <p class="mt-2 text-xs text-[#94A3B8]">
                                Grafik menunjukkan perubahan {{ strtolower($grafik['label']) }}
                                berdasarkan hasil pemeriksaan yang tersimpan.
                            </p>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </section>

    {{-- Riwayat pemeriksaan --}}
    <section
        id="riwayat-pemeriksaan"
        data-riwayat
        class="{{ $kartu }} overflow-hidden"
    >
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-5">
            <h2 class="text-base font-semibold text-[#222222]">
                Riwayat Pemeriksaan
            </h2>

            @if ($filterPeriode && $daftarPeriode->isNotEmpty())
                <select
                    data-periode-filter
                    aria-label="Filter periode"
                    class="rounded-lg border border-[#D1D5DB] bg-white px-3 py-2 text-xs text-[#475569] outline-none focus:border-[#C9517E] focus:ring-2 focus:ring-[#E99AB7]/30"
                >
                    <option value="">Semua Periode</option>

                    @foreach ($daftarPeriode as $periode)
                        <option value="{{ $periode }}">
                            {{ \Carbon\Carbon::createFromFormat('Y-m-d', $periode.'-01')->translatedFormat('F Y') }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>

        @if ($riwayatTerbaru->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-y border-[#EEEEEE] bg-[#F8FAFC] text-xs text-[#475569]">
                        <tr>
                            <th class="px-4 py-3 font-semibold sm:px-5">Tanggal</th>
                            <th class="px-4 py-3 text-center font-semibold">BB</th>
                            <th class="px-4 py-3 text-center font-semibold">TB</th>
                            <th class="px-4 py-3 text-center font-semibold">Lingkar Kepala</th>
                            <th class="px-4 py-3 text-center font-semibold">LILA</th>
                            <th class="px-4 py-3 text-center font-semibold">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#EEEEEE]">
                        @foreach ($riwayatTerbaru as $item)
                            @php
                                $lebih = $loop->index >= $batasRiwayat;
                                $tglRow = $item->kegiatan?->tanggal;

                                $lila = $ambil($item, [
                                    'lila',
                                    'lingkar_lengan_atas',
                                    'lingkar_lengan',
                                ]);

                                $lingkarKepala = $ambil($item, [
                                    'lingkar_kepala',
                                    'lk',
                                ]);
                            @endphp

                            <tr
                                class="text-[#444444] transition-colors hover:bg-[#F8FAFC] {{ $lebih ? 'hidden' : '' }}"
                                data-periode="{{ $tglRow ? \Carbon\Carbon::parse($tglRow)->format('Y-m') : '' }}"
                                @if ($lebih) data-extra @endif
                            >
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    {{ $formatTanggal($tglRow) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-center">
                                    {{ $angka($item->berat_badan) }}{{ $item->berat_badan !== null ? ' kg' : '' }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-center">
                                    {{ $angka($item->tinggi_badan) }}{{ $item->tinggi_badan !== null ? ' cm' : '' }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-center">
                                    {{ $angka($lingkarKepala) }}{{ $lingkarKepala !== null ? ' cm' : '' }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-center">
                                    {{ $angka($lila) }}{{ $lila !== null ? ' cm' : '' }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-center">
                                    @if ($item->status_kesehatan === 'normal')
                                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                            Normal
                                        </span>
                                    @elseif ($item->status_kesehatan === 'perlu_perhatian')
                                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                            Perlu Perhatian
                                        </span>
                                    @else
                                        <span class="text-xs text-[#94A3B8]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-5">
                <p class="text-xs text-[#94A3B8]">
                    Menampilkan {{ min($batasRiwayat, $riwayatTerbaru->count()) }}
                    dari {{ $riwayatTerbaru->count() }} data
                </p>

                @if ($adaLebih)
                    <button
                        type="button"
                        data-riwayat-toggle
                        data-label-buka="Tampilkan semua"
                        data-label-tutup="Tampilkan sedikit"
                        class="rounded-lg border border-[#D1D5DB] bg-white px-3 py-2 text-xs font-medium text-[#475569] transition hover:bg-[#F8FAFC] focus:outline-none focus:ring-2 focus:ring-[#E99AB7]/40"
                    >
                        Tampilkan semua
                    </button>
                @endif
            </div>
        @else
            <div class="px-5 py-10 text-center">
                <p class="text-sm font-medium text-[#64748B]">
                    Tidak ada riwayat pemeriksaan anak.
                </p>
                <p class="mt-1 text-xs text-[#94A3B8]">
                    Data akan ditampilkan setelah pemeriksaan tersimpan.
                </p>
            </div>
        @endif
    </section>

    {{-- Tombol kembali --}}
    @if (empty($tampilanGabungan))
        <div class="flex flex-wrap justify-end gap-2 pb-2">
            @if ($base === 'admin')
                @can('anak.update')
                    <a
                        href="{{ route('admin.data-anak.edit', $anak) }}"
                        class="inline-flex items-center justify-center rounded-lg border border-[#D1D5DB] bg-white px-5 py-2.5 text-sm font-medium text-[#475569] transition hover:bg-[#F8FAFC]"
                    >
                        Edit
                    </a>
                @endcan
            @endif

            <a
                href="{{ route($base.'.data-anak.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-[#D1D5DB] bg-white px-5 py-2.5 text-sm font-medium text-[#333333] transition hover:bg-[#F8FAFC] focus:outline-none focus:ring-2 focus:ring-[#E99AB7]/40"
            >
                Kembali
            </a>
        </div>
    @endif

</div>

@once
    @push('scripts')
        <script>
            // Dropdown pilihan grafik berat badan / tinggi badan.
            document.addEventListener('change', function (e) {
                if (e.target.id !== 'pilihan-grafik') return;

                const pilihan = e.target.value;

                document.querySelectorAll('[data-grafik]').forEach(function (grafik) {
                    grafik.classList.toggle(
                        'hidden',
                        grafik.dataset.grafik !== pilihan
                    );
                });
            });

            // Tombol tampilkan semua / tampilkan sedikit.
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-riwayat-toggle]');
                if (!btn) return;

                const box = btn.closest('[data-riwayat]');
                const rows = box.querySelectorAll('[data-extra]');
                const buka = rows.length > 0 && rows[0].classList.contains('hidden');

                rows.forEach(function (row) {
                    row.classList.toggle('hidden', !buka);
                });

                btn.textContent = buka
                    ? btn.dataset.labelTutup
                    : btn.dataset.labelBuka;
            });

            // Filter periode.
            document.addEventListener('change', function (e) {
                const sel = e.target.closest('[data-periode-filter]');
                if (!sel) return;

                const box = sel.closest('[data-riwayat]');
                const rows = box.querySelectorAll('tbody tr[data-periode]');
                const btn = box.querySelector('[data-riwayat-toggle]');
                const nilai = sel.value;

                rows.forEach(function (row) {
                    if (nilai === '') {
                        row.classList.toggle(
                            'hidden',
                            row.hasAttribute('data-extra')
                        );
                    } else {
                        row.classList.toggle(
                            'hidden',
                            row.dataset.periode !== nilai
                        );
                    }
                });

                if (btn) {
                    btn.classList.toggle('hidden', nilai !== '');
                    btn.textContent = btn.dataset.labelBuka;
                }
            });
        </script>
    @endpush
@endonce
