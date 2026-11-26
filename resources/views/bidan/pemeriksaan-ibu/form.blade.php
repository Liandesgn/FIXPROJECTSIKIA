
@extends('layouts.app')

@section('title', $pemeriksaan ? 'Edit Pemeriksaan Ibu Hamil' : 'Input Pemeriksaan Ibu Hamil')

@section('content')
<div class="min-h-screen bg-[#FFF8FA] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">

        {{-- Judul --}}
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-[#542D3D]">
                {{ $pemeriksaan ? 'Edit Pemeriksaan ' : 'Input Pemeriksaan ' }}
                {{ $ibu->user?->name ?? 'Ibu Hamil' }}
            </h1>

            <p class="mt-1 text-sm text-gray-600">
                Pemeriksaan / {{ $pemeriksaan ? 'Edit' : 'Input' }}
            </p>
        </div>

        {{-- Pesan validasi --}}
        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">
                    Data belum valid. Mohon perbaiki isian yang ditandai.
                </p>

                <ul class="mt-2 list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form
            method="POST"
            action="{{ $pemeriksaan
                ? route('bidan.pemeriksaan-ibu.update', [$ibu->id, $pemeriksaan->id])
                : route('bidan.pemeriksaan-ibu.store', $ibu->id) }}"
            class="rounded-xl border border-gray-300 bg-white p-5 sm:p-6"
        >
            @csrf

            @if($pemeriksaan)
                @method('PUT')
            @endif

            {{-- Identitas --}}
            <div class="mb-7 flex flex-wrap items-center gap-3 text-sm">
                <span class="font-medium text-gray-700">Nama :</span>
                <span class="text-gray-800">
                    {{ $ibu->user?->name ?? '-' }}
                </span>
            </div>

            {{-- Tanggal, usia, berat badan --}}
            <div class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-3">

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-700">
                        Tanggal Pemeriksaan
                    </label>

                    <input
                        type="text"
                        value="{{ $tanggalPemeriksaan->format('d/m/Y') }}"
                        readonly
                        class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-3 text-sm text-gray-700"
                    >
                    <p class="mt-1 text-[10px] text-gray-400">
                        Diisi otomatis oleh sistem
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-700">
                        Usia Kehamilan (otomatis)
                    </label>

                    <input
                        type="text"
                        value="{{ $usiaKehamilan }} minggu"
                        readonly
                        class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-3 text-sm text-gray-700"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-700">
                        Berat Badan (kg)<span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        name="berat_badan"
                        step="0.01"
                        min="20"
                        max="250"
                        value="{{ old('berat_badan', $pemeriksaan?->berat_badan) }}"
                        required
                        class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]"
                        placeholder="Contoh: 62.0"
                    >
                </div>

                {{-- Tinggi badan, LILA, Hb --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-700">
                        Tinggi Badan (cm)<span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        name="tinggi_badan"
                        step="0.01"
                        min="100"
                        max="220"
                        value="{{ old('tinggi_badan', $pemeriksaan?->tinggi_badan) }}"
                        required
                        class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]"
                        placeholder="Contoh: 152"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-700">
                        LILA (cm)<span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        name="lila"
                        step="0.01"
                        min="10"
                        max="60"
                        value="{{ old('lila', $pemeriksaan?->lila) }}"
                        required
                        class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]"
                        placeholder="Contoh: 24.0"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-700">
                        Hb (g/dL)<span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        name="hb"
                        step="0.01"
                        min="3"
                        max="25"
                        value="{{ old('hb', $pemeriksaan?->hb) }}"
                        required
                        class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]"
                        placeholder="Contoh: 11.5"
                    >
                </div>

                {{-- Tekanan darah dan proteinuria --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-700">
                        Tekanan Darah - Sistolik (mmHg)<span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        name="tekanan_sistolik"
                        min="50"
                        max="250"
                        value="{{ old('tekanan_sistolik', $pemeriksaan?->tekanan_sistolik) }}"
                        required
                        class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]"
                        placeholder="Contoh: 110"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-700">
                        Tekanan Darah - Diastolik (mmHg)<span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        name="tekanan_diastolik"
                        min="30"
                        max="160"
                        value="{{ old('tekanan_diastolik', $pemeriksaan?->tekanan_diastolik) }}"
                        required
                        class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]"
                        placeholder="Contoh: 70"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-700">
                        Proteinuria<span class="text-red-500">*</span>
                    </label>

                    <select
                        name="proteinuria"
                        required
                        class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]"
                    >
                        <option value="">Pilih hasil</option>

                        @foreach(['negatif', '+', '1+', '2+', '3+', '4+'] as $nilai)
                            <option
                                value="{{ $nilai }}"
                                @selected(old('proteinuria', $pemeriksaan?->proteinuria) === $nilai)
                            >
                                {{ ucfirst($nilai) }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            {{-- Gejala berat --}}
            <div class="mt-3">
                <label class="flex items-start gap-2 text-[11px] text-gray-500">
                    <input
                        type="hidden"
                        name="gejala_berat"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="gejala_berat"
                        value="1"
                        @checked((bool) old('gejala_berat', $pemeriksaan?->gejala_berat ?? false))
                        class="mt-0.5 rounded border-gray-300 text-[#C9517E] focus:ring-[#C9517E]"
                    >

                    <span>
                        Gejala berat: sakit kepala hebat / pandangan kabur / nyeri ulu hati
                    </span>
                </label>
            </div>

            {{-- Keluhan dan kondisi kehamilan --}}
            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-700">
                        Keluhan
                    </label>

                    <textarea
                        name="keluhan"
                        rows="2"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]"
                        placeholder="Tuliskan keluhan ibu (jika ada)"
                    >{{ old('keluhan', $pemeriksaan?->keluhan) }}</textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-700">
                        Kondisi Kehamilan
                    </label>

                    <textarea
                        name="kondisi_kehamilan"
                        rows="2"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]"
                        placeholder="Catatan kondisi"
                    >{{ old('kondisi_kehamilan', $pemeriksaan?->kondisi_kehamilan) }}</textarea>
                </div>

            </div>

            {{-- Tombol --}}
            <div class="mt-8 flex justify-end gap-2">
                <a
                    href="{{ route('bidan.pemeriksaan-ibu.index') }}"
                    class="rounded-lg border border-gray-300 px-6 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="rounded-lg bg-[#C9517E] px-7 py-2 text-sm font-semibold text-white hover:bg-[#ad416b]"
                >
                    Simpan
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
