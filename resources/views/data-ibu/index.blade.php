@extends('layouts.app')

@section('title', 'Data Ibu')

@section('content')
@php
    $pg        = $ibus instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $ibus : null;
    $nomorAwal = $pg ? ($pg->firstItem() ?? 1) : 1;

    if ($pg) { $pg->appends(request()->query()); }

    // Filter Desa/Kelurahan hanya tampil bila controller mengirim $wilayahs
    $daftarWilayah = collect($wilayahs ?? []);
    $sedangMencari = request()->hasAny(['search', 'status_kehamilan', 'wilayah_kerja_id']);

    // Susun alamat: Kec. A, Desa B, RT 001/RW 01 (memakai kolom lama bila relasi belum ada)
    $alamatDari = function ($w) {
    if (! $w) return '-';

    $relasi = function (string $nama) use ($w) {
        if ($w->relationLoaded($nama)) return $w->getRelation($nama);
        return method_exists($w, $nama) ? $w->{$nama}()->first() : null;
    };

    $kec  = $relasi('kecamatan');
    $desa = $relasi('kelurahanDesa');
    $rw   = $relasi('rw');
    $rt   = $relasi('rt');

    $kolomKec  = $w->getAttribute('kecamatan');
    $kolomDesa = $w->getAttribute('kelurahan');
    $namaKec   = $kec?->nama_kecamatan ?? (is_string($kolomKec) && $kolomKec !== '' ? $kolomKec : null);

    $bagian = [];

    if ($namaKec) {
        $bagian[] = $namaKec;
    }

    if ($desa?->nama_kelurahan_desa) {
        $bagian[] = $desa->nama_kelurahan_desa;
    } elseif (is_string($kolomDesa) && $kolomDesa !== '') {
        $bagian[] = $kolomDesa;
    }

    $nomor = array_filter([
        $rt?->nomor_rt ? 'RT ' . $rt->nomor_rt : null,
        $rw?->nomor_rw ? 'RW ' . $rw->nomor_rw : null,
    ]);

    if ($nomor) {
        $bagian[] = implode('/', $nomor);
    }

    return $bagian ? implode(', ', $bagian) : ($w->getAttribute('nama_wilayah') ?: '-');
};

$inputClass = 'w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul + tombol Registrasi --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Ibu</h1>
            @if ($base === 'admin')
                <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Ibu</p>
            @endif
        </div>

        @if ($base === 'admin')
            @can('user.create')
                <a href="{{ route('admin.ibu.create') }}"
                   class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.98] sm:w-auto">
                    <span class="text-lg leading-none">+</span> Registrasi Ibu
                </a>
            @endcan
        @endif
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Pencarian + filter --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <form method="GET" action="{{ route($base.'.data-ibu.index') }}" class="flex flex-col gap-3 md:flex-row">
                <div class="relative flex-1">
                    <label for="searchInput" class="sr-only">Cari ibu</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="searchInput" type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                           placeholder="Cari nama / NIK" class="{{ $inputClass }} pl-11">
                </div>

                @if ($daftarWilayah->isNotEmpty())
                    <div class="md:w-64">
                        <label for="filterWilayah" class="sr-only">Filter desa / kelurahan</label>
                        <select id="filterWilayah" name="wilayah_kerja_id" onchange="this.form.submit()" class="{{ $inputClass }}">
                            <option value="">RW</option>
                            @foreach ($daftarWilayah as $w)
                                <option value="{{ $w->id }}" @selected((string) request('wilayah_kerja_id') === (string) $w->id)>{{ $alamatDari($w) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="md:w-60">
                    <label for="filterStatus" class="sr-only">Filter status kehamilan</label>
                    <select id="filterStatus" name="status_kehamilan" onchange="this.form.submit()" class="{{ $inputClass }}">
                        <option value="">Semua status kehamilan</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}" @selected(request('status_kehamilan') === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.98]">
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
                        <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">NIK</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Nama</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 lg:table-cell">Alamat</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Status Kehamilan</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 xl:table-cell">Usia Kehamilan</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 xl:table-cell">HPL</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($ibus as $ibu)
                        @php $alamat = $alamatDari($ibu->wilayahKerja); @endphp

                        <tr class="transition-colors hover:bg-[#FCE7F0]/50">
                            <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 sm:px-4">{{ $nomorAwal + $loop->index }}</td>
                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 md:table-cell">{{ $ibu->nik ?? '-' }}</td>

                            <td class="px-3 py-3.5 sm:px-4">
                                <p class="break-words text-sm font-bold text-[#542D3D]">{{ $ibu->user?->name ?? '-' }}</p>
                                <p class="text-xs text-slate-700 md:hidden">NIK {{ $ibu->nik ?? '-' }}</p>
                                <p class="text-xs text-slate-700 lg:hidden">{{ $alamat }}</p>
                            </td>

                            <td class="hidden px-4 py-3.5 text-sm text-slate-800 lg:table-cell">{{ $alamat }}</td>
                            <td class="px-3 py-3.5 sm:px-4">@include('data-ibu.partials.badge', ['status' => $ibu->status_kehamilan])</td>
                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 xl:table-cell">
                                {{ $ibu->usia_kehamilan_sekarang !== null ? $ibu->usia_kehamilan_sekarang.' minggu' : '-' }}
                            </td>
                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 xl:table-cell">{{ $ibu->hpl?->format('d/m/Y') ?? '-' }}</td>

                            {{-- Detail untuk Admin, Bidan, dan Kader. Edit hanya untuk Admin. --}}
                            <td class="whitespace-nowrap px-3 py-3.5 sm:px-4">
                                <div class="flex items-center justify-center gap-2">                       
                                <a href="{{ $base === 'bidan'
                                        ? route('bidan.pemeriksaan-ibu.show', $ibu->id)
                                        : route($base.'.data-ibu.show', $ibu->id) }}"
                                class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white shadow-sm transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.97] sm:h-8">
                                    Detail
                                </a>


                                    @if ($base === 'admin')
                                        @can('ibu.update')
                                            <a href="{{ route('admin.data-ibu.edit', $ibu) }}"
                                               class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-lg bg-[#E99AB7] px-3 text-xs font-bold text-[#542D3D] shadow-sm transition hover:bg-[#DD87A8] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.97] sm:h-8">Edit</a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">{{ $sedangMencari ? 'Data tidak ditemukan.' : 'Belum ada data ibu.' }}</p>
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