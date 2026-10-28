@extends('layouts.app')

@section('title', 'Detail Kegiatan Posyandu')

@section('content')
@php
    $baris = fn ($label, $nilai) => '<div class="flex justify-between gap-4 border-b border-[#F7E6ED] py-2.5 text-sm"><span class="font-semibold text-slate-600">'.e($label).'</span><span class="text-right font-semibold text-[#542D3D]">'.e($nilai ?: '-').'</span></div>';
@endphp
<div class="mx-auto w-full max-w-5xl space-y-5">

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Detail Kegiatan Posyandu</h1>
            <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Posyandu / Detail</p>
        </div>
        <a href="{{ route('admin.data-posyandu.index') }}"
           class="rounded-xl border border-[#E99AB7] bg-white px-5 py-2.5 text-sm font-semibold text-[#C9517E] hover:bg-[#FCE7F0]">Kembali</a>
    </div>

    <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
        <h2 class="mb-2 text-sm font-bold text-[#542D3D]">Kegiatan</h2>
        {!! $baris('Posyandu', $kegiatan->posyandu?->nama_posyandu) !!}
        {!! $baris('Wilayah', $kegiatan->posyandu?->wilayahKerja?->nama_wilayah) !!}
        {!! $baris('Tanggal', $kegiatan->tanggal?->format('d/m/Y')) !!}
        {!! $baris('Waktu', $kegiatan->waktu ? substr($kegiatan->waktu, 0, 5).' WIB' : null) !!}
        {!! $baris('Lokasi', $kegiatan->lokasi ?: $kegiatan->posyandu?->alamat) !!}
        {!! $baris('Jumlah Anak Diperiksa', $tersedia ? (string) $pemeriksaan->count() : null) !!}
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <h2 class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3 text-sm font-bold text-[#542D3D]">Anak yang Diperiksa</h2>

        @if (! $tersedia)
            <p class="px-5 py-10 text-center text-sm text-slate-500">Data pemeriksaan belum tersedia.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-bold uppercase text-[#542D3D]">
                        <tr>
                            <th class="px-5 py-3">Nama Anak</th>
                            <th class="px-5 py-3">NIK</th>
                            <th class="px-5 py-3">Berat Badan</th>
                            <th class="px-5 py-3">Tinggi Badan</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F7E6ED]">
                        @forelse ($pemeriksaan as $p)
                            <tr>
                                <td class="px-5 py-3 font-semibold text-[#542D3D]">{{ $p->nama_anak }}</td>
                                <td class="px-5 py-3 text-slate-800">{{ $p->nik_anak }}</td>
                                <td class="px-5 py-3 text-slate-800">{{ $p->berat_badan ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-800">{{ $p->tinggi_badan ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-800">{{ $p->status_kesehatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Belum ada anak yang diperiksa pada kegiatan ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection