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

    $warnaStatus = fn ($s) => match ($s) {
        'normal'          => '#16a34a',
        'perlu_perhatian' => '#dc2626',
        default           => '#94a3b8',
    };

    $labelStatus = fn ($s) => match ($s) {
        'normal'          => 'Normal',
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

    $dataAnak = [
        'NIK'           => $anak->nik_anak,
        'Nama'          => $anak->nama_anak,
        'Tanggal Lahir' => $formatTanggal($anak->tgl_lahir),
        'Tempat Lahir'  => $anak->tempat_lahir,
        'JK'            => $anak->jenis_kelamin === 'L' ? 'Laki-laki' : ($anak->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
    ];
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
                   class="inline-flex justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-5 py-2.5 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">
                    Kembali
                </a>

                @if ($base === 'admin')
                    @can('anak.update')
                        <a href="{{ route('admin.data-anak.edit', $anak) }}"
                           class="inline-flex justify-center rounded-xl bg-[#E99AB7] px-5 py-2.5 text-sm font-semibold text-[#542D3D] shadow-sm transition hover:bg-[#DD87A8]">
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

        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
                <h2 class="text-base font-bold text-[#542D3D]">Data Anak</h2>
            </div>

            <div class="divide-y divide-[#F7E6ED] px-5">
                @foreach ($dataAnak as $label => $nilai)
                    <div class="flex justify-between gap-4 py-3 text-sm">
                        <span class="font-semibold text-slate-600">{{ $label }}</span>
                        <span class="text-right font-semibold text-[#542D3D]">{{ $nilai ?: '-' }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
                <h2 class="text-base font-bold text-[#542D3D]">Status Terkini</h2>
            </div>

            <div class="p-5">
                @if ($statusTerakhir === 'normal')
                    <span class="inline-flex rounded-full bg-green-100 px-4 py-1.5 text-sm font-bold text-green-700">Normal</span>
                @elseif ($statusTerakhir === 'perlu_perhatian')
                    <span class="inline-flex rounded-full bg-red-100 px-4 py-1.5 text-sm font-bold text-red-700">Perlu Perhatian</span>
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
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
            <h2 class="text-base font-bold text-[#542D3D]">Data Ibu</h2>
        </div>

        @if ($ibu)
            @php
                $dataIbu = [
                    'NIK'           => $ibu->nik,
                    'Nama'          => $ibu->user?->name,
                    'Tanggal Lahir' => $formatTanggal($ibu->tgl_lahir),
                    'Nomor HP'      => $ibu->user?->phone,
                    'Wilayah'       => $ibu->wilayahKerja?->nama_wilayah,
                ];
            @endphp

            <div class="divide-y divide-[#F7E6ED] px-5">
                @foreach ($dataIbu as $label => $nilai)
                    <div class="flex justify-between gap-4 py-3 text-sm">
                        <span class="font-semibold text-slate-600">{{ $label }}</span>
                        <span class="text-right font-semibold text-[#542D3D]">{{ $nilai ?: '-' }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="px-5 py-8 text-center text-sm text-slate-500">Data ibu belum terhubung.</p>
        @endif
    </div>

    {{-- Grafik Pertumbuhan --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
            <h2 class="text-base font-bold text-[#542D3D]">Grafik Pertumbuhan (Berat Badan menurut Umur)</h2>
            <p class="mt-0.5 text-sm text-slate-600">Hijau berarti normal, merah berarti perlu perhatian.</p>
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
                    <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-green-600"></span> Normal</span>
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
    <div id="riwayat-pemeriksaan" data-riwayat class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
            <div>
                <h2 class="text-base font-bold text-[#542D3D]">Riwayat Pemeriksaan Anak</h2>
                <p class="mt-0.5 text-sm text-slate-600">Riwayat hasil pemeriksaan berdasarkan kegiatan Posyandu.</p>
            </div>

            @if ($adaLebih)
                <button type="button" data-riwayat-toggle
                        data-label-buka="Tampilkan semua"
                        data-label-tutup="Tampilkan sedikit"
                        class="rounded-xl border-2 border-[#C9517E] bg-white px-4 py-2 text-sm font-semibold text-[#C9517E] transition hover:bg-[#C9517E] hover:text-white">
                    Tampilkan semua
                </button>
            @endif
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
                            <th class="px-5 py-3 text-center font-bold">Status</th>
                            <th class="px-5 py-3 font-bold">Imunisasi / Vitamin</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#F7E6ED]">
                        @foreach ($riwayatTerbaru as $item)
                            @php $lebih = $loop->index >= $batasRiwayat; @endphp
                            <tr class="transition hover:bg-[#FCE7F0]/40 {{ $lebih ? 'hidden' : '' }}" @if ($lebih) data-extra @endif>
                                <td class="px-5 py-3">{{ $formatTanggal($item->kegiatan?->tanggal) }}</td>
                                <td class="px-5 py-3 text-center">{{ $angka($item->berat_badan) }}</td>
                                <td class="px-5 py-3 text-center">{{ $angka($item->tinggi_badan) }}</td>
                                <td class="px-5 py-3 text-center">{{ $angka($ambil($item, ['lingkar_kepala', 'lk'])) }}</td>
                                <td class="px-5 py-3 text-center">
                                    @if ($item->status_kesehatan === 'normal')
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Normal</span>
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
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-riwayat-toggle]');
                if (!btn) return;

                const box  = btn.closest('[data-riwayat]');
                const rows = box.querySelectorAll('[data-extra]');
                const buka = rows.length > 0 && rows[0].classList.contains('hidden');

                rows.forEach(function (r) { r.classList.toggle('hidden', !buka); });
                btn.textContent = buka ? btn.dataset.labelTutup : btn.dataset.labelBuka;
            });
        </script>
    @endpush
@endonce
