@extends('layouts.app')

@section('title', 'Dashboard Bidan')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Dashboard Bidan</h2>
            <p class="text-sm text-slate-500">
                Selamat datang, {{ $user->name }}. Anda bertugas di
                <span class="font-medium text-slate-700">{{ $user->wilayahKerja?->nama ?? 'wilayah belum ditetapkan' }}</span>.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Orang Tua Terdaftar</p>
                <p class="mt-2 text-3xl font-bold text-sky-600">{{ $stats['orang_tua'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Pemeriksaan Hari Ini</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['pemeriksaan'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Konsultasi Aktif</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ $stats['konsultasi'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Jadwal Minggu Ini</p>
                <p class="mt-2 text-3xl font-bold text-violet-600">{{ $stats['jadwal'] }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-semibold text-slate-800">Akses Cepat</h3>
            <p class="mt-1 text-sm text-slate-500">Pilih menu layanan yang ingin Anda buka.</p>

            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ([
                    ['Pemeriksaan Ibu Hamil', 'pemeriksaan-ibu'],
                    ['Pemeriksaan Anak', 'pemeriksaan-anak'],
                    ['Riwayat Pemeriksaan', 'riwayat-pemeriksaan'],
                    ['Konsultasi', 'konsultasi'],
                    ['Jadwal Posyandu', 'jadwal-posyandu'],
                ] as [$label, $slug])
                    <a href="{{ route('panel.module', $slug) }}"
                       class="rounded-lg border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-sky-400 hover:bg-sky-50 hover:text-sky-700">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
