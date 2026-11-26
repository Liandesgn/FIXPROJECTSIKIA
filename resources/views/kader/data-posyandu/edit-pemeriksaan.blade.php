
@extends('layouts.app')

@section('title', 'Edit Pemeriksaan Anak')

@section('content')
<div class="min-h-screen bg-white px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">

        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('kader.data-posyandu.show', $kegiatan->id) }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-[#64748B] hover:text-[#C9517E]">
                <span aria-hidden="true">←</span>
                Kembali ke daftar anak
            </a>

            <h1 class="mt-4 text-2xl font-bold tracking-tight text-[#222222]">
                Edit Pemeriksaan Anak
            </h1>

            <p class="mt-1 text-sm text-[#64748B]">
                Perbarui hasil pengukuran anak.
            </p>
        </div>

        {{-- Identitas Anak --}}
        <section class="mb-5 rounded-xl border border-[#D9D9D9] bg-white p-5">
            <h2 class="text-base font-semibold text-[#333333]">
                Identitas Anak
            </h2>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-[#64748B]">Nama Anak</p>
                    <p class="mt-1 text-sm font-medium text-[#333333]">
                        {{ $anak->nama_anak ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-[#64748B]">NIK Anak</p>
                    <p class="mt-1 text-sm font-medium text-[#333333]">
                        {{ $anak->nik_anak ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-[#64748B]">Tanggal Lahir</p>
                    <p class="mt-1 text-sm font-medium text-[#333333]">
                        {{ $anak->tgl_lahir ? \Carbon\Carbon::parse($anak->tgl_lahir)->format('d/m/Y') : '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-[#64748B]">Jenis Kelamin</p>
                    <p class="mt-1 text-sm font-medium text-[#333333]">
                        {{ $anak->jenis_kelamin === 'L' ? 'Laki-laki' : ($anak->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Form Pemeriksaan --}}
        <section class="rounded-xl border border-[#D9D9D9] bg-white p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-semibold text-[#333333]">
                    Data Pemeriksaan
                </h2>

                <p class="mt-1 text-sm text-[#64748B]">
                    Periksa kembali data sebelum menyimpan perubahan.
                </p>
            </div>

            <form method="POST"
                  action="{{ route('kader.data-posyandu.pemeriksaan.update', [
                      'kegiatan' => $kegiatan->id,
                      'anak' => $anak->id,
                      'pemeriksaan' => $pemeriksaan->id,
                  ]) }}">

                @csrf
                @method('PUT')

                <div class="grid gap-5 sm:grid-cols-2">

                    {{-- Tanggal Pemeriksaan --}}
                    <div>
                        <label for="tgl_pemeriksaan"
                               class="mb-1.5 block text-sm font-medium text-[#333333]">
                            Tanggal Pemeriksaan
                        </label>

                        <input
                            type="date"
                            id="tgl_pemeriksaan"
                            name="tgl_pemeriksaan"
                            value="{{ old('tgl_pemeriksaan', optional($pemeriksaan->tgl_pemeriksaan)->format('Y-m-d') ?? $pemeriksaan->tgl_pemeriksaan) }}"
                            required
                            class="w-full rounded-lg border border-[#D1D5DB] px-3 py-2.5 text-sm text-[#333333] outline-none transition focus:border-[#C9517E] focus:ring-2 focus:ring-[#E99AB7]/30"
                        >

                        @error('tgl_pemeriksaan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Berat Badan --}}
                    <div>
                        <label for="berat_badan"
                               class="mb-1.5 block text-sm font-medium text-[#333333]">
                            Berat Badan (kg)
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0.1"
                            max="100"
                            id="berat_badan"
                            name="berat_badan"
                            value="{{ old('berat_badan', $pemeriksaan->berat_badan) }}"
                            required
                            class="w-full rounded-lg border border-[#D1D5DB] px-3 py-2.5 text-sm text-[#333333] outline-none transition focus:border-[#C9517E] focus:ring-2 focus:ring-[#E99AB7]/30"
                        >

                        @error('berat_badan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tinggi Badan --}}
                    <div>
                        <label for="tinggi_badan"
                               class="mb-1.5 block text-sm font-medium text-[#333333]">
                            Tinggi Badan (cm)
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="1"
                            max="200"
                            id="tinggi_badan"
                            name="tinggi_badan"
                            value="{{ old('tinggi_badan', $pemeriksaan->tinggi_badan) }}"
                            required
                            class="w-full rounded-lg border border-[#D1D5DB] px-3 py-2.5 text-sm text-[#333333] outline-none transition focus:border-[#C9517E] focus:ring-2 focus:ring-[#E99AB7]/30"
                        >

                        @error('tinggi_badan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Lingkar Kepala --}}
                    <div>
                        <label for="lingkar_kepala"
                               class="mb-1.5 block text-sm font-medium text-[#333333]">
                            Lingkar Kepala (cm)
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="1"
                            max="100"
                            id="lingkar_kepala"
                            name="lingkar_kepala"
                            value="{{ old('lingkar_kepala', $pemeriksaan->lingkar_kepala) }}"
                            class="w-full rounded-lg border border-[#D1D5DB] px-3 py-2.5 text-sm text-[#333333] outline-none transition focus:border-[#C9517E] focus:ring-2 focus:ring-[#E99AB7]/30"
                        >

                        @error('lingkar_kepala')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- LILA --}}
                    <div>
                        <label for="lila"
                               class="mb-1.5 block text-sm font-medium text-[#333333]">
                            LILA (cm)
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="1"
                            max="100"
                            id="lila"
                            name="lila"
                            value="{{ old('lila', $pemeriksaan->lila) }}"
                            class="w-full rounded-lg border border-[#D1D5DB] px-3 py-2.5 text-sm text-[#333333] outline-none transition focus:border-[#C9517E] focus:ring-2 focus:ring-[#E99AB7]/30"
                        >

                        @error('lila')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Catatan --}}
                    <div class="sm:col-span-2">
                        <label for="catatan"
                               class="mb-1.5 block text-sm font-medium text-[#333333]">
                            Catatan Pemeriksaan
                        </label>

                        <textarea
                            id="catatan"
                            name="catatan"
                            rows="4"
                            class="w-full rounded-lg border border-[#D1D5DB] px-3 py-2.5 text-sm text-[#333333] outline-none transition focus:border-[#C9517E] focus:ring-2 focus:ring-[#E99AB7]/30"
                        >{{ old('catatan', $pemeriksaan->catatan) }}</textarea>

                        @error('catatan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="mt-7 flex flex-wrap justify-end gap-3 border-t border-[#EEEEEE] pt-5">
                    <a href="{{ route('kader.data-posyandu.show', $kegiatan->id) }}"
                       class="inline-flex items-center justify-center rounded-lg border border-[#D1D5DB] bg-white px-5 py-2.5 text-sm font-medium text-[#475569] transition hover:bg-[#F8FAFC]">
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#B74470] focus:outline-none focus:ring-2 focus:ring-[#E99AB7]/40"
                    >
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </section>

    </div>
</div>
@endsection
