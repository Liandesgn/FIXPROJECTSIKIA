@extends('layouts.app')

@section('title', 'Dashboard Bidan')

@section('content')
@php
    $user  = $user ?? auth()->user();
    $stats = $stats ?? [];

    $wk = $user->wilayahKerja ?? null;
    $wilayahLabel = $wk
        ? trim(($wk->desa ? 'Desa ' . $wk->desa : '') . ($wk->kecamatan ? ', Kec. ' . $wk->kecamatan : ''), ', ')
        : null;

    $cards = [
        ['label' => 'Ibu Terdaftar',         'value' => $stats['orang_tua'] ?? $stats['ibu'] ?? 0, 'icon' => 'M12 7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm-3 3h6l2 5-3 2v5m-4-8-2 3 3 2v3'],
        ['label' => 'Pemeriksaan Hari Ini',  'value' => $stats['pemeriksaan'] ?? 0,                'icon' => 'M8 3h8l4 4v14H4V3h4Zm0 9h8m-8 4h8'],
        ['label' => 'Konsultasi Aktif',      'value' => $stats['konsultasi'] ?? 0,                 'icon' => 'M21 11.5a8.5 8.5 0 0 1-12.8 7.3L3 20l1.2-4.2A8.5 8.5 0 1 1 21 11.5Z'],
        ['label' => 'Jadwal Minggu Ini',     'value' => $stats['jadwal'] ?? 0,                     'icon' => 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z'],
    ];

    $alur = [
        ['Pemeriksaan ibu hamil', 'Input hasil pemeriksaan ibu hamil di wilayah tugas Anda.'],
        ['Masuk riwayat & monitoring', 'Data pemeriksaan tersimpan sebagai riwayat dan bahan pemantauan.'],
        ['Ibu melahirkan', 'Data anak didaftarkan dan status ibu berubah menjadi melahirkan.'],
        ['Pantau tumbuh kembang anak', 'Pemantauan beralih ke perkembangan anak saat Posyandu.'],
    ];
@endphp

<div class="space-y-6">

    {{-- Sambutan --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#C9517E] to-[#E99AB7] p-5 text-white shadow-md shadow-[#C9517E]/20 sm:p-6">
        <div class="pointer-events-none absolute -right-10 -top-14 h-40 w-40 rounded-full border border-white/25"></div>
        <div class="pointer-events-none absolute -bottom-16 right-24 h-32 w-32 rounded-full bg-white/10"></div>
        <div class="relative">
            <p class="text-xs font-semibold uppercase tracking-wider text-white/90">Dashboard Bidan</p>
            <h1 class="mt-1 text-xl font-bold sm:text-2xl">Selamat bertugas, {{ $user->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-white">
                Pantau kesehatan ibu hamil dan tumbuh kembang anak di wilayah tugas Anda.
            </p>
            <div class="mt-3 inline-flex items-center gap-2 rounded-lg bg-white/20 px-3 py-1.5 text-xs font-semibold sm:text-sm">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.5 7-12a7 7 0 1 0-14 0c0 6.5 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                {{ $wilayahLabel ?: 'Wilayah belum ditetapkan' }}
            </div>
        </div>
    </section>

    {{-- Kartu statistik --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($cards as $card)
            <div class="flex items-center gap-3 rounded-xl border border-[#F1D5E0] bg-[#FCE7F0] p-3.5 sm:p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E] shadow-sm">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold leading-tight text-[#542D3D]">{{ $card['label'] }}</p>
                    <p class="mt-0.5 text-2xl font-bold leading-none text-[#C9517E]">{{ $card['value'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Alur pemantauan --}}
        <section class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm sm:p-6 lg:col-span-2">
            <h2 class="text-base font-bold text-[#542D3D]">Alur Pemantauan</h2>
            <p class="mt-1 text-sm text-slate-600">Dari kehamilan hingga tumbuh kembang anak.</p>

            <ol class="mt-5 space-y-4">
                @foreach ($alur as $i => [$judul, $desk])
                    <li class="flex gap-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#C9517E] text-sm font-bold text-white">{{ $i + 1 }}</span>
                        <div class="min-w-0 pt-0.5">
                            <p class="text-sm font-bold text-[#542D3D]">{{ $judul }}</p>
                            <p class="mt-0.5 text-sm leading-6 text-slate-600">{{ $desk }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Wilayah tugas --}}
        <section class="rounded-2xl border border-[#F1D5E0] bg-[#FCE7F0] p-5 sm:p-6">
            <h2 class="text-base font-bold text-[#542D3D]">Wilayah Tugas</h2>
            <div class="mt-4 space-y-3">
                <div class="rounded-xl bg-white p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#C9517E]">Desa</p>
                    <p class="mt-1 text-sm font-semibold text-[#542D3D]">{{ $wk->desa ?? 'Belum ditetapkan' }}</p>
                </div>
                <div class="rounded-xl bg-white p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#C9517E]">Kecamatan</p>
                    <p class="mt-1 text-sm font-semibold text-[#542D3D]">{{ $wk->kecamatan ?? '-' }}</p>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection