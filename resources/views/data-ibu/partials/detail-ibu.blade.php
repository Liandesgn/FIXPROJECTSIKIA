@php
    $riwayatPemeriksaanIbu = $riwayatPemeriksaanIbu ?? collect();
    $filterPeriode = $filterPeriode ?? false;

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

    $warnaStatus = fn ($s) => $s === 'normal' ? '#16a34a' : '#dc2626';
    $labelStatus = fn ($s) => $s === 'normal' ? 'Normal' : 'Perlu Perhatian';

    $dataIbu = [
        'NIK'           => $ibu->nik,
        'Nama'          => $ibu->user?->name,
        'Tanggal Lahir' => $formatTanggal($ibu->tgl_lahir),
        'Nomor HP'      => $ibu->user?->phone,
        'Wilayah'       => $ibu->wilayahKerja?->nama_wilayah,
    ];

    // Riwayat pemeriksaan (terbaru di atas)
    $riwayat = collect($riwayatPemeriksaanIbu)->map(function ($p) use ($ambil) {
        $sistol  = $ambil($p, ['sistol', 'sistolik']);
        $diastol = $ambil($p, ['diastol', 'diastolik']);

        return [
            'tanggal' => $p->tgl_pemeriksaan,
            'usia'    => $ambil($p, ['usia_kehamilan', 'usia_kehamilan_minggu']),
            'bb'      => $ambil($p, ['berat_badan', 'bb']),
            'td'      => $ambil($p, ['tekanan_darah'])
                         ?? (($sistol !== null && $diastol !== null) ? $sistol.'/'.$diastol : null),
            'status'  => $p->status_kesehatan,
        ];
    })->sortByDesc('tanggal')->values();

    $batasRiwayat = 4;
    $adaLebih     = $riwayat->count() > $batasRiwayat;

    // Daftar bulan untuk dropdown "Semua Periode"
    $daftarPeriode = $riwayat
        ->map(fn ($r) => $r['tanggal'] ? \Carbon\Carbon::parse($r['tanggal'])->format('Y-m') : null)
        ->filter()
        ->unique()
        ->values();

    // ===== Grafik (hanya untuk ibu hamil) =====
    $tampilGrafik = $ibu->sedangHamil();
    $adaBB        = $tampilGrafik && $riwayat->contains(fn ($r) => $r['bb'] !== null);

    $dataGrafik = $riwayat->sortBy('tanggal')->values()
        ->filter(fn ($r) => in_array($r['status'], ['normal', 'perlu_perhatian'], true))
        ->when($adaBB, fn ($c) => $c->filter(fn ($r) => $r['bb'] !== null))
        ->values()
        ->take(-12)
        ->values();

    $jumlahGrafik = $dataGrafik->count();
    $xKiri = $adaBB ? 70 : 130;
    $xKanan = 570; $yAtas = 40; $yBawah = 170;

    $bbMin = $adaBB && $jumlahGrafik > 0 ? (float) $dataGrafik->min(fn ($r) => (float) $r['bb']) : 0;
    $bbMax = $adaBB && $jumlahGrafik > 0 ? (float) $dataGrafik->max(fn ($r) => (float) $r['bb']) : 0;

    $titik = [];
    foreach ($dataGrafik as $i => $r) {
        $x = $jumlahGrafik > 1
            ? $xKiri + ($i * (($xKanan - $xKiri) / ($jumlahGrafik - 1)))
            : ($xKiri + $xKanan) / 2;

        if ($adaBB) {
            $y = $bbMax === $bbMin
                ? ($yAtas + $yBawah) / 2
                : $yBawah - ((((float) $r['bb']) - $bbMin) / ($bbMax - $bbMin)) * ($yBawah - $yAtas);
        } else {
            $y = $r['status'] === 'normal' ? 150 : 60;
        }

        $titik[] = [
            'x'       => round($x, 1),
            'y'       => round($y, 1),
            'bb'      => $r['bb'],
            'usia'    => $r['usia'],
            'tanggal' => $r['tanggal'],
            'status'  => $r['status'],
        ];
    }
@endphp

