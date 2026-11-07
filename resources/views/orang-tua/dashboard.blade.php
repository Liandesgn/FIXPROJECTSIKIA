
@extends('layouts.app')

@section('title', 'Dashboard Ibu')

@section('content')
@php
    $user = $user ?? auth()->user();

    $profil = $user?->ibu;
    $wk = $profil?->wilayahKerja;

    $wilayahLabel = $wk
        ? trim(
            ($wk->kelurahan ? 'Desa ' . $wk->kelurahan : '') .
            ($wk->kecamatan ? ', Kec. ' . $wk->kecamatan : ''),
            ', '
        )
        : null;

    $hamil = $profil?->sedangHamil() ?? false;

    $hpl = $profil?->hpl
        ? $profil->hpl->translatedFormat('d F Y')
        : 'Belum ditentukan';

    $statusKehamilan = $profil?->status_kehamilan?->value;

    $anakList = $anakList ?? $profil?->anak ?? collect();
    $jadwalList = $jadwalPosyandu ?? collect();

    $statusPemeriksaanIbu = $pemeriksaanIbuTerakhir?->status_kesehatan;
    $statusIbuNormal = $statusPemeriksaanIbu === 'normal';
@endphp

<div class="space-y-6">

    {{-- 1. Sambutan --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#C9517E] to-[#E99AB7] p-5 text-white shadow-md sm:p-6">

        <div class="pointer-events-none absolute -right-10 -top-14 h-40 w-40 rounded-full border border-white/25"></div>
        <div class="pointer-events-none absolute -bottom-16 right-24 h-32 w-32 rounded-full bg-white/10"></div>

        <div class="relative">
            <p class="text-xs font-semibold uppercase tracking-wider text-white/90">
                Dashboard Orang Tua
            </p>

            <h1 class="mt-1 text-xl font-bold sm:text-2xl">
                Halo, {{ $user?->name ?? 'Ibu' }}!
            </h1>

            <p class="mt-2 max-w-2xl text-sm leading-6 text-white">
                Selamat datang di SI-KIA. Pantau kesehatan ibu dan anak
                serta dapatkan informasi layanan Posyandu di wilayah Anda.
            </p>

            @if ($wilayahLabel)
                <div class="mt-3 inline-flex items-center gap-2 rounded-lg bg-white/20 px-3 py-1.5 text-xs font-semibold sm:text-sm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 21s7-5.5 7-12a7 7 0 1 0-14 0c0 6.5 7 12 7 12Z" />
                        <circle cx="12" cy="9" r="2.5" />
                    </svg>

                    {{ $wilayahLabel }}
                </div>
            @endif
        </div>
    </section>


    {{-- 2. Data Ibu dan Data Anak --}}
    <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">

       {{-- Data Ibu --}}
<div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-bold text-[#542D3D]">Data Ibu</h2>
            <p class="mt-1 text-xs text-slate-500">
                Informasi kesehatan ibu
            </p>
        </div>

        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 21s-8-4.5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 6.5-8 11-8 11Z" />
            </svg>
        </div>
    </div>

    <div class="mt-5 space-y-3">

        {{-- Status --}}
        <div class="flex items-center justify-between rounded-xl bg-[#FFF5F8] p-3">
            <span class="text-sm text-slate-600">
                Status
            </span>

            <span class="rounded-full px-3 py-1 text-xs font-bold
                {{ $statusKehamilan === 'hamil'
                    ? 'bg-green-100 text-green-700'
                    : ($statusKehamilan === 'melahirkan'
                        ? 'bg-blue-100 text-blue-700'
                        : 'bg-slate-100 text-slate-600') }}">
                {{ $statusKehamilan === 'hamil'
                    ? 'Hamil'
                    : ($statusKehamilan === 'melahirkan'
                        ? 'Melahirkan'
                        : 'Belum diisi') }}
            </span>
        </div>

        {{-- HPL --}}
        <div class="rounded-xl bg-[#FCE7F0] p-3">
            <p class="text-xs text-[#C9517E]">
                Hari Perkiraan Lahir (HPL)
            </p>

            <p class="mt-1 text-lg font-bold text-[#542D3D]">
                {{ $hamil ? $hpl : '-' }}
            </p>
        </div>

        {{-- Status Kesehatan --}}
        <div class="flex items-center justify-between rounded-xl bg-[#FFF5F8] p-3">
            <span class="text-sm text-slate-600">
                Status Kesehatan
            </span>

            @if ($statusPemeriksaanIbu === 'normal')
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">
                    Normal
                </span>
            @elseif ($statusPemeriksaanIbu === 'perlu_perhatian')
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                    Perlu Perhatian
                </span>
            @else
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">
                    Belum diperiksa
                </span>
            @endif
        </div>

    </div>
</div>

        {{-- Data Anak --}}
        <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-[#542D3D]">
                        Data Anak
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Informasi dan pemeriksaan kesehatan anak
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="8" r="4" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 21a8 8 0 0 1 16 0" />
                    </svg>
                </div>
            </div>

            <div class="mt-5 space-y-3">

                @forelse ($anakList as $anak)
                    @php
                        $pemeriksaanAnak = $pemeriksaanAnakTerakhir->get($anak->id);

                        $tanggalPemeriksaanAnak = $pemeriksaanAnak?->kegiatan?->tanggal;

                        $statusPemeriksaanAnak = $pemeriksaanAnak?->status_kesehatan;

                        $anakNormal = $statusPemeriksaanAnak === 'normal';
                    @endphp

                    <div class="rounded-xl bg-[#FFF5F8] p-4">

                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-slate-500">
                                    Nama Anak
                                </p>

                                <p class="mt-1 font-bold text-[#542D3D]">
                                    {{ $anak->nama_anak }}
                                </p>
                            </div>

                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-[#C9517E]">
                                {{ $anak->jenis_kelamin === 'L' ? 'Laki-laki' : ($anak->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                            </span>
                        </div>

                        <div class="mt-3 space-y-2 border-t border-[#F1D5E0] pt-3">

                            <div class="flex items-center justify-between gap-3">
                                
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs text-slate-500">
                                    Pemeriksaan Terakhir
                                </span>

                                <span class="text-right text-xs font-semibold text-[#542D3D]">
                                    {{ $tanggalPemeriksaanAnak?->translatedFormat('d F Y') ?? 'Belum diperiksa' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs text-slate-500">
                                    Status Kesehatan
                                </span>

                                @if ($statusPemeriksaanAnak)
                                    <span class="rounded-full px-3 py-1 text-xs font-bold
                                        {{ $anakNormal ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $anakNormal ? 'Normal' : 'Perlu Perhatian' }}
                                    </span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">
                                        Belum diperiksa
                                    </span>
                                @endif
                            </div>

                        </div>
                    </div>

                @empty
                    <div class="rounded-xl bg-[#FFF5F8] p-5 text-center">
                        <p class="text-sm font-semibold text-[#542D3D]">
                            Belum ada data anak
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Data anak yang terdaftar akan ditampilkan di sini.
                        </p>
                    </div>
                @endforelse

            </div>
        </div>

    </section>


    {{-- 3. Jadwal Posyandu dan Notifikasi --}}
    <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Jadwal Posyandu --}}
        <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-[#542D3D]">
                        Jadwal Posyandu
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Kegiatan Posyandu mendatang di wilayah Anda
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="5" width="18" height="16" rx="2" />
                        <path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18" />
                    </svg>
                </div>
            </div>

            <div class="mt-4 space-y-3">

                @forelse ($jadwalList as $jadwal)
                    <div class="rounded-xl bg-[#FFF5F8] p-4">

                        <p class="font-bold text-[#542D3D]">
                            {{ $jadwal->jenis_kegiatan }}
                        </p>

                        <p class="mt-2 text-xs text-slate-600">
                            {{ $jadwal->tanggal?->translatedFormat('d F Y') ?? '-' }}

                            @if ($jadwal->waktu)
                                • {{ $jadwal->waktu }}
                            @endif
                        </p>

                        <p class="mt-1 text-xs text-slate-600">
                            Lokasi: {{ $jadwal->lokasi ?? '-' }}
                        </p>

                    </div>
                @empty
                    <div class="rounded-xl bg-[#FFF5F8] p-5 text-center">
                        <p class="text-sm font-semibold text-[#542D3D]">
                            Belum ada jadwal Posyandu
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Jadwal kegiatan mendatang akan muncul di sini.
                        </p>
                    </div>
                @endforelse

            </div>
        </div>


        {{-- Notifikasi --}}
        <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-[#542D3D]">
                        Notifikasi Terbaru
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Informasi terbaru untuk Anda
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 12h4" />
                    </svg>
                </div>
            </div>

            <div class="mt-4 rounded-xl bg-[#FFF5F8] p-5 text-center">
                <p class="text-sm font-semibold text-[#542D3D]">
                    Belum ada notifikasi
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Notifikasi jadwal dan informasi layanan akan ditampilkan di sini.
                </p>
            </div>

        </div>

    </section>

</div>
@endsection
