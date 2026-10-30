@extends('layouts.app')

@section('title', 'Data Ibu')

@section('content')
@php
    // Bawa filter aktif ke halaman berikutnya (untuk virtual scrolling)
    if (method_exists($ibus, 'appends')) {
        $ibus->appends(request()->query());
    }
    $nextUrl   = $ibus->nextPageUrl() ?? '';
    $adaAksi   = $base !== 'kader';   // Kader hanya melihat data, tanpa kolom Aksi
    $fieldBase = 'w-full rounded-xl border-2 border-[#C9517E]/60 bg-white text-sm font-semibold text-gray-900 outline-none transition placeholder:font-medium placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
@endphp

<div class="mx-auto w-full max-w-[96rem] space-y-5">

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Ibu</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Ibu</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Bar cari + filter (dikirim ke controller seperti sebelumnya) --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <form method="GET" action="{{ route($base.'.data-ibu.index') }}" class="flex flex-col gap-3 md:flex-row">
                <div class="relative md:flex-1">
                    <label for="searchIbu" class="sr-only">Cari nama atau NIK</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="searchIbu" type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                           placeholder="Cari nama / NIK" class="{{ $fieldBase }} py-2.5 pl-11 pr-3">
                </div>

                @if ($base === 'admin')
                    <div class="md:w-56">
                        <label for="filterWilayah" class="sr-only">Filter wilayah</label>
                        <select id="filterWilayah" name="wilayah_kerja_id" onchange="this.form.submit()" class="{{ $fieldBase }} px-3 py-2.5">
                            <option value="">Semua wilayah</option>
                            @foreach ($wilayahs as $w)
                                <option value="{{ $w->id }}" @selected((string) request('wilayah_kerja_id') === (string) $w->id)>{{ $w->nama_wilayah }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="md:w-60">
                    <label for="filterStatus" class="sr-only">Filter status kehamilan</label>
                    <select id="filterStatus" name="status_kehamilan" onchange="this.form.submit()" class="{{ $fieldBase }} px-3 py-2.5">
                        <option value="">Semua status kehamilan</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}" @selected(request('status_kehamilan') === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                    Cari
                </button>
            </form>
        </div>

        {{-- Tabel (virtual scrolling) --}}
        <div id="listWrap" data-next="{{ $nextUrl }}">
            <div id="listScroll" class="max-h-[28rem] overflow-x-auto overflow-y-auto">
                <table class="w-full text-left">
                    <thead class="sticky top-0 z-10 text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                        <tr>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">No</th>
                            <th class="hidden whitespace-nowrap bg-[#FCE7F0] px-4 py-3 xl:table-cell">NIK</th>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">Nama</th>
                            <th class="hidden whitespace-nowrap bg-[#FCE7F0] px-4 py-3 md:table-cell">Wilayah</th>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">Status Kehamilan</th>
                            <th class="hidden whitespace-nowrap bg-[#FCE7F0] px-4 py-3 lg:table-cell">Usia Kehamilan</th>
                            <th class="hidden whitespace-nowrap bg-[#FCE7F0] px-4 py-3 lg:table-cell">HPL</th>
                            @if ($adaAksi)
                                <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 text-center lg:px-4">Aksi</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody id="listBody" class="divide-y divide-[#F7E6ED]">
                        @forelse ($ibus as $ibu)
                            <tr class="row-item transition hover:bg-[#FCE7F0]/50">
                                <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 lg:px-4"><span class="row-no">{{ $loop->iteration }}</span></td>
                                <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 xl:table-cell">{{ $ibu->nik ?? '-' }}</td>
                                <td class="px-3 py-3.5 lg:px-4">
                                    <p class="text-sm font-bold text-[#542D3D] lg:whitespace-nowrap">{{ $ibu->user?->name ?? '-' }}</p>
                                    <p class="whitespace-nowrap text-xs font-medium text-slate-700 xl:hidden">NIK {{ $ibu->nik ?? '-' }}</p>
                                    <p class="text-xs font-semibold text-slate-800 md:hidden">{{ $ibu->wilayahKerja?->desa ?? '-' }}</p>
                                </td>
                                <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm font-semibold text-slate-800 md:table-cell">{{ $ibu->wilayahKerja?->desa ?? '-' }}</td>
                                <td class="px-3 py-3.5 lg:px-4">@include('data-ibu.partials.badge', ['status' => $ibu->status_kehamilan])</td>
                                <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 lg:table-cell">{{ $ibu->usia_kehamilan ? $ibu->usia_kehamilan.' mgg' : '-' }}</td>
                                <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 lg:table-cell">{{ $ibu->hpl?->format('d/m/Y') ?? '-' }}</td>

                                @if ($adaAksi)
                                    <td class="whitespace-nowrap px-3 py-3.5 lg:px-4">
                                        <div class="flex flex-nowrap items-center justify-center gap-2">
                                            {{-- DETAIL: outline pink, tanpa ikon --}}
                                            <a href="{{ route($base.'.data-ibu.show', $ibu) }}"
                                               class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border-2 border-[#C9517E] bg-white px-4 text-xs font-bold text-[#C9517E] transition hover:bg-[#FCE7F0] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                                                Detail
                                            </a>
                                            @if ($base === 'admin')
                                                @can('ibu.update')
                                                    {{-- EDIT: pink tua solid --}}
                                                    <a href="{{ route('admin.data-ibu.edit', $ibu) }}"
                                                       class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white shadow-sm transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 20h4L19 9l-4-4L4 16v4Zm9-13 4 4"/></svg>
                                                        Edit
                                                    </a>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $adaAksi ? 8 : 7 }}" class="px-5 py-12 text-center">
                                    <p class="font-bold text-[#542D3D]">Data tidak ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[#F1D5E0] bg-white px-4 py-3">
                <span id="listInfo" class="text-xs font-semibold text-slate-700"></span>
                <span class="text-xs text-slate-600">Gulir ke bawah untuk melihat data lainnya</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.getElementById('listBody');
    const wrap = document.getElementById('listWrap');
    const scroller = document.getElementById('listScroll');
    const info = document.getElementById('listInfo');
    const TOTAL = @json($ibus->total());
    const CHUNK = 20;
    let limit = CHUNK, next = wrap.dataset.next || '', loading = false;

    const rows = () => Array.from(body.querySelectorAll('tr.row-item'));

    // Virtual scrolling: tampilkan 20 baris pertama, tambah 20 saat mendekati dasar daftar
    function apply() {
        const all = rows();
        let shown = 0;
        all.forEach((r, i) => {
            const show = i < limit;
            r.classList.toggle('hidden', !show);
            if (show) { shown++; const no = r.querySelector('.row-no'); if (no) no.textContent = i + 1; }
        });
        info.textContent = all.length ? 'Menampilkan ' + shown + ' dari ' + TOTAL + ' data' + (loading ? ' · memuat data...' : '') : '';
    }

    // Muat sisa halaman di latar belakang (filter yang aktif ikut terbawa)
    async function loadAll() {
        if (loading) return;
        loading = true; apply();
        try {
            while (next) {
                const res = await fetch(next, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                if (!res.ok) break;
                const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                doc.querySelectorAll('tr.row-item').forEach(r => body.appendChild(document.importNode(r, true)));
                const w = doc.getElementById('listWrap');
                next = (w && w.dataset.next) || '';
            }
        } catch (e) { /* data yang sudah termuat tetap tampil */ }
        loading = false; apply();
    }

    scroller.addEventListener('scroll', function () {
        const nearBottom = scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 80;
        if (nearBottom && limit < rows().length) { limit += CHUNK; apply(); }
    });

    apply();
    if (next) loadAll();
});
</script>
@endsection