<div class="space-y-6">

    {{-- Data Ibu (kiri) dan Status Kehamilan (kanan) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
                <h2 class="text-base font-bold text-[#542D3D]">Data Ibu</h2>
            </div>

            <div class="divide-y divide-[#F7E6ED] px-5">
                @foreach ($dataIbu as $label => $nilai)
                    <div class="flex justify-between gap-4 py-3 text-sm">
                        <span class="font-semibold text-slate-600">{{ $label }}</span>
                        <span class="text-right font-semibold text-[#542D3D]">{{ $nilai ?: '-' }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
                <h2 class="text-base font-bold text-[#542D3D]">Status Kehamilan</h2>
            </div>

            <div class="p-5">
                <div class="mb-3">
                    @include('data-ibu.partials.badge', ['status' => $ibu->status_kehamilan])
                </div>

                @if ($ibu->sedangHamil())
                    <p class="text-sm text-slate-700">
                        Usia kehamilan:
                        <span class="font-semibold text-[#542D3D]">
                            {{ $ibu->usia_kehamilan_sekarang !== null ? $ibu->usia_kehamilan_sekarang.' minggu' : '0 minggu' }}
                        </span>
                    </p>
                    <p class="mt-1 text-sm text-slate-700">
                        HPL: <span class="font-semibold text-[#542D3D]">{{ $formatTanggal($ibu->hpl) }}</span>
                    </p>
                @else
                    <p class="text-sm text-slate-600">Ibu saat ini tidak dalam status kehamilan.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Data Anak --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
            <h2 class="text-base font-bold text-[#542D3D]">Data Anak</h2>
        </div>

        @if ($ibu->anak->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-left text-sm">
                    <thead class="bg-[#FFF5F8] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                        <tr>
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">NIK Anak</th>
                            <th class="px-5 py-3">Nama Anak</th>
                            <th class="px-5 py-3">Tanggal Lahir</th>
                            <th class="px-5 py-3">Jenis Kelamin</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#F7E6ED]">
                        @foreach ($ibu->anak as $anak)
                            <tr class="transition hover:bg-[#FCE7F0]/40">
                                <td class="px-5 py-3 font-semibold text-slate-700">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3">{{ $anak->nik_anak ?: '-' }}</td>
                                <td class="px-5 py-3 font-semibold text-[#542D3D]">{{ $anak->nama_anak ?: '-' }}</td>
                                <td class="px-5 py-3">{{ $formatTanggal($anak->tgl_lahir) }}</td>
                                <td class="px-5 py-3">
                                    {{ $anak->jenis_kelamin === 'L' ? 'Laki-laki' : ($anak->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="px-5 py-6 text-sm font-medium text-slate-600">Belum ada data anak</p>
        @endif
    </div>

    {{-- Grafik Perkembangan Kehamilan (hanya ibu hamil) --}}
    @if ($tampilGrafik)
        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
                <h2 class="text-base font-bold text-[#542D3D]">
                    Grafik Perkembangan Kehamilan ({{ $adaBB ? 'Berat Badan' : 'Status Pemeriksaan' }})
                </h2>
                <p class="mt-0.5 text-sm text-slate-600">Hijau berarti normal, merah berarti perlu perhatian.</p>
            </div>

            <div class="p-5">
                @if ($jumlahGrafik > 0)
                    <div class="w-full overflow-x-auto">
                        <svg viewBox="0 0 600 222" class="w-full min-w-[480px]" role="img"
                             aria-label="Grafik perkembangan kehamilan">

                            @if ($adaBB)
                                @foreach ([40, 82, 126, 170] as $gy)
                                    <line x1="{{ $xKiri - 10 }}" y1="{{ $gy }}" x2="{{ $xKanan + 10 }}" y2="{{ $gy }}"
                                          stroke="#F1D5E0" stroke-dasharray="4 4"/>
                                @endforeach

                                <text x="4" y="20" font-size="11" font-weight="700" fill="#64748B">BB (kg)</text>
                                <text x="{{ $xKiri - 16 }}" y="44" font-size="11" text-anchor="end" fill="#64748B">{{ number_format($bbMax, 1) }}</text>
                                <text x="{{ $xKiri - 16 }}" y="174" font-size="11" text-anchor="end" fill="#64748B">{{ number_format($bbMin, 1) }}</text>
                            @else
                                <line x1="{{ $xKiri - 20 }}" y1="60" x2="{{ $xKanan + 10 }}" y2="60" stroke="#FECACA" stroke-dasharray="4 4"/>
                                <line x1="{{ $xKiri - 20 }}" y1="150" x2="{{ $xKanan + 10 }}" y2="150" stroke="#BBF7D0" stroke-dasharray="4 4"/>
                                <text x="8" y="64" font-size="11" font-weight="700" fill="#dc2626">Perlu Perhatian</text>
                                <text x="8" y="154" font-size="11" font-weight="700" fill="#16a34a">Normal</text>
                            @endif

                            {{-- Garis: warna mengikuti status di titik tujuan --}}
                            @foreach ($titik as $i => $p)
                                @if ($i > 0)
                                    <line x1="{{ $titik[$i - 1]['x'] }}" y1="{{ $titik[$i - 1]['y'] }}"
                                          x2="{{ $p['x'] }}" y2="{{ $p['y'] }}"
                                          stroke="{{ $warnaStatus($p['status']) }}" stroke-width="3" stroke-linecap="round"/>
                                @endif
                            @endforeach

                            {{-- Titik, nilai, dan usia kehamilan --}}
                            @foreach ($titik as $i => $p)
                                <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="6"
                                        fill="{{ $warnaStatus($p['status']) }}" stroke="white" stroke-width="2">
                                    <title>{{ $formatTanggal($p['tanggal']) }}{{ $p['bb'] !== null ? ' · '.$p['bb'].' kg' : '' }} · {{ $labelStatus($p['status']) }}</title>
                                </circle>

                                @if ($adaBB && $jumlahGrafik <= 8)
                                    <text x="{{ $p['x'] }}" y="{{ $p['y'] - 12 }}" font-size="11" font-weight="700"
                                          text-anchor="middle" fill="#542D3D">{{ $p['bb'] }}</text>
                                @endif

                                @if ($jumlahGrafik <= 8 || $i % 2 === 0)
                                    <text x="{{ $p['x'] }}" y="192" font-size="10" text-anchor="middle" fill="#64748B">
                                        {{ $p['usia'] !== null ? $p['usia'].' mgg' : ($p['tanggal'] ? \Carbon\Carbon::parse($p['tanggal'])->format('d/m') : '') }}
                                    </text>
                                @endif
                            @endforeach

                            <text x="{{ ($xKiri + $xKanan) / 2 }}" y="214" font-size="11" text-anchor="middle" fill="#64748B">usia kehamilan (minggu)</text>
                        </svg>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs font-semibold text-slate-700">
                        <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-green-600"></span> Normal</span>
                        <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-red-600"></span> Perlu Perhatian</span>
                    </div>
                @else
                    <div class="flex min-h-48 flex-col items-center justify-center rounded-xl bg-[#FFF5F8]">
                        <p class="text-sm font-semibold text-slate-600">Belum ada data pemeriksaan</p>
                        <p class="mt-1 text-sm text-slate-500">Grafik akan tampil setelah ada hasil pemeriksaan.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Riwayat Pemeriksaan Kehamilan --}}
    <div data-riwayat class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
            <div>
                <h2 class="text-base font-bold text-[#542D3D]">Riwayat Pemeriksaan Kehamilan</h2>
                <p class="mt-0.5 text-sm text-slate-600">Riwayat pemeriksaan ibu berdasarkan data pemeriksaan yang tersimpan.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Dropdown periode (hanya halaman Ibu, sesuai wireframe) --}}
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
                            class="rounded-xl border-2 border-[#C9517E] bg-white px-4 py-2 text-sm font-semibold text-[#C9517E] transition hover:bg-[#C9517E] hover:text-white">
                        Tampilkan semua
                    </button>
                @endif
            </div>
        </div>

        @if ($riwayat->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-[#FFF5F8] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                        <tr>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Usia Kehamilan</th>
                            <th class="px-5 py-3 text-center">BB (kg)</th>
                            <th class="px-5 py-3 text-center">Tekanan Darah</th>
                            <th class="px-5 py-3 text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#F7E6ED]">
                        @foreach ($riwayat as $r)
                            @php $lebih = $loop->index >= $batasRiwayat; @endphp
                            <tr class="transition hover:bg-[#FCE7F0]/40 {{ $lebih ? 'hidden' : '' }}"
                                data-periode="{{ $r['tanggal'] ? \Carbon\Carbon::parse($r['tanggal'])->format('Y-m') : '' }}"
                                @if ($lebih) data-extra @endif>
                                <td class="px-5 py-3">{{ $formatTanggal($r['tanggal']) }}</td>
                                <td class="px-5 py-3">{{ $r['usia'] !== null ? $r['usia'].' mgg' : '-' }}</td>
                                <td class="px-5 py-3 text-center">{{ $angka($r['bb']) }}</td>
                                <td class="px-5 py-3 text-center">{{ $r['td'] ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">
                                    @if ($r['status'] === 'normal')
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Normal</span>
                                    @elseif ($r['status'] === 'perlu_perhatian')
                                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Perlu Perhatian</span>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-5 py-10 text-center">
                <p class="text-sm font-medium text-slate-600">Belum ada riwayat pemeriksaan kehamilan.</p>
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