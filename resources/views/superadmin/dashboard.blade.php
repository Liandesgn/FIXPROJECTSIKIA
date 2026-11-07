@extends('layouts.app')

@section('title', 'Dashboard Super Admin')

@section('content')
    @php
        $cards = [
            [
                'label' => 'Jumlah Admin',
                'value' => $stats['admin'],
                'icon' => 'M16 11a3 3 0 1 0 0-6m-8 6a3 3 0 1 0 0-6m-6 13a6 6 0 0 1 12 0m2-5a6 6 0 0 1 6 5'
            ],
            [
                'label' => 'Jumlah Bidan',
                'value' => $stats['bidan'],
                'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0M12 15v4m-2-2h4'
            ],
            [
                'label' => 'Jumlah Kader',
                'value' => $stats['kader'],
                'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0'
            ],
            [
                'label' => 'Jumlah Ibu Hamil',
                'value' => $stats['ibu'],
                'icon' => 'M12 7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm-3 3h6l2 5-3 2v5'
            ],
            [
                'label' => 'Jumlah Wilayah',
                'value' => $stats['wilayah'],
                'icon' => 'M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z'
            ],
            [
                'label' => 'Jumlah Anak Terdaftar',
                'value' => $stats['anak'],
                'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0'
            ],
            [
                'label' => 'Jumlah Kegiatan Posyandu',
                'value' => $stats['kegiatan'],
                'icon' => 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z'
            ],
        ];
    @endphp

    <div class="space-y-6">

        {{-- Judul Dashboard --}}
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
                Dashboard Super Admin
            </h1>

            <p class="mt-1 text-sm text-slate-600">
                Haii Selamat datang kembali, {{ auth()->user()->name }}.
            </p>
        </div>

        {{-- Kartu Statistik --}}
        <div class="grid max-w-5xl grid-cols-2 gap-3 md:grid-cols-3">
            @foreach ($cards as $card)
                <div class="flex items-center gap-3 rounded-xl border border-[#F1D5E0] bg-[#FCE7F0] p-3.5 sm:p-4">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E] shadow-sm sm:h-11 sm:w-11">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.7"
                             aria-hidden="true">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="{{ $card['icon'] }}"/>
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

        {{-- Perbandingan Antar Wilayah --}}
        <div class="overflow-hidden rounded-xl border border-[#F1D5E0] bg-white shadow-sm">

            <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-4">
                <h2 class="text-lg font-bold text-[#542D3D]">
                    Perbandingan Antar Wilayah
                </h2>

                <p class="mt-1 text-sm text-slate-600">
                    Ringkasan data ibu hamil, anak, kegiatan, dan kondisi yang perlu perhatian di setiap wilayah.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#F1D5E0]">

                    <thead class="bg-[#FFF5F8]">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-[#542D3D]">
                                Wilayah
                            </th>

                            <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#542D3D]">
                                Ibu Hamil
                            </th>

                            <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#542D3D]">
                                Anak
                            </th>

                            <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#542D3D]">
                                Kegiatan
                            </th>

                            <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-[#542D3D]">
                                Perlu Perhatian
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">

                        @forelse ($perbandinganWilayah as $wilayah)
                            <tr class="transition hover:bg-[#FFF8FA]">

                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="font-semibold text-[#542D3D]">
                                        {{ $wilayah->nama_wilayah }}
                                    </div>

                                    @if ($wilayah->kecamatan || $wilayah->kelurahan)
                                        <div class="mt-0.5 text-xs text-slate-500">
                                            {{ $wilayah->kecamatan }}
                                            @if ($wilayah->kelurahan)
                                                / {{ $wilayah->kelurahan }}
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-center font-semibold text-slate-700">
                                    {{ $wilayah->ibu_hamil_count }}
                                </td>

                                <td class="px-5 py-4 text-center font-semibold text-slate-700">
                                    {{ $wilayah->anak_count }}
                                </td>

                                <td class="px-5 py-4 text-center font-semibold text-slate-700">
                                    {{ $wilayah->kegiatan_count }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    @if ($wilayah->perlu_perhatian_count > 0)
                                        <span class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-sm font-bold text-red-600">
                                            {{ $wilayah->perlu_perhatian_count }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-sm font-bold text-green-600">
                                            0
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">
                                    Belum ada data wilayah kerja.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>

            <div class="border-t border-[#F1D5E0] bg-[#FFF8FA] px-5 py-3">
                <p class="text-xs text-slate-500">
                    Data ditampilkan berdasarkan wilayah kerja dan diperbarui mengikuti data terbaru di sistem.
                </p>
            </div>

        </div>

    </div>
@endsection