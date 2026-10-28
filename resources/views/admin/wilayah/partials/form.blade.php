@php
    $inputClass = 'w-full rounded-xl border-2 border-[#F1D5E0] bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $labelClass = 'mb-1 block text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
@endphp

<div>
    <label for="nama_wilayah" class="{{ $labelClass }}">Nama Wilayah <span class="text-red-600">*</span></label>
    <input id="nama_wilayah" type="text" name="nama_wilayah" maxlength="100"
           value="{{ old('nama_wilayah', $wilayah->nama_wilayah ?? '') }}"
           placeholder="Desa. a Kec. a" class="{{ $inputClass }}">
    @error('nama_wilayah') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
</div>

<div>
    <label for="desa" class="{{ $labelClass }}">Kelurahan/Desa <span class="text-red-600">*</span></label>
    <input id="desa" type="text" name="desa" maxlength="100"
           value="{{ old('desa', $wilayah->desa ?? '') }}"
           placeholder="Nama kelurahan/desa" class="{{ $inputClass }}">
    @error('desa') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
</div>

<div>
    <label for="kecamatan" class="{{ $labelClass }}">Kecamatan <span class="text-red-600">*</span></label>
    <input id="kecamatan" type="text" name="kecamatan" maxlength="100"
           value="{{ old('kecamatan', $wilayah->kecamatan ?? '') }}"
           placeholder="Nama kecamatan" class="{{ $inputClass }}">
    @error('kecamatan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
</div>

<div class="flex justify-end gap-3 pt-2">
    <a href="{{ route('admin.wilayah-kerja.index') }}"
       class="rounded-xl border border-[#E99AB7] bg-white px-6 py-2.5 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">Batal</a>
    <button type="submit"
            class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#C9517E]/25 transition hover:bg-[#B7436D]">Simpan</button>
</div>