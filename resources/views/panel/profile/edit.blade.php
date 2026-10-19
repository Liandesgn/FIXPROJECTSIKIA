@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
@php
    $roleKey   = \Illuminate\Support\Str::before($user->primaryRole()?->dashboard() ?? '', '.');
    $roleLabel = $roleKey === 'orang-tua' ? 'Ibu' : ($user->primaryRole()?->label() ?? '-');
    $isIbu     = $user->isOrangTua();
    $isAdmin   = $roleKey === 'admin';
    $wk        = $user->wilayahKerja;
    $initial   = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1));

    $hamil = $isIbu && method_exists($user, 'sedangHamil') ? $user->sedangHamil() : false;

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
                {!! $field('NIK', $user->nik) !!}
                {!! $field('Tempat, Tanggal Lahir', $user->ttl()) !!}
                {!! $field('Status Kehamilan', $user->status_kehamilan?->label()) !!}
                @if ($hamil)
                    {!! $field('Usia Kehamilan', $user->usia_kehamilan ? $user->usia_kehamilan . ' minggu' : null) !!}
                    {!! $field('HPL (Hari Perkiraan Lahir)', $user->hpl?->format('d-m-Y')) !!}
                @endif
            </dl>
        </section>
    @endif

    {{-- Wilayah (tidak ditampilkan untuk Admin) --}}
    @unless ($isAdmin)
        <section class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm sm:p-8">
            <h3 class="text-base font-bold text-[#542D3D]">{{ $isIbu ? 'Wilayah & Petugas' : 'Wilayah Kerja' }}</h3>
            <p class="mt-0.5 text-sm text-slate-700">
                {{ $isIbu ? 'Wilayah tempat tinggal dan petugas yang menangani Anda.' : 'Wilayah yang ditugaskan kepada Anda.' }}
            </p>

            <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                {!! $field('Kabupaten', $wk?->kabupaten) !!}
                {!! $field('Kecamatan', $wk?->kecamatan) !!}
                {!! $field('Desa', $wk?->desa) !!}
            </dl>

            @if ($isIbu)
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="flex items-center gap-4 rounded-xl border border-[#F1D5E0] bg-[#FCE7F0] p-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E] shadow-sm">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0M12 15v4m-2-2h4"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#C9517E]">Bidan</p>
                            <p class="break-words text-sm font-bold text-[#542D3D]">{{ $user->bidanWilayah?->name ?? 'Belum ada' }}</p>
                            <p class="text-xs font-medium text-slate-700">{{ $user->bidanWilayah?->phone ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-xl border border-[#F1D5E0] bg-[#FCE7F0] p-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E] shadow-sm">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11a3 3 0 1 0 0-6m-8 6a3 3 0 1 0 0-6m-6 13a6 6 0 0 1 12 0m2-5a6 6 0 0 1 6 5"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-[#C9517E]">Kader</p>
                            <p class="break-words text-sm font-bold text-[#542D3D]">{{ $user->kaderWilayah?->name ?? 'Belum ada' }}</p>
                            <p class="text-xs font-medium text-slate-700">{{ $user->kaderWilayah?->phone ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    @endunless
</div>
@endsection