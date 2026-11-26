
@extends('layouts.app')

@section('title', 'Edit Pemeriksaan Posyandu')

@section('content')
<div class="min-h-screen bg-[#FFF8FA] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">

        {{-- Judul halaman --}}
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-[#542D3D]">
                Edit Pemeriksaan Anak
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Posyandu / Edit
            </p>
        </div>

        {{-- Form utama --}}
        <form
            action="{{ route('bidan.data-posyandu.pemeriksaan.update', [
                'kegiatan' => $kegiatan->id,
                'anak' => $anak->id,
                'pemeriksaan' => $pemeriksaan->id,
            ]) }}"
            method="POST"
            class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6"
        >
            @csrf
            @method('PUT')

            {{-- Informasi anak --}}
            <div class="mb-5 border-b border-gray-200 pb-4">
                <p class="text-sm text-gray-700">
                    <span class="font-semibold">Anak:</span>
                    {{ $anak->nama_anak }}
                    - {{ $umurBulan }} bulan
                    - <span class="font-semibold">Ibu:</span>
                    {{ $anak->ibu?->user?->name ?? '-' }}
                </p>
            </div>

            {{-- Ringkasan error --}}
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-3">
                    <p class="mb-2 text-sm font-semibold text-red-700">
                        Ada data yang perlu diperbaiki:
                    </p>

                    <ul class="list-inside list-disc space-y-1 text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Data pengukuran --}}
            <div class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">

                <div>
                    <label for="tgl_pemeriksaan" class="mb-1.5 block text-xs font-medium text-gray-700">
                        Tanggal Pemeriksaan
                    </label>
                    <input
                        type="date"
                        id="tgl_pemeriksaan"
                        name="tgl_pemeriksaan"
                        value="{{ old('tgl_pemeriksaan', $pemeriksaan->tgl_pemeriksaan?->format('Y-m-d')) }}"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#E99AB7]"
                    >
                    @error('tgl_pemeriksaan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="berat_badan" class="mb-1.5 block text-xs font-medium text-gray-700">
                        Berat Badan (kg)
                    </label>
                    <input
                        type="number"
                        id="berat_badan"
                        name="berat_badan"
                        value="{{ old('berat_badan', $pemeriksaan->berat_badan) }}"
                        min="0.1"
                        max="100"
                        step="0.01"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#E99AB7]"
                    >
                    @error('berat_badan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tinggi_badan" class="mb-1.5 block text-xs font-medium text-gray-700">
                        Tinggi Badan (cm)
                    </label>
                    <input
                        type="number"
                        id="tinggi_badan"
                        name="tinggi_badan"
                        value="{{ old('tinggi_badan', $pemeriksaan->tinggi_badan) }}"
                        min="1"
                        max="200"
                        step="0.01"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#E99AB7]"
                    >
                    @error('tinggi_badan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="lingkar_kepala" class="mb-1.5 block text-xs font-medium text-gray-700">
                        Lingkar Kepala (cm)
                    </label>
                    <input
                        type="number"
                        id="lingkar_kepala"
                        name="lingkar_kepala"
                        value="{{ old('lingkar_kepala', $pemeriksaan->lingkar_kepala) }}"
                        min="1"
                        max="100"
                        step="0.01"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#E99AB7]"
                    >
                    @error('lingkar_kepala')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="lila" class="mb-1.5 block text-xs font-medium text-gray-700">
                        LILA (cm)
                    </label>
                    <input
                        type="number"
                        id="lila"
                        name="lila"
                        value="{{ old('lila', $pemeriksaan->lila) }}"
                        min="1"
                        max="100"
                        step="0.01"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#E99AB7]"
                    >
                    @error('lila')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="catatan" class="mb-1.5 block text-xs font-medium text-gray-700">
                        Catatan
                    </label>
                    <input
                        type="text"
                        id="catatan"
                        name="catatan"
                        value="{{ old('catatan', $pemeriksaan->catatan) }}"
                        maxlength="5000"
                        placeholder="Catatan pemeriksaan"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#E99AB7]"
                    >
                    @error('catatan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Imunisasi --}}
            <div class="mt-5 border-t border-gray-200 pt-4">
                @include('bidan.pemeriksaan-anak.partials.imunisasi')
            </div>

            {{-- Vitamin --}}
            <div class="mt-5 border-t border-gray-200 pt-4">
                @include('bidan.pemeriksaan-anak.partials.vitamin')
            </div>

            {{-- Tombol aksi --}}
            <div class="mt-7 flex justify-end gap-3 border-t border-gray-200 pt-5">
            <a href="{{ route('bidan.data-posyandu.index') }}"
            class="inline-flex items-center rounded-xl border-2 border-[#E99AB7] bg-white px-5 py-2.5 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">
                Batal
            </a>

                <button
                    type="submit"
                    class="rounded-lg bg-[#C9517E] px-6 py-2 text-sm font-semibold text-white hover:bg-[#ad416b]"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
