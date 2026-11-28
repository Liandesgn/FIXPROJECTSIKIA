@extends('layouts.app')

@section('title', $pemeriksaan ? 'Edit Pemeriksaan Ibu Hamil' : 'Input Pemeriksaan Ibu Hamil')

@section('content')
@php
    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $errorFieldClass = 'mt-1.5 w-full rounded-xl border border-red-400 bg-red-50 px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-red-500 focus:ring-4 focus:ring-red-200';
    $lockedClass = 'mt-1.5 w-full cursor-not-allowed rounded-xl border border-[#F1D5E0] bg-slate-100 px-3.5 py-2.5 text-sm font-semibold text-slate-700 outline-none';
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $star = '<span class="text-[#C9517E]">*</span>';

    // Kolom yang gagal validasi diberi garis merah
    $field = fn (string $nama) => $errors->has($nama) ? $errorFieldClass : $fieldClass;

    $aksi = $pemeriksaan ? 'Edit' : 'Input';
    $namaIbu = $ibu->user?->name ?? 'Ibu Hamil';
@endphp

<div class="mx-auto w-full max-w-4xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
            {{ $aksi }} Pemeriksaan {{ $namaIbu }}
        </h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Pemeriksaan / {{ $aksi }}</p>
    </div>

    {{-- Pesan validasi --}}
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

    {{-- Form --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <form method="POST"
              action="{{ $pemeriksaan
                  ? route('bidan.pemeriksaan-ibu.update', [$ibu->id, $pemeriksaan->id])
                  : route('bidan.pemeriksaan-ibu.store', $ibu->id) }}"
              class="space-y-5 p-4 sm:p-6">
            @csrf

            @if ($pemeriksaan)
                @method('PUT')
            @endif

            {{-- Identitas --}}
            <div class="border-b border-[#F1D5E0] pb-4 text-sm">
                <span class="font-semibold text-slate-600">Nama :</span>
                <span class="ml-1 font-bold text-[#542D3D]">{{ $ibu->user?->name ?? '-' }}</span>
            </div>

            {{-- Tanggal, usia, berat badan --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                <div>
                    <label for="tgl_pemeriksaan_tampil" class="{{ $labelClass }}">Tanggal Pemeriksaan</label>
                    <input id="tgl_pemeriksaan_tampil" type="text" value="{{ $tanggalPemeriksaan->format('d/m/Y') }}" readonly
                           class="{{ $lockedClass }}">
                    <p class="mt-1 text-xs font-medium text-slate-600">Diisi otomatis oleh sistem</p>
                </div>

                <div>
                    <label for="usia_kehamilan_tampil" class="{{ $labelClass }}">Usia Kehamilan (otomatis)</label>
                    <input id="usia_kehamilan_tampil" type="text" value="{{ $usiaKehamilan }} minggu" readonly
                           class="{{ $lockedClass }}">
                </div>

                <div>
                    <label for="berat_badan" class="{{ $labelClass }}">Berat Badan (kg) {!! $star !!}</label>
                    <input id="berat_badan" type="number" name="berat_badan" step="0.01" min="20" max="250"
                           value="{{ old('berat_badan', $pemeriksaan?->berat_badan) }}" required
                           placeholder="Contoh: 62.0" class="{{ $field('berat_badan') }}">
                </div>

                {{-- Tinggi badan, LILA, Hb --}}
                <div>
                    <label for="tinggi_badan" class="{{ $labelClass }}">Tinggi Badan (cm) {!! $star !!}</label>
                    <input id="tinggi_badan" type="number" name="tinggi_badan" step="0.01" min="100" max="220"
                           value="{{ old('tinggi_badan', $pemeriksaan?->tinggi_badan) }}" required
                           placeholder="Contoh: 152" class="{{ $field('tinggi_badan') }}">
                </div>

                <div>
                    <label for="lila" class="{{ $labelClass }}">LILA (cm) {!! $star !!}</label>
                    <input id="lila" type="number" name="lila" step="0.01" min="10" max="60"
                           value="{{ old('lila', $pemeriksaan?->lila) }}" required
                           placeholder="Contoh: 24.0" class="{{ $field('lila') }}">
                </div>

                <div>
                    <label for="hb" class="{{ $labelClass }}">Hb (g/dL) {!! $star !!}</label>
                    <input id="hb" type="number" name="hb" step="0.01" min="3" max="25"
                           value="{{ old('hb', $pemeriksaan?->hb) }}" required
                           placeholder="Contoh: 11.5" class="{{ $field('hb') }}">
                </div>

                {{-- Tekanan darah dan proteinuria --}}
                <div>
                    <label for="tekanan_sistolik" class="{{ $labelClass }}">Tekanan Darah - Sistolik (mmHg) {!! $star !!}</label>
                    <input id="tekanan_sistolik" type="number" name="tekanan_sistolik" min="50" max="250"
                           value="{{ old('tekanan_sistolik', $pemeriksaan?->tekanan_sistolik) }}" required
                           placeholder="Contoh: 110" class="{{ $field('tekanan_sistolik') }}">
                </div>

                <div>
                    <label for="tekanan_diastolik" class="{{ $labelClass }}">Tekanan Darah - Diastolik (mmHg) {!! $star !!}</label>
                    <input id="tekanan_diastolik" type="number" name="tekanan_diastolik" min="30" max="160"
                           value="{{ old('tekanan_diastolik', $pemeriksaan?->tekanan_diastolik) }}" required
                           placeholder="Contoh: 70" class="{{ $field('tekanan_diastolik') }}">
                </div>

                <div>
                    <label for="proteinuria" class="{{ $labelClass }}">Proteinuria {!! $star !!}</label>
                    <select id="proteinuria" name="proteinuria" required class="{{ $field('proteinuria') }}">
                        <option value="">Pilih hasil</option>

                        @foreach (['negatif', '+', '1+', '2+', '3+', '4+'] as $nilai)
                            <option value="{{ $nilai }}"
                                    @selected(old('proteinuria', $pemeriksaan?->proteinuria) === $nilai)>
                                {{ ucfirst($nilai) }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            {{-- Gejala berat --}}
            <label class="flex items-start gap-2.5 rounded-xl border border-[#F1D5E0] bg-[#FCE7F0]/40 px-4 py-3 text-sm font-medium text-slate-700">
                <input type="hidden" name="gejala_berat" value="0">

                <input type="checkbox" name="gejala_berat" value="1"
                       @checked((bool) old('gejala_berat', $pemeriksaan?->gejala_berat ?? false))
                       class="mt-0.5 h-4 w-4 rounded border-[#E99AB7] text-[#C9517E] focus:ring-[#C9517E]">

                <span>Gejala berat: sakit kepala hebat / pandangan kabur / nyeri ulu hati</span>
            </label>

            {{-- Keluhan dan kondisi kehamilan --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                <div>
                    <label for="keluhan" class="{{ $labelClass }}">Keluhan</label>
                    <textarea id="keluhan" name="keluhan" rows="2"
                              placeholder="Tuliskan keluhan ibu (jika ada)"
                              class="{{ $field('keluhan') }}">{{ old('keluhan', $pemeriksaan?->keluhan) }}</textarea>
                </div>

                <div>
                    <label for="kondisi_kehamilan" class="{{ $labelClass }}">Kondisi Kehamilan</label>
                    <textarea id="kondisi_kehamilan" name="kondisi_kehamilan" rows="2"
                              placeholder="Catatan kondisi"
                              class="{{ $field('kondisi_kehamilan') }}">{{ old('kondisi_kehamilan', $pemeriksaan?->kondisi_kehamilan) }}</textarea>
                </div>

            </div>

            {{-- Tombol --}}
            <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('bidan.pemeriksaan-ibu.index') }}"
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