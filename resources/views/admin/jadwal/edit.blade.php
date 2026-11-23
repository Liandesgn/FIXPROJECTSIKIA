
@extends('layouts.app')

@section('title', 'Edit Jadwal')

@section('content')
<div class="min-h-screen bg-[#FFF8FA] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">

        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('admin.jadwal.index') }}"
               class="text-sm font-medium text-[#C9517E] hover:underline">
                ← Kembali ke Daftar Jadwal
            </a>

            <h1 class="mt-3 text-2xl font-bold text-[#542D3D]">
                Edit Jadwal Kegiatan
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui informasi dan penugasan jadwal kegiatan.
            </p>
        </div>

        {{-- Error Validasi --}}
        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4">
                <p class="font-semibold text-red-700">
                    Terdapat kesalahan pada pengisian form:
                </p>
                <ul class="mt-2 list-inside list-disc text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.jadwal.update', $jadwal->id_jadwal) }}"
              method="POST"
              class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Jenis Jadwal --}}
            <div class="rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">
                <h2 class="mb-4 font-semibold text-[#542D3D]">
                    1. Jenis Jadwal
                </h2>

                <label for="tipe_jadwal" class="mb-2 block text-sm font-medium text-gray-700">
                    Jenis Jadwal
                </label>

                <input type="text"
                       value="{{ $detail['tipe_jadwal'] === 'posyandu' ? 'Jadwal Posyandu' : 'Jadwal Pemeriksaan Kehamilan' }}"
                       disabled
                       class="w-full rounded-xl border border-gray-300 bg-gray-100 px-4 py-3 text-sm text-gray-600">

                <p class="mt-2 text-xs text-gray-400">
                    Jenis jadwal tidak dapat diubah setelah dibuat.
                </p>
            </div>

            {{-- Informasi Umum --}}
            <div class="rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">
                <h2 class="mb-4 font-semibold text-[#542D3D]">
                    2. Informasi Kegiatan
                </h2>

                <div class="grid gap-5 sm:grid-cols-2">

                    <div class="sm:col-span-2">
                        <label for="jenis_kegiatan" class="mb-2 block text-sm font-medium text-gray-700">
                            Nama / Jenis Kegiatan <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="jenis_kegiatan" id="jenis_kegiatan"
                               value="{{ old('jenis_kegiatan', $jadwal->jenis_kegiatan) }}"
                               required
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-2 focus:ring-pink-100">
                    </div>

                    <div>
                        <label for="tanggal" class="mb-2 block text-sm font-medium text-gray-700">
                            Tanggal <span class="text-red-500">*</span>
                        </label>

                        <input type="date" name="tanggal" id="tanggal"
                               value="{{ old('tanggal', \Carbon\Carbon::parse($jadwal->tanggal)->format('Y-m-d')) }}"
                               required
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-2 focus:ring-pink-100">
                    </div>

                    <div>
                        <label for="waktu" class="mb-2 block text-sm font-medium text-gray-700">
                            Waktu <span class="text-red-500">*</span>
                        </label>

                        <input type="time" name="waktu" id="waktu"
                               value="{{ old('waktu', substr($jadwal->waktu, 0, 5)) }}"
                               required
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-2 focus:ring-pink-100">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="lokasi" class="mb-2 block text-sm font-medium text-gray-700">
                            Lokasi Kegiatan <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="lokasi" id="lokasi"
                               value="{{ old('lokasi', $jadwal->lokasi) }}"
                               required
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-2 focus:ring-pink-100">
                    </div>

                </div>
            </div>

            {{-- Form Posyandu --}}
            @if ($detail['tipe_jadwal'] === 'posyandu')

                <div class="rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">

                    <h2 class="mb-2 font-semibold text-[#542D3D]">
                        3. Penugasan Jadwal Posyandu
                    </h2>

                    <p class="mb-5 text-sm text-gray-500">
                        Jadwal ditujukan untuk seluruh RT dalam RW yang dipilih.
                    </p>

                    <div class="space-y-5">

                        <div>
                            <label for="rw_id" class="mb-2 block text-sm font-medium text-gray-700">
                                RW Tujuan <span class="text-red-500">*</span>
                            </label>

                            <select name="rw_id" id="rw_id" required
                                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-2 focus:ring-pink-100">
                                <option value="">-- Pilih RW --</option>

                                @foreach ($rws as $rw)
                                    <option value="{{ $rw->id }}"
                                        @selected((string) old('rw_id', $detail['rw_id']) === (string) $rw->id)>
                                        RW {{ $rw->nomor_rw }}
                                        - {{ $rw->kelurahanDesa?->nama_kelurahan_desa ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="id_kader" class="mb-2 block text-sm font-medium text-gray-700">
                                Kader Penanggung Jawab <span class="text-red-500">*</span>
                            </label>

                            <select name="id_kader" id="id_kader" required
                                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-2 focus:ring-pink-100">
                                <option value="">-- Pilih Kader --</option>

                                @foreach ($kaders as $kader)
                                    <option value="{{ $kader->id }}"
                                        @selected((string) old('id_kader', $detail['id_kader']) === (string) $kader->id)>
                                        {{ $kader->user?->name ?? 'Kader #' . $kader->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

            @else

                {{-- Form Pemeriksaan --}}
                <div class="rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">

                    <h2 class="mb-2 font-semibold text-[#542D3D]">
                        3. Penugasan Pemeriksaan Kehamilan
                    </h2>

                    <p class="mb-5 text-sm text-gray-500">
                        Tentukan wilayah kerja untuk jadwal pemeriksaan.
                    </p>

                    <label for="id_wilayah_kerja" class="mb-2 block text-sm font-medium text-gray-700">
                        Wilayah Kerja <span class="text-red-500">*</span>
                    </label>

                    <select name="id_wilayah_kerja" id="id_wilayah_kerja" required
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-2 focus:ring-pink-100">
                        <option value="">-- Pilih Wilayah --</option>

                        @foreach ($wilayahs as $wilayah)
                            <option value="{{ $wilayah->id }}"
                                @selected((string) old('id_wilayah_kerja', $detail['id_wilayah_kerja']) === (string) $wilayah->id)>
                                {{ $wilayah->kelurahanDesa?->nama_kelurahan_desa ?? $wilayah->nama_wilayah }}
                                - {{ $wilayah->nama_wilayah }}
                            </option>
                        @endforeach
                    </select>

                </div>

            @endif

            {{-- Bidan --}}
            <div class="rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">

                <h2 class="mb-4 font-semibold text-[#542D3D]">
                    4. Bidan Pemeriksa
                </h2>

                <label for="id_bidan" class="mb-2 block text-sm font-medium text-gray-700">
                    Bidan yang Bertugas <span class="text-red-500">*</span>
                </label>

                <select name="id_bidan" id="id_bidan" required
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-2 focus:ring-pink-100">
                    <option value="">-- Pilih Bidan --</option>

                    @foreach ($bidans as $bidan)
                        <option value="{{ $bidan->id }}"
                            @selected((string) old('id_bidan', $detail['id_bidan']) === (string) $bidan->id)>
                            {{ $bidan->user?->name ?? 'Bidan #' . $bidan->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Catatan Pemeriksaan --}}
            @if ($detail['tipe_jadwal'] === 'pemeriksaan')
                <div class="rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">

                    <h2 class="mb-4 font-semibold text-[#542D3D]">
                        5. Catatan Tambahan
                    </h2>

                    <label for="catatan" class="mb-2 block text-sm font-medium text-gray-700">
                        Catatan
                    </label>

                    <textarea name="catatan" id="catatan" rows="4"
                              placeholder="Masukkan catatan tambahan..."
                              class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-2 focus:ring-pink-100">{{ old('catatan', $detail['catatan']) }}</textarea>
                </div>
            @endif

            {{-- Tombol --}}
            <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">

                <a href="{{ route('admin.jadwal.index') }}"
                   class="rounded-xl border border-gray-300 bg-white px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-50">
                    Batal
                </a>

                <button type="submit"
                        class="rounded-xl bg-[#C9517E] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#B7436D]">
                    Simpan Perubahan
                </button>

            </div>

        </form>
    </div>
</div>
@endsection
