@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    @php
        $cards = [
            ['label' => 'Jumlah Bidan',          'value' => $stats['bidan'],     'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0M12 15v4m-2-2h4'],
            ['label' => 'Jumlah Kader',          'value' => $stats['kader'],     'icon' => 'M16 11a3 3 0 1 0 0-6m-8 6a3 3 0 1 0 0-6m-6 13a6 6 0 0 1 12 0m2-5a6 6 0 0 1 6 5'],
            ['label' => 'Jumlah Ibu Hamil',      'value' => $stats['ibu_hamil'], 'icon' => 'M12 7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm-3 3h6l2 5-3 2v5m-4-8-2 3 3 2v3'],
            ['label' => 'Jumlah Anak',           'value' => $stats['anak'],      'icon' => 'M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6'],
            ['label' => 'Jumlah Kegiatan Posyandu',       'value' => $stats['jadwal'],    'icon' => 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z'],
        ];

        $editRoutes = ['bidan' => 'admin.bidan.edit', 'kader' => 'admin.kader.edit', 'ibu' => 'admin.ibu.edit'];
    @endphp

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Dashboard Admin</h1>
            <p class="mt-1 text-sm text-slate-600">Selamat datang kembali, {{ auth()->user()->name }}.</p>
        </div>

        {{-- Kartu statistik: hanya menampilkan angka, tidak bisa diklik --}}
        <div class="grid max-w-4xl grid-cols-2 gap-3 md:grid-cols-3">
            @foreach ($cards as $card)
                <div class="flex items-center gap-3 rounded-xl border border-[#F1D5E0] bg-[#FCE7F0] p-3.5 sm:p-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E] shadow-sm sm:h-11 sm:w-11">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold leading-tight text-[#542D3D] sm:text-sm">{{ $card['label'] }}</p>
                        <p class="mt-0.5 text-2xl font-bold leading-none text-[#C9517E]">{{ $card['value'] }}</p>
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
            Daftar kegiatan Posyandu mendatang dari seluruh wilayah kerja.
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#FCE7F0] text-xs uppercase text-[#542D3D]">
                <tr>
                    <th class="px-5 py-3">No</th>
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="px-5 py-3">Waktu</th>
                    <th class="px-5 py-3">Wilayah</th>
                    <th class="px-5 py-3">Lokasi</th>
                    <th class="px-5 py-3">Jenis Kegiatan</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($jadwalPosyandu as $jadwal)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-5 py-3">
                            {{ $jadwal->tanggal->format('d-m-Y') }}
                        </td>

                        <td class="px-5 py-3">
                            {{ $jadwal->waktu
                                ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i')
                                : '-' }}
                        </td>

                        <td class="px-5 py-3 font-medium text-gray-800">
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
                        <td colspan="6"
                            class="px-5 py-10 text-center text-gray-500">
                            Belum ada jadwal Posyandu mendatang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
            
    </div>
@endsection