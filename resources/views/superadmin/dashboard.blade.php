@extends('layouts.app')

@section('title', 'Dashboard Super Admin')

@section('content')
    @php
        $cards = [
            ['label' => 'Admin Wilayah', 'value' => $stats['admin'], 'icon' => 'M16 11a3 3 0 1 0 0-6m-8 6a3 3 0 1 0 0-6m-6 13a6 6 0 0 1 12 0m2-5a6 6 0 0 1 6 5'],
            ['label' => 'Bidan', 'value' => $stats['bidan'], 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0M12 15v4m-2-2h4'],
            ['label' => 'Kader', 'value' => $stats['kader'], 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0'],
            ['label' => 'Ibu', 'value' => $stats['ibu'], 'icon' => 'M12 7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm-3 3h6l2 5-3 2v5'],
            ['label' => 'Wilayah Kerja', 'value' => $stats['wilayah'], 'icon' => 'M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z'],
            ['label' => 'Belum Ada Admin', 'value' => $stats['admin_tanpa_wilayah'], 'icon' => 'M12 9v4m0 4h.01M10.3 3.9 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z'],
        ];
    @endphp

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Dashboard Super Admin</h1>
            <p class="mt-1 text-sm text-slate-600">Haii Selamat datang kembali, {{ auth()->user()->name }}.</p>
        </div>

        <div class="grid max-w-5xl grid-cols-2 gap-3 md:grid-cols-3">
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

        @if ($stats['admin_tanpa_wilayah'] > 0)
            <div class="max-w-3xl rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                Ada {{ $stats['admin_tanpa_wilayah'] }} akun Admin yang belum memiliki profil wilayah. Lengkapi melalui menu Admin Wilayah.
            </div>
        @endif
    </div>
@endsection