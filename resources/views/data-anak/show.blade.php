@extends('layouts.app')

@section('title', 'Detail Anak')

@section('content')
    @include('data-anak.partials.detail-anak', [
        'anak' => $anak,
        'base' => $base,
        'riwayatPemeriksaan' => $riwayatPemeriksaan,
        'pemeriksaanTerakhir' => $pemeriksaanTerakhir,
        'tampilanGabungan' => false,
    ])
@endsection