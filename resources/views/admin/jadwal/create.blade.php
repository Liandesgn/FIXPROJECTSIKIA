 @extends('layouts.app')

@section('title', 'Tambah Jadwal')

@section('content')
@php
    $tipe = old('tipe_jadwal', request('tipe', 'posyandu'));
    $isPemeriksaan = $tipe === 'pemeriksaan';
    $tabKembali = $isPemeriksaan ? 'pemeriksaan' : 'posyandu';
@endphp

<div class="min-h-screen bg-white px-4 py-6 sm:px-6">
    <div class="mx-auto max-w-5xl">

        <h1 class="mb-8 text-2xl font-bold text-gray-900">
            {{ $isPemeriksaan ? 'Tambah Jadwal Pemeriksaan Ibu Hamil' : 'Tambah Jadwal Posyandu' }}
        </h1>

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="font-semibold">Terdapat kesalahan pengisian:</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.jadwal.store') }}" method="POST">
            @csrf

            <input type="hidden" name="tipe_jadwal" value="{{ $tipe }}">

            {{-- FORM PEMERIKSAAN IBU HAMIL --}}
            @if ($isPemeriksaan)

                <div class="grid grid-cols-1 gap-x-5 gap-y-5 md:grid-cols-2">

                    {{-- Kelurahan/Desa --}}
                    <div>
                        <label for="id_wilayah_kerja" class="mb-2 block text-sm font-medium text-gray-700">
                            Kelurahan/desa <span class="text-red-500">*</span>
                        </label>

                        <select name="id_wilayah_kerja" id="id_wilayah_kerja" required
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-pink-200">
                            <option value="">Pilih kelurahan/desa</option>
                            @foreach ($wilayahs as $wilayah)
                                <option value="{{ $wilayah->id }}"
                                    @selected((string) old('id_wilayah_kerja') === (string) $wilayah->id)>
                                    {{ $wilayah->kelurahanDesa?->nama_kelurahan_desa ?? $wilayah->nama_wilayah }}
                                    - {{ $wilayah->nama_wilayah }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jenis Kegiatan --}}
                    <div>
                        <label for="jenis_kegiatan" class="mb-2 block text-sm font-medium text-gray-700">
                            Jenis kegiatan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="jenis_kegiatan" id="jenis_kegiatan"
                               value="{{ old('jenis_kegiatan') }}"
                               placeholder=""
                               required
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-pink-200">
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label for="tanggal" class="mb-2 block text-sm font-medium text-gray-700">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal" id="tanggal"
                               value="{{ old('tanggal') }}"
                               min="{{ date('Y-m-d') }}"
                               required
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-pink-200">
                    </div>

                    {{-- Waktu --}}
                    <div>
                        <label for="waktu" class="mb-2 block text-sm font-medium text-gray-700">
                            Waktu <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="waktu" id="waktu"
                               value="{{ old('waktu', '09:00') }}"
                               required
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-pink-200">
                    </div>

                    {{-- Lokasi --}}
                    <div>
                        <label for="lokasi" class="mb-2 block text-sm font-medium text-gray-700">
                            Lokasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="lokasi" id="lokasi"
                               value="{{ old('lokasi') }}"
                               placeholder=""
                               required
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-pink-200">
                    </div>

                    {{-- Bidan Pemeriksa --}}
                    <div>
                        <label for="id_bidan" class="mb-2 block text-sm font-medium text-gray-700">
                            Bidan Pemeriksa
                        </label>
                        <select name="id_bidan" id="id_bidan"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-pink-200">
                            <option value="">Pilih bidan pemeriksa</option>
                            @foreach ($bidans as $bidan)
                                <option value="{{ $bidan->id }}"
                                    @selected((string) old('id_bidan') === (string) $bidan->id)>
                                    {{ $bidan->user?->name ?? 'Bidan #' . $bidan->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label for="catatan" class="mb-2 block text-sm font-medium text-gray-700">
                            Catatan
                        </label>
                        <input type="text" name="catatan" id="catatan"
                               value="{{ old('catatan') }}"
                               placeholder="Opsional"
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-pink-200">
                    </div>

                </div>

            {{-- FORM POSYANDU --}}
            @else

                <div class="grid grid-cols-1 gap-x-5 gap-y-5 md:grid-cols-2">

                    <div>
                        <label for="rw_id" class="mb-2 block text-sm font-medium text-gray-700">
                            Posyandu (RW) <span class="text-red-500">*</span>
                        </label>
                        <select name="rw_id" id="rw_id" required
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm">
                            <option value="">Pilih Posyandu (RW)</option>
                            @foreach ($rws as $rw)
                                <option value="{{ $rw->id }}"
                                    @selected((string) old('rw_id') === (string) $rw->id)>
                                    Posyandu RW {{ $rw->nomor_rw }} -
                                    {{ $rw->kelurahanDesa?->nama_kelurahan_desa ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="jenis_kegiatan" class="mb-2 block text-sm font-medium text-gray-700">
                            Jenis kegiatan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="jenis_kegiatan" id="jenis_kegiatan"
                               value="{{ old('jenis_kegiatan') }}"
                               placeholder=""
                               required class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label for="tanggal" class="mb-2 block text-sm font-medium text-gray-700">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal" id="tanggal"
                               value="{{ old('tanggal') }}" min="{{ date('Y-m-d') }}"
                               required class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label for="waktu" class="mb-2 block text-sm font-medium text-gray-700">
                            Waktu <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="waktu" id="waktu"
                               value="{{ old('waktu') }}" required
                               class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label for="id_kader" class="mb-2 block text-sm font-medium text-gray-700">
                            Kader <span class="text-red-500">*</span>
                        </label>
                        <select name="id_kader" id="id_kader" required
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm">
                            <option value="">Pilih kader</option>
                            @foreach ($kaders as $kader)
                                <option value="{{ $kader->id }}"
                                    @selected((string) old('id_kader') === (string) $kader->id)>
                                    {{ $kader->user?->name ?? 'Kader #' . $kader->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="id_bidan" class="mb-2 block text-sm font-medium text-gray-700">
                            Bidan Pemeriksa <span class="text-red-500">*</span>
                        </label>
                        <select name="id_bidan" id="id_bidan" required
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm">
                            <option value="">Pilih bidan pemeriksa</option>
                            @foreach ($bidans as $bidan)
                                <option value="{{ $bidan->id }}"
                                    @selected((string) old('id_bidan') === (string) $bidan->id)>
                                    {{ $bidan->user?->name ?? 'Bidan #' . $bidan->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="lokasi" class="mb-2 block text-sm font-medium text-gray-700">
                            Lokasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="lokasi" id="lokasi"
                               value="{{ old('lokasi') }}"
                               placeholder=""
                               required class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm">
                    </div>

                </div>

            @endif

            {{-- Tombol --}}
            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('admin.jadwal.index', ['tab' => $tabKembali]) }}"
                   class="rounded-lg border border-gray-300 bg-white px-7 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Batal
                </a>

                <button type="submit"
                        class="rounded-lg bg-[#555555] px-7 py-3 text-sm font-semibold text-white hover:bg-gray-700">
                    Simpan
                </button>
            </div>

        </form>
    </div>
</div>
@endsection