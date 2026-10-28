@extends('layouts.app')

@section('title', 'Data Anak')

@section('content')
@php
    $inputClass = 'rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 outline-none focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
@endphp
<div class="mx-auto w-full max-w-[96rem] space-y-5">

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Anak</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Anak</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:flex-row sm:items-center sm:justify-between sm:p-4">
            <form method="GET" action="{{ route($base.'.data-anak.index') }}" class="flex flex-1 gap-2">
                <input type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                       placeholder="Cari nama / NIK anak" class="{{ $inputClass }} w-full max-w-md">
                <button type="submit" class="rounded-xl bg-[#C9517E] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#B7436D]">Cari</button>
                @if (request()->filled('search'))
                    <a href="{{ route($base.'.data-anak.index') }}" class="px-3 py-2.5 text-sm text-slate-600 hover:underline">Reset</a>
                @endif
            </form>

            @if ($base === 'admin')
                @can('anak.create')
                    <a href="{{ route('admin.data-anak.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 hover:bg-[#B7436D]">
                        <span class="text-lg leading-none">+</span> Tambah Data Anak
                    </a>
                @endcan
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">NIK Anak</th>
                        <th class="px-4 py-3">Nama Anak</th>
                        <th class="px-4 py-3">Tempat, Tgl Lahir</th>
                        <th class="px-4 py-3">JK</th>
                        <th class="px-4 py-3">Nama Ayah</th>
                        <th class="px-4 py-3">Nama Ibu</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($anaks as $a)
                        <tr class="hover:bg-[#FCE7F0]/50">
                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">{{ $anaks->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $a->nik_anak }}</td>
                            <td class="px-4 py-3.5 text-sm font-bold text-[#542D3D]">{{ $a->nama_anak }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $a->tempat_lahir }}, {{ $a->tanggal_lahir?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $a->jenis_kelamin }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $a->nama_ayah }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $a->ibu?->user?->name ?? '(belum terhubung)' }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route($base.'.data-anak.show', $a) }}"
                                       class="inline-flex h-8 items-center rounded-lg border border-[#C9517E] px-3 text-xs font-bold text-[#C9517E] hover:bg-[#FCE7F0]">Detail</a>
                                    @if ($base === 'admin')
                                        @can('anak.update')
                                            <a href="{{ route('admin.data-anak.edit', $a) }}"
                                               class="inline-flex h-8 items-center rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white hover:bg-[#B7436D]">Edit</a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">Data tidak ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[#F1D5E0] bg-white px-4 py-3">
            <span class="text-xs font-semibold text-slate-700">
                Menampilkan {{ $anaks->firstItem() ?? 0 }}-{{ $anaks->lastItem() ?? 0 }} dari {{ $anaks->total() }} data
            </span>
            <div>{{ $anaks->links() }}</div>
        </div>
    </div>
</div>
@endsection