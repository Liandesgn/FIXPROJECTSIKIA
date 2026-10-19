@extends('layouts.app')

@section('title', 'Dashboard Kader')

@section('content')
@php
    $user  = $user ?? auth()->user();
    $stats = $stats ?? [];

    $wk = $user->wilayahKerja ?? null;
    $wilayahLabel = $wk
        ? trim(($wk->desa ? 'Desa ' . $wk->desa : '') . ($wk->kecamatan ? ', Kec. ' . $wk->kecamatan : ''), ', ')
        : null;

    $cards = [
        ['label' => 'Jadwal Posyandu',       'value' => $stats['posyandu'] ?? $stats['jadwal'] ?? 0, 'icon' => 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z'],
        ['label' => 'Pengukuran Bulan Ini',  'value' => $stats['pengukuran'] ?? 0,                   'icon' => 'M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6'],
        ['label' => 'Notifikasi',            'value' => $stats['notifikasi'] ?? 0,                   'icon' => 'M6 9a6 6 0 1 1 12 0c0 5 2 6.5 2 6.5H4S6 14 6 9Zm4 9.5a2 2 0 0 0 4 0'],
    ];

    $tugas = [
        ['Cari data ibu', 'Peserta Posyandu dicari lewat data ibu, lalu data anak yang terkait akan tampil.'],
        ['Bantu pengukuran', 'Bantu timbang berat badan dan ukur tinggi badan anak saat Posyandu.'],
        ['Input hasil pengukuran', 'Catat hasil pengukuran. Kehadiran anak otomatis tercatat dari input ini.'],
        ['Laporkan kondisi anak', 'Sampaikan kondisi seperti risiko stunting kepada bidan wilayah.'],
    ];
@endphp

<div class="space-y-6">

    {{-- Sambutan --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#C9517E] to-[#E99AB7] p-5 text-white shadow-md shadow-[#C9517E]/20 sm:p-6">
        <div class="pointer-events-none absolute -right-10 -top-14 h-40 w-40 rounded-full border border-white/25"></div>
        <div class="pointer-events-none absolute -bottom-16 right-24 h-32 w-32 rounded-full bg-white/10"></div>
        <div class="relative">
            <p class="text-xs font-semibold uppercase tracking-wider text-white/90">Dashboard Kader</p>
            <h1 class="mt-1 text-xl font-bold sm:text-2xl">Selamat bertugas, {{ $user->name }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-white">
                Bantu kegiatan Posyandu berjalan lancar: catat pengukuran dan pastikan setiap data tercatat dengan benar.
            </p>
            <div class="mt-3 inline-flex items-center gap-2 rounded-lg bg-white/20 px-3 py-1.5 text-xs font-semibold sm:text-sm">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.5 7-12a7 7 0 1 0-14 0c0 6.5 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
                {{ $wilayahLabel ?: 'Wilayah belum ditetapkan' }}
            </div>
        </div>
    </section>

    {{-- Kartu statistik --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
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
        {{-- Tugas di Posyandu --}}
        <section class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm sm:p-6 lg:col-span-2">
            <h2 class="text-base font-bold text-[#542D3D]">Tugas Anda di Posyandu</h2>
            <p class="mt-1 text-sm text-slate-600">Urutan kerja yang biasa dilakukan kader saat kegiatan berlangsung.</p>

            <ol class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach ($tugas as $i => [$judul, $desk])
                    <li class="flex gap-3 rounded-xl border border-[#F1D5E0] bg-[#FFF7FA] p-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#C9517E] text-sm font-bold text-white">{{ $i + 1 }}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-[#542D3D]">{{ $judul }}</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $desk }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Catatan penting --}}
        <section class="rounded-2xl border border-[#F1D5E0] bg-[#FCE7F0] p-5 sm:p-6">
            <h2 class="text-base font-bold text-[#542D3D]">Yang Perlu Diingat</h2>
            <ul class="mt-4 space-y-3 text-sm leading-6 text-[#542D3D]">
                <li class="flex gap-2.5">
                    <svg class="mt-1 h-4 w-4 shrink-0 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5 9-10"/></svg>
                    <span><strong>NIK wajib dicatat</strong> pada setiap data.</span>
                </li>
                <li class="flex gap-2.5">
                    <svg class="mt-1 h-4 w-4 shrink-0 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5 9-10"/></svg>
                    <span>Data yang tampil <strong>sesuai tugas kader</strong> saja.</span>
                </li>
                <li class="flex gap-2.5">
                    <svg class="mt-1 h-4 w-4 shrink-0 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5 9-10"/></svg>
                    <span>Ada kondisi yang mengkhawatirkan? <strong>Laporkan ke bidan.</strong></span>
                </li>
            </ul>
        </section>
    </div>
</div>
@endsection