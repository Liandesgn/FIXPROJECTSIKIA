@extends('layouts.app')

@section('title', 'Pemeriksaan Ibu Hamil')

@section('content')
@php
    if (method_exists($ibu, 'appends')) {
        $ibu->appends(request()->query());
    }

    $nomorAwal    = $ibu->firstItem() ?? 1;
    $sedangMencari = request()->hasAny(['search', 'risiko', 'periode']);

    $pilihClass = 'w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $btnAksi    = 'inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg px-3 text-xs font-bold shadow-sm transition focus:outline-none focus:ring-4';
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Pemeriksaan Ibu Hamil</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Pemeriksaan</p>
    </div>

    {{-- Peringatan --}}
    @if (session('warning'))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
            {{ session('warning') }}
        </div>
    @endif

    {{-- Daftar --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Pencarian + filter --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <form method="GET" action="{{ route('bidan.pemeriksaan-ibu.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="relative flex-1">
                    <label for="searchInput" class="sr-only">Cari ibu hamil</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="searchInput" type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                           placeholder="Cari nama / NIK"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>

                <div class="lg:w-44">
                    <label for="filterRisiko" class="sr-only">Filter risiko</label>
                    <select id="filterRisiko" name="risiko" class="{{ $pilihClass }}">
                        <option value="">Semua Risiko</option>
                        <option value="tinggi" @selected(request('risiko') === 'tinggi')>Risiko Tinggi</option>
                        <option value="sedang" @selected(request('risiko') === 'sedang')>Risiko Sedang</option>
                        <option value="rendah" @selected(request('risiko') === 'rendah')>Risiko Rendah</option>
                    </select>
                </div>

                <div class="lg:w-48">
                    <label for="filterPeriode" class="sr-only">Filter periode</label>
                    <select id="filterPeriode" name="periode" class="{{ $pilihClass }}">
                        <option value="">Semua Periode</option>
                        @foreach (($periodes ?? []) as $p)
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
                    <a href="{{ route('bidan.pemeriksaan-ibu.index') }}"
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
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Nama Ibu</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Usia Kehamilan</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">HPL</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Status Risiko</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($ibu as $item)
                        @php
                            $terakhir = $item->pemeriksaanIbu()
                                ->latest('tgl_pemeriksaan')
                                ->first();

                            $usia = $item->usia_kehamilan_sekarang;

                            $warna = match ($terakhir?->tingkat_risiko) {
                                'tinggi' => 'bg-red-100 text-red-700',
                                'sedang' => 'bg-amber-100 text-amber-700',
                                'rendah' => 'bg-green-100 text-green-700',
                                default  => 'bg-slate-100 text-slate-600',
                            };

                            $hpl = $item->hpl?->format('d/m/Y') ?? '-';
                        @endphp

                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 sm:px-4">{{ $nomorAwal + $loop->index }}</td>

                            <td class="px-3 py-3.5 sm:px-4">
                                <p class="break-words text-sm font-bold text-[#542D3D]">{{ $item->user?->name ?? '-' }}</p>
                                <p class="text-xs text-slate-700 md:hidden">HPL {{ $hpl }}</p>
                            </td>

                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-slate-800 sm:px-4">
                                {{ $usia !== null ? $usia . ' minggu' : '-' }}
                            </td>

                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 md:table-cell">{{ $hpl }}</td>

                            <td class="px-3 py-3.5 text-center sm:px-4">
                                @if ($terakhir)
                                    <span class="inline-flex min-w-[4.5rem] justify-center rounded-full px-3 py-1 text-xs font-bold {{ $warna }}">
                                        {{ ucfirst($terakhir->tingkat_risiko ?? '-') }}
                                    </span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-3 py-3.5 sm:px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('bidan.pemeriksaan-ibu.show', $item->id) }}"
                                       class="{{ $btnAksi }} border-2 border-[#E99AB7] bg-white text-[#C9517E] hover:bg-[#FCE7F0] focus:ring-[#E99AB7]/30">
                                        Detail
                                    </a>

                                    @if ($terakhir)
                                        <a href="{{ route('bidan.pemeriksaan-ibu.edit', [$item->id, $terakhir->id]) }}"
                                           class="{{ $btnAksi }} bg-[#E99AB7] text-[#542D3D] hover:bg-[#DD87A8] focus:ring-[#E99AB7]/40">
                                            Edit
                                        </a>
                                    @else
                                        <a href="{{ route('bidan.pemeriksaan-ibu.create', $item->id) }}"
                                           class="{{ $btnAksi }} bg-[#C9517E] text-white hover:bg-[#B7436D] focus:ring-[#E99AB7]/40">
                                            Input
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">
                                    {{ $sedangMencari ? 'Data tidak ditemukan.' : 'Belum ada data ibu hamil.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-[#F1D5E0] bg-white px-4 py-3">
            @php
                $pg     = $ibu;
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