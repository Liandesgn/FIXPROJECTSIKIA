@extends('layouts.app')

@section('title', 'Laporan Super Admin')

@section('content')
@php
    $tabs = [
        'rekap-kesehatan'   => 'Rekap Kesehatan',
        'kegiatan-posyandu' => 'Kegiatan Posyandu',
        'data-risiko'       => 'Data Risiko',
        'rekap-pemeriksaan' => 'Rekap Pemeriksaan',
    ];

    $tabAktif     = $tab;
    $periodeLabel = \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y');
    $hitung       = fn ($x) => $x instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $x->total() : collect($x)->count();

    $fieldClass = 'w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $thClass    = 'whitespace-nowrap px-4 py-3 text-xs font-bold uppercase tracking-wide text-[#542D3D]';
    $tdClass    = 'px-4 py-3.5 text-sm text-slate-800';

    // Data kosong per tab
    $kosong = match ($tabAktif) {
        'rekap-kesehatan'   => collect($rekapWilayah)->isEmpty(),
        'kegiatan-posyandu' => $hitung($kegiatanPosyandu) === 0,
        'data-risiko'       => $hitung($dataRisiko) === 0,
        'rekap-pemeriksaan' => (collect($rekapPemeriksaan)->sum('pemeriksaan_ibu') + collect($rekapPemeriksaan)->sum('pemeriksaan_anak') + collect($rekapPemeriksaan)->sum('kegiatan_posyandu')) === 0,
        default             => false,
    };
    $pesanKosong = match ($tabAktif) {
        'rekap-kesehatan'   => 'Data rekap belum tersedia pada periode ini.',
        'kegiatan-posyandu' => 'Belum ada kegiatan Posyandu pada periode ini.',
        'data-risiko'       => 'Tidak ada pasien berisiko pada periode ini.',
        default             => 'Belum ada pemeriksaan atau kegiatan pada periode ini.',
    };

    // Pagination hanya jika controller mengirim paginator
    $pg = match ($tabAktif) {
        'kegiatan-posyandu' => $kegiatanPosyandu ?? null,
        'data-risiko'       => $dataRisiko ?? null,
        default             => null,
    };
    $pg = $pg instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $pg : null;
    if ($pg) { $pg->appends(request()->query()); }
    $nomorAwal = $pg ? ($pg->firstItem() ?? 1) : 1;
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Laporan Super Admin</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Pantau laporan kesehatan dan kegiatan dari seluruh wilayah kerja.</p>
    </div>

    {{-- Tab laporan --}}
    <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
        <nav class="flex w-max gap-2 rounded-2xl border border-[#F1D5E0] bg-white p-1.5 shadow-sm sm:w-auto sm:flex-wrap" aria-label="Jenis laporan">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('superadmin.laporan.index', array_merge(request()->query(), ['tab' => $key, 'page' => null])) }}"
                   @if ($tabAktif === $key) aria-current="page" @endif
                   class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition
                          {{ $tabAktif === $key ? 'bg-[#C9517E] text-white shadow-sm' : 'text-[#542D3D] hover:bg-[#FCE7F0]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Filter periode + wilayah --}}
    <form method="GET" action="{{ route('superadmin.laporan.index') }}"
          class="grid gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-4 shadow-sm sm:grid-cols-2 lg:flex lg:items-center">
        <input type="hidden" name="tab" value="{{ $tabAktif }}">

        <div>
            <label for="periode" class="sr-only">Periode</label>
            <input id="periode" type="month" name="periode" value="{{ $periode }}" class="{{ $fieldClass }} lg:w-52">
        </div>

        <div>
            <label for="wilayah_id" class="sr-only">Wilayah</label>
            <select id="wilayah_id" name="wilayah_id" class="{{ $fieldClass }} lg:w-56">
                <option value="">Semua Wilayah</option>
                @foreach ($wilayah as $item)
                    <option value="{{ $item->id }}" @selected((string) $wilayahId === (string) $item->id)>{{ $item->nama_wilayah }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit"
                class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
            Tampilkan
        </button>

        <button type="button" id="btnUnduhPdf"
                data-url="{{ route('superadmin.laporan.pdf', request()->query()) }}"
                data-jenis="{{ $tabs[$tabAktif] ?? '-' }}"
                data-periode="{{ $periodeLabel }}"
                data-wilayah="{{ $namaWilayah }}"
                data-file="{{ str_replace(' ', '_', ($tabs[$tabAktif] ?? 'Laporan') . '_' . $namaWilayah . '_' . $periode) }}.pdf"
                class="w-full rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-2.5 text-center text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/30 sm:col-span-2 lg:ml-auto lg:w-auto">
            Unduh PDF
        </button>
    </form>

    @if ($kosong)

        {{-- Data belum tersedia --}}
        <div class="flex items-start gap-2.5 rounded-xl border-2 border-red-500 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" role="alert">
            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v5m0 3h.01"/></svg>
            <p>{{ $pesanKosong }} Silakan pilih periode lain.</p>
        </div>

        <div class="flex min-h-56 flex-col items-center justify-center gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-10 text-center shadow-sm">
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-[#FCE7F0] text-[#C9517E]">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3h8l4 4v14H4V3h4Zm0 9h8m-8 4h8"/></svg>
            </span>
            <p class="text-sm font-semibold text-slate-600">Tidak ada data untuk ditampilkan</p>
        </div>

    {{-- 1. REKAP KESEHATAN: kartu + tabel per wilayah --}}
    @elseif ($tabAktif === 'rekap-kesehatan')

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="flex items-center gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm-3 3h6l2 5-3 2v5"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#542D3D]">Ibu Hamil</p>
                    <p class="mt-0.5 text-2xl font-bold leading-none text-[#C9517E]">{{ $ringkasan['ibu_hamil'] }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#542D3D]">Anak Terdaftar</p>
                    <p class="mt-0.5 text-2xl font-bold leading-none text-[#C9517E]">{{ $ringkasan['anak'] }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9a6 6 0 1 1 12 0c0 5 2 6.5 2 6.5H4S6 14 6 9Zm4 9.5a2 2 0 0 0 4 0"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#542D3D]">Perlu Perhatian</p>
                    <p class="mt-0.5 text-2xl font-bold leading-none text-red-600">{{ $ringkasan['perlu_perhatian'] }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[38rem] text-left">
                    <thead class="bg-[#FCE7F0]">
                        <tr>
                            <th class="{{ $thClass }}">Wilayah</th>
                            <th class="{{ $thClass }} text-center">Ibu Hamil</th>
                            <th class="{{ $thClass }} text-center">Anak</th>
                            <th class="{{ $thClass }} text-center">Diperiksa</th>
                            <th class="{{ $thClass }} text-center">Perlu Perhatian</th>
                            <th class="{{ $thClass }} text-center">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F7E6ED]">
                        @foreach ($rekapWilayah as $item)
                            <tr class="transition hover:bg-[#FCE7F0]/50">
                                <td class="{{ $tdClass }} font-bold text-[#542D3D]">{{ $item->nama_wilayah }}</td>
                                <td class="{{ $tdClass }} text-center">{{ $item->ibu_hamil }}</td>
                                <td class="{{ $tdClass }} text-center">{{ $item->anak }}</td>
                                <td class="{{ $tdClass }} text-center">{{ $item->diperiksa }}</td>
                                <td class="{{ $tdClass }} text-center">{{ $item->perlu_perhatian }}</td>
                                <td class="{{ $tdClass }} text-center font-bold text-[#C9517E]">{{ $item->persentase }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    {{-- 2 & 3. KEGIATAN POSYANDU dan DATA RISIKO (satu kartu tabel + pagination) --}}
    @elseif (in_array($tabAktif, ['kegiatan-posyandu', 'data-risiko'], true))

        @if ($tabAktif === 'kegiatan-posyandu')
            @php
                $jumlahKegiatan = $ringkasanKegiatan['jumlah_kegiatan'] ?? $hitung($kegiatanPosyandu);
                $anakDiperiksa  = $ringkasanKegiatan['anak_diperiksa']
                    ?? collect($pg ? $pg->items() : $kegiatanPosyandu)->sum('pemeriksaan_count');
            @endphp

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="flex items-center gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-4 shadow-sm">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3h8l4 4v14H4V3h4Zm0 9h8m-8 4h8"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#542D3D]">Jumlah Kegiatan</p>
                        <p class="mt-0.5 text-2xl font-bold leading-none text-[#C9517E]">{{ $jumlahKegiatan }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-4 shadow-sm">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#542D3D]">Anak Diperiksa</p>
                        <p class="mt-0.5 text-2xl font-bold leading-none text-[#C9517E]">{{ $anakDiperiksa }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <div class="overflow-x-auto">
                @if ($tabAktif === 'kegiatan-posyandu')
                    <table class="w-full min-w-[40rem] text-left">
                        <thead class="bg-[#FCE7F0]">
                            <tr>
                                <th class="{{ $thClass }}">No</th>
                                <th class="{{ $thClass }}">Tanggal</th>
                                <th class="{{ $thClass }}">Wilayah</th>
                                <th class="{{ $thClass }}">Jenis Kegiatan</th>
                                <th class="{{ $thClass }}">Lokasi</th>
                                <th class="{{ $thClass }} text-center">Anak Diperiksa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F7E6ED]">
                            @foreach ($kegiatanPosyandu as $item)
                                <tr class="transition hover:bg-[#FCE7F0]/50">
                                    <td class="{{ $tdClass }} font-semibold">{{ $nomorAwal + $loop->index }}</td>
                                    <td class="{{ $tdClass }} whitespace-nowrap">{{ $item->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="{{ $tdClass }}">{{ $item->wilayah?->nama_wilayah ?? '-' }}</td>
                                    <td class="{{ $tdClass }} font-bold text-[#542D3D]">{{ $item->jenis_kegiatan ?? '-' }}</td>
                                    <td class="{{ $tdClass }}">{{ $item->lokasi ?? '-' }}</td>
                                    <td class="{{ $tdClass }} text-center font-bold text-[#C9517E]">{{ $item->pemeriksaan_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <table class="w-full min-w-[46rem] text-left">
                        <thead class="bg-[#FCE7F0]">
                            <tr>
                                <th class="{{ $thClass }}">No</th>
                                <th class="{{ $thClass }}">Nama</th>
                                <th class="{{ $thClass }}">Jenis</th>
                                <th class="{{ $thClass }}">Wilayah</th>
                                <th class="{{ $thClass }}">Pemeriksaan Terakhir</th>
                                <th class="{{ $thClass }}">Indikator</th>
                                <th class="{{ $thClass }}">Penanda</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F7E6ED]">
                            @foreach ($dataRisiko as $item)
                                @php
                                    $penanda = $item->penanda instanceof \Illuminate\Support\Collection
                                        ? $item->penanda->all()
                                        : \Illuminate\Support\Arr::wrap($item->penanda);
                                @endphp
                                <tr class="transition hover:bg-[#FCE7F0]/50">
                                    <td class="{{ $tdClass }} font-semibold">{{ $nomorAwal + $loop->index }}</td>
                                    <td class="{{ $tdClass }} font-bold text-[#542D3D]">{{ $item->nama }}</td>
                                    <td class="{{ $tdClass }}">{{ $item->jenis }}</td>
                                    <td class="{{ $tdClass }}">{{ $item->wilayah }}</td>
                                    <td class="{{ $tdClass }} whitespace-nowrap">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
                                    <td class="{{ $tdClass }}">{{ $item->indikator }}</td>
                                    <td class="{{ $tdClass }}">
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach (array_filter($penanda) as $p)
                                                <span class="inline-flex whitespace-nowrap rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-600">{{ $p }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            @if ($pg)
                @php
                    $awal   = max($pg->currentPage() - 1, 1);
                    $akhir  = min($pg->currentPage() + 1, $pg->lastPage());
                    $btn    = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-[#F1D5E0] bg-white px-2 text-sm font-semibold text-[#542D3D] transition hover:bg-[#FCE7F0]';
                    $btnOff = 'inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-[#F1D5E0] bg-white text-lg text-slate-300';
                @endphp
                <div class="border-t border-[#F1D5E0] bg-white px-4 py-3">
                    <nav class="flex flex-col items-center justify-between gap-3 sm:flex-row" aria-label="Navigasi halaman">
                        <p class="text-xs font-medium text-slate-600">
                            Menampilkan {{ $pg->firstItem() ?? 0 }}–{{ $pg->lastItem() ?? 0 }} dari {{ $pg->total() }} data
                        </p>

                        @if ($pg->hasPages())
                            <div class="inline-flex items-center gap-1">
                                @if ($pg->onFirstPage())
                                    <span class="{{ $btnOff }}" aria-disabled="true">‹</span>
                                @else
                                    <a href="{{ $pg->previousPageUrl() }}" aria-label="Halaman sebelumnya" class="{{ $btn }} text-lg text-[#C9517E]">‹</a>
                                @endif

                                @if ($awal > 1)
                                    <a href="{{ $pg->url(1) }}" class="{{ $btn }}">1</a>
                                    @if ($awal > 2) <span class="px-1 text-slate-400">…</span> @endif
                                @endif

                                @for ($i = $awal; $i <= $akhir; $i++)
                                    @if ($i === $pg->currentPage())
                                        <span aria-current="page" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-[#C9517E] px-2 text-sm font-bold text-white shadow-sm">{{ $i }}</span>
                                    @else
                                        <a href="{{ $pg->url($i) }}" class="{{ $btn }}">{{ $i }}</a>
                                    @endif
                                @endfor

                                @if ($akhir < $pg->lastPage())
                                    @if ($akhir < $pg->lastPage() - 1) <span class="px-1 text-slate-400">…</span> @endif
                                    <a href="{{ $pg->url($pg->lastPage()) }}" class="{{ $btn }}">{{ $pg->lastPage() }}</a>
                                @endif

                                @if ($pg->hasMorePages())
                                    <a href="{{ $pg->nextPageUrl() }}" aria-label="Halaman berikutnya" class="{{ $btn }} text-lg text-[#C9517E]">›</a>
                                @else
                                    <span class="{{ $btnOff }}" aria-disabled="true">›</span>
                                @endif
                            </div>
                        @endif
                    </nav>
                </div>
            @endif
        </div>

    {{-- 4. REKAP PEMERIKSAAN --}}
    @elseif ($tabAktif === 'rekap-pemeriksaan')

        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[36rem] text-left">
                    <thead class="bg-[#FCE7F0]">
                        <tr>
                            <th class="{{ $thClass }}">Bulan</th>
                            <th class="{{ $thClass }} text-center">Pemeriksaan Ibu Hamil</th>
                            <th class="{{ $thClass }} text-center">Pemeriksaan Anak</th>
                            <th class="{{ $thClass }} text-center">Kegiatan Posyandu</th>
                            <th class="{{ $thClass }} text-center">Total Pemeriksaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F7E6ED]">
                        @foreach ($rekapPemeriksaan as $item)
                            <tr class="transition hover:bg-[#FCE7F0]/50">
                                <td class="{{ $tdClass }} font-bold text-[#542D3D]">{{ $item->bulan }}</td>
                                <td class="{{ $tdClass }} text-center">{{ $item->pemeriksaan_ibu }}</td>
                                <td class="{{ $tdClass }} text-center">{{ $item->pemeriksaan_anak }}</td>
                                <td class="{{ $tdClass }} text-center">{{ $item->kegiatan_posyandu }}</td>
                                <td class="{{ $tdClass }} text-center font-bold text-[#C9517E]">{{ $item->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @endif

</div>
@endsection

@push('scripts')
<script>
// Dialog Unduh Laporan PDF (memakai popup SweetAlert dari layout)
document.getElementById('btnUnduhPdf')?.addEventListener('click', function () {
    const btn = this;
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const baris = [
        ['Jenis Laporan', btn.dataset.jenis],
        ['Periode', btn.dataset.periode],
        ['Wilayah', btn.dataset.wilayah],
        ['Nama File', btn.dataset.file],
    ];

    SikiaSwal.fire({
        title: 'Unduh Laporan PDF',
        html: '<dl class="mt-2 space-y-2 text-left text-sm">' + baris.map(function (b) {
            return '<div class="flex flex-col gap-0.5 sm:flex-row sm:gap-4">'
                 + '<dt class="w-32 shrink-0 text-slate-500">' + esc(b[0]) + '</dt>'
                 + '<dd class="break-all font-semibold text-[#542D3D]">' + esc(b[1]) + '</dd></div>';
        }).join('') + '</dl>',
        showCancelButton: true,
        confirmButtonText: 'Unduh PDF',
        cancelButtonText: 'Batal'
    }).then(function (r) {
        if (r.isConfirmed) window.open(btn.dataset.url, '_blank');
    });
});
</script>
@endpush