@extends('layouts.app')

@section('title', 'Detail Anak')

@section('content')
@php
    $baris = fn ($label, $nilai) => '<div class="flex justify-between gap-4 border-b border-[#F7E6ED] py-2.5 text-sm"><span class="font-semibold text-slate-600">'.e($label).'</span><span class="text-right font-semibold text-[#542D3D]">'.e($nilai ?: '-').'</span></div>';
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
               class="rounded-xl border border-[#E99AB7] bg-white px-5 py-2.5 text-sm font-semibold text-[#C9517E] hover:bg-[#FCE7F0]">Kembali</a>
            @if ($base === 'admin')
                @can('anak.update')
                    <a href="{{ route('admin.data-anak.edit', $anak) }}"
                       class="rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#B7436D]">Edit</a>
                @endcan
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
        <h2 class="mb-2 text-sm font-bold text-[#542D3D]">Data Anak</h2>
        {!! $baris('NIK', $anak->nik_anak) !!}
        {!! $baris('Nama', $anak->nama_anak) !!}
        {!! $baris('Tanggal Lahir', $anak->tanggal_lahir?->format('d/m/Y')) !!}
        {!! $baris('Tempat Lahir', $anak->tempat_lahir) !!}
        {!! $baris('Jenis Kelamin', $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan') !!}
        {!! $baris('Nama Ayah', $anak->nama_ayah) !!}
    </div>

    <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
        <h2 class="mb-2 text-sm font-bold text-[#542D3D]">Data Ibu</h2>
        @if ($ibu)
            {!! $baris('NIK', $ibu->nik) !!}
            {!! $baris('Nama', $ibu->user?->name) !!}
            {!! $baris('Tanggal Lahir', $ibu->tanggal_lahir?->format('d/m/Y')) !!}
            {!! $baris('Nomor HP', $ibu->user?->phone) !!}
            {!! $baris('Wilayah', $ibu->wilayahKerja?->nama_wilayah) !!}
        @else
            <p class="py-4 text-sm text-slate-500">(belum terhubung)</p>
        @endif
    </div>
</div>
@endsection