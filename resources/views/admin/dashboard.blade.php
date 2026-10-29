@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    @php
        $cards = [
            ['label' => 'Jumlah Bidan',          'value' => $stats['bidan'],     'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0M12 15v4m-2-2h4'],
            ['label' => 'Jumlah Kader',          'value' => $stats['kader'],     'icon' => 'M16 11a3 3 0 1 0 0-6m-8 6a3 3 0 1 0 0-6m-6 13a6 6 0 0 1 12 0m2-5a6 6 0 0 1 6 5'],
            ['label' => 'Jumlah Ibu Hamil',      'value' => $stats['ibu_hamil'], 'icon' => 'M12 7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm-3 3h6l2 5-3 2v5m-4-8-2 3 3 2v3'],
            ['label' => 'Jumlah Anak',           'value' => $stats['anak'],      'icon' => 'M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6'],
            ['label' => 'Jumlah Wilayah',  'value' => $stats['wilayah'],   'icon' => 'M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z'],
            ['label' => 'Jadwal Posyandu',       'value' => $stats['jadwal'],    'icon' => 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z'],
        ];

        $editRoutes = ['bidan' => 'admin.bidan.edit', 'kader' => 'admin.kader.edit', 'orang-tua' => 'admin.ibu.edit'];
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

        {{-- User yang baru dibuatkan akun --}}
        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#F1D5E0] px-5 py-4">
                <div>
                    <h3 class="font-bold text-[#542D3D]">User Terbaru</h3>
                    <p class="mt-0.5 text-xs text-slate-600">Akun yang baru dibuatkan oleh admin.</p>
                </div>
                <span class="rounded-full bg-[#FCE7F0] px-3 py-1 text-xs font-semibold text-[#C9517E]">
                    {{ count($latestUsers) }} akun terbaru
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[520px] text-left text-sm">
                    <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F7E6ED]">
                        @forelse ($latestUsers as $u)
                            @php
                                $uKey  = \Illuminate\Support\Str::before($u->primaryRole()?->dashboard() ?? '', '.');
                                $uEdit = isset($editRoutes[$uKey]) && \Illuminate\Support\Facades\Route::has($editRoutes[$uKey])
                                    ? route($editRoutes[$uKey], $u) : null;
                            @endphp
                            <tr class="transition hover:bg-[#FCE7F0]/40">
                                <td class="px-5 py-3 font-semibold text-[#542D3D]">
                                    @if ($uEdit)
                                        <a href="{{ $uEdit }}" class="transition hover:text-[#C9517E] hover:underline">{{ $u->name }}</a>
                                    @else
                                        {{ $u->name }}
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-700">{{ $u->email }}</td>
                                <td class="px-5 py-3 text-slate-700">{{ $uKey === 'orang-tua' ? 'Ibu' : ($u->primaryRole()?->label() ?? '-') }}</td>
                                <td class="px-5 py-3">
                                    @include('admin.partials.status', ['status' => $u->status])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-slate-600">Belum ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection