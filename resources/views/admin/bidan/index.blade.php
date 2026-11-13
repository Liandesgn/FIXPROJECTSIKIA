@extends('layouts.app')

@section('title', 'Kelola Bidan')

@section('content')
@php
    $pg        = $bidans instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $bidans : null;
    $total     = $pg ? $pg->total() : $bidans->count();
    $nomorAwal = $pg ? ($pg->firstItem() ?? 1) : 1;
    $readOnly  = $readOnly ?? false;
    $listWilayah = collect($wilayahs ?? []);
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Bidan</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Manajemen User / Kelola Bidan</p>
    </div>

    {{-- Ringkasan + tombol Tambah --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
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

        @if (!$readOnly)
            @can('user.create')
                <a href="{{ route('admin.bidan.create') }}"
                   class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 sm:w-auto">
                    <span class="text-lg leading-none">+</span> Tambah Bidan
                </a>
            @endcan
        @endif
    </div>

    {{-- Daftar --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Pencarian (+ filter wilayah khusus Super Admin) --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <label for="searchInput" class="sr-only">Cari bidan</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="searchInput" type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                           placeholder="Cari nama / email"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>

                @if ($readOnly)
                    <div class="sm:w-64">
                        <label for="wilayahFilter" class="sr-only">Filter wilayah kerja</label>
                        <select id="wilayahFilter" name="wilayah_kerja_id" onchange="this.form.submit()"
                                class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                            <option value="">Semua wilayah kerja</option>
                            @foreach ($listWilayah->groupBy('kecamatan') as $kecamatan => $desas)
                                <optgroup label="Kec. {{ $kecamatan ?: '-' }}">
                                    @foreach ($desas as $wilayah)
                                        <option value="{{ $wilayah->id }}" @selected((string) request('wilayah_kerja_id') === (string) $wilayah->id)>
                                            Desa {{ $wilayah->kelurahan }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                @endif

                <button type="submit"
                        class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                    Cari
                </button>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">No</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Nama</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">Email</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 lg:table-cell">No. Telepon</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Wilayah Kerja</th>
                        @if (!$readOnly)
                            <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Aksi</th>
                        @endif
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($bidans as $bidan)
                        @php $wilayah = optional($bidan->bidan?->wilayahKerja)->kelurahan ?? '-'; @endphp
                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 sm:px-4">{{ $nomorAwal + $loop->index }}</td>

                            <td class="px-3 py-3.5 sm:px-4">
                                <p class="break-words text-sm font-bold text-[#542D3D]">{{ $bidan->name ?? '-' }}</p>
                                <p class="break-all text-xs text-slate-700 md:hidden">{{ $bidan->email ?? '-' }}</p>
                                <p class="text-xs text-slate-700 lg:hidden">{{ $bidan->phone ?? '-' }}</p>
                            </td>

                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 md:table-cell">{{ $bidan->email ?? '-' }}</td>
                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 lg:table-cell">{{ $bidan->phone ?? '-' }}</td>
                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-slate-800 sm:px-4">{{ $wilayah }}</td>

                            @if (!$readOnly)
                                <td class="whitespace-nowrap px-3 py-3.5 sm:px-4">
                                    <div class="flex items-center justify-center gap-2">
                                        @can('user.update')
                                            <a href="{{ route('admin.bidan.edit', $bidan->id) }}"
                                               class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-[#E99AB7] px-3 text-xs font-bold text-[#542D3D] shadow-sm transition hover:bg-[#DD87A8] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                                                Edit
                                            </a>
                                        @endcan

                                        @can('user.delete')
                                            <form action="{{ route('admin.bidan.destroy', $bidan->id) }}" method="POST" class="m-0"
                                                  data-confirm-delete data-name="{{ $bidan->name }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-red-600 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $readOnly ? 5 : 6 }}" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">{{ request('search') ? 'Data tidak ditemukan.' : 'Belum ada data bidan.' }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-[#F1D5E0] bg-white px-4 py-3">
            @if ($pg)
                @php
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
            @else
                <p class="text-xs font-medium text-slate-600">Total {{ $total }} data</p>
            @endif
        </div>
    </div>
</div>
@endsection