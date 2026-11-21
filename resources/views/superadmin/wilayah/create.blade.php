@extends('layouts.app')

@section('title', 'Tambah Wilayah Kerja')

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Wilayah</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Kelola Wilayah / Tambah</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-gradient-to-r from-[#FCE7F0] to-[#FFF7FA] px-6 py-4">
            <h2 class="text-lg font-bold text-[#542D3D]">Tambah Wilayah Kerja</h2>
            <p class="mt-0.5 text-sm text-slate-600">Pilih data wilayah sesuai alamat kerja.</p>
        </div>

        <form action="{{ route('superadmin.wilayah-kerja.store') }}" method="POST" class="space-y-5 p-4 sm:p-6">
            @csrf

            @include('superadmin.wilayah.partials.form', ['kecamatans' => $kecamatans ?? collect()])
        </form>
    </div>
</div>
@endsection