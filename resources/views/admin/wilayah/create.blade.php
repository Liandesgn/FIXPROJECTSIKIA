@extends('layouts.app')

@section('title', 'Tambah Wilayah Kerja')

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-[#542D3D]">Tambah Wilayah Kerja</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Kelola Wilayah Kerja / Tambah</p>
    </div>

    <form method="POST" action="{{ route('admin.wilayah-kerja.store') }}"
          class="space-y-5 rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">
        @csrf
        @include('admin.wilayah.partials.form')
    </form>
</div>
@endsection