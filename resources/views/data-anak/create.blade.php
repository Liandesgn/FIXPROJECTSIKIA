@extends('layouts.app')

@section('title', 'Tambah Data Anak')

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Anak</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Anak / Tambah Data Anak</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-6 py-4">
            <h2 class="text-lg font-bold text-[#542D3D]">Tambah Data Anak</h2>
            <p class="mt-0.5 text-sm text-slate-600">Masukkan data anak dan hubungkan dengan ibunya.</p>
        </div>

        <form method="POST" action="{{ route('admin.data-anak.store') }}" class="space-y-5 p-4 sm:p-6">
            @csrf
            @include('data-anak.partials.form')
        </form>
    </div>
</div>
@endsection