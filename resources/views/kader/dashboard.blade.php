@extends('layouts.app')

@section('title', 'Dashboard Kader')

@section('content')
@php
    $user = $user ?? auth()->user();

    $wk = $user?->wilayahKerja;

    $wilayahLabel = $wk
        ? trim(
            ($wk->desa ? 'Desa ' . $wk->desa : '') .
            ($wk->kecamatan ? ', Kec. ' . $wk->kecamatan : ''),
            ', '
        )
        : null;
@endphp

<div class="space-y-6">

    {{-- Sambutan --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#C9517E] to-[#E99AB7] p-5 text-white shadow-md shadow-[#C9517E]/20 sm:p-6">

        {{-- Dekorasi --}}
        <div class="pointer-events-none absolute -right-10 -top-14 h-40 w-40 rounded-full border border-white/25"></div>

        <div class="pointer-events-none absolute -bottom-16 right-24 h-32 w-32 rounded-full bg-white/10"></div>

        {{-- Informasi Kader --}}
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

            {{-- Wilayah Kerja --}}
            <div class="mt-3 inline-flex items-center gap-2 rounded-lg bg-white/20 px-3 py-1.5 text-xs font-semibold sm:text-sm">

                <svg
                    class="h-4 w-4 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 21s7-5.5 7-12a7 7 0 1 0-14 0c0 6.5 7 12 7 12Z"
                    />

                    <circle cx="12" cy="9" r="2.5"/>
                </svg>

                {{ $wilayahLabel ?: 'Wilayah belum ditetapkan' }}

            </div>

        </div>
    </section>

</div>
@endsection