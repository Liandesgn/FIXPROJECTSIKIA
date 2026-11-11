@extends('layouts.app')

@section('title', 'Dashboard Super Admin')

@section('content')
    @php
        $cards = [
            ['label' => 'Wilayah', 'value' => $stats['wilayah'], 'icon' => 'M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z'],
            ['label' => 'Admin', 'value' => $stats['admin'], 'icon' => 'M16 11a3 3 0 1 0 0-6m-8 6a3 3 0 1 0 0-6m-6 13a6 6 0 0 1 12 0m2-5a6 6 0 0 1 6 5'],
            ['label' => 'Bidan', 'value' => $stats['bidan'], 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0M12 15v4m-2-2h4'],
            ['label' => 'Kader', 'value' => $stats['kader'], 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0'],
            ['label' => 'Ibu Hamil', 'value' => $stats['ibu'], 'icon' => 'M12 7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm-3 3h6l2 5-3 2v5'],
            ['label' => 'Anak Terdaftar', 'value' => $stats['anak'], 'icon' => 'M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6'],
            ['label' => 'Kegiatan Posyandu', 'value' => $stats['kegiatan'], 'icon' => 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z'],
        ];

        // Dashboard hanya menampilkan 5 wilayah; selengkapnya ada di halaman Laporan
        $wilayahTampil = collect($perbandinganWilayah instanceof \Illuminate\Pagination\AbstractPaginator
            ? $perbandinganWilayah->items()
            : $perbandinganWilayah)->take(5);
    @endphp

    <div class="mx-auto w-full max-w-5xl space-y-5">

        {{-- ===== BANNER SAPAAN ===== --}}
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#C9517E] to-[#E99AB7] p-4 text-white shadow-md shadow-[#C9517E]/20 sm:p-5">
            <div class="pointer-events-none absolute -right-8 -top-12 h-32 w-32 rounded-full border border-white/25"></div>
            <div class="pointer-events-none absolute -bottom-14 right-20 h-28 w-28 rounded-full bg-white/10"></div>

            <div class="relative max-w-2xl">
                <p class="text-[11px] font-bold uppercase tracking-wider text-white/90 sm:text-xs">Dashboard Super Admin</p>

                <h1 class="mt-1 text-lg font-bold leading-tight sm:text-xl">
                    <span data-greeting>Selamat datang</span>, {{ auth()->user()->name }}!
                </h1>

                <p class="mt-1.5 text-xs leading-5 text-white/95 sm:text-sm">
                    Selamat datang di SI-KIA. Kelola data Puskesmas, pengguna, dan kegiatan Posyandu dari seluruh wilayah melalui halaman ini.
                </p>

                <span class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-white/20 px-3 py-1 text-xs font-semibold text-white">
                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                    </svg>
                    {{ $stats['wilayah'] }} Puskesmas Terdaftar
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

        {{-- ===== PERBANDINGAN ANTAR WILAYAH (5 data) ===== --}}
        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

            <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-4">
                <h2 class="text-lg font-bold text-[#542D3D]">Perbandingan Antar Wilayah</h2>
                <p class="mt-1 text-sm text-slate-600">Ringkasan ibu hamil, anak, kegiatan, dan kondisi perlu perhatian.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[34rem] divide-y divide-[#F1D5E0]">
                    <thead class="bg-[#FFF5F8]">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#542D3D]">Wilayah</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#542D3D]">Ibu Hamil</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#542D3D]">Anak</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#542D3D]">Kegiatan</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#542D3D]">Perlu Perhatian</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#F7E6ED] bg-white">
                        @forelse ($wilayahTampil as $wilayah)
                            <tr class="transition hover:bg-[#FCE7F0]/40">
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <div class="font-semibold text-[#542D3D]">{{ $wilayah->nama_wilayah }}</div>
                                    @if ($wilayah->kecamatan || $wilayah->kelurahan)
                                        <div class="mt-0.5 text-xs text-slate-500">
                                            {{ $wilayah->kecamatan }}
                                            @if ($wilayah->kelurahan)
                                                / {{ $wilayah->kelurahan }}
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center font-semibold text-slate-700">{{ $wilayah->ibu_hamil_count }}</td>
                                <td class="px-5 py-3.5 text-center font-semibold text-slate-700">{{ $wilayah->anak_count }}</td>
                                <td class="px-5 py-3.5 text-center font-semibold text-slate-700">{{ $wilayah->kegiatan_count }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($wilayah->perlu_perhatian_count > 0)
                                        <span class="inline-flex min-w-8 items-center justify-center rounded-full bg-red-50 px-3 py-1 text-sm font-bold text-red-600">{{ $wilayah->perlu_perhatian_count }}</span>
                                    @else
                                        <span class="inline-flex min-w-8 items-center justify-center rounded-full bg-green-50 px-3 py-1 text-sm font-bold text-green-600">0</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada data wilayah kerja.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end border-t border-[#F1D5E0] bg-white px-5 py-3">
                <a href="{{ route('superadmin.laporan.index', ['tab' => 'rekap-kesehatan']) }}"
                   class="inline-flex items-center gap-1 rounded-lg border-2 border-[#E99AB7] bg-white px-3 py-1.5 text-xs font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">
                    Lihat Selengkapnya
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                </a>
            </div>

        </div>

    </div>
@endsection