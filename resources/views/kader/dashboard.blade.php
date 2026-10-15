@extends('layouts.app')

@section('title', 'Dashboard Kader')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Dashboard Kader</h2>
            <p class="text-sm text-slate-500">
                Selamat datang, {{ $user->name }}. Posyandu:
                <span class="font-medium text-slate-700">{{ $user->wilayahKerja?->nama ?? 'belum ditetapkan' }}</span>.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Jadwal Posyandu</p>
                <p class="mt-2 text-3xl font-bold text-sky-600">{{ $stats['posyandu'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Pengukuran Bulan Ini</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['pengukuran'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Notifikasi</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ $stats['notifikasi'] }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-semibold text-slate-800">Akses Cepat</h3>
            <p class="mt-1 text-sm text-slate-500">Tugas utama kader posyandu.</p>

            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ([
                    ['Pendataan Kegiatan', 'pendataan-posyandu'],
                    ['Cari Data Ibu', 'cari-ibu'],
                    ['Input Hasil Pengukuran', 'input-pengukuran'],
                    ['Jadwal Posyandu', 'jadwal-posyandu'],
                    ['Notifikasi', 'notifikasi'],
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
