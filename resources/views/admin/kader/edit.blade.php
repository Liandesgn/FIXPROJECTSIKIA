@extends('layouts.app')

@section('title', 'Edit Kader')

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Kader</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Manajemen User / Kelola Kader / Edit</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-6 py-4">
            <h2 class="text-lg font-bold text-[#542D3D]">Edit Akun Kader</h2>
            <p class="mt-0.5 text-sm text-slate-600">Perbarui informasi akun dan wilayah kerja kader.</p>
        </div>

        <form action="{{ route('admin.kader.update', $kader->id) }}" method="POST" class="space-y-5 p-4 sm:p-6">
            @csrf
            @method('PUT')

            @include('admin.petugas.partials.fields', [
                'user' => $kader,
                'wilayahs' => $wilayahs
            ])

            <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.kader.index') }}"
                   class="inline-flex w-full justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] sm:w-auto">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex w-full justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 sm:w-auto">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection