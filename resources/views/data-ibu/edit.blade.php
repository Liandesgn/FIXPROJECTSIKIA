@extends('layouts.app')

@section('title', 'Edit Data Ibu')

@section('content')
@php
    $inputClass = 'w-full rounded-xl border border-[#E2C3D0] bg-white px-4 py-3 text-sm text-gray-900 outline-none transition placeholder:text-slate-500 hover:border-[#E99AB7] focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/25';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $star       = '<span class="text-[#C9517E]">*</span>';
    $statusDipilih = old('status_kehamilan', $ibu->status_kehamilan?->value);
@endphp
<div class="mx-auto w-full max-w-4xl space-y-4">

    <div>
        <a href="{{ route('admin.data-ibu.show', $ibu) }}"
           class="inline-flex items-center gap-2 rounded-xl border-2 border-[#C9517E] bg-white px-4 py-2 text-sm font-semibold text-[#C9517E] shadow-sm transition hover:bg-[#C9517E] hover:text-white focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"/></svg>
            Kembali ke Detail Ibu
        </a>
        <p class="mt-3 text-sm font-medium text-slate-700">Data Master / Data Ibu / Edit Data</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-6 py-5 sm:px-8">
            <h1 class="text-xl font-bold text-[#542D3D] sm:text-2xl">Edit Ibu</h1>
            <p class="mt-1 text-sm text-slate-700">Perbarui data akun dan data ibu.</p>
        </div>

        <form method="POST" action="{{ route('admin.data-ibu.update', $ibu) }}" class="space-y-6 p-6 sm:p-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label for="name" class="{{ $labelClass }}">Nama {!! $star !!}</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $ibu->user?->name) }}" class="{{ $inputClass }}">
                    @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="{{ $labelClass }}">Email {!! $star !!}</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $ibu->user?->email) }}" class="{{ $inputClass }}">
                    @error('email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="{{ $labelClass }}">Nomor HP</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $ibu->user?->phone) }}"
                           inputmode="numeric" maxlength="13" pattern="[0-9]{10,13}" title="Hanya angka, 10-13 digit"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="08xxxxxxxxxx" class="{{ $inputClass }}">
                    @error('phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="nik" class="{{ $labelClass }}">NIK {!! $star !!}</label>
                    <input id="nik" type="text" name="nik" value="{{ old('nik', $ibu->nik) }}"
                           maxlength="16" inputmode="numeric" pattern="[0-9]{16}" title="NIK harus 16 digit angka"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="16 digit NIK" class="{{ $inputClass }}">
                    @error('nik') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tempat_lahir" class="{{ $labelClass }}">Tempat Lahir {!! $star !!}</label>
                    <input id="tempat_lahir" type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $ibu->tempat_lahir) }}" class="{{ $inputClass }}">
                    @error('tempat_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tanggal_lahir" class="{{ $labelClass }}">Tanggal Lahir {!! $star !!}</label>
                    <input id="tanggal_lahir" type="date" name="tanggal_lahir"
                           value="{{ old('tanggal_lahir', $ibu->tanggal_lahir?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                    @error('tanggal_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="wilayah_kerja_id" class="{{ $labelClass }}">Wilayah {!! $star !!}</label>
                    <select id="wilayah_kerja_id" name="wilayah_kerja_id" class="{{ $inputClass }}">
                        <option value="">-- Pilih Wilayah --</option>
                        @foreach ($wilayahs->groupBy('kecamatan') as $kecamatan => $desas)
                            <optgroup label="Kec. {{ $kecamatan ?: '-' }}">
                                @foreach ($desas as $w)
                                    <option value="{{ $w->id }}" @selected((string) old('wilayah_kerja_id', $ibu->wilayah_kerja_id) === (string) $w->id)>Desa {{ $w->desa }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('wilayah_kerja_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status_kehamilan" class="{{ $labelClass }}">Status Kehamilan {!! $star !!}</label>
                    <select id="status_kehamilan" name="status_kehamilan" class="{{ $inputClass }}">
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}" @selected($statusDipilih === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                    @error('status_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div data-hamil>
                    <label for="usia_kehamilan" class="{{ $labelClass }}">Usia Kehamilan (minggu) {!! $star !!}</label>
                    <input id="usia_kehamilan" type="number" name="usia_kehamilan" min="1" max="45"
                           value="{{ old('usia_kehamilan', $ibu->usia_kehamilan) }}" placeholder="Minggu" class="{{ $inputClass }}">
                    @error('usia_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div data-hamil>
                    <label for="hpl" class="{{ $labelClass }}">HPL {!! $star !!}</label>
                    <input id="hpl" type="date" name="hpl" value="{{ old('hpl', $ibu->hpl?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                    @error('hpl') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.data-ibu.show', $ibu) }}"
                   class="inline-flex justify-center rounded-xl border border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">Batal</a>
                <button type="submit"
                        class="inline-flex justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const status = document.getElementById('status_kehamilan');
    const blok = document.querySelectorAll('[data-hamil]');

    // Hamil: tampilkan usia & HPL. Melahirkan: sembunyikan dan kosongkan.
    function atur() {
        const hamil = status.value === 'hamil';
        blok.forEach(function (el) {
            el.classList.toggle('hidden', !hamil);
            if (!hamil) el.querySelectorAll('input').forEach(function (i) { i.value = ''; });
        });
    }

    status.addEventListener('change', atur);
    atur();
});
</script>
@endsection