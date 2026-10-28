@extends('layouts.app')

@section('title', 'Detail Ibu')

@section('content')
@php
    $baris = fn ($label, $nilai) => '<div class="flex justify-between gap-4 border-b border-[#F7E6ED] py-2.5 text-sm"><span class="font-semibold text-slate-600">'.e($label).'</span><span class="text-right font-semibold text-[#542D3D]">'.e($nilai ?: '-').'</span></div>';
@endphp
<div class="mx-auto w-full max-w-5xl space-y-5">

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Ibu</h1>
            <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Ibu / Detail</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route($base.'.data-ibu.index') }}"
               class="rounded-xl border border-[#E99AB7] bg-white px-5 py-2.5 text-sm font-semibold text-[#C9517E] hover:bg-[#FCE7F0]">Kembali</a>
            @if ($base === 'admin')
                @can('ibu.update')
                    <a href="{{ route('admin.data-ibu.edit', $ibu) }}"
                       class="rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#B7436D]">Edit</a>
                @endcan
            @endif
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm md:col-span-2">
            <h2 class="mb-2 text-sm font-bold text-[#542D3D]">Identitas Ibu</h2>
            {!! $baris('NIK', $ibu->nik) !!}
            {!! $baris('Nama', $ibu->user?->name) !!}
            {!! $baris('Tempat, Tanggal Lahir', $ibu->ttl()) !!}
            {!! $baris('Nomor HP', $ibu->user?->phone) !!}
            {!! $baris('Wilayah', $ibu->wilayahKerja?->nama_wilayah) !!}
        </div>

        <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
            <h2 class="mb-2 text-sm font-bold text-[#542D3D]">Status Kehamilan</h2>
            <div class="mb-2">@include('data-ibu.partials.badge', ['status' => $ibu->status_kehamilan])</div>
            @if ($ibu->sedangHamil())
                {!! $baris('Usia Kehamilan', $ibu->usia_kehamilan ? $ibu->usia_kehamilan.' Minggu' : null) !!}
                {!! $baris('HPL', $ibu->hpl?->format('d/m/Y')) !!}
            @endif
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <h2 class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3 text-sm font-bold text-[#542D3D]">Anak yang Terhubung</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs font-bold uppercase text-[#542D3D]">
                    <tr>
                        <th class="px-5 py-3">NIK</th>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Tanggal Lahir</th>
                        <th class="px-5 py-3">JK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($ibu->anak as $a)
                        <tr>
                            <td class="px-5 py-3 text-slate-800">{{ $a->nik_anak }}</td>
                            <td class="px-5 py-3 font-semibold text-[#542D3D]">{{ $a->nama_anak }}</td>
                            <td class="px-5 py-3 text-slate-800">{{ $a->tanggal_lahir?->format('d/m/Y') }}</td>
                            <td class="px-5 py-3 text-slate-800">{{ $a->jenis_kelamin }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada anak yang terhubung.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection