
@extends('layouts.app')

@section('title', 'Detail Pemeriksaan Ibu Hamil')

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6">

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
                Detail Pemeriksaan Ibu Hamil
            </h1>
            <p class="mt-1 text-sm font-medium text-slate-600">
                Pemeriksaan Ibu Hamil / Detail
            </p>
        </div>

        <a href="{{ route('bidan.pemeriksaan-ibu.index') }}"
           class="inline-flex justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-5 py-2.5 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">
            Kembali
        </a>
    </div>


    @include('bidan.pemeriksaan-ibu.partials.detail-ibu', [
        'ibu' => $ibu,
        'riwayatPemeriksaanIbu' => $riwayatPemeriksaanIbu,
        'pemeriksaanTerakhir' => $pemeriksaanTerakhir,
        'filterPeriode' => false,
        'base' => 'bidan',
    ])


</div>
@endsection
