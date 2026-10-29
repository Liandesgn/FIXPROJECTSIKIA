@extends('layouts.app')

@section('title', 'Tambah Data Anak')

@section('content')
<div class="mx-auto w-full max-w-4xl space-y-4">

    <div>
        <a href="{{ route('admin.data-anak.index') }}"
           class="inline-flex items-center gap-2 rounded-xl border-2 border-[#C9517E] bg-white px-4 py-2 text-sm font-semibold text-[#C9517E] shadow-sm transition hover:bg-[#C9517E] hover:text-white focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"/></svg>
            Kembali ke Data Anak
        </a>
        <p class="mt-3 text-sm font-medium text-slate-700">Data Master / Data Anak / Tambah Data Anak</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-6 py-5 sm:px-8">
            <h1 class="text-xl font-bold text-[#542D3D] sm:text-2xl">Tambah Data Anak</h1>
            <p class="mt-1 text-sm text-slate-700">Pilih ibu, lalu isi data anak.</p>
        </div>

        <form method="POST" action="{{ route('admin.data-anak.store') }}" class="space-y-6 p-6 sm:p-8">
            @csrf
            @include('data-anak.partials.form')
        </form>
    </div>
</div>
@endsection