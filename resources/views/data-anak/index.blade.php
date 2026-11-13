@extends('layouts.app')

@section('title', 'Data Anak')

@section('content')
@php
    $pg        = $anaks instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $anaks : null;
    $nomorAwal = $pg ? ($pg->firstItem() ?? 1) : 1;
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul + tombol Tambah --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Anak</h1>
            @if ($base === 'admin')
                <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Anak</p>
            @endif
        </div>

        @if ($base === 'admin')
            @can('anak.create')
                <a href="{{ route('admin.data-anak.create') }}"
                   class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 sm:w-auto">
                    <span class="text-lg leading-none">+</span> Tambah Data Anak
                </a>
            @endcan
        @endif
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Pencarian --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <form method="GET" action="{{ route($base.'.data-anak.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <label for="searchInput" class="sr-only">Cari anak</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="searchInput" type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                           data-pencarian data-aktif="{{ request()->filled('search') ? '1' : '0' }}"
                           placeholder="Cari nama / NIK anak"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>

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
                        <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">NIK Anak</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Nama Anak</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 lg:table-cell">Tempat, Tgl Lahir</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 lg:table-cell">JK</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 2xl:table-cell">Nama Ayah</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 sm:table-cell">Nama Ibu</th>
                        {{-- Kader hanya melihat data, tanpa kolom Aksi --}}
                        @if ($base !== 'kader')
                            <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Aksi</th>
                        @endif
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($anaks as $a)
                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 sm:px-4">{{ $nomorAwal + $loop->index }}</td>
                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 md:table-cell">{{ $a->nik_anak }}</td>

                            <td class="px-3 py-3.5 sm:px-4">
                                <p class="break-words text-sm font-bold text-[#542D3D]">{{ $a->nama_anak }}</p>
                                <p class="text-xs text-slate-700 md:hidden">NIK {{ $a->nik_anak }}</p>
                                <p class="text-xs text-slate-700 sm:hidden">Ibu: {{ $a->ibu?->user?->name ?? '(belum terhubung)' }}</p>
                            </td>

                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 lg:table-cell">{{ $a->tempat_lahir }}, {{ $a->tgl_lahir?->format('d/m/Y') }}</td>
                            <td class="hidden px-4 py-3.5 text-sm text-slate-800 lg:table-cell">{{ $a->jenis_kelamin }}</td>
                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 2xl:table-cell">{{ $a->nama_ayah }}</td>
                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 sm:table-cell">{{ $a->ibu?->user?->name ?? '(belum terhubung)' }}</td>

                            @if ($base !== 'kader')
                                <td class="whitespace-nowrap px-3 py-3.5 sm:px-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route($base.'.data-anak.show', $a) }}"
                                           class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white shadow-sm transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">Detail</a>
                                        @if ($base === 'admin')
                                            @can('anak.update')
                                                <a href="{{ route('admin.data-anak.edit', $a) }}"
                                                   class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-[#E99AB7] px-3 text-xs font-bold text-[#542D3D] shadow-sm transition hover:bg-[#DD87A8] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">Edit</a>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $base === 'kader' ? 7 : 8 }}" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">Data tidak ditemukan.</p>
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
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Kalau kotak pencarian dikosongkan, otomatis kembali ke daftar semula
    (function () {
        const input = document.querySelector('[data-pencarian]');
        if (!input) return;

        const kembali = function () {
            if (input.value.trim() === '' && input.dataset.aktif === '1') {
                input.dataset.aktif = '0';
                input.form.submit();
            }
        };

        input.addEventListener('input', kembali);
        input.addEventListener('search', kembali);
    })();
</script>
@endpush