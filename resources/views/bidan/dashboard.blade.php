@extends('layouts.app')

@section('title', 'Dashboard Bidan')

@section('content')
@php
$cards = [
    [
        'label' => 'Jumlah Ibu Wilayah',
        'value' => $stats['ibu_melahirkan'] + $stats['ibu_hamil'],
        'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0',
    ],
    [
        'label' => 'Jumlah Anak Wilayah',
        'value' => $stats['anak'],
        'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0',
    ],
    [
        'label' => 'Jumlah Pemeriksaan Hari Ini',
        'value' => $stats['pemeriksaan_hari_ini'] ?? 0,
        'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6l4 4v12a2 2 0 0 1-2 2',
    ],
    [
        'label' => 'Jumlah Pasien Perlu Perhatian',
        'value' => $stats['perlu_perhatian'] ?? 0,
        'icon' => 'M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z',
    ],
    [
    'label' => 'Jumlah Kegiatan Posyandu',
    'value' => $stats['kegiatan_posyandu'],
    'icon' => 'M12 21s7-5.5 7-12a7 7 0 1 0-14 0c0 6.5 7 12 7 12Z',
    ],
        [
            'label' => 'Jumlah Konsultasi Menunggu',
            'value' => $stats['konsultasi_menunggu'] ?? 0,
            'icon' => 'M21 11.5a8.5 8.5 0 0 1-12.5 7.5L3 21l2-5.5A8.5 8.5 0 1 1 21 11.5Z',
        ],
    ];
@endphp

<div class="space-y-5">

    {{-- Judul Dashboard --}}
    <section>
        <h1 class="text-2xl font-bold text-[#542D3D]">
            Dashboard Bidan
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            Ringkasan kondisi ibu, anak, dan kegiatan kesehatan di wilayah tugas Anda.
        </p>
    </section>

    {{-- Statistik Dashboard --}}
    <section>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            @foreach ($cards as $card)
                <div class="flex min-h-[130px] items-center justify-between rounded-xl border border-[#E5D5DC] bg-white p-5 shadow-sm">

                    <div class="min-w-0">
                        <p class="text-sm font-medium leading-5 text-slate-600">
                            {{ $card['label'] }}
                        </p>

                        <p class="mt-3 text-3xl font-bold text-[#C9517E]">
                            {{ number_format($card['value']) }}
                        </p>
                    </div>

                    <div class="ml-3 flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
                        <svg
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>

                </div>
            @endforeach

        </div>
    </section>


    {{-- Jadwal dan Notifikasi --}}
    <section class="grid grid-cols-1 gap-5 lg:grid-cols-2">

        {{-- Jadwal Terdekat --}}
        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

            <div class="flex items-center justify-between border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-4">
                <div>
                    <h2 class="font-bold text-[#542D3D]">
                        Jadwal Terdekat
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Kegiatan Posyandu di wilayah tugas Anda.
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-[#C9517E]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/>
                    </svg>
                </div>
            </div>

            <div class="p-5">
                @forelse ($jadwalTerdekat as $jadwal)
                    <div class="border-b border-[#F7E6ED] py-3 last:border-0">

                        <p class="font-bold text-[#542D3D]">
                            {{ $jadwal->jenis_kegiatan ?? 'Kegiatan Posyandu' }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ $jadwal->tanggal->format('d F Y') }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Pukul {{ $jadwal->waktu ?? '-' }}
                        </p>

                        <span class="mt-2 inline-flex rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                            Akan datang
                        </span>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-9 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#FCE7F0] text-[#C9517E]">
                            <svg class="h-7 w-7" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/>
                            </svg>
                        </div>

                        <p class="mt-3 text-sm font-semibold text-[#542D3D]">
                            Belum ada jadwal Posyandu
                        </p>

                        <p class="mt-1 max-w-xs text-xs leading-5 text-slate-500">
                            Jadwal akan ditampilkan setelah data kegiatan Posyandu tersedia.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Notifikasi Terbaru --}}
        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

            <div class="flex items-center justify-between border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-4">

                <div>
                    <h2 class="font-bold text-[#542D3D]">
                        Notifikasi Terbaru
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Informasi pemeriksaan yang perlu diperhatikan.
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-[#C9517E]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 12h4"/>
                    </svg>
                </div>
            </div>

            <div class="p-5">

                @forelse ($notifikasiTerbaru as $notifikasi)
                    <div class="flex gap-3 border-b border-[#F7E6ED] py-3 first:pt-0 last:border-0">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                            <svg class="h-5 w-5" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v4m0 4h.01M10.3 3.9 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-[#542D3D]">
                                Pemeriksaan perlu perhatian
                            </p>

                            <p class="mt-1 text-xs text-slate-600">
                                {{ $notifikasi->ibu?->user?->name ?? 'Nama ibu tidak tersedia' }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Tanggal pemeriksaan:
                                {{ $notifikasi->tgl_pemeriksaan?->format('d-m-Y') ?? '-' }}
                            </p>
                        </div>

                        <span class="h-fit shrink-0 rounded-full bg-red-50 px-2 py-1 text-[10px] font-semibold text-red-600">
                            Perhatian
                        </span>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-9 text-center">

                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-50 text-green-600">
                            <svg class="h-7 w-7" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m5 12 4 4L19 6"/>
                            </svg>
                        </div>

                        <p class="mt-3 text-sm font-semibold text-[#542D3D]">
                            Tidak ada notifikasi terbaru
                        </p>

                        <p class="mt-1 max-w-xs text-xs leading-5 text-slate-500">
                            Belum ada pemeriksaan ibu dengan status perlu perhatian.
                        </p>
                    </div>
                @endforelse

            </div>
        </div>

    </section>



</div>
@endsection