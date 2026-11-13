@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
@php
    $roleKey   = \Illuminate\Support\Str::before($user->primaryRole()?->dashboard() ?? '', '.');
    $roleLabel = $user->primaryRole()?->label() ?? '-';
    $isIbu     = $user->isOrangTua();
    $wk        = match ($roleKey) {
        'admin' => $user->admin?->wilayahKerja,
        'bidan' => $user->bidan?->wilayahKerja,
        'kader' => $user->kader?->wilayahKerja,
        'ibu' => $user->ibu?->wilayahKerja,
        default => null,
    };
    $initial   = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1));

    $hamil = $isIbu && $user->ibu?->sedangHamil();

    // Ikon kecil untuk setiap label (path SVG 24x24)
    $ikon = [
        'user'     => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0',
        'mail'     => 'M3 7l9 6 9-6M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        'phone'    => 'M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z',
        'shield'   => 'M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3Z',
        'pin'      => 'M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
        'idcard'   => 'M3 6h18v12H3V6Zm4 4h4m-4 4h6m4-4h2',
        'calendar' => 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z',
        'heart'    => 'M12 21s-8-4.5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 6.5-8 11-8 11Z',
        'clock'    => 'M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    ];

    // Satu isian: ikon + label di atas, nilai tegas di dalam kotak (hanya tampil, tidak bisa diedit)
    $field = fn (string $label, $value, string $ikonKey, bool $lebar = false) =>
        '<div class="min-w-0' . ($lebar ? ' sm:col-span-2' : '') . '">'
        . '<dt class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-[#C9517E]">'
        . '<svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">'
        . '<path stroke-linecap="round" stroke-linejoin="round" d="' . $ikon[$ikonKey] . '"/></svg>'
        . e($label) . '</dt>'
        . '<dd class="mt-1.5 break-words rounded-xl border-2 border-[#E99AB7] bg-[#FFF7FA] px-4 py-3 text-base font-bold text-[#542D3D]">'
        . e(filled($value) ? $value : '-') . '</dd>'
        . '</div>';
@endphp

<div class="mx-auto w-full max-w-4xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Profil Saya</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Informasi akun yang terdaftar di SI-KIA</p>
    </div>

    {{-- Data akun: foto di kiri, isian di kanan --}}
    <section class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-6 py-4">
            <h2 class="text-lg font-bold text-[#542D3D]">Data Akun</h2>
        </div>

        <div class="flex flex-col items-center gap-6 p-6 sm:flex-row sm:items-start sm:gap-8">

            {{-- Foto + role --}}
            <div class="flex shrink-0 flex-col items-center gap-3">
                <span class="flex h-28 w-28 items-center justify-center rounded-full bg-[#C9517E] text-5xl font-bold text-white shadow-md ring-4 ring-[#FCE7F0] sm:h-32 sm:w-32"
                      aria-hidden="true">
                    {{ $initial }}
                </span>

                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#C9517E] px-4 py-1.5 text-sm font-bold text-white shadow-sm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['shield'] }}"/></svg>
                    {{ $roleLabel }}
                </span>
            </div>

            <dl class="grid w-full grid-cols-1 gap-5 sm:grid-cols-2">
                {!! $field('Nama', $user->name, 'user') !!}
                {!! $field('Email', $user->email, 'mail') !!}
                {!! $field('Nomor HP', $user->phone, 'phone') !!}
                {!! $field('Role', $roleLabel, 'shield') !!}
                @if ($wk)
                    {!! $field('Wilayah Kerja', $wk->label(), 'pin', true) !!}
                @endif
            </dl>
        </div>
    </section>

    {{-- Data Ibu (khusus role Ibu) --}}
    @if ($isIbu)
        <section class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-6 py-4">
                <h2 class="text-lg font-bold text-[#542D3D]">Data Ibu</h2>
                <p class="mt-0.5 text-sm text-slate-600">Data registrasi ibu yang tercatat di sistem.</p>
            </div>

            <dl class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
                {!! $field('NIK', $user->ibu?->nik, 'idcard') !!}
                {!! $field('Tempat, Tanggal Lahir', $user->ibu?->ttl(), 'calendar') !!}
                {!! $field('Status Kehamilan', $user->ibu?->status_kehamilan?->label(), 'heart') !!}
                @if ($hamil)
                    {!! $field('Usia Kehamilan', $user->ibu?->usia_kehamilan ? $user->ibu?->usia_kehamilan . ' minggu' : null, 'clock') !!}
                    {!! $field('HPL (Hari Perkiraan Lahir)', $user->ibu?->hpl?->format('d-m-Y'), 'calendar') !!}
                @endif
            </dl>
        </section>
    @endif

</div>
@endsection