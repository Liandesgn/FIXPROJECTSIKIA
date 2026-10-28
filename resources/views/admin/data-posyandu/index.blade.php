@extends('layouts.app')

@section('title', 'Data Posyandu')

@section('content')
@php
    $inputClass = 'rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 outline-none focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
@endphp
<div class="mx-auto w-full max-w-[96rem] space-y-5">

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Posyandu</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Posyandu</p>
    </div>

    @include('admin.data-posyandu.partials.tabs', ['active' => 'kegiatan'])

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <form method="GET" action="{{ route('admin.data-posyandu.index') }}" class="flex flex-wrap gap-2">
                <input type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                       placeholder="Cari Posyandu" class="{{ $inputClass }} w-full max-w-xs">

                <select name="periode" onchange="this.form.submit()" class="{{ $inputClass }}">
                    <option value="">Semua Periode</option>
                    @foreach ($periodes as $p)
                        <option value="{{ $p }}" @selected(request('periode') === $p)>
                            {{ \Illuminate\Support\Carbon::createFromFormat('!Y-m', $p)->translatedFormat('F Y') }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-xl bg-[#C9517E] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#B7436D]">Cari</button>
                @if (request()->hasAny(['search', 'periode']))
                    <a href="{{ route('admin.data-posyandu.index') }}" class="px-3 py-2.5 text-sm text-slate-600 hover:underline">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Posyandu</th>
                        <th class="px-4 py-3">Lokasi</th>
                        <th class="px-4 py-3">Anak Diperiksa</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($kegiatans as $k)
                        <tr class="hover:bg-[#FCE7F0]/50">
                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">{{ $kegiatans->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $k->tanggal?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3.5 text-sm font-bold text-[#542D3D]">{{ $k->posyandu?->nama_posyandu ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $k->lokasi ?: ($k->posyandu?->alamat ?? '-') }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ ($jumlahAnak[$k->id] ?? 0) ?: '-' }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <a href="{{ route('admin.data-posyandu.show', $k) }}"
                                   class="inline-flex h-8 items-center rounded-lg border border-[#C9517E] px-3 text-xs font-bold text-[#C9517E] hover:bg-[#FCE7F0]">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">Belum ada data kegiatan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[#F1D5E0] bg-white px-4 py-3">
            <span class="text-xs font-semibold text-slate-700">
                Menampilkan {{ $kegiatans->firstItem() ?? 0 }}-{{ $kegiatans->lastItem() ?? 0 }} dari {{ $kegiatans->total() }} data
            </span>
            <div>{{ $kegiatans->links() }}</div>
        </div>
    </div>
</div>
@endsection