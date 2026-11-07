@extends('layouts.app')

@section('title', 'Detail Ibu')

@section('content')
@php
    $riwayatPemeriksaanIbu = $riwayatPemeriksaanIbu ?? collect();
    $pemeriksaanIbuTerakhir = $pemeriksaanIbuTerakhir ?? null;

    $statusKehamilan = $ibu->status_kehamilan?->value
        ?? $ibu->status_kehamilan;

    $formatTanggal = fn ($tanggal) => $tanggal
        ? \Carbon\Carbon::parse($tanggal)->format('d/m/Y')
        : '-';

    $dataIdentitas = [
        'NIK' => $ibu->nik,
        'Tempat Lahir' => $ibu->tempat_lahir,
        'Tanggal Lahir' => $formatTanggal($ibu->tgl_lahir),
        'Wilayah' => $ibu->wilayahKerja?->nama_wilayah,
    ];

    $dataAkun = [
        'Nama' => $ibu->user?->name,
        'Email' => $ibu->user?->email,
        'Nomor HP' => $ibu->user?->phone,
    ];
@endphp

<div class="mx-auto w-full max-w-6xl space-y-5">

    {{-- Judul dan tombol --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
                Detail Ibu
            </h1>
            <p class="mt-1 text-sm font-medium text-slate-600">
                Data Master / Data Ibu / Detail
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route($base.'.data-ibu.index') }}"
               class="rounded-xl border border-[#E99AB7] bg-white px-5 py-2.5 text-sm font-semibold text-[#C9517E] hover:bg-[#FCE7F0]">
                Kembali
            </a>

            @if ($base === 'admin')
                @can('ibu.update')
                    <a href="{{ route('admin.data-ibu.edit', $ibu) }}"
                       class="rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#B7436D]">
                        Edit
                    </a>
                @endcan
            @endif
        </div>
    </div>

    {{-- Identitas dan status kehamilan --}}
    <div class="grid gap-5 lg:grid-cols-2">

        {{-- Identitas Ibu --}}
        <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-base font-bold text-[#542D3D]">
                Identitas Ibu
            </h2>

            <div class="divide-y divide-[#F7E6ED]">
                @foreach ($dataIdentitas as $label => $nilai)
                    <div class="flex justify-between gap-4 py-3 text-sm">
                        <span class="font-semibold text-slate-600">
                            {{ $label }}
                        </span>
                        <span class="text-right font-semibold text-[#542D3D]">
                            {{ $nilai ?: '-' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Status Kehamilan --}}
        <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-base font-bold text-[#542D3D]">
                Status Kehamilan
            </h2>

            <div class="mb-4">
                @include('data-ibu.partials.badge', [
                    'status' => $ibu->status_kehamilan
                ])
            </div>

            @if ($ibu->sedangHamil())
                <div class="divide-y divide-[#F7E6ED]">
                    <div class="flex justify-between gap-4 py-3 text-sm">
                        <span class="font-semibold text-slate-600">
                            Usia Kehamilan
                        </span>
                        <span class="text-right font-semibold text-[#542D3D]">
                            {{ $ibu->usia_kehamilan_sekarang !== null
                                ? $ibu->usia_kehamilan_sekarang.' Minggu'
                                : '0 Minggu' }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4 py-3 text-sm">
                        <span class="font-semibold text-slate-600">
                            HPL
                        </span>
                        <span class="text-right font-semibold text-[#542D3D]">
                            {{ $formatTanggal($ibu->hpl) }}
                        </span>
                    </div>
                </div>
            @else
                <div class="rounded-xl bg-[#FFF5F8] p-4 text-sm text-slate-600">
                    Ibu saat ini tidak dalam status kehamilan.
                </div>
            @endif
        </div>
    </div>

    {{-- Anak yang terhubung --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-4">
            <h2 class="text-base font-bold text-[#542D3D]">
                Anak yang Terhubung
            </h2>
            <p class="mt-1 text-sm text-slate-600">
                Daftar anak yang terdaftar pada ibu ini.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead class="bg-[#FFF5F8] text-xs font-bold uppercase text-[#542D3D]">
                    <tr>
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">NIK Anak</th>
                        <th class="px-5 py-3">Nama Anak</th>
                        <th class="px-5 py-3">Tanggal Lahir</th>
                        <th class="px-5 py-3">Jenis Kelamin</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($ibu->anak as $anak)
                        <tr class="hover:bg-[#FFF9FB]">
                            <td class="px-5 py-3">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-5 py-3">
                                {{ $anak->nik_anak ?: '-' }}
                            </td>
                            <td class="px-5 py-3 font-semibold text-[#542D3D]">
                                {{ $anak->nama_anak ?: '-' }}
                            </td>
                            <td class="px-5 py-3">
                                {{ $formatTanggal($anak->tgl_lahir) }}
                            </td>
                            <td class="px-5 py-3">
                                @if ($anak->jenis_kelamin === 'L')
                                    Laki-laki
                                @elseif ($anak->jenis_kelamin === 'P')
                                    Perempuan
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                                Belum ada anak yang terhubung.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Riwayat Pemeriksaan Ibu --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] px-5 py-4">
            <h2 class="text-base font-bold text-[#542D3D]">
                Riwayat Pemeriksaan Kehamilan
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Riwayat pemeriksaan ibu berdasarkan data pemeriksaan yang tersimpan.
            </p>
        </div>

        @if ($riwayatPemeriksaanIbu->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px] text-left text-sm">
                    <thead class="bg-[#FFF5F8] text-xs font-bold uppercase text-[#542D3D]">
                        <tr>
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">Tanggal Pemeriksaan</th>
                            <th class="px-5 py-3 text-center">Status Kesehatan</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#F7E6ED]">
                        @foreach ($riwayatPemeriksaanIbu as $pemeriksaan)
                            <tr class="hover:bg-[#FFF9FB]">
                                <td class="px-5 py-3">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-5 py-3">
                                    {{ $formatTanggal($pemeriksaan->tgl_pemeriksaan) }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if ($pemeriksaan->status_kesehatan === 'normal')
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                            Normal
                                        </span>
                                    @elseif ($pemeriksaan->status_kesehatan === 'perlu_perhatian')
                                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                            Perlu Perhatian
                                        </span>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-5 py-10 text-center">
                <p class="text-sm font-medium text-slate-600">
                    Belum ada riwayat pemeriksaan kehamilan.
                </p>
                <p class="mt-1 text-xs text-slate-400">
                    Data akan ditampilkan setelah pemeriksaan tersimpan.
                </p>
            </div>
        @endif
    </div>

</div>
@endsection