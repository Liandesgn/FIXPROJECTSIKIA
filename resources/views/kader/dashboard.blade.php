@extends('layouts.app')

@section('title', 'Dashboard Kader')

@section('content')
    @php
        $user = $user ?? auth()->user();
        $wk = $wilayah ?? $user?->wilayahKerja ?? $user?->kader?->wilayahKerja;

        $wilayahLabel = $wk
            ? (trim(
                ($wk->kelurahan ? 'Desa ' . $wk->kelurahan : '') .
                ($wk->kecamatan ? ', Kec. ' . $wk->kecamatan : ''),
                ', '
            ) ?: $wk->nama_wilayah)
            : null;

        $cards = [
            [
                'label' => 'Jumlah Kegiatan Posyandu',
                'value' => $stats['posyandu'] ?? 0,
                'icon' => 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z',
            ],
            [
                'label' => 'Jumlah Pengukuran Anak',
                'value' => $stats['pengukuran'] ?? 0,
                'icon' => 'M4 20 20 4M7 7l2 2m2-6 2 2m2 6 2 2m-6 6 2 2',
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
                <p class="text-[11px] font-bold uppercase tracking-wider text-white/90 sm:text-xs">Dashboard Kader</p>

                <h1 class="mt-1 text-lg font-bold leading-tight sm:text-xl">
                    <span data-greeting>Selamat datang</span>, {{ $user?->name ?? 'Kader' }}!
                </h1>

                <p class="mt-1.5 text-xs leading-5 text-white/95 sm:text-sm">
                    Bantu kegiatan Posyandu berjalan lancar dan pastikan setiap data kesehatan ibu dan anak tercatat dengan benar.
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
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach ($cards as $card)
                <div class="flex items-center gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-3.5 shadow-sm">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold leading-snug text-[#542D3D]">{{ $card['label'] }}</p>
                        <p class="mt-0.5 text-2xl font-bold leading-none text-[#C9517E]">{{ $card['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ===== JADWAL POSYANDU DAN NOTIFIKASI TERBARU ===== --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

            {{-- Jadwal Posyandu --}}
            <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
                <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-4 py-3">
                    <h2 class="text-base font-bold text-[#542D3D]">Jadwal Posyandu</h2>
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
                                        - {{ $jadwal->lokasi ?? '-' }}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="flex flex-1 items-center justify-center px-4 py-8 text-center text-sm text-slate-500">Belum ada jadwal Posyandu mendatang.</p>
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
                                        Anak perlu perhatian: {{ $notifikasi->anak?->nama_anak ?? 'Nama anak tidak tersedia' }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-600">
                                        {{ $notifikasi->kegiatan?->tanggal?->format('d/m/Y') ?? '-' }}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="flex flex-1 items-center justify-center px-4 py-8 text-center text-sm text-slate-500">Belum ada notifikasi pemeriksaan anak.</p>
                @endif
            </div>

        </div>

    </div>
@endsection