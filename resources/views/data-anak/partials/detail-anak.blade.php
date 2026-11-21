@php
    $ibu = $anak->ibu;

    $riwayatPemeriksaan  = $riwayatPemeriksaan ?? collect();
    $pemeriksaanTerakhir = $pemeriksaanTerakhir ?? null;

    $formatTanggal = fn ($t) => $t ? \Carbon\Carbon::parse($t)->format('d/m/Y') : '-';
    $angka = fn ($v) => ($v === null || $v === '') ? '-' : number_format((float) $v, 1, ',', '.');

    // Ambil nilai kolom secara aman: kalau kolom tidak ada, hasilnya null (tampil "-")
    $ambil = function ($model, array $kolom) {
        $atribut = $model->getAttributes();
        foreach ($kolom as $k) {
            if (array_key_exists($k, $atribut) && $atribut[$k] !== null && $atribut[$k] !== '') {
                return $atribut[$k];
            }
        }
        return null;
    };

    // Susun alamat: Kec. A, Desa B, RT 001/RW 01 (memakai kolom lama bila relasi belum ada)
    $alamatDari = function ($w) {
        if (! $w) return '-';

        $relasi = function (string $nama) use ($w) {
            if ($w->relationLoaded($nama)) return $w->getRelation($nama);
            return method_exists($w, $nama) ? $w->{$nama}()->first() : null;
        };

        $kec  = $relasi('kecamatan');
        $desa = $relasi('kelurahanDesa');
        $rw   = $relasi('rw');
        $rt   = $relasi('rt');

        $kolomKec  = $w->getAttribute('kecamatan');
        $kolomDesa = $w->getAttribute('kelurahan');
        $namaKec   = $kec?->nama_kecamatan ?? (is_string($kolomKec) && $kolomKec !== '' ? $kolomKec : null);

        $bagian = [];
        if ($namaKec) $bagian[] = 'Kec. ' . $namaKec;

        if ($desa?->nama_kelurahan_desa) {
            $bagian[] = (strtolower((string) $desa->jenis) === 'kelurahan' ? 'Kel. ' : 'Desa ') . $desa->nama_kelurahan_desa;
        } elseif (is_string($kolomDesa) && $kolomDesa !== '') {
            $bagian[] = 'Desa ' . $kolomDesa;
        }

        $nomor = array_filter([
            $rt?->nomor_rt ? 'RT ' . $rt->nomor_rt : null,
            $rw?->nomor_rw ? 'RW ' . $rw->nomor_rw : null,
        ]);
        if ($nomor) $bagian[] = implode('/', $nomor);

        return $bagian ? implode(', ', $bagian) : ($w->getAttribute('nama_wilayah') ?: '-');
    };

    $warnaStatus = fn ($s) => match ($s) {
        'normal'          => '#16a34a',
        'perlu_perhatian' => '#dc2626',
        default           => '#94a3b8',
    };

    $labelStatus = fn ($s) => match ($s) {
        'normal'          => 'Gizi Baik',
        'perlu_perhatian' => 'Perlu Perhatian',
        default           => 'Belum ada status',
    };

    // Umur anak (bulan) saat pemeriksaan
    $tglLahir  = $anak->tgl_lahir ? \Carbon\Carbon::parse($anak->tgl_lahir) : null;
    $umurBulan = function ($tanggal) use ($tglLahir) {
        if (! $tglLahir || ! $tanggal) return null;
        return (int) floor(abs($tglLahir->diffInMonths(\Carbon\Carbon::parse($tanggal))));
    };

    // ===== Data grafik berat badan (maksimal 12 pemeriksaan terakhir) =====
    $dataGrafik = $riwayatPemeriksaan
        ->filter(fn ($item) => $item->berat_badan !== null)
        ->values()
        ->take(-12)
        ->values();

    $jumlahGrafik  = $dataGrafik->count();
    $beratMinimum  = $jumlahGrafik > 0 ? (float) $dataGrafik->min('berat_badan') : 0;
    $beratMaksimum = $jumlahGrafik > 0 ? (float) $dataGrafik->max('berat_badan') : 0;
    $rentangBerat  = max($beratMaksimum - $beratMinimum, 1);

    $xKiri = 70; $xKanan = 570; $yAtas = 40; $yBawah = 170;

    $chartPoints = [];
    foreach ($dataGrafik as $index => $item) {
        $x = $jumlahGrafik > 1
            ? $xKiri + ($index * (($xKanan - $xKiri) / ($jumlahGrafik - 1)))
            : ($xKiri + $xKanan) / 2;

        $y = $beratMaksimum === $beratMinimum
            ? ($yAtas + $yBawah) / 2
            : $yBawah - ((((float) $item->berat_badan) - $beratMinimum) / $rentangBerat) * ($yBawah - $yAtas);

        $tanggal = $item->kegiatan?->tanggal;

        $chartPoints[] = [
            'x'       => round($x, 1),
            'y'       => round($y, 1),
            'berat'   => $item->berat_badan,
            'tanggal' => $tanggal,
            'umur'    => $umurBulan($tanggal),
            'status'  => $item->status_kesehatan,
        ];
    }

    $statusTerakhir = $pemeriksaanTerakhir?->status_kesehatan;

    // ===== Riwayat: 4 terbaru tampil, sisanya lewat tombol =====
    $riwayatTerbaru = $riwayatPemeriksaan
        ->sortByDesc(fn ($item) => $item->kegiatan?->tanggal)
        ->values();

    $batasRiwayat = 4;
    $adaLebih     = $riwayatTerbaru->count() > $batasRiwayat;

    // Dropdown "Semua Periode" hanya di tampilan role Ibu (sesuai wireframe)
    $filterPeriode = ! empty($tampilanGabungan);
    $daftarPeriode = $riwayatTerbaru
        ->map(fn ($item) => $item->kegiatan?->tanggal ? \Carbon\Carbon::parse($item->kegiatan->tanggal)->format('Y-m') : null)
        ->filter()
        ->unique()
        ->values();

    $dataAnak = [
        'NIK'           => $anak->nik_anak,
        'Nama'          => $anak->nama_anak,
        'Tanggal Lahir' => $formatTanggal($anak->tgl_lahir),
        'Tempat Lahir'  => $anak->tempat_lahir,
        'JK'            => $anak->jenis_kelamin === 'L' ? 'Laki-laki' : ($anak->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
    ];

    // Gaya bersama
    $kartu  = 'overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#E99AB7] hover:shadow-md motion-reduce:transform-none motion-reduce:transition-none';
    $kepala = 'flex flex-wrap items-center justify-between gap-3 border-b border-[#F1D5E0] bg-gradient-to-r from-[#FCE7F0] to-[#FFF7FA] px-5 py-3.5';
    $ikon   = 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-[#C9517E] shadow-sm';
@endphp

<div class="mx-auto w-full max-w-6xl space-y-6">

    {{-- Judul dan tombol --}}
    @if (empty($tampilanGabungan))
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Detail Anak</h1>
                <p class="mt-1 text-sm font-medium text-slate-600">{{ $base === 'admin' ? 'Data Master / ' : '' }}Data Anak / Detail</p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route($base.'.data-anak.index') }}"
                   class="inline-flex justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-5 py-2.5 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/30 active:scale-[0.98]">
                    Kembali
                </a>

                @if ($base === 'admin')
                    @can('anak.update')
                        <a href="{{ route('admin.data-anak.edit', $anak) }}"
                           class="inline-flex justify-center rounded-xl border-2 border-[#E99AB7] bg-[#E99AB7] px-5 py-2.5 text-sm font-semibold text-[#542D3D] shadow-sm transition hover:border-[#DD87A8] hover:bg-[#DD87A8] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.98]">
                            Edit
                        </a>
                    @endcan
                @endif
            </div>
        </div>
    @elseif (($jumlahAnak ?? 1) > 1)
        <div class="border-b border-[#F1D5E0] pb-3">
            <h2 class="text-xl font-bold text-[#542D3D]">Data Anak {{ $nomor }}</h2>
        </div>
    @endif

    {{-- Data Anak (kiri) dan Status Terkini (kanan) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="{{ $kartu }} lg:col-span-2">
            <div class="{{ $kepala }}">
                <div class="flex items-center gap-3">
                    <span class="{{ $ikon }}"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6"/></svg></span>
                    <h2 class="text-base font-bold text-[#542D3D]">Data Anak</h2>
                </div>
            </div>

            <dl class="divide-y divide-[#F7E6ED] px-5">
                @foreach ($dataAnak as $label => $nilai)
                    <div class="flex flex-col gap-0.5 py-3 text-sm sm:flex-row sm:justify-between sm:gap-4">
                        <dt class="font-semibold text-slate-600">{{ $label }}</dt>
                        <dd class="font-semibold text-[#542D3D] sm:text-right">{{ $nilai ?: '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="{{ $kartu }}">
            <div class="{{ $kepala }}">
                <div class="flex items-center gap-3">
                    <span class="{{ $ikon }}"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-8-4.5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 6.5-8 11-8 11Z"/></svg></span>
                    <h2 class="text-base font-bold text-[#542D3D]">Status Terkini</h2>
                </div>
            </div>

            <div class="p-5">
                @if ($statusTerakhir === 'normal')
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-4 py-1.5 text-sm font-bold text-green-700"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Gizi Baik</span>
                @elseif ($statusTerakhir === 'perlu_perhatian')
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-4 py-1.5 text-sm font-bold text-red-700"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Perlu Perhatian</span>
                @else
                    <span class="inline-flex rounded-full bg-gray-100 px-4 py-1.5 text-sm font-bold text-gray-600">Belum ada pemeriksaan</span>
                @endif

                <p class="mt-4 text-sm text-slate-600">
                    Pemeriksaan {{ $formatTanggal($pemeriksaanTerakhir?->kegiatan?->tanggal) }}
                </p>
            </div>
        </div>
    </div>

    {{-- Data Ibu --}}
    <div class="{{ $kartu }}">
        <div class="{{ $kepala }}">
            <div class="flex items-center gap-3">
                <span class="{{ $ikon }}"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0"/></svg></span>
                <h2 class="text-base font-bold text-[#542D3D]">Data Ibu</h2>
            </div>
        </div>

        @if ($ibu)
            @php
                $dataIbu = [
                    'NIK'           => $ibu->nik,
                    'Nama'          => $ibu->user?->name,
                    'Tanggal Lahir' => $formatTanggal($ibu->tgl_lahir),
                    'Nomor HP'      => $ibu->user?->phone,
                    'Alamat'        => $alamatDari($ibu->wilayahKerja),
                ];
            @endphp

            <dl class="divide-y divide-[#F7E6ED] px-5">
                @foreach ($dataIbu as $label => $nilai)
                    <div class="flex flex-col gap-0.5 py-3 text-sm sm:flex-row sm:justify-between sm:gap-4">
                        <dt class="font-semibold text-slate-600">{{ $label }}</dt>
                        <dd class="font-semibold text-[#542D3D] sm:text-right">{{ $nilai ?: '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        @else
            <p class="px-5 py-8 text-center text-sm text-slate-500">Data ibu belum terhubung.</p>
        @endif
    </div>

    {{-- Grafik Pertumbuhan --}}
    <div class="{{ $kartu }}">
        <div class="{{ $kepala }}">
            <div>
                <h2 class="text-base font-bold text-[#542D3D]">Grafik Pertumbuhan (Berat Badan menurut Umur)</h2>
                <p class="mt-0.5 text-sm text-slate-600">Hijau berarti gizi baik, merah berarti perlu perhatian.</p>
            </div>
        </div>

        <div class="p-5">
            @if ($jumlahGrafik > 0)
                <div class="w-full overflow-x-auto">
                    <svg viewBox="0 0 600 222" class="w-full min-w-[480px]" role="img"
                         aria-label="Grafik berat badan anak menurut umur">

                        @foreach ([40, 82, 126, 170] as $gy)
                            <line x1="{{ $xKiri - 10 }}" y1="{{ $gy }}" x2="{{ $xKanan + 10 }}" y2="{{ $gy }}"
                                  stroke="#F1D5E0" stroke-dasharray="4 4"/>
                        @endforeach

                        {{-- Garis: warna mengikuti status pemeriksaan di titik tujuan --}}
                        @foreach ($chartPoints as $i => $p)
                            @if ($i > 0)
                                <line x1="{{ $chartPoints[$i - 1]['x'] }}" y1="{{ $chartPoints[$i - 1]['y'] }}"
                                      x2="{{ $p['x'] }}" y2="{{ $p['y'] }}"
                                      stroke="{{ $warnaStatus($p['status']) }}" stroke-width="3" stroke-linecap="round"/>
                            @endif
                        @endforeach

                        {{-- Titik, nilai, dan umur --}}
                        @foreach ($chartPoints as $i => $p)
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="6"
                                    fill="{{ $warnaStatus($p['status']) }}" stroke="white" stroke-width="2">
                                <title>{{ $formatTanggal($p['tanggal']) }} · {{ $p['berat'] }} kg · {{ $labelStatus($p['status']) }}</title>
                            </circle>

                            @if ($jumlahGrafik <= 8)
                                <text x="{{ $p['x'] }}" y="{{ $p['y'] - 12 }}" font-size="11" font-weight="700"
                                      text-anchor="middle" fill="#542D3D">{{ $p['berat'] }}</text>
                            @endif

                            @if ($jumlahGrafik <= 8 || $i % 2 === 0)
                                <text x="{{ $p['x'] }}" y="192" font-size="10" text-anchor="middle" fill="#64748B">
                                    {{ $p['umur'] !== null ? $p['umur'].' bln' : ($p['tanggal'] ? \Carbon\Carbon::parse($p['tanggal'])->format('d/m') : '') }}
                                </text>
                            @endif
                        @endforeach

                        <text x="4" y="20" font-size="11" font-weight="700" fill="#64748B">BB (kg)</text>
                        <text x="{{ $xKiri - 16 }}" y="44" font-size="11" text-anchor="end" fill="#64748B">{{ number_format($beratMaksimum, 1) }}</text>
                        <text x="{{ $xKiri - 16 }}" y="174" font-size="11" text-anchor="end" fill="#64748B">{{ number_format($beratMinimum, 1) }}</text>
                        <text x="{{ ($xKiri + $xKanan) / 2 }}" y="214" font-size="11" text-anchor="middle" fill="#64748B">umur (bulan)</text>
                    </svg>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs font-semibold text-slate-700">
                    <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-green-600"></span> Gizi Baik</span>
                    <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-red-600"></span> Perlu Perhatian</span>
                </div>
            @else
                <div class="flex min-h-48 flex-col items-center justify-center rounded-xl bg-[#FFF5F8]">
                    <p class="text-sm font-semibold text-slate-600">Belum ada data berat badan</p>
                    <p class="mt-1 text-sm text-slate-500">Grafik akan tampil setelah ada hasil pemeriksaan.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Riwayat Pemeriksaan --}}
    <div id="riwayat-pemeriksaan" data-riwayat class="{{ $kartu }}">
        <div class="{{ $kepala }}">
            <div>
                <h2 class="text-base font-bold text-[#542D3D]">Riwayat Pemeriksaan Anak</h2>
                <p class="mt-0.5 text-sm text-slate-600">Riwayat hasil pemeriksaan berdasarkan kegiatan Posyandu.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($filterPeriode && $daftarPeriode->isNotEmpty())
                    <select data-periode-filter aria-label="Filter periode"
                            class="rounded-xl border-2 border-[#E99AB7] bg-white px-3 py-2 text-sm font-semibold text-[#542D3D] outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                        <option value="">Semua Periode</option>
                        @foreach ($daftarPeriode as $periode)
                            <option value="{{ $periode }}">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $periode.'-01')->translatedFormat('F Y') }}</option>
                        @endforeach
                    </select>
                @endif

                @if ($adaLebih)
                    <button type="button" data-riwayat-toggle
                            data-label-buka="Tampilkan semua"
                            data-label-tutup="Tampilkan sedikit"
                            class="rounded-xl border-2 border-[#C9517E] bg-white px-4 py-2 text-sm font-semibold text-[#C9517E] transition hover:bg-[#C9517E] hover:text-white focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.98]">
                        Tampilkan semua
                    </button>
                @endif
            </div>
        </div>

        @if ($riwayatTerbaru->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-[#FFF5F8] text-xs uppercase tracking-wide text-[#542D3D]">
                        <tr>
                            <th class="px-5 py-3 font-bold">Tanggal</th>
                            <th class="px-5 py-3 text-center font-bold">BB (kg)</th>
                            <th class="px-5 py-3 text-center font-bold">TB (cm)</th>
                            <th class="px-5 py-3 text-center font-bold">LK (cm)</th>
                            <th class="px-5 py-3 text-center font-bold">Status Gizi</th>
                            <th class="px-5 py-3 font-bold">Imunisasi / Vitamin</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#F7E6ED]">
                        @foreach ($riwayatTerbaru as $item)
                            @php
                                $lebih  = $loop->index >= $batasRiwayat;
                                $tglRow = $item->kegiatan?->tanggal;
                            @endphp
                            <tr class="transition-colors hover:bg-[#FCE7F0]/40 {{ $lebih ? 'hidden' : '' }}"
                                data-periode="{{ $tglRow ? \Carbon\Carbon::parse($tglRow)->format('Y-m') : '' }}"
                                @if ($lebih) data-extra @endif>
                                <td class="px-5 py-3">{{ $formatTanggal($tglRow) }}</td>
                                <td class="px-5 py-3 text-center">{{ $angka($item->berat_badan) }}</td>
                                <td class="px-5 py-3 text-center">{{ $angka($item->tinggi_badan) }}</td>
                                <td class="px-5 py-3 text-center">{{ $angka($ambil($item, ['lingkar_kepala', 'lk'])) }}</td>
                                <td class="px-5 py-3 text-center">
                                    @if ($item->status_kesehatan === 'normal')
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Gizi Baik</span>
                                    @elseif ($item->status_kesehatan === 'perlu_perhatian')
                                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Perlu Perhatian</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ $ambil($item, ['imunisasi', 'vitamin', 'imunisasi_vitamin']) ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <p class="text-sm font-semibold text-slate-600">Tidak ada riwayat pemeriksaan anak.</p>
                <p class="mt-1 text-xs text-slate-500">Data akan ditampilkan setelah pemeriksaan tersimpan.</p>
            </div>
        @endif
    </div>

</div>

@once
    @push('scripts')
        <script>
            // Tombol "Tampilkan semua" / "Tampilkan sedikit"
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-riwayat-toggle]');
                if (!btn) return;

                const box  = btn.closest('[data-riwayat]');
                const rows = box.querySelectorAll('[data-extra]');
                const buka = rows.length > 0 && rows[0].classList.contains('hidden');

                rows.forEach(function (r) { r.classList.toggle('hidden', !buka); });
                btn.textContent = buka ? btn.dataset.labelTutup : btn.dataset.labelBuka;
            });

            // Dropdown "Semua Periode": menyaring baris riwayat berdasarkan bulan
            document.addEventListener('change', function (e) {
                const sel = e.target.closest('[data-periode-filter]');
                if (!sel) return;

                const box   = sel.closest('[data-riwayat]');
                const rows  = box.querySelectorAll('tbody tr[data-periode]');
                const btn   = box.querySelector('[data-riwayat-toggle]');
                const nilai = sel.value;

                rows.forEach(function (r) {
                    if (nilai === '') {
                        r.classList.toggle('hidden', r.hasAttribute('data-extra'));
                    } else {
                        r.classList.toggle('hidden', r.dataset.periode !== nilai);
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