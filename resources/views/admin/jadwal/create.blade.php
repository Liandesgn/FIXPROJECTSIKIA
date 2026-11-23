@extends('layouts.app')

@section('title', 'Tambah Jadwal')

@section('content')
@php
    $tipe = old('tipe_jadwal', request('tipe', 'posyandu'));
    $isPemeriksaan = $tipe === 'pemeriksaan';
    $tabKembali = $isPemeriksaan ? 'pemeriksaan' : 'posyandu';

    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $star = '<span class="text-[#C9517E]">*</span>';
@endphp

<div class="mx-auto w-full max-w-3xl space-y-5">

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Jadwal</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Kelola Jadwal / Tambah</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-6 py-4">
            <h2 class="text-lg font-bold text-[#542D3D]">
                {{ $isPemeriksaan ? 'Tambah Jadwal Pemeriksaan Ibu Hamil' : 'Tambah Jadwal Posyandu' }}
            </h2>
            <p class="mt-0.5 text-sm text-slate-600">
                {{ $isPemeriksaan ? 'Tentukan wilayah, waktu, dan bidan pemeriksa.' : 'Tentukan Posyandu (RW), waktu, kader, dan bidan pemeriksa.' }}
            </p>
        </div>

        <form action="{{ route('admin.jadwal.store') }}" method="POST" class="space-y-5 p-4 sm:p-6">
            @csrf

            <input type="hidden" name="tipe_jadwal" value="{{ $tipe }}">

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold">Terdapat kesalahan pengisian:</p>
                    <ul class="mt-2 list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- FORM PEMERIKSAAN IBU HAMIL --}}
            @if ($isPemeriksaan)

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    {{-- Kelurahan/Desa --}}
                    <div>
                        <label for="id_wilayah_kerja" class="{{ $labelClass }}">Kelurahan/desa {!! $star !!}</label>

                        <select name="id_wilayah_kerja" id="id_wilayah_kerja" required class="{{ $fieldClass }}">
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
                        <label for="jenis_kegiatan" class="{{ $labelClass }}">Jenis kegiatan {!! $star !!}</label>
                        <input type="text" name="jenis_kegiatan" id="jenis_kegiatan"
                               value="{{ old('jenis_kegiatan') }}"
                               placeholder="Jenis kegiatan"
                               required class="{{ $fieldClass }}">
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label for="tanggal" class="{{ $labelClass }}">Tanggal {!! $star !!}</label>
                        <input type="date" name="tanggal" id="tanggal"
                               value="{{ old('tanggal') }}"
                               min="{{ date('Y-m-d') }}"
                               required class="{{ $fieldClass }}">
                    </div>

                    {{-- Waktu --}}
                    <div>
                        <label for="waktu" class="{{ $labelClass }}">Waktu {!! $star !!}</label>
                        <input type="time" name="waktu" id="waktu"
                               value="{{ old('waktu', '09:00') }}"
                               required class="{{ $fieldClass }}">
                    </div>

                    {{-- Lokasi --}}
                    <div>
                        <label for="lokasi" class="{{ $labelClass }}">Lokasi {!! $star !!}</label>
                        <input type="text" name="lokasi" id="lokasi"
                               value="{{ old('lokasi') }}"
                               placeholder="Lokasi kegiatan"
                               required class="{{ $fieldClass }}">
                    </div>

                    {{-- Bidan Pemeriksa --}}
                    <div>
                        <label for="id_bidan" class="{{ $labelClass }}">Bidan Pemeriksa</label>
                        <select name="id_bidan" id="id_bidan" class="{{ $fieldClass }}">
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
                    <div class="sm:col-span-2">
                        <label for="catatan" class="{{ $labelClass }}">Catatan</label>
                        <input type="text" name="catatan" id="catatan"
                               value="{{ old('catatan') }}"
                               placeholder="Opsional"
                               class="{{ $fieldClass }}">
                    </div>

                </div>

            {{-- FORM POSYANDU --}}
            @else

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>
                        <label for="rw_id" class="{{ $labelClass }}">Posyandu (RW) {!! $star !!}</label>
                        <select name="rw_id" id="rw_id" required class="{{ $fieldClass }}">
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
                        <label for="jenis_kegiatan" class="{{ $labelClass }}">Jenis kegiatan {!! $star !!}</label>
                        <input type="text" name="jenis_kegiatan" id="jenis_kegiatan"
                               value="{{ old('jenis_kegiatan') }}"
                               placeholder="Jenis kegiatan"
                               required class="{{ $fieldClass }}">
                    </div>

                    <div>
                        <label for="tanggal" class="{{ $labelClass }}">Tanggal {!! $star !!}</label>
                        <input type="date" name="tanggal" id="tanggal"
                               value="{{ old('tanggal') }}" min="{{ date('Y-m-d') }}"
                               required class="{{ $fieldClass }}">
                    </div>

                    <div>
                        <label for="waktu" class="{{ $labelClass }}">Waktu {!! $star !!}</label>
                        <input type="time" name="waktu" id="waktu"
                               value="{{ old('waktu') }}" required
                               class="{{ $fieldClass }}">
                    </div>

                    <div>
                        <label for="id_kader" class="{{ $labelClass }}">Kader {!! $star !!}</label>
                        <select name="id_kader" id="id_kader" required class="{{ $fieldClass }}">
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
                        <label for="id_bidan" class="{{ $labelClass }}">Bidan Pemeriksa {!! $star !!}</label>
                        <select name="id_bidan" id="id_bidan" required class="{{ $fieldClass }}">
                            <option value="">Pilih bidan pemeriksa</option>
                            @foreach ($bidans as $bidan)
                                <option value="{{ $bidan->id }}"
                                    @selected((string) old('id_bidan') === (string) $bidan->id)>
                                    {{ $bidan->user?->name ?? 'Bidan #' . $bidan->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="lokasi" class="{{ $labelClass }}">Lokasi {!! $star !!}</label>
                        <input type="text" name="lokasi" id="lokasi"
                               value="{{ old('lokasi') }}"
                               placeholder="Lokasi kegiatan"
                               required class="{{ $fieldClass }}">
                    </div>

                </div>

            @endif

            {{-- Tombol --}}
            <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.jadwal.index', ['tab' => $tabKembali]) }}"
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