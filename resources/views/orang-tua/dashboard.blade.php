@extends('layouts.app')

@section('title', 'Dashboard Ibu')

@section('content')
    <div class="space-y-6">
        <div class="rounded-xl bg-gradient-to-r from-sky-600 to-sky-500 p-6 text-white shadow-sm">
            <h2 class="text-xl font-semibold">Halo, {{ $user->name }} 👋</h2>
            <p class="mt-1 text-sm text-white/80">
                Silakan gunakan menu layanan berikut untuk memantau kesehatan ibu dan anak Anda.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Bidan Wilayah Anda</p>
                <p class="mt-1 font-semibold text-slate-800">{{ $user->bidanWilayah?->name ?? 'Belum ada' }}</p>
                <p class="text-sm text-slate-500">{{ $user->bidanWilayah?->phone ?? '-' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kader Wilayah Anda</p>
                <p class="mt-1 font-semibold text-slate-800">{{ $user->kaderWilayah?->name ?? 'Belum ada' }}</p>
                <p class="text-sm text-slate-500">{{ $user->kaderWilayah?->phone ?? '-' }}</p>
            </div>
        </div>

        <div>
            <h3 class="font-semibold text-slate-800">Layanan untuk Anda</h3>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Data Ibu', 'data-ibu', 'Lihat data kehamilan dan kesehatan ibu.'],
                    ['Data Anak', 'data-anak', 'Lihat data anak dan balita Anda.'],
                    ['Riwayat Pemeriksaan', 'riwayat-ibu', 'Riwayat pemeriksaan kesehatan.'],
                    ['Konsultasi Bidan', 'konsultasi', 'Tanya langsung kepada bidan.'],
                    ['Edukasi Kesehatan', 'edukasi', 'Artikel kesehatan ibu dan anak.'],
                    ['Jadwal Pemeriksaan', 'jadwal-pemeriksaan', 'Jadwal posyandu dan pemeriksaan.'],
                ] as [$label, $slug, $description])
                    <a href="{{ route('panel.module', $slug) }}"
                       class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-sky-400 hover:shadow">
                        <p class="font-semibold text-slate-800">{{ $label }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
