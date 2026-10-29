@extends('layouts.app')

@section('title', 'Detail Kegiatan Posyandu')

@section('content')
@php
    $baris = fn ($label, $nilai) => '<div class="flex justify-between gap-4 border-b border-[#F7E6ED] py-3 text-sm last:border-b-0"><span class="font-semibold text-slate-700">'.e($label).'</span><span class="text-right font-bold text-[#542D3D]">'.e($nilai ?: '-').'</span></div>';
@endphp
<div class="mx-auto w-full max-w-5xl space-y-5">

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Detail Kegiatan Posyandu</h1>
            <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Posyandu / Detail</p>
        </div>
        <a href="{{ route('admin.data-posyandu.index') }}"
           class="inline-flex items-center gap-2 rounded-xl border-2 border-[#C9517E] bg-white px-4 py-2 text-sm font-semibold text-[#C9517E] shadow-sm transition hover:bg-[#C9517E] hover:text-white focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"/></svg>
            Kembali
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <h2 class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-5 py-3 text-sm font-bold text-[#542D3D]">Kegiatan</h2>
        <div class="px-5 py-2">
            {!! $baris('Posyandu', $kegiatan->posyandu?->nama_posyandu) !!}
            {!! $baris('Wilayah', $kegiatan->posyandu?->wilayahKerja?->nama_wilayah) !!}
            {!! $baris('Tanggal', $kegiatan->tanggal?->format('d/m/Y')) !!}
            {!! $baris('Waktu', $kegiatan->waktu ? substr($kegiatan->waktu, 0, 5).' WIB' : null) !!}
            {!! $baris('Lokasi', $kegiatan->lokasi ?: $kegiatan->posyandu?->alamat) !!}
            {!! $baris('Jumlah Anak Diperiksa', $tersedia ? (string) $pemeriksaan->count() : null) !!}
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <h2 class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-5 py-3 text-sm font-bold text-[#542D3D]">Anak yang Diperiksa</h2>

        @if (! $tersedia)
            <p class="px-5 py-10 text-center text-sm font-medium text-slate-700">Data pemeriksaan belum tersedia.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                        <tr class="border-b border-[#F1D5E0]">
                            <th class="px-5 py-3">Nama Anak</th>
                            <th class="px-5 py-3">NIK</th>
                            <th class="px-5 py-3">Berat Badan</th>
                            <th class="px-5 py-3">Tinggi Badan</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F7E6ED]">
                        @forelse ($pemeriksaan as $p)
                            <tr class="hover:bg-[#FCE7F0]/40">
                                <td class="px-5 py-3 font-bold text-[#542D3D]">{{ $p->nama_anak }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-slate-800">{{ $p->nik_anak }}</td>
                                <td class="px-5 py-3 text-slate-800">{{ $p->berat_badan ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-800">{{ $p->tinggi_badan ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-800">{{ $p->status_kesehatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center font-medium text-slate-700">Belum ada anak yang diperiksa pada kegiatan ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection