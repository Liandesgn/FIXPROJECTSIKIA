{{-- Isi form wilayah. Parameter: $wilayah (model|null), $cancelUrl (opsional) --}}
@php
    $wilayah   = $wilayah ?? null;
    $cancelUrl = $cancelUrl ?? route('admin.wilayah-kerja.index');

    $inputClass = 'w-full rounded-xl border border-[#E2C3D0] bg-white px-4 py-3 text-sm text-gray-900 outline-none transition placeholder:text-slate-500 hover:border-[#E99AB7] focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/25';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $star       = '<span class="text-[#C9517E]">*</span>';
@endphp

<div class="space-y-4">
    <div>
        <label for="nama_wilayah" class="{{ $labelClass }}">Nama Wilayah {!! $star !!}</label>
        <input id="nama_wilayah" type="text" name="nama_wilayah" maxlength="100"
               value="{{ old('nama_wilayah', $wilayah->nama_wilayah ?? '') }}"
               placeholder="Wilayah A" class="{{ $inputClass }}">
        @error('nama_wilayah') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="desa" class="{{ $labelClass }}">Kelurahan/Desa {!! $star !!}</label>
        <input id="desa" type="text" name="desa" maxlength="100"
               value="{{ old('desa', $wilayah->desa ?? '') }}"
               placeholder="Nama kelurahan/desa" class="{{ $inputClass }}">
        @error('desa') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="kecamatan" class="{{ $labelClass }}">Kecamatan {!! $star !!}</label>
        <input id="kecamatan" type="text" name="kecamatan" maxlength="100"
               value="{{ old('kecamatan', $wilayah->kecamatan ?? '') }}"
               placeholder="Nama kecamatan" class="{{ $inputClass }}">
        @error('kecamatan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>
</div>

<div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-6 sm:flex-row sm:justify-end">
    <a href="{{ $cancelUrl }}"
       class="inline-flex justify-center rounded-xl border border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">Batal</a>
    <button type="submit"
            class="inline-flex justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">Simpan</button>
</div>