@extends('layouts.app')

@section('title', 'Detail Ibu')

@section('content')
@php
    $baris = fn ($label, $nilai) => '<div class="flex justify-between gap-4 border-b border-[#F7E6ED] py-3 text-sm last:border-b-0"><span class="font-semibold text-slate-700">'.e($label).'</span><span class="text-right font-bold text-[#542D3D]">'.e($nilai ?: '-').'</span></div>';
@endphp
<div class="mx-auto w-full max-w-5xl space-y-5">

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Ibu</h1>
            <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Ibu / Detail</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route($base.'.data-ibu.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border-2 border-[#C9517E] bg-white px-4 py-2 text-sm font-semibold text-[#C9517E] shadow-sm transition hover:bg-[#C9517E] hover:text-white focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"/></svg>
                Kembali
            </a>
            @if ($base === 'admin')
                @can('ibu.update')
                    <a href="{{ route('admin.data-ibu.edit', $ibu) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-[#C9517E] px-5 py-2 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">Edit</a>
                @endcan
            @endif
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm md:col-span-2">
            <h2 class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-5 py-3 text-sm font-bold text-[#542D3D]">Identitas Ibu</h2>
            <div class="px-5 py-2">
                {!! $baris('NIK', $ibu->nik) !!}
                {!! $baris('Nama', $ibu->user?->name) !!}
                {!! $baris('Tempat, Tanggal Lahir', $ibu->ttl()) !!}
                {!! $baris('Nomor HP', $ibu->user?->phone) !!}
                {!! $baris('Wilayah', $ibu->wilayahKerja?->nama_wilayah) !!}
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <h2 class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-5 py-3 text-sm font-bold text-[#542D3D]">Status Kehamilan</h2>
            <div class="px-5 py-3">
                <div class="mb-1">@include('data-ibu.partials.badge', ['status' => $ibu->status_kehamilan])</div>
                @if ($ibu->sedangHamil())
                    {!! $baris('Usia Kehamilan', $ibu->usia_kehamilan ? $ibu->usia_kehamilan.' Minggu' : null) !!}
                    {!! $baris('HPL', $ibu->hpl?->format('d/m/Y')) !!}
                @endif
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <h2 class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-5 py-3 text-sm font-bold text-[#542D3D]">Anak yang Terhubung</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr class="border-b border-[#F1D5E0]">
                        <th class="px-5 py-3">NIK</th>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Tanggal Lahir</th>
                        <th class="px-5 py-3">JK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($ibu->anak as $a)
                        <tr class="hover:bg-[#FCE7F0]/40">
                            <td class="whitespace-nowrap px-5 py-3 text-slate-800">{{ $a->nik_anak }}</td>
                            <td class="px-5 py-3 font-bold text-[#542D3D]">{{ $a->nama_anak }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-slate-800">{{ $a->tanggal_lahir?->format('d/m/Y') }}</td>
                            <td class="px-5 py-3 text-slate-800">{{ $a->jenis_kelamin }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center font-medium text-slate-700">Belum ada anak yang terhubung.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection