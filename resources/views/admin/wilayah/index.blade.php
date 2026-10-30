@extends('layouts.app')

@section('title', 'Kelola Wilayah Kerja')

@section('content')
<div class="mx-auto w-full max-w-[96rem] space-y-5">

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Wilayah Kerja</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Kelola Wilayah Kerja</p>
    </div>

    @include('admin.wilayah.partials.tabs', ['active' => 'wilayah'])

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Bar cari + tombol tambah --}}
        <div class="flex flex-col gap-3 border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:flex-row sm:items-center sm:justify-between sm:p-4">
            <form method="GET" action="{{ route('admin.wilayah-kerja.index') }}" class="flex flex-1 flex-col gap-3 sm:flex-row">
                <div class="relative sm:max-w-md sm:flex-1">
                    <label for="searchWilayah" class="sr-only">Cari wilayah</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="searchWilayah" type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                           placeholder="Cari wilayah, desa, atau kecamatan"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-semibold text-gray-900 outline-none transition placeholder:font-medium placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>
                <button type="submit"
                        class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                    Cari
                </button>
            </form>

            @can('wilayah.create')
                <a href="{{ route('admin.wilayah-kerja.create') }}"
                   class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                    <span class="text-lg leading-none">+</span> Tambah Wilayah
                </a>
            @endcan
        </div>

        {{-- Tabel --}}
        <div class="max-h-[28rem] overflow-x-auto overflow-y-auto">
            <table class="w-full text-left">
                <thead class="sticky top-0 z-10 text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">No</th>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">Nama Wilayah</th>
                        <th class="hidden whitespace-nowrap bg-[#FCE7F0] px-4 py-3 md:table-cell">Posyandu</th>
                        <th class="hidden whitespace-nowrap bg-[#FCE7F0] px-4 py-3 md:table-cell">Bidan</th>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 text-center lg:px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($wilayahs as $wilayah)
                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 lg:px-4">{{ $loop->iteration }}</td>
                            <td class="px-3 py-3.5 lg:px-4">
                                <p class="text-sm font-bold text-[#542D3D]">{{ $wilayah->nama_wilayah }}</p>
                                <p class="mt-0.5 text-xs font-medium text-slate-700">Desa {{ $wilayah->desa }}, Kec. {{ $wilayah->kecamatan }}</p>
                                <p class="mt-0.5 text-xs font-medium text-slate-700 md:hidden">{{ $wilayah->posyandu_count }} posyandu · Bidan: {{ $wilayah->bidan?->name ?? '-' }}</p>
                            </td>
                            <td class="hidden px-4 py-3.5 text-sm font-semibold text-slate-800 md:table-cell">{{ $wilayah->posyandu_count }}</td>
                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 md:table-cell">{{ $wilayah->bidan?->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-3 py-3.5 lg:px-4">
                                <div class="flex flex-nowrap items-center justify-center gap-2">
                                    @can('wilayah.update')
                                        {{-- EDIT: pink tua solid --}}
                                        <a href="{{ route('admin.wilayah-kerja.edit', $wilayah) }}"
                                           class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white shadow-sm transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 20h4L19 9l-4-4L4 16v4Zm9-13 4 4"/></svg>
                                            Edit
                                        </a>
                                    @endcan

                                    @can('wilayah.delete')
                                        {{-- HAPUS: merah solid. Konfirmasi memakai SweetAlert (diatur di layout) --}}
                                        <form method="POST" action="{{ route('admin.wilayah-kerja.destroy', $wilayah) }}" class="m-0"
                                              data-confirm-delete
                                              data-name="{{ $wilayah->nama_wilayah }}"
                                              data-title="Apakah akan menghapus wilayah {{ $wilayah->nama_wilayah }}?"
                                              data-text="Data wilayah akan dihapus secara permanen dan tidak dapat dipulihkan. Lanjutkan?"
                                              data-confirm-text="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-red-600 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200">
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
                            <td colspan="5" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">Data wilayah tidak ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-[#F1D5E0] bg-white px-4 py-3 text-xs font-semibold text-slate-700">
            Menampilkan {{ $wilayahs->count() }} data
        </div>
    </div>
</div>
@endsection