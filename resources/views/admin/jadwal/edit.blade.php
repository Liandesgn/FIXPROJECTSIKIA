@extends('layouts.app')

@section('title', 'Edit Jadwal')

@section('content')
@php
    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $lockedClass = 'mt-1.5 w-full cursor-not-allowed rounded-xl border border-[#F1D5E0] bg-slate-100 px-3.5 py-2.5 text-sm font-medium text-slate-600';
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $star = '<span class="text-[#C9517E]">*</span>';
    $judulBagian = 'mb-3 border-b border-[#F1D5E0] pb-2 text-sm font-bold uppercase tracking-wide text-[#C9517E]';
@endphp

<div class="mx-auto w-full max-w-3xl space-y-5">

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Jadwal</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Kelola Jadwal / Edit</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-6 py-4">
            <h2 class="text-lg font-bold text-[#542D3D]">Edit Jadwal Kegiatan</h2>
            <p class="mt-0.5 text-sm text-slate-600">Perbarui informasi dan penugasan jadwal kegiatan.</p>
        </div>

        <form action="{{ route('admin.jadwal.update', $jadwal->id_jadwal) }}"
              method="POST"
              class="space-y-6 p-4 sm:p-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold">Terdapat kesalahan pada pengisian form:</p>
                    <ul class="mt-2 list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 1. Jenis Jadwal --}}
            <div>
                <h3 class="{{ $judulBagian }}">1. Jenis Jadwal</h3>

                <label for="tipe_jadwal" class="{{ $labelClass }}">Jenis Jadwal</label>

                <input type="text" id="tipe_jadwal"
                       value="{{ $detail['tipe_jadwal'] === 'posyandu' ? 'Jadwal Posyandu' : 'Jadwal Pemeriksaan Kehamilan' }}"
                       disabled
                       class="{{ $lockedClass }}">

                <p class="mt-1 text-xs text-slate-500">
                    Jenis jadwal tidak dapat diubah setelah dibuat.
                </p>
            </div>

            {{-- 2. Informasi Kegiatan --}}
            <div>
                <h3 class="{{ $judulBagian }}">2. Informasi Kegiatan</h3>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div class="sm:col-span-2">
                        <label for="jenis_kegiatan" class="{{ $labelClass }}">Nama / Jenis Kegiatan {!! $star !!}</label>

                        <input type="text" name="jenis_kegiatan" id="jenis_kegiatan"
                               value="{{ old('jenis_kegiatan', $jadwal->jenis_kegiatan) }}"
                               required class="{{ $fieldClass }}">
                    </div>

                    <div>
                        <label for="tanggal" class="{{ $labelClass }}">Tanggal {!! $star !!}</label>

                        <input type="date" name="tanggal" id="tanggal"
                               value="{{ old('tanggal', \Carbon\Carbon::parse($jadwal->tanggal)->format('Y-m-d')) }}"
                               required class="{{ $fieldClass }}">
                    </div>

                    <div>
                        <label for="waktu" class="{{ $labelClass }}">Waktu {!! $star !!}</label>

                        <input type="time" name="waktu" id="waktu"
                               value="{{ old('waktu', substr($jadwal->waktu, 0, 5)) }}"
                               required class="{{ $fieldClass }}">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="lokasi" class="{{ $labelClass }}">Lokasi Kegiatan {!! $star !!}</label>

                        <input type="text" name="lokasi" id="lokasi"
                               value="{{ old('lokasi', $jadwal->lokasi) }}"
                               required class="{{ $fieldClass }}">
                    </div>

                </div>
            </div>

            {{-- 3. Penugasan --}}
            @if ($detail['tipe_jadwal'] === 'posyandu')

                <div>
                    <h3 class="{{ $judulBagian }}">3. Penugasan Jadwal Posyandu</h3>

                    <p class="mb-4 text-sm text-slate-600">
                        Jadwal ditujukan untuk seluruh RT dalam RW yang dipilih.
                    </p>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div>
                            <label for="rw_id" class="{{ $labelClass }}">RW Tujuan {!! $star !!}</label>

                            <select name="rw_id" id="rw_id" required class="{{ $fieldClass }}">
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
                            <label for="id_kader" class="{{ $labelClass }}">Kader Penanggung Jawab {!! $star !!}</label>

                            <select name="id_kader" id="id_kader" required class="{{ $fieldClass }}">
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

                <div>
                    <h3 class="{{ $judulBagian }}">3. Penugasan Pemeriksaan Kehamilan</h3>

                    <p class="mb-4 text-sm text-slate-600">
                        Tentukan wilayah kerja untuk jadwal pemeriksaan.
                    </p>

                    <label for="id_wilayah_kerja" class="{{ $labelClass }}">Wilayah Kerja {!! $star !!}</label>

                    <select name="id_wilayah_kerja" id="id_wilayah_kerja" required class="{{ $fieldClass }}">
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

            {{-- 4. Bidan --}}
            <div>
                <h3 class="{{ $judulBagian }}">4. Bidan Pemeriksa</h3>

                <label for="id_bidan" class="{{ $labelClass }}">Bidan yang Bertugas {!! $star !!}</label>

                <select name="id_bidan" id="id_bidan" required class="{{ $fieldClass }}">
                    <option value="">-- Pilih Bidan --</option>

                    @foreach ($bidans as $bidan)
                        <option value="{{ $bidan->id }}"
                            @selected((string) old('id_bidan', $detail['id_bidan']) === (string) $bidan->id)>
                            {{ $bidan->user?->name ?? 'Bidan #' . $bidan->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 5. Catatan Pemeriksaan --}}
            @if ($detail['tipe_jadwal'] === 'pemeriksaan')
                <div>
                    <h3 class="{{ $judulBagian }}">5. Catatan Tambahan</h3>

                    <label for="catatan" class="{{ $labelClass }}">Catatan</label>

                    <textarea name="catatan" id="catatan" rows="4"
                              placeholder="Masukkan catatan tambahan..."
                              class="{{ $fieldClass }}">{{ old('catatan', $detail['catatan']) }}</textarea>
                </div>
            @endif

            {{-- Tombol --}}
            <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.jadwal.index') }}"
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