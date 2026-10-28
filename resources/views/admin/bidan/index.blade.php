@extends('layouts.app')

@section('title', 'Kelola Bidan')

@section('content')
@php
    $total   = method_exists($bidans, 'total') ? $bidans->total() : $bidans->count();
    $nextUrl = method_exists($bidans, 'nextPageUrl') ? ($bidans->nextPageUrl() ?? '') : '';
@endphp

<div class="mx-auto w-full max-w-[96rem] space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Bidan</h1>
            <p class="mt-1 text-sm font-medium text-slate-700">Manajemen User / Kelola Bidan</p>
        </div>
@can('user.create')
        <a href="{{ route('admin.bidan.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
            <span class="text-lg leading-none">+</span> Tambah Bidan
        </a>
@endcan
    </div>

    {{-- Pesan berhasil / gagal ditampilkan sebagai popup SweetAlert dari layout --}}

    {{-- Ringkasan (ringkas) --}}
    <div class="inline-flex items-center gap-3 rounded-xl border border-[#F1D5E0] bg-white px-4 py-2.5 shadow-sm">
        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#FCE7F0] text-[#C9517E]">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11a3 3 0 1 0 0-6m-8 6a3 3 0 1 0 0-6m-6 13a6 6 0 0 1 12 0m2-5a6 6 0 0 1 6 5"/>
            </svg>
        </span>
        <div class="leading-tight">
            <p class="text-xs font-semibold text-slate-700">Total data bidan</p>
            <p class="text-xl font-bold text-[#C9517E]">{{ $total }}</p>
        </div>
    </div>

    {{-- Daftar --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Bar cari + filter --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <label for="searchInput" class="sr-only">Cari bidan</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="searchInput" type="search" autocomplete="off"
                           placeholder="Cari nama, email, atau wilayah"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>

                <div class="sm:w-64">
                    <label for="wilayahFilter" class="sr-only">Filter wilayah kerja</label>
                    <select id="wilayahFilter"
                            class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                        <option value="">Semua wilayah kerja</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Tabel (virtual scrolling) --}}
        <div id="listWrap" data-next="{{ $nextUrl }}">
            <div id="listScroll" class="max-h-[28rem] overflow-y-auto overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="sticky top-0 z-10 bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                        <tr>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">No</th>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">Nama Bidan</th>
                            <th class="hidden whitespace-nowrap bg-[#FCE7F0] px-4 py-3 2xl:table-cell">Email</th>
                            <th class="hidden whitespace-nowrap bg-[#FCE7F0] px-4 py-3 xl:table-cell">No. Telepon</th>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">Wilayah Kerja</th>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 text-center lg:px-4">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="listBody" class="divide-y divide-[#F7E6ED]">
                        @forelse ($bidans as $bidan)
                            @php $wilayah = optional($bidan->bidan?->wilayahKerja)->desa ?? '-'; @endphp
                            <tr class="row-item transition hover:bg-[#FCE7F0]/50"
                                data-wilayah="{{ $wilayah }}"
                                data-search="{{ mb_strtolower(($bidan->name ?? '') . ' ' . ($bidan->email ?? '') . ' ' . ($bidan->phone ?? '') . ' ' . $wilayah) }}">
                                <td class="px-3 py-3 text-sm font-semibold text-slate-700 lg:px-4"><span class="row-no">{{ $loop->iteration }}</span></td>

                                {{-- Nama (+ email & HP ditumpuk di layar yang lebih kecil) --}}
                                <td class="px-3 py-3 lg:px-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#C9517E] text-sm font-bold text-white">
                                            {{ strtoupper(substr($bidan->name ?? 'B', 0, 1)) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="break-words text-sm font-bold text-[#542D3D] lg:whitespace-nowrap">{{ $bidan->name ?? '-' }}</p>
                                            <p class="break-all text-xs text-slate-700 lg:break-normal lg:whitespace-nowrap 2xl:hidden">{{ $bidan->email ?? '-' }}</p>
                                            <p class="text-xs text-slate-700 xl:hidden">{{ $bidan->phone ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="hidden whitespace-nowrap px-4 py-3 text-sm text-slate-800 2xl:table-cell">{{ $bidan->email ?? '-' }}</td>
                                <td class="hidden whitespace-nowrap px-4 py-3 text-sm text-slate-800 xl:table-cell">{{ $bidan->phone ?? '-' }}</td>
                                <td class="px-3 py-3 text-sm font-semibold text-slate-800 lg:whitespace-nowrap lg:px-4">{{ $wilayah }}</td>

                                <td class="whitespace-nowrap px-3 py-3 lg:px-4">
                                    <div class="flex flex-nowrap items-center justify-center gap-2">
                                        {{-- EDIT: pink tua solid --}}
                                        @can('user.update')
                                        <a href="{{ route('admin.bidan.edit', $bidan->id) }}"
                                           class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white shadow-sm transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 sm:px-3.5">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 20h4L19 9l-4-4L4 16v4Zm9-13 4 4"/></svg>
                                            Edit
                                        </a>
                                        @endcan

                                        {{-- HAPUS: merah solid. Konfirmasi memakai SweetAlert (diatur di layout) --}}
                                        @can('user.delete')
                                        <form action="{{ route('admin.bidan.destroy', $bidan->id) }}" method="POST" class="m-0"
                                              data-confirm-delete data-name="{{ $bidan->name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-red-600 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200 sm:px-3.5">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 11v6m4-6v6M6 7l1 12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-12M9 7V4h6v3"/></svg>
                                                Hapus
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <p class="font-bold text-[#542D3D]">Belum ada data bidan.</p>
                                    <p class="mt-1 text-sm text-slate-700">Tambahkan data bidan melalui tombol Tambah Bidan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div id="emptySearch" class="hidden px-5 py-10 text-center text-sm font-semibold text-slate-700">
                    Data bidan yang dicari tidak ditemukan.
                </div>
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
    const q = document.getElementById('searchInput');
    const sel = document.getElementById('wilayahFilter');
    const info = document.getElementById('listInfo');
    const empty = document.getElementById('emptySearch');

    const CHUNK = 20;
    let limit = CHUNK;
    let lastMatched = 0;
    let next = wrap.dataset.next || '';
    let loading = false;

    const rows = () => Array.from(body.querySelectorAll('tr.row-item'));

    function buildOptions() {
        const cur = sel.value;
        const set = new Set();
        rows().forEach(r => { const w = r.dataset.wilayah; if (w && w !== '-') set.add(w); });
        sel.length = 1;
        Array.from(set).sort((a, b) => a.localeCompare(b, 'id')).forEach(w => {
            const o = document.createElement('option');
            o.value = o.textContent = w;
            sel.appendChild(o);
        });
        sel.value = cur;
    }

    function apply() {
        const kw = q.value.trim().toLowerCase();
        const wl = sel.value;
        let matched = 0, shown = 0;

        rows().forEach(r => {
            const ok = (!kw || (r.dataset.search || '').includes(kw)) && (!wl || r.dataset.wilayah === wl);
            let show = false;
            if (ok) {
                matched++;
                if (matched <= limit) {
                    show = true; shown++;
                    const no = r.querySelector('.row-no');
                    if (no) no.textContent = matched;
                }
            }
            r.classList.toggle('hidden', !show);
        });

        lastMatched = matched;
        empty.classList.toggle('hidden', matched !== 0 || rows().length === 0);
        info.textContent = 'Menampilkan ' + shown + ' dari ' + matched + ' data' + (loading ? ' · memuat data...' : '');
    }

    // Muat sisa halaman di latar belakang agar pencarian & filter mencakup semua data
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
        } catch (e) { /* abaikan: data yang sudah termuat tetap tampil */ }
        loading = false;
        buildOptions();
        apply();
    }

    // Virtual scrolling: tampilkan 20 baris tambahan setiap kali mendekati dasar daftar
    scroller.addEventListener('scroll', function () {
        const nearBottom = scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 80;
        if (nearBottom && limit < lastMatched) { limit += CHUNK; apply(); }
    });

    const reset = () => { limit = CHUNK; scroller.scrollTop = 0; apply(); };
    q.addEventListener('input', reset);
    sel.addEventListener('change', reset);

    buildOptions();
    apply();
    if (next) loadAll();
});
</script>
@endsection