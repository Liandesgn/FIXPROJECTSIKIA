@extends('layouts.app')

@section('title', 'Detail Anak')

@section('content')
@php
    $baris = fn ($label, $nilai) => '<div class="flex justify-between gap-4 border-b border-[#F7E6ED] py-3 text-sm last:border-b-0"><span class="font-semibold text-slate-700">'.e($label).'</span><span class="text-right font-bold text-[#542D3D]">'.e($nilai ?: '-').'</span></div>';
    $ibu = $anak->ibu;
@endphp
<div class="mx-auto w-full max-w-3xl space-y-5">

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Anak</h1>
            <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Anak / Detail</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route($base.'.data-anak.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border-2 border-[#C9517E] bg-white px-4 py-2 text-sm font-semibold text-[#C9517E] shadow-sm transition hover:bg-[#C9517E] hover:text-white focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"/></svg>
                Kembali
            </a>
            @if ($base === 'admin')
                @can('anak.update')
                    <a href="{{ route('admin.data-anak.edit', $anak) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-[#C9517E] px-5 py-2 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">Edit</a>
                @endcan
            @endif
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <h2 class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-5 py-3 text-sm font-bold text-[#542D3D]">Data Anak</h2>
        <div class="px-5 py-2">
            {!! $baris('NIK', $anak->nik_anak) !!}
            {!! $baris('Nama', $anak->nama_anak) !!}
            {!! $baris('Tanggal Lahir', $anak->tanggal_lahir?->format('d/m/Y')) !!}
            {!! $baris('Tempat Lahir', $anak->tempat_lahir) !!}
            {!! $baris('Jenis Kelamin', $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan') !!}
            {!! $baris('Nama Ayah', $anak->nama_ayah) !!}
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <h2 class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-5 py-3 text-sm font-bold text-[#542D3D]">Data Ibu</h2>
        <div class="px-5 py-2">
            @if ($ibu)
                {!! $baris('NIK', $ibu->nik) !!}
                {!! $baris('Nama', $ibu->user?->name) !!}
                {!! $baris('Tanggal Lahir', $ibu->tanggal_lahir?->format('d/m/Y')) !!}
                {!! $baris('Nomor HP', $ibu->user?->phone) !!}
                {!! $baris('Wilayah', $ibu->wilayahKerja?->nama_wilayah) !!}
            @else
                <p class="py-5 text-sm font-medium text-slate-700">(belum terhubung)</p>
            @endif
        </div>
    </div>
</div>
@endsection