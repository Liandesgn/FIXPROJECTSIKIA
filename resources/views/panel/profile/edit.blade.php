
@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
@php
    $roleKey = \Illuminate\Support\Str::before(
        $user->primaryRole()?->dashboard() ?? '',
        '.'
    );

    $roleLabel = $user->primaryRole()?->label() ?? '-';
    $isIbu = $user->isOrangTua();

    $wk = match ($roleKey) {
        'admin' => $user->admin?->wilayahKerja,
        'bidan' => $user->bidan?->wilayahKerja,
        'kader' => $user->kader?->wilayahKerja,
        'ibu' => $user->ibu?->wilayahKerja,
        default => null,
    };

    // Data Puskesmas
    $puskesmas = $roleKey === 'admin'
        ? $user->admin?->puskesmas
        : $wk?->puskesmas;

    // Data Kecamatan
    $kecamatan = $roleKey === 'admin'
        ? $puskesmas?->kecamatan
        : $wk?->masterKecamatan;

    $initial = \Illuminate\Support\Str::upper(
        \Illuminate\Support\Str::substr($user->name, 0, 1)
    );

    $hamil = $isIbu && $user->ibu?->sedangHamil();

    // Ikon SVG
    $ikon = [
        'user' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0',
        'mail' => 'M3 7l9 6 9-6M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        'phone' => 'M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z',
        'shield' => 'M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3Z',
        'pin' => 'M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
        'building' => 'M3 21h18M5 21V8l7-5 7 5v13M10 21v-6h4v6',
        'idcard' => 'M3 6h18v12H3V6Zm4 4h4m-4 4h6m4-4h2',
        'calendar' => 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z',
        'heart' => 'M12 21s-8-4.5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 6.5-8 11-8 11Z',
        'clock' => 'M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    ];

    // Komponen isian profil
    $field = fn (string $label, $value, string $ikonKey, bool $lebar = false) =>
        '<div class="group min-w-0' . ($lebar ? ' sm:col-span-2' : '') . '">'
        . '<dt class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-[#C9517E]">'
        . '<span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#FCE7F0] text-[#C9517E] transition-colors duration-200 group-hover:bg-[#C9517E] group-hover:text-white motion-reduce:transition-none">'
        . '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">'
        . '<path stroke-linecap="round" stroke-linejoin="round" d="' . $ikon[$ikonKey] . '"/></svg></span>'
        . e($label) . '</dt>'
        . '<dd class="mt-2 break-words rounded-xl border-2 border-[#F1D5E0] bg-white px-4 py-3 text-base font-bold text-[#542D3D] shadow-sm transition duration-200 group-hover:border-[#E99AB7] group-hover:bg-[#FFF7FA] group-hover:shadow-md motion-reduce:transition-none">'
        . e(filled($value) ? $value : '-') . '</dd>'
        . '</div>';

    $adaWilayah = $wk || $kecamatan || $puskesmas;
@endphp

<div class="mx-auto w-full max-w-4xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
            Profil Saya
        </h1>
        <p class="mt-1 text-sm font-medium text-slate-700">
            Informasi akun yang terdaftar di SI-KIA
        </p>
    </div>

    {{-- Kartu Profil --}}
    <section class="overflow-hidden rounded-3xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="flex flex-col sm:flex-row">

            {{-- Panel kiri --}}
            <div class="flex shrink-0 flex-col items-center gap-3 border-b border-[#F1D5E0] bg-gradient-to-b from-[#FCE7F0] via-[#FDF0F5] to-white px-6 py-8 sm:w-64 sm:border-b-0 sm:border-r">
                <span class="flex h-28 w-28 items-center justify-center rounded-full bg-[#C9517E] text-5xl font-bold text-white shadow-lg ring-4 ring-white sm:h-32 sm:w-32"
                      aria-hidden="true">
                    {{ $initial }}
                </span>

                <h2 class="break-words text-center text-lg font-bold text-[#542D3D]">
                    {{ $user->name }}
                </h2>

                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#C9517E] px-4 py-1.5 text-sm font-bold text-white shadow-sm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['shield'] }}"/>
                    </svg>
                    {{ $roleLabel }}
                </span>
            </div>

            {{-- Panel kanan --}}
            <div class="min-w-0 flex-1 p-6">

                <h3 class="mb-4 flex items-center gap-2 text-sm font-bold text-[#542D3D]">
                    <span class="h-4 w-1 rounded-full bg-[#C9517E]"></span>
                    Data Akun
                </h3>

                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    {!! $field('Nama', $user->name, 'user') !!}
                    {!! $field('Email', $user->email, 'mail') !!}
                    {!! $field('Nomor HP', $user->phone, 'phone') !!}
                    {!! $field('Role', $roleLabel, 'shield') !!}
                </dl>

                @if ($adaWilayah)
                    <h3 class="mb-4 mt-8 flex items-center gap-2 text-sm font-bold text-[#542D3D]">
                        <span class="h-4 w-1 rounded-full bg-[#C9517E]"></span>
                        Wilayah Tugas
                    </h3>

                    <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        @if ($wk)
                            {!! $field('Wilayah Kerja', $wk->label(), 'pin', true) !!}
                        @endif

                        @if ($kecamatan)
                            {!! $field('Kecamatan', $kecamatan->nama_kecamatan, 'pin') !!}
                        @endif

                        @if ($puskesmas)
                            {!! $field('Puskesmas', $puskesmas->nama_puskesmas, 'building') !!}
                        @endif

                    </dl>
                @endif

            </div>
        </div>
    </section>

    {{-- Data Ibu --}}
    @if ($isIbu)
        <section class="overflow-hidden rounded-3xl border border-[#F1D5E0] bg-white shadow-sm">

            <div class="border-b border-[#F1D5E0] bg-gradient-to-r from-[#FCE7F0] to-white px-6 py-4">
                <h2 class="text-lg font-bold text-[#542D3D]">
                    Data Ibu
                </h2>
                <p class="mt-0.5 text-sm text-slate-600">
                    Data registrasi ibu yang tercatat di sistem.
                </p>
            </div>

            <dl class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">

                {!! $field('NIK', $user->ibu?->nik, 'idcard') !!}

                {!! $field('Tempat, Tanggal Lahir', $user->ibu?->ttl(), 'calendar') !!}

                {!! $field('Status Kehamilan', $user->ibu?->status_kehamilan?->label(), 'heart') !!}

                @if ($hamil)
                    {!! $field(
                        'Usia Kehamilan',
                        $user->ibu?->usia_kehamilan
                            ? $user->ibu->usia_kehamilan . ' minggu'
                            : null,
                        'clock'
                    ) !!}

                    {!! $field(
                        'HPL (Hari Perkiraan Lahir)',
                        $user->ibu?->hpl?->format('d-m-Y'),
                        'calendar'
                    ) !!}
                @endif

            </dl>
        </section>
    @endif

</div>
@endsection
