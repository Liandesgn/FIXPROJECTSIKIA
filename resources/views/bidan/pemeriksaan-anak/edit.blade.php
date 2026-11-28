@extends('layouts.app')

@section('title', 'Edit Pemeriksaan Posyandu')

@section('content')
@php
    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $errorFieldClass = 'mt-1.5 w-full rounded-xl border border-red-400 bg-red-50 px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-red-500 focus:ring-4 focus:ring-red-200';
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';

    // Kolom yang gagal validasi diberi garis merah
    $field = fn (string $nama) => $errors->has($nama) ? $errorFieldClass : $fieldClass;
@endphp

<div class="mx-auto w-full max-w-4xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
            Edit Pemeriksaan {{ $anak->nama_anak }}
        </h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Posyandu / Edit</p>
    </div>

    {{-- Ringkasan error --}}
    @if ($errors->any())
        <div class="flex gap-3 rounded-xl border-2 border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 2.4 17.5A2 2 0 0 0 4.1 20.5h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
            <div>
                <p class="font-semibold">Data belum valid. Mohon perbaiki isian yang ditandai.</p>
                <ul class="mt-1.5 list-inside list-disc space-y-0.5 font-medium">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Form utama --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <form action="{{ route('bidan.data-posyandu.pemeriksaan.update', [
                    'kegiatan' => $kegiatan->id,
                    'anak' => $anak->id,
                    'pemeriksaan' => $pemeriksaan->id,
                ]) }}"
              method="POST"
              class="space-y-5 p-4 sm:p-6">
            @csrf
            @method('PUT')

            {{-- Informasi anak --}}
            <div class="border-b border-[#F1D5E0] pb-4 text-sm text-slate-700">
                <span class="font-semibold text-slate-600">Anak :</span>
                <span class="ml-1 font-bold text-[#542D3D]">{{ $anak->nama_anak }}</span>
                <span class="mx-1.5 text-slate-400">-</span>
                <span class="font-bold text-[#542D3D]">{{ $umurBulan }} bulan</span>
                <span class="mx-1.5 text-slate-400">-</span>
                <span class="font-semibold text-slate-600">Ibu:</span>
                <span class="ml-1 font-bold text-[#542D3D]">{{ $anak->ibu?->user?->name ?? '-' }}</span>
            </div>

            {{-- Data pengukuran --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

                <div>
                    <label for="tgl_pemeriksaan" class="{{ $labelClass }}">Tanggal Pemeriksaan</label>
                    <input type="date" id="tgl_pemeriksaan" name="tgl_pemeriksaan"
                           value="{{ old('tgl_pemeriksaan', $pemeriksaan->tgl_pemeriksaan?->format('Y-m-d')) }}"
                           required class="{{ $field('tgl_pemeriksaan') }}">
                    @error('tgl_pemeriksaan')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="berat_badan" class="{{ $labelClass }}">Berat Badan (kg)</label>
                    <input type="number" id="berat_badan" name="berat_badan"
                           value="{{ old('berat_badan', $pemeriksaan->berat_badan) }}"
                           min="0.1" max="100" step="0.01" required
                           class="{{ $field('berat_badan') }}">
                    @error('berat_badan')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tinggi_badan" class="{{ $labelClass }}">Tinggi Badan (cm)</label>
                    <input type="number" id="tinggi_badan" name="tinggi_badan"
                           value="{{ old('tinggi_badan', $pemeriksaan->tinggi_badan) }}"
                           min="1" max="200" step="0.01" required
                           class="{{ $field('tinggi_badan') }}">
                    @error('tinggi_badan')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="lingkar_kepala" class="{{ $labelClass }}">Lingkar Kepala (cm)</label>
                    <input type="number" id="lingkar_kepala" name="lingkar_kepala"
                           value="{{ old('lingkar_kepala', $pemeriksaan->lingkar_kepala) }}"
                           min="1" max="100" step="0.01"
                           class="{{ $field('lingkar_kepala') }}">
                    @error('lingkar_kepala')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="lila" class="{{ $labelClass }}">LILA (cm)</label>
                    <input type="number" id="lila" name="lila"
                           value="{{ old('lila', $pemeriksaan->lila) }}"
                           min="1" max="100" step="0.01"
                           class="{{ $field('lila') }}">
                    @error('lila')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="catatan" class="{{ $labelClass }}">Catatan</label>
                    <input type="text" id="catatan" name="catatan"
                           value="{{ old('catatan', $pemeriksaan->catatan) }}"
                           maxlength="5000" placeholder="Catatan pemeriksaan"
                           class="{{ $field('catatan') }}">
                    @error('catatan')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Imunisasi --}}
            <div class="border-t border-[#F1D5E0] pt-5">
                @include('bidan.pemeriksaan-anak.partials.imunisasi')
            </div>

            {{-- Vitamin --}}
            <div class="border-t border-[#F1D5E0] pt-5">
                @include('bidan.pemeriksaan-anak.partials.vitamin')
            </div>

            {{-- Tombol aksi --}}
            <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('bidan.data-posyandu.index') }}"
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