@extends('layouts.app')

@section('title', 'Edit Data Ibu')

@section('content')
@php
    $inputClass = 'w-full rounded-xl border-2 border-[#F1D5E0] bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $labelClass = 'mb-1 block text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $statusDipilih = old('status_kehamilan', $ibu->status_kehamilan?->value);
@endphp
<div class="mx-auto w-full max-w-4xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-[#542D3D]">Edit Ibu</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Ibu / Edit Data</p>
    </div>

    <form method="POST" action="{{ route('admin.data-ibu.update', $ibu) }}"
          class="space-y-5 rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="name" class="{{ $labelClass }}">Nama <span class="text-red-600">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name', $ibu->user?->name) }}" class="{{ $inputClass }}">
                @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="{{ $labelClass }}">Email <span class="text-red-600">*</span></label>
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
                <label for="nik" class="{{ $labelClass }}">NIK <span class="text-red-600">*</span></label>
                <input id="nik" type="text" name="nik" value="{{ old('nik', $ibu->nik) }}"
                       maxlength="16" inputmode="numeric" pattern="[0-9]{16}" title="NIK harus 16 digit angka"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="16 digit NIK" class="{{ $inputClass }}">
                @error('nik') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="tempat_lahir" class="{{ $labelClass }}">Tempat Lahir <span class="text-red-600">*</span></label>
                <input id="tempat_lahir" type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $ibu->tempat_lahir) }}" class="{{ $inputClass }}">
                @error('tempat_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="tanggal_lahir" class="{{ $labelClass }}">Tanggal Lahir <span class="text-red-600">*</span></label>
                <input id="tanggal_lahir" type="date" name="tanggal_lahir"
                       value="{{ old('tanggal_lahir', $ibu->tanggal_lahir?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                @error('tanggal_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="wilayah_kerja_id" class="{{ $labelClass }}">Wilayah <span class="text-red-600">*</span></label>
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
                <label for="status_kehamilan" class="{{ $labelClass }}">Status Kehamilan <span class="text-red-600">*</span></label>
                <select id="status_kehamilan" name="status_kehamilan" class="{{ $inputClass }}">
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected($statusDipilih === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
                @error('status_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div data-hamil>
                <label for="usia_kehamilan" class="{{ $labelClass }}">Usia Kehamilan (minggu) <span class="text-red-600">*</span></label>
                <input id="usia_kehamilan" type="number" name="usia_kehamilan" min="1" max="45"
                       value="{{ old('usia_kehamilan', $ibu->usia_kehamilan) }}" placeholder="Minggu" class="{{ $inputClass }}">
                @error('usia_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div data-hamil>
                <label for="hpl" class="{{ $labelClass }}">HPL <span class="text-red-600">*</span></label>
                <input id="hpl" type="date" name="hpl" value="{{ old('hpl', $ibu->hpl?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                @error('hpl') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-[#F1D5E0] pt-5">
            <a href="{{ route('admin.data-ibu.show', $ibu) }}"
               class="rounded-xl border border-[#E99AB7] bg-white px-6 py-2.5 text-sm font-semibold text-[#C9517E] hover:bg-[#FCE7F0]">Batal</a>
            <button type="submit"
                    class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#C9517E]/25 hover:bg-[#B7436D]">Simpan</button>
        </div>
    </form>
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