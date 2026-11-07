
@extends('layouts.app')

@section('title', 'Dashboard Kader')

@section('content')
    @php
        $user = $user ?? auth()->user();
        $wk = $wilayah ?? $user?->wilayahKerja;

        $wilayahLabel = $wk
            ? trim(
                ($wk->kelurahan ? 'Desa ' . $wk->kelurahan : '') .
                ($wk->kecamatan ? ', Kec. ' . $wk->kecamatan : ''),
                ', '
            )
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
    @endphp

    <div class="space-y-6">

        {{-- Sambutan --}}
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#C9517E] to-[#E99AB7] p-5 text-white shadow-md shadow-[#C9517E]/20 sm:p-6">

            <div class="pointer-events-none absolute -right-10 -top-14 h-40 w-40 rounded-full border border-white/25"></div>
            <div class="pointer-events-none absolute -bottom-16 right-24 h-32 w-32 rounded-full bg-white/10"></div>

            <div class="relative">
                <p class="text-xs font-semibold uppercase tracking-wider text-white/90">
                    Dashboard Kader
                </p>

                <h1 class="mt-1 text-xl font-bold sm:text-2xl">
                    Selamat bertugas, {{ $user?->name ?? 'Kader' }}
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-white">
                    Bantu kegiatan Posyandu berjalan lancar dan pastikan setiap data kesehatan ibu dan anak tercatat dengan benar.
                </p>

                <div class="mt-3 inline-flex items-center gap-2 rounded-lg bg-white/20 px-3 py-1.5 text-xs font-semibold sm:text-sm">
                    <svg class="h-4 w-4 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 21s7-5.5 7-12a7 7 0 1 0-14 0c0 6.5 7 12 7 12Z" />
                        <circle cx="12" cy="9" r="2.5" />
                    </svg>

                    {{ $wilayahLabel ?: 'Wilayah belum ditetapkan' }}
                </div>
            </div>
        </section>

        {{-- Kartu Statistik --}}
        <div class="grid max-w-2xl grid-cols-1 gap-3 sm:grid-cols-2">

            @foreach ($cards as $card)
                <div class="flex items-center gap-3 rounded-xl border border-[#F1D5E0] bg-[#FCE7F0] p-3.5 sm:p-4">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E] shadow-sm sm:h-11 sm:w-11">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="{{ $card['icon'] }}" />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-semibold leading-tight text-[#542D3D] sm:text-sm">
                            {{ $card['label'] }}
                        </p>

                        <p class="mt-0.5 text-2xl font-bold leading-none text-[#C9517E]">
                            {{ $card['value'] }}
                        </p>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- Jadwal Posyandu Terdekat --}}
        <div class="overflow-hidden rounded-xl border border-[#F1D5E0] bg-white shadow-sm">

            <div class="border-b border-[#F1D5E0] px-5 py-4">
                <h2 class="text-lg font-bold text-[#542D3D]">
                    Jadwal Posyandu Terdekat
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Daftar kegiatan Posyandu mendatang di wilayah kerja kamu.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#FCE7F0] text-[#542D3D]">
                        <tr>
                            <th class="px-5 py-3 font-semibold">No</th>
                            <th class="px-5 py-3 font-semibold">Tanggal</th>
                            <th class="px-5 py-3 font-semibold">Waktu</th>
                            <th class="px-5 py-3 font-semibold">Wilayah</th>
                            <th class="px-5 py-3 font-semibold">Lokasi</th>
                            <th class="px-5 py-3 font-semibold">Jenis Kegiatan</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($jadwalTerdekat ?? [] as $jadwal)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">{{ $loop->iteration }}</td>

                                <td class="whitespace-nowrap px-5 py-3">
                                    {{ $jadwal->tanggal?->format('d-m-Y') ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-3">
                                    {{ $jadwal->waktu ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i') : '-' }}
                                </td>

                                <td class="px-5 py-3">
                                    {{ $jadwal->wilayah?->nama_wilayah ?? '-' }}
                                </td>

                                <td class="px-5 py-3">
                                    {{ $jadwal->lokasi ?? '-' }}
                                </td>

                                <td class="px-5 py-3">
                                    {{ $jadwal->jenis_kegiatan ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-gray-500">
                                    Belum ada jadwal Posyandu mendatang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Notifikasi Terbaru --}}
        <div class="overflow-hidden rounded-xl border border-[#F1D5E0] bg-white shadow-sm">

            <div class="border-b border-[#F1D5E0] px-5 py-4">
                <h2 class="text-lg font-bold text-[#542D3D]">
                    Notifikasi Terbaru
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Informasi pemeriksaan anak yang memerlukan perhatian.
                </p>
            </div>

            <div class="divide-y divide-gray-100">

                @forelse ($notifikasiTerbaru ?? [] as $notifikasi)
                    <div class="flex items-start gap-3 px-5 py-4">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#FCE7F0] text-[#C9517E]">
                            <svg class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v4m0 4h.01M10.3 3.9 2.8 17.5A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-2.5L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-[#542D3D]">
                                Pemeriksaan Anak Memerlukan Perhatian
                            </p>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ $notifikasi->anak?->nama_anak ?? 'Nama anak tidak tersedia' }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                Tanggal pemeriksaan:
                                {{ $notifikasi->kegiatan?->tanggal?->format('d-m-Y') ?? '-' }}
                            </p>
                        </div>

                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500">
                        Belum ada notifikasi pemeriksaan anak.
                    </div>
                @endforelse

            </div>
        </div>

    </div>
@endsection
