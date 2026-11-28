@extends('layouts.app')

@section('title', 'Detail Pemeriksaan Ibu Hamil')

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
            Detail {{ $ibu->user?->name ?? 'Ibu Hamil' }}
        </h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Pemeriksaan / Detail</p>
    </div>

    @include('bidan.pemeriksaan-ibu.partials.detail-ibu', [
        'ibu' => $ibu,
        'riwayatPemeriksaanIbu' => $riwayatPemeriksaanIbu,
        'pemeriksaanTerakhir' => $pemeriksaanTerakhir,
        'filterPeriode' => false,
        'base' => 'bidan',
    ])

    {{-- Tombol kembali --}}
    <div class="flex justify-end">
        <a href="{{ route('bidan.pemeriksaan-ibu.index') }}"
           class="inline-flex justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-2.5 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/30">
            Kembali
        </a>
    </div>

</div>
@endsection