@extends('layouts.app')

@section('title', 'Dashboard Bidan')

@section('content')
@php
    $user = auth()->user();
    $wk = $user->bidan?->wilayahKerja;

    $wilayahLabel = $wk
        ? (trim(
            ($wk->kelurahan ? 'Desa ' . $wk->kelurahan : '') .
            ($wk->kecamatan ? ', Kec. ' . $wk->kecamatan : ''),
            ', '
        ) ?: $wk->nama_wilayah)
        : null;

    $cards = [
        [
            'label' => 'Jumlah Ibu Wilayah',
            'value' => $stats['ibu_melahirkan'] + $stats['ibu_hamil'],
            'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0',
        ],
        [
            'label' => 'Jumlah Anak Wilayah',
            'value' => $stats['anak'],
            'icon' => 'M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6',
        ],
        [
            'label' => 'Jumlah Pemeriksaan Hari Ini',
            'value' => $stats['pemeriksaan_hari_ini'] ?? 0,
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6l4 4v12a2 2 0 0 1-2 2',
        ],
        [
            'label' => 'Jumlah Pasien Perlu Perhatian',
            'value' => $stats['perlu_perhatian'] ?? 0,
            'icon' => 'M6 9a6 6 0 1 1 12 0c0 5 2 6.5 2 6.5H4S6 14 6 9Zm4 9.5a2 2 0 0 0 4 0',
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

    $jadwalList = collect($jadwalTerdekat ?? [])->take(3);
    $notifList  = collect($notifikasiTerbaru ?? [])->take(3);
@endphp

<div class="mx-auto w-full max-w-5xl space-y-5">

    {{-- ===== BANNER SAPAAN ===== --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#C9517E] to-[#E99AB7] p-4 text-white shadow-md shadow-[#C9517E]/20 sm:p-5">
        <div class="pointer-events-none absolute -right-8 -top-12 h-32 w-32 rounded-full border border-white/25"></div>
        <div class="pointer-events-none absolute -bottom-14 right-20 h-28 w-28 rounded-full bg-white/10"></div>

        <div class="relative max-w-2xl">
            <p class="text-[11px] font-bold uppercase tracking-wider text-white/90 sm:text-xs">Dashboard Bidan</p>

            <h1 class="mt-1 text-lg font-bold leading-tight sm:text-xl">
                <span data-greeting>Selamat datang</span>, {{ $user->name }}!
            </h1>

            <p class="mt-1.5 text-xs leading-5 text-white/95 sm:text-sm">
                Selamat datang di SI-KIA. Pantau kondisi ibu, anak, dan kegiatan kesehatan di wilayah tugas Anda.
            </p>

            <span class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-white/20 px-3 py-1 text-xs font-semibold text-white">
                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                </svg>
                {{ $wilayahLabel ?: 'Wilayah belum ditetapkan' }}
            </span>
        </div>
    </section>

    {{-- ===== KARTU STATISTIK ===== --}}
    <section class="grid grid-cols-2 gap-3 md:grid-cols-3">
        @foreach ($cards as $card)
            <div class="flex items-center gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-3.5 shadow-sm">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold leading-snug text-[#542D3D]">{{ $card['label'] }}</p>
                    <p class="mt-0.5 text-2xl font-bold leading-none text-[#C9517E]">{{ number_format($card['value']) }}</p>
                </div>
            </div>
        @endforeach
    </section>

    {{-- ===== JADWAL TERDEKAT DAN NOTIFIKASI TERBARU ===== --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-2">

        {{-- Jadwal Terdekat --}}
        <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-4 py-3">
                <h2 class="text-base font-bold text-[#542D3D]">Jadwal Terdekat</h2>
            </div>

            @if ($jadwalList->isNotEmpty())
                <ul class="divide-y divide-[#F7E6ED] p-4">
                    @foreach ($jadwalList as $jadwal)
                        <li class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#FCE7F0] text-[#C9517E]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-[#542D3D]">{{ $jadwal->jenis_kegiatan ?? 'Kegiatan Posyandu' }}</p>
                                <p class="mt-0.5 text-xs text-slate-600">
                                    {{ $jadwal->tanggal?->format('d/m/Y') ?? '-' }},
                                    {{ $jadwal->waktu ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i') : '-' }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="flex flex-1 items-center justify-center px-4 py-8 text-center text-sm text-slate-500">Belum ada jadwal Posyandu.</p>
            @endif
        </div>

        {{-- Notifikasi Terbaru --}}
        <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-4 py-3">
                <h2 class="text-base font-bold text-[#542D3D]">Notifikasi Terbaru</h2>
            </div>

            @if ($notifList->isNotEmpty())
                <ul class="divide-y divide-[#F7E6ED] p-4">
                    @foreach ($notifList as $notifikasi)
                        <li class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9a6 6 0 1 1 12 0c0 5 2 6.5 2 6.5H4S6 14 6 9Zm4 9.5a2 2 0 0 0 4 0"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-[#542D3D]">
                                    Pasien perlu perhatian: {{ $notifikasi->ibu?->user?->name ?? 'Nama ibu tidak tersedia' }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-600">
                                    {{ $notifikasi->tgl_pemeriksaan?->format('d/m/Y') ?? '-' }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="flex flex-1 items-center justify-center px-4 py-8 text-center text-sm text-slate-500">Tidak ada notifikasi terbaru.</p>
            @endif
        </div>

    </section>

</div>
@endsection