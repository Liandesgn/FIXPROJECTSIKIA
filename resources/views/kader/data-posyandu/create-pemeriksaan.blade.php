
@extends('layouts.app')

@section('title', 'Input Pemeriksaan Anak')

@section('content')
<div class="min-h-screen bg-[#FFF8FA] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">

        <div class="mb-5">
            <a href="{{ route($base . '.data-posyandu.show', $kegiatan->id) }}"
               class="text-sm font-medium text-[#C9517E] hover:underline">
                ← Kembali ke daftar anak
            </a>

            <h1 class="mt-3 text-2xl font-bold text-[#542D3D]">
                Input Pemeriksaan Anak
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                Masukkan hasil pengukuran anak pada kegiatan Posyandu.
            </p>
        </div>

        <div class="mb-5 rounded-xl border border-[#F3D5E1] bg-white p-5">
            <h2 class="font-semibold text-[#542D3D]">Identitas Anak</h2>

            <div class="mt-3 grid gap-3 sm:grid-cols-2 text-sm">
                <div>
                    <p class="text-gray-500">Nama anak</p>
                    <p class="font-semibold text-gray-800">{{ $anak->nama_anak }}</p>
                </div>
                <div>
                    <p class="text-gray-500">NIK anak</p>
                    <p class="font-semibold text-gray-800">{{ $anak->nik_anak ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal lahir</p>
                    <p class="font-semibold text-gray-800">
                        {{ $anak->tgl_lahir ? \Carbon\Carbon::parse($anak->tgl_lahir)->format('d-m-Y') : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Jenis kelamin</p>
                    <p class="font-semibold text-gray-800">
                        {{ $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-[#F3D5E1] bg-white p-5">
            <h2 class="mb-1 font-semibold text-[#542D3D]">Data Pemeriksaan</h2>
            <p class="mb-5 text-sm text-gray-500">
                Isi pengukuran sesuai hasil pemeriksaan.
            </p>

            <form method="POST"
            action="{{ route($base . '.data-posyandu.pemeriksaan.store', [
                'kegiatan' => $kegiatan->id,
                'anak' => $anak->id,
            ]) }}">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">

                    <div>
                        <label for="tgl_pemeriksaan" class="mb-1 block text-sm font-medium text-gray-700">
                            Tanggal pemeriksaan
                        </label>
                        <input type="date" id="tgl_pemeriksaan" name="tgl_pemeriksaan"
                               value="{{ old('tgl_pemeriksaan', $kegiatan->tanggal) }}"
                               required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#C9517E] focus:ring-[#C9517E]">
                        @error('tgl_pemeriksaan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="berat_badan" class="mb-1 block text-sm font-medium text-gray-700">
                            Berat badan (kg)
                        </label>
                        <input type="number" step="0.01" min="0.1" max="100"
                               id="berat_badan" name="berat_badan"
                               value="{{ old('berat_badan') }}" required
                               placeholder="Contoh: 12.50"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @error('berat_badan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tinggi_badan" class="mb-1 block text-sm font-medium text-gray-700">
                            Tinggi badan (cm)
                        </label>
                        <input type="number" step="0.01" min="1" max="200"
                               id="tinggi_badan" name="tinggi_badan"
                               value="{{ old('tinggi_badan') }}" required
                               placeholder="Contoh: 85.50"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @error('tinggi_badan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="lingkar_kepala" class="mb-1 block text-sm font-medium text-gray-700">
                            Lingkar kepala (cm)
                        </label>
                        <input type="number" step="0.01" min="1" max="100"
                               id="lingkar_kepala" name="lingkar_kepala"
                               value="{{ old('lingkar_kepala') }}"
                               placeholder="Contoh: 45.00"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @error('lingkar_kepala')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="lila" class="mb-1 block text-sm font-medium text-gray-700">
                            LILA (cm)
                        </label>
                        <input type="number" step="0.01" min="1" max="100"
                               id="lila" name="lila"
                               value="{{ old('lila') }}"
                               placeholder="Contoh: 15.00"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        @error('lila')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    <div class="sm:col-span-2">
                        <label for="catatan" class="mb-1 block text-sm font-medium text-gray-700">
                            Catatan pemeriksaan
                        </label>
                        <textarea id="catatan" name="catatan" rows="4"
                                  placeholder="Tambahkan catatan jika diperlukan..."
                                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <a href="{{ route($base . '.data-posyandu.show', $kegiatan->id) }}"
                       class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                        Batal
                    </a>

                    <button type="submit"
                            class="rounded-lg bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#B74470]">
                        Simpan Pemeriksaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
