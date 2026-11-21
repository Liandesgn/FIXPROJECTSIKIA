@extends('layouts.app')

@section('title', 'Data Anak')

@section('content')
@php
    $namaDepan  = \Illuminate\Support\Str::before(auth()->user()->name, ' ');
    $jumlahAnak = collect($detailAnak)->count();
@endphp

<div class="mx-auto w-full max-w-6xl space-y-8">

    @if ($jumlahAnak > 0)
        {{-- Sapaan sesuai nama ibu (wireframe: "Halo, Siti") --}}
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
            Halo, {{ $namaDepan }}
        </h1>

        @foreach ($detailAnak as $detail)
            @include('data-anak.partials.detail-anak', [
                'anak' => $detail['anak'],
                'base' => $base,
                'riwayatPemeriksaan' => $detail['riwayatPemeriksaan'],
                'pemeriksaanTerakhir' => $detail['pemeriksaanTerakhir'],
                'tampilanGabungan' => true,
                'nomor' => $loop->iteration,
                'jumlahAnak' => $jumlahAnak,
            ])
        @endforeach
    @else
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Anak</h1>

        <section class="rounded-2xl border border-[#F1D5E0] bg-white p-6 text-center shadow-sm">
            <h2 class="text-base font-bold text-[#542D3D]">Data Anak Belum Tersedia</h2>
            <p class="mt-2 text-sm text-slate-600">
                Belum ada data anak yang terhubung dengan akun Anda. Silakan hubungi admin.
            </p>
        </section>
    @endif

</div>
@endsection