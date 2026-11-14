@extends('layouts.app')

@section('title', 'Data Posyandu')

@section('content')
@php
    $base = $base ?? 'admin';

    if (method_exists($kegiatans, 'appends')) {
        $kegiatans->appends(request()->query());
    }
    $nomorAwal = $kegiatans->firstItem() ?? 1;

    $kartu = [
        ['Jumlah Kegiatan', $jumlahKegiatan ?? 0, 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z'],
        ['Anak Diperiksa', $jumlahAnakDiperiksa ?? 0, 'M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6'],
        ['Posyandu Aktif', $posyanduAktif ?? 0, 'M3 21h18M5 21V8l7-5 7 5v13M10 21v-6h4v6h'],
    ];

    $sedangMencari = request()->hasAny(['search', 'periode']);
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Posyandu</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Posyandu</p>
    </div>

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach ($kartu as [$label, $nilai, $ikon])
            <div class="flex items-center gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#542D3D]">{{ $label }}</p>
                    <p class="mt-0.5 text-2xl font-bold leading-none text-[#C9517E]">{{ number_format($nilai, 0, ',', '.') }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Daftar --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Pencarian + filter periode --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <label for="searchInput" class="sr-only">Cari Posyandu</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="searchInput" type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                           placeholder="Cari Posyandu"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>

                <div class="sm:w-56">
                    <label for="filterPeriode" class="sr-only">Filter periode</label>
                    <select id="filterPeriode" name="periode" onchange="this.form.submit()"
                            class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                        <option value="">Semua Periode</option>
                        @foreach ($periodes as $p)
                            <option value="{{ $p }}" @selected(request('periode') === $p)>
                                {{ \Illuminate\Support\Carbon::createFromFormat('!Y-m', $p)->translatedFormat('F Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                    Cari
                </button>

                @if ($sedangMencari)
                    <a href="{{ url()->current() }}"
                       class="text-center text-sm font-semibold text-[#C9517E] hover:underline">Reset</a>
                @endif
            </form>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">No</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Tanggal</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Jenis Kegiatan</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">Lokasi</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Anak Diperiksa</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($kegiatans as $k)
                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 sm:px-4">{{ $nomorAwal + $loop->index }}</td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-slate-800 sm:px-4">{{ $k->tanggal?->format('d/m/Y') ?? '-' }}</td>

                            <td class="px-3 py-3.5 sm:px-4">
                                <p class="break-words text-sm font-bold text-[#542D3D]">{{ $k->jenis_kegiatan ?? '-' }}</p>
                                <p class="text-xs text-slate-700 md:hidden">{{ $k->lokasi ?: '-' }}</p>
                            </td>

                            <td class="hidden px-4 py-3.5 text-sm text-slate-800 md:table-cell">{{ $k->lokasi ?: '-' }}</td>
                            <td class="px-3 py-3.5 text-center text-sm font-bold text-[#C9517E] sm:px-4">{{ ($jumlahAnak[$k->id] ?? 0) ?: '-' }}</td>

                            <td class="whitespace-nowrap px-3 py-3.5 text-center sm:px-4">
                                <a href="{{ route($base . '.data-posyandu.show', $k) }}"
                                   class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border-2 border-[#E99AB7] bg-white px-3 text-xs font-bold text-[#C9517E] transition hover:bg-[#FCE7F0] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/30">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">{{ $sedangMencari ? 'Data tidak ditemukan.' : 'Belum ada data kegiatan Posyandu.' }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-[#F1D5E0] bg-white px-4 py-3">
            @php
                $pg     = $kegiatans;
                $awal   = max($pg->currentPage() - 1, 1);
                $akhir  = min($pg->currentPage() + 1, $pg->lastPage());
                $btn    = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-[#F1D5E0] bg-white px-2 text-sm font-semibold text-[#542D3D] transition hover:bg-[#FCE7F0]';
                $btnOff = 'inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-[#F1D5E0] bg-white text-lg text-slate-300';
            @endphp
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
    </div>
</div>
@endsection