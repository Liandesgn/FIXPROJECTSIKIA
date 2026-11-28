@extends('layouts.app')

@section('title', 'Data Anak')

@section('content')
@php
    $pg        = $anaks instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $anaks : null;
    $daftarWilayah = collect($wilayahs ?? []);
    $sedangMencari = request()->hasAny(['search', 'wilayah_kerja_id']);
    $nomorAwal = $pg ? ($pg->firstItem() ?? 1) : 1;

    if ($pg) { $pg->appends(request()->query()); }
 $daftarWilayah = collect($wilayahs ?? []);

    $sedangMencari = request()->hasAny([
        'search',
        'wilayah_kerja_id',
        'rw_id',
    ]);

$alamatDari = function ($w) {
    if (! $w) {
        return '-';
    }

    $relasi = function (string $nama) use ($w) {
        if ($w->relationLoaded($nama)) {
            return $w->getRelation($nama);
        }

        return method_exists($w, $nama)
            ? $w->{$nama}()->first()
            : null;
    };

    $kec = $relasi('masterKecamatan') ?? $relasi('kecamatan');
    $desa = $relasi('kelurahanDesa');
    $rw = $relasi('rw');
    $rt = $relasi('rt');

    $bagian = [];

    if ($kec?->nama_kecamatan) {
        $bagian[] = $kec->nama_kecamatan;
    }

    if ($desa?->nama_kelurahan_desa) {
        $bagian[] = 'Desa ' . $desa->nama_kelurahan_desa;
    }

    if ($rw?->nomor_rw) {
        $bagian[] = 'RW ' . $rw->nomor_rw;
    }

    if ($rt?->nomor_rt) {
        $bagian[] = 'RT ' . $rt->nomor_rt;
    }

    return $bagian
        ? implode(', ', $bagian)
        : ($w->nama_wilayah ?: '-');
};
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
                   class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.98] sm:w-auto">
                    <span class="text-lg leading-none">+</span> Tambah Data Anak
                </a>
            @endcan
        @endif
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

{{-- Pencarian dan Filter --}}

{{-- Pencarian + Filter RW --}}
<div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
    <form method="GET"
          action="{{ route($base.'.data-anak.index') }}"
          class="flex flex-col gap-3 md:flex-row">

        {{-- Cari nama / NIK anak --}}
        <div class="relative flex-1">
            <label for="searchInput" class="sr-only">Cari anak</label>

            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2"
                 aria-hidden="true">
                <circle cx="11" cy="11" r="7"/>
                <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
            </svg>

            <input
                id="searchInput"
                type="search"
                name="search"
                value="{{ request('search') }}"
                autocomplete="off"
                placeholder="Cari nama / NIK anak"
                class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3.5 py-2.5 pl-11 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
        </div>

        {{-- Filter RW sesuai wilayah pengguna --}}
        @if ($daftarWilayah->isNotEmpty())
            <div class="md:w-64">
                <label for="filterWilayah" class="sr-only">Filter RW</label>

                <select
                    id="filterWilayah"
                    name="wilayah_kerja_id"
                    onchange="this.form.submit()"
                    class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">

                    <option value="">RW</option>

                    @foreach ($daftarWilayah as $w)
                        <option
                            value="{{ $w->id }}"
                            @selected((string) request('wilayah_kerja_id') === (string) $w->id)>
                            {{ $alamatDari($w) }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <button
            type="submit"
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
                        <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">NIK Anak</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Nama Anak</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 lg:table-cell">Tempat, Tgl Lahir</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 lg:table-cell">JK</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 2xl:table-cell">Nama Ayah</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 sm:table-cell">Nama Ibu</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($anaks as $a)
                        <tr class="transition-colors hover:bg-[#FCE7F0]/50">
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

                            {{-- Detail untuk Admin, Bidan, dan Kader. Edit hanya untuk Admin. --}}
                            <td class="whitespace-nowrap px-3 py-3.5 sm:px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route($base.'.data-anak.show', $a) }}"
                                       class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white shadow-sm transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.97] sm:h-8">Detail</a>

                                    @if ($base === 'admin')
                                        @can('anak.update')
                                            <a href="{{ route('admin.data-anak.edit', $a) }}"
                                               class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-lg bg-[#E99AB7] px-3 text-xs font-bold text-[#542D3D] shadow-sm transition hover:bg-[#DD87A8] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.97] sm:h-8">Edit</a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">{{ $sedangMencari ? 'Data tidak ditemukan.' : 'Belum ada data anak.' }}</p>
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