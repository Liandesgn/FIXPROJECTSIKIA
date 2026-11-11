@extends('layouts.app')

@section('title', 'Edit Wilayah Kerja')

@section('content')
@php
    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
@endphp

<div class="mx-auto w-full max-w-3xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Wilayah</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Kelola Wilayah / Edit</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-6 py-4">
            <h2 class="text-lg font-bold text-[#542D3D]">Edit Wilayah Kerja</h2>
            <p class="mt-0.5 text-sm text-slate-600">Ubah data wilayah.</p>
        </div>

        <form action="{{ route('superadmin.wilayah-kerja.update', $wilayah) }}" method="POST" class="space-y-5 p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="nama_wilayah" class="text-sm font-semibold text-[#542D3D]">Nama Wilayah <span class="text-[#C9517E]">*</span></label>
                <input id="nama_wilayah" type="text" name="nama_wilayah" value="{{ old('nama_wilayah', $wilayah->nama_wilayah) }}" maxlength="100"
                       placeholder="Desa. a Kec. a" class="{{ $fieldClass }}" required>
                @error('nama_wilayah')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="kelurahan" class="text-sm font-semibold text-[#542D3D]">Kelurahan / Desa <span class="text-[#C9517E]">*</span></label>
                    <input id="kelurahan" type="text" name="kelurahan" value="{{ old('kelurahan', $wilayah->kelurahan) }}" maxlength="100"
                           placeholder="Nama kelurahan/desa" class="{{ $fieldClass }}" required>
                    @error('kelurahan')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="kecamatan" class="text-sm font-semibold text-[#542D3D]">Kecamatan <span class="text-[#C9517E]">*</span></label>
                    <input id="kecamatan" type="text" name="kecamatan" value="{{ old('kecamatan', $wilayah->kecamatan) }}" maxlength="100"
                           placeholder="Nama kecamatan" class="{{ $fieldClass }}" required>
                    @error('kecamatan')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('superadmin.wilayah-kerja.index') }}"
                   class="inline-flex justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection