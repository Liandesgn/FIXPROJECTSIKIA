@extends('layouts.app')

@section('title', 'Pendataan Anak')

@section('content')
@php
    $routePrefix = match (true) {
        request()->routeIs('bidan.*') => 'bidan',
        request()->routeIs('kader.*') => 'kader',
        default => 'admin',
    };

    // Judul mengikuti wireframe: Bidan "Anak / Posyandu", Kader "Pendataan Anak / Pendataan Posyandu"
    $judulHalaman = $routePrefix === 'bidan' ? 'Anak' : 'Pendataan Anak';
    $remahRoti    = $routePrefix === 'bidan' ? 'Posyandu' : 'Pendataan Posyandu';

    if (method_exists($anakList, 'appends')) {
        $anakList->appends(request()->query());
    }

    $nomorAwal     = $anakList->firstItem() ?? 1;
    $sedangMencari = request()->hasAny(['search', 'status', 'periode']);

    $pilihClass = 'w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $btnAksi    = 'inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg px-3 text-xs font-bold shadow-sm transition focus:outline-none focus:ring-4';
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">{{ $judulHalaman }}</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">{{ $remahRoti }}</p>
    </div>

    {{-- Informasi kegiatan Posyandu hari ini --}}
    <div class="rounded-2xl border border-[#F1D5E0] bg-white px-5 py-4 shadow-sm">
        @if ($jadwalHariIni)
            @php
                $kegiatan     = $jadwalHariIni->jadwalPosyandu?->kegiatan;
                $namaKegiatan = $kegiatan?->jenis_kegiatan ?? 'Kegiatan Posyandu';
                $lokasi       = $jadwalHariIni->lokasi
                    ?? $kegiatan?->lokasi
                    ?? '-';
            @endphp

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#542D3D]">{{ $namaKegiatan }}</h2>

                    <p class="mt-1 text-sm font-medium text-slate-700">
                        Kegiatan
                        {{ \Carbon\Carbon::parse($jadwalHariIni->tanggal)->format('d/m/Y') }}
                        - {{ $lokasi }}
                    </p>

                    <p class="mt-0.5 text-xs font-medium text-slate-600">
                        Pukul {{ $jadwalHariIni->waktu ? \Carbon\Carbon::parse($jadwalHariIni->waktu)->format('H:i') : '-' }}
                    </p>
                </div>

                <span class="inline-flex w-fit items-center gap-1.5 rounded-full border border-green-300 bg-green-50 px-3 py-1 text-xs font-bold text-green-700">
                    <span class="h-2 w-2 rounded-full bg-green-500" aria-hidden="true"></span>
                    Berlangsung
                </span>
            </div>
        @else
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#542D3D]">Informasi Kegiatan Posyandu Hari Ini</h2>
                    <p class="mt-1 text-sm font-medium text-slate-700">
                        Tidak ada kegiatan Posyandu yang dijadwalkan hari ini.
                    </p>
                </div>

                <span class="inline-flex w-fit items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                    Tidak Ada Kegiatan
                </span>
            </div>
        @endif
    </div>

    {{-- Daftar --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Pencarian + filter --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <form action="{{ route($routePrefix . '.data-posyandu.index') }}" method="GET"
                  class="flex flex-col gap-3 lg:flex-row lg:items-center">

                <div class="relative flex-1">
                    <label for="searchInput" class="sr-only">Cari anak</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="searchInput" type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                           placeholder="Cari nama anak atau NIK"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>

                <div class="lg:w-48">
                    <label for="filterStatus" class="sr-only">Filter status</label>
                    <select id="filterStatus" name="status" class="{{ $pilihClass }}">
                        <option value="">Semua Status</option>
                        <option value="belum_diperiksa" @selected(request('status') === 'belum_diperiksa')>Belum Diperiksa</option>
                        <option value="Normal" @selected(request('status') === 'Normal')>Normal</option>
                        <option value="Pendek" @selected(request('status') === 'Pendek')>Pendek</option>
                        <option value="Sangat Pendek" @selected(request('status') === 'Sangat Pendek')>Sangat Pendek</option>
                        <option value="Tinggi" @selected(request('status') === 'Tinggi')>Tinggi</option>
                    </select>
                </div>

                <div class="lg:w-48">
                    <label for="filterPeriode" class="sr-only">Filter periode</label>
                    <input id="filterPeriode" type="month" name="periode" value="{{ request('periode') }}" class="{{ $pilihClass }}">
                </div>

                <button type="submit"
                        class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                    Cari
                </button>

                @if ($sedangMencari)
                    <a href="{{ route($routePrefix . '.data-posyandu.index') }}"
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
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Anak</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">NIK</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Usia</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Status</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($anakList as $anak)
                        @php
                            $usia = $anak->tgl_lahir
                                ? \Carbon\Carbon::parse($anak->tgl_lahir)->diff(now())
                                : null;

                            $status = $anak->status_stunting;

                            $statusLower = strtolower((string) $status);

                            $warnaStatus = $statusLower === 'normal'
                                ? 'bg-green-100 text-green-700'
                                : (str_contains($statusLower, 'pendek') || str_contains($statusLower, 'stunting')
                                    ? 'bg-red-100 text-red-700'
                                    : 'bg-amber-100 text-amber-700');
                        @endphp

                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 sm:px-4">
                                {{ $nomorAwal + $loop->index }}
                            </td>

                            <td class="px-3 py-3.5 sm:px-4">
                                <p class="break-words text-sm font-bold text-[#542D3D]">{{ $anak->nama_anak }}</p>
                                <p class="text-xs text-slate-700">{{ $anak->jenis_kelamin ?? '-' }}</p>
                                <p class="text-xs text-slate-700 md:hidden">{{ $anak->nik_anak ?? '-' }}</p>
                            </td>

                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 md:table-cell">
                                {{ $anak->nik_anak ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-slate-800 sm:px-4">
                                @if ($usia)
                                    {{ $usia->y > 0 ? $usia->y . ' th ' : '' }}{{ $usia->m }} bln
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-3 py-3.5 text-center sm:px-4">
                                @if (!$anak->pemeriksaan_id)
                                    <span class="inline-flex whitespace-nowrap rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                        Belum Diperiksa
                                    </span>
                                @else
                                    <span class="inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold {{ $warnaStatus }}">
                                        {{ $status ?? 'Sudah Diperiksa' }}
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-3 py-3.5 sm:px-4">
                                <div class="flex items-center justify-center gap-2">

                                    {{-- Detail --}}
                                    <a href="{{ route($routePrefix . '.data-anak.show', $anak->anak_id) }}"
                                       class="{{ $btnAksi }} border-2 border-[#E99AB7] bg-white text-[#C9517E] hover:bg-[#FCE7F0] focus:ring-[#E99AB7]/30">
                                        Detail
                                    </a>

                                    @if ($anak->pemeriksaan_id && $anak->kegiatan_posyandu_id)

                                        {{-- Edit pemeriksaan --}}
                                        <a href="{{ route($routePrefix . '.data-posyandu.pemeriksaan.edit', [
                                            'kegiatan' => $anak->kegiatan_posyandu_id,
                                            'anak' => $anak->anak_id,
                                            'pemeriksaan' => $anak->pemeriksaan_id,
                                        ]) }}"
                                           class="{{ $btnAksi }} bg-[#E99AB7] text-[#542D3D] hover:bg-[#DD87A8] focus:ring-[#E99AB7]/40">
                                            Edit
                                        </a>

                                    @elseif (!$anak->pemeriksaan_id && $jadwalHariIni?->jadwalPosyandu?->kegiatan)

                                        @php
                                            $kegiatanInput = $jadwalHariIni->jadwalPosyandu->kegiatan;
                                        @endphp

                                        {{-- Input pemeriksaan --}}
                                        <a href="{{ route($routePrefix . '.data-posyandu.pemeriksaan.create', [
                                            'kegiatan' => $kegiatanInput->id,
                                            'anak' => $anak->anak_id,
                                        ]) }}"
                                           class="{{ $btnAksi }} bg-[#C9517E] text-white hover:bg-[#B7436D] focus:ring-[#E99AB7]/40">
                                            Input
                                        </a>

                                    @else

                                        <span class="text-xs font-medium text-slate-500">Belum Ada Kegiatan</span>

                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">
                                    {{ $sedangMencari ? 'Data tidak ditemukan.' : 'Belum Ada Data Anak' }}
                                </p>
                                @unless ($sedangMencari)
                                    <p class="mt-1 text-sm font-medium text-slate-600">
                                        Belum terdapat anak yang terdaftar di wilayah kerja Anda.
                                    </p>
                                @endunless
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-[#F1D5E0] bg-white px-4 py-3">
            @php
                $pg     = $anakList;
                $awal   = max($pg->currentPage() - 1, 1);
                $akhir  = min($pg->currentPage() + 1, $pg->lastPage());
                $btn    = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-[#F1D5E0] bg-white px-2 text-sm font-semibold text-[#542D3D] transition hover:bg-[#FCE7F0]';
                $btnOff = 'inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-[#F1D5E0] bg-white text-lg text-slate-300';
            @endphp
            <nav class="flex flex-col items-center justify-between gap-3 sm:flex-row" aria-label="Navigasi halaman">
                <p class="text-xs font-medium text-slate-600">
                    Menampilkan {{ $pg->firstItem() ?? 0 }}–{{ $pg->lastItem() ?? 0 }} dari {{ $pg->total() }} anak
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