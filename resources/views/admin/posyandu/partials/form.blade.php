@php
    $inputClass = 'w-full rounded-xl border-2 border-[#F1D5E0] bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $labelClass = 'mb-1 block text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $selectedWilayah = old('wilayah_kerja_id', $posyandu->wilayah_kerja_id ?? '');
@endphp

<div>
    <label for="nama_posyandu" class="{{ $labelClass }}">Nama Posyandu <span class="text-red-600">*</span></label>
    <input id="nama_posyandu" type="text" name="nama_posyandu" maxlength="100"
           value="{{ old('nama_posyandu', $posyandu->nama_posyandu ?? '') }}"
           placeholder="Posyandu Anggrek" class="{{ $inputClass }}">
    @error('nama_posyandu') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
</div>

<div>
    <label for="wilayah_kerja_id" class="{{ $labelClass }}">Wilayah <span class="text-red-600">*</span></label>
    <select id="wilayah_kerja_id" name="wilayah_kerja_id" class="{{ $inputClass }}">
        <option value="">-- Pilih Wilayah --</option>
        @foreach ($wilayahs->groupBy('kecamatan') as $kecamatan => $desas)
            <optgroup label="Kec. {{ $kecamatan ?: '-' }}">
                @foreach ($desas as $w)
                    <option value="{{ $w->id }}" @selected((string) $selectedWilayah === (string) $w->id)>
                        Desa {{ $w->desa }}
                    </option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
    @error('wilayah_kerja_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
</div>

<div>
    <label for="alamat" class="{{ $labelClass }}">Alamat <span class="text-red-600">*</span></label>
    <input id="alamat" type="text" name="alamat" maxlength="255"
           value="{{ old('alamat', $posyandu->alamat ?? '') }}"
           placeholder="Alamat / lokasi posyandu" class="{{ $inputClass }}">
    @error('alamat') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
</div>

<div class="flex justify-end gap-3 pt-2">
    <a href="{{ route('admin.posyandu.index') }}"
       class="rounded-xl border border-[#E99AB7] bg-white px-6 py-2.5 text-sm font-semibold text-[#C9517E] hover:bg-[#FCE7F0]">Batal</a>
    <button type="submit"
            class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#C9517E]/25 hover:bg-[#B7436D]">Simpan</button>
</div>