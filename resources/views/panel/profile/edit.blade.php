@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
@php
    $roleKey   = \Illuminate\Support\Str::before($user->primaryRole()?->dashboard() ?? '', '.');
    $roleLabel = $roleKey === 'orang-tua' ? 'Ibu' : ($user->primaryRole()?->label() ?? '-');
    $isIbu     = $user->isOrangTua();
    $initial   = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1));

    // Data ibu (hanya untuk role Ibu). sedangHamil() milik model Ibu, bukan User.
    $ibu   = $isIbu ? $user->ibu : null;
    $hamil = $ibu && method_exists($ibu, 'sedangHamil') ? $ibu->sedangHamil() : false;

    // Satu baris informasi: label kecil berwarna + nilai tegas
    $field = fn (string $label, $value) =>
        '<div class="min-w-0 rounded-xl border border-[#F1D5E0] bg-[#FFF7FA] px-4 py-3">'
        . '<dt class="text-xs font-bold uppercase tracking-wide text-[#C9517E]">' . e($label) . '</dt>'
        . '<dd class="mt-1 break-words text-sm font-semibold text-[#542D3D]">' . e(filled($value) ? $value : '-') . '</dd>'
        . '</div>';
@endphp

<div class="mx-auto w-full max-w-4xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Profil Saya</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Informasi akun yang terdaftar di SI-KIA</p>
    </div>

    {{-- Kartu profil utama --}}
    <section class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Banner + identitas --}}
        <div class="relative bg-gradient-to-r from-[#C9517E] to-[#E99AB7] px-5 pb-16 pt-6 sm:px-8">
            <div class="pointer-events-none absolute -right-10 -top-14 h-40 w-40 rounded-full border border-white/25"></div>
            <div class="pointer-events-none absolute -bottom-16 right-28 h-32 w-32 rounded-full bg-white/10"></div>
            <p class="relative text-xs font-semibold uppercase tracking-wider text-white">Akun SI-KIA</p>
        </div>

        <div class="relative px-5 pb-6 sm:px-8">
            <div class="-mt-12 flex flex-col gap-4 sm:-mt-14 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex items-end gap-4">
                    <span class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full border-4 border-white bg-[#C9517E] text-4xl font-bold text-white shadow-lg sm:h-28 sm:w-28">
                        {{ $initial }}
                    </span>
                    <div class="min-w-0 pb-1">
                        <h2 class="break-words text-xl font-bold text-[#542D3D] sm:text-2xl">{{ $user->name }}</h2>
                        <p class="break-all text-sm font-medium text-slate-700">{{ $user->email }}</p>
                    </div>
                </div>

                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-[#FCE7F0] px-4 py-1.5 text-sm font-bold text-[#C9517E]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3Z"/></svg>
                    {{ $roleLabel }}
                </span>
            </div>

            <h3 class="mb-3 mt-7 text-sm font-bold text-[#542D3D]">Data Akun</h3>
            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                {!! $field('Nama Lengkap', $user->name) !!}
                {!! $field('Email', $user->email) !!}
                {!! $field('Nomor HP', $user->phone) !!}
                {!! $field('Role', $roleLabel) !!}
            </dl>
        </div>
    </section>

    {{-- Data Ibu (khusus role Ibu) --}}
    @if ($isIbu)
        <section class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm sm:p-8">
            <h3 class="text-base font-bold text-[#542D3D]">Data Ibu</h3>
            <p class="mt-0.5 text-sm text-slate-700">Data registrasi ibu yang tercatat di sistem.</p>

            <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                {!! $field('NIK', $ibu?->nik) !!}
                {!! $field('Tempat, Tanggal Lahir', $ibu?->ttl()) !!}
                {!! $field('Status Kehamilan', $ibu?->status_kehamilan?->label()) !!}

                {{-- Usia kehamilan & HPL hanya tampil jika sedang hamil --}}
                @if ($hamil)
                    {!! $field('Usia Kehamilan', $ibu?->usia_kehamilan ? $ibu->usia_kehamilan . ' minggu' : null) !!}
                    {!! $field('HPL (Hari Perkiraan Lahir)', $ibu?->hpl?->format('d-m-Y')) !!}
                @endif
            </dl>
        </section>
    @endif
</div>
@endsection