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

        <div class="flex flex-col gap-3 border-b border-[#F1D5E0] bg-[#FCE7F0] p-3 sm:flex-row sm:items-center sm:justify-between sm:p-4">
            <form method="GET" action="{{ route('admin.wilayah-kerja.index') }}" class="flex flex-1 gap-2">
                <input type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                       placeholder="Cari wilayah, desa, atau kecamatan"
                       class="w-full max-w-md rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                <button type="submit"
                        class="rounded-xl bg-[#C9517E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#B7436D]">Cari</button>
                @if (request()->filled('search'))
                    <a href="{{ route('admin.wilayah-kerja.index') }}"
                       class="px-3 py-2.5 text-sm text-slate-600 hover:underline">Reset</a>
                @endif
            </form>

            @can('wilayah.create')
                <a href="{{ route('admin.wilayah-kerja.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D]">
                    <span class="text-lg leading-none">+</span> Tambah Wilayah
                </a>
            @endcan
        </div>

        <div class="max-h-[28rem] overflow-x-auto overflow-y-auto">
            <table class="w-full text-left">
                <thead class="sticky top-0 z-10 bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-4 py-3">No</th>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-4 py-3">Nama Wilayah</th>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-4 py-3">Kelurahan/Desa</th>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-4 py-3">Kecamatan</th>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-4 py-3">Posyandu</th>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-4 py-3">Bidan</th>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($wilayahs as $wilayah)
                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3.5 text-sm font-bold text-[#542D3D]">{{ $wilayah->nama_wilayah }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $wilayah->desa }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $wilayah->kecamatan }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $wilayah->posyandu_count }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $wilayah->bidan?->name ?? '-' }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-center gap-2">
                                    @can('wilayah.update')
                                        <a href="{{ route('admin.wilayah-kerja.edit', $wilayah) }}"
                                           class="inline-flex h-8 items-center rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white transition hover:bg-[#B7436D]">Edit</a>
                                    @endcan

                                    @can('wilayah.delete')
                                        <form method="POST" action="{{ route('admin.wilayah-kerja.destroy', $wilayah) }}" class="m-0"
                                              data-confirm-delete
                                              data-name="{{ $wilayah->nama_wilayah }}"
                                              data-title="Apakah akan menghapus wilayah {{ $wilayah->nama_wilayah }}?"
                                              data-text="Data wilayah akan dihapus secara permanen dan tidak dapat dipulihkan. Lanjutkan?"
                                              data-confirm-text="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex h-8 items-center rounded-lg bg-red-600 px-3 text-xs font-bold text-white transition hover:bg-red-700">Hapus</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
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