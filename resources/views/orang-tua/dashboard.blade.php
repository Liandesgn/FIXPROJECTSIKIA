@extends('layouts.app')

@section('title', 'Dashboard Ibu')

@section('content')
@php
    $user = $user ?? auth()->user();

    $profil = $user?->ibu;
    $wk = $profil?->wilayahKerja;

    $wilayahLabel = $wk
        ? trim(
            ($wk->desa ? 'Desa ' . $wk->desa : '') .
            ($wk->kecamatan ? ', Kec. ' . $wk->kecamatan : ''),
            ', '
        )
        : null;


    // Data kehamilan
    $hamil = $profil?->sedangHamil() ?? false;
    $usia = (int) ($profil?->usia_kehamilan ?? 0);
    $progress = max(0, min(100, (int) round($usia / 40 * 100)));
    $trimester = $usia <= 13 ? 1 : ($usia <= 27 ? 2 : 3);
    $hpl = $profil?->hpl
        ? $profil->hpl->translatedFormat('d F Y')
        : null;
@endphp

<div class="space-y-6">

    {{-- Sambutan --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#C9517E] to-[#E99AB7] p-5 text-white shadow-md shadow-[#C9517E]/20 sm:p-6">

        <div class="pointer-events-none absolute -right-10 -top-14 h-40 w-40 rounded-full border border-white/25"></div>

        <div class="pointer-events-none absolute -bottom-16 right-24 h-32 w-32 rounded-full bg-white/10"></div>

        <div class="relative">
            <p class="text-xs font-semibold uppercase tracking-wider text-white/90">
                Dashboard Ibu
            </p>

            <h1 class="mt-1 text-xl font-bold sm:text-2xl">
                Halo, {{ $user?->name ?? 'Ibu' }}
            </h1>

            <p class="mt-2 max-w-2xl text-sm leading-6 text-white">
                Pantau kehamilan Anda bersama bidan dan kader di wilayah Anda.
            </p>

            @if ($wilayahLabel)
                <div class="mt-3 inline-flex items-center gap-2 rounded-lg bg-white/20 px-3 py-1.5 text-xs font-semibold sm:text-sm">

                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.5 7-12a7 7 0 1 0-14 0c0 6.5 7 12 7 12Z"/>
                        <circle cx="12" cy="9" r="2.5"/>
                    </svg>

                    {{ $wilayahLabel }}

                </div>
            @endif
        </div>
    </section>


    {{-- Kehamilan Anda --}}
    @if ($hamil)
        <section class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm sm:p-6">

            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-[#542D3D]">
                        Kehamilan Anda
                    </h2>

                    <p class="mt-1 text-sm text-slate-600">
                        Perkembangan kehamilan saat ini.
                    </p>
                </div>

                <span class="rounded-full bg-[#FCE7F0] px-3 py-1 text-xs font-bold text-[#C9517E]">
                    Trimester {{ $trimester }}
                </span>
            </div>

            {{-- Informasi Kehamilan --}}
            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">

                <div class="rounded-xl bg-[#FCE7F0] p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#C9517E]">
                        Usia Kehamilan
                    </p>

                    <p class="mt-1 text-2xl font-bold text-[#542D3D]">
                        {{ $usia ?: '-' }}
                        <span class="text-sm font-semibold">minggu</span>
                    </p>
                </div>

                <div class="rounded-xl bg-[#FCE7F0] p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#C9517E]">
                        Perkiraan Lahir (HPL)
                    </p>

                    <p class="mt-1 text-base font-bold text-[#542D3D] sm:text-lg">
                        {{ $hpl ?? '-' }}
                    </p>
                </div>

            </div>

            {{-- Progress Kehamilan --}}
            <div class="mt-5">

                <div class="flex items-center justify-between text-xs font-semibold text-[#542D3D]">
                    <span>Minggu 1</span>
                    <span>{{ $progress }}%</span>
                    <span>Minggu 40</span>
                </div>

                <div
                    class="mt-2 h-3 overflow-hidden rounded-full bg-[#FCE7F0]"
                    role="progressbar"
                    aria-valuenow="{{ $progress }}"
                    aria-valuemin="0"
                    aria-valuemax="100"
                >
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-[#E99AB7] to-[#C9517E]"
                        style="width: {{ $progress }}%"
                    ></div>
                </div>

            </div>

        </section>
    @endif


    {{-- Petugas Wilayah --}}
    <section>

        <h2 class="text-base font-bold text-[#542D3D]">
            Petugas Wilayah Anda
        </h2>

        <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">

            @foreach ([
                [
                    'Bidan',
                    $bidanWilayah,
                    'Belum ada data bidan',
                    'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0M12 15v4m-2-2h4'
                ],
                [
                    'Kader',
                    $kaderWilayah,
                    'Belum ada data kader',
                    'M16 11a3 3 0 1 0 0-6m-8 6a3 3 0 1 0 0-6m-6 13a6 6 0 0 1 12 0m2-5a6 6 0 0 1 6 5'
                ],
            ] as [$peran, $orang, $kosong, $ikon])

                <div class="flex items-center gap-4 rounded-xl border border-[#F1D5E0] bg-white p-4 shadow-sm">

                    {{-- Ikon Petugas --}}
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">

                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="{{ $ikon }}"
                            />
                        </svg>

                    </div>

                    {{-- Informasi Petugas --}}
                    <div class="min-w-0 flex-1">

                        <p class="text-xs font-bold uppercase tracking-wide text-[#C9517E]">
                            {{ $peran }}
                        </p>

                        <p class="mt-0.5 break-words text-sm font-bold text-[#542D3D]">
                            {{ $orang?->name ?? $kosong }}
                        </p>

                        @if ($orang?->phone)

                            <a
                                href="tel:{{ preg_replace('/\s+/', '', $orang->phone) }}"
                                class="mt-0.5 inline-block text-sm font-semibold text-slate-700 hover:text-[#C9517E] hover:underline"
                            >
                                {{ $orang->phone }}
                            </a>

                        @else

                            <p class="mt-0.5 text-sm text-slate-600">
                                -
                            </p>

                        @endif

                    </div>

                </div>

            @endforeach

        </div>

    </section>

</div>
@endsection
