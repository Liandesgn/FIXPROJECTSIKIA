{{-- Isi form posyandu. Parameter: $posyandu (model|null), $wilayahs, $cancelUrl (opsional) --}}
@php
    $posyandu  = $posyandu ?? null;
    $wilayahs  = $wilayahs ?? collect();
    $cancelUrl = $cancelUrl ?? route('admin.posyandu.index');

    $inputClass = 'w-full rounded-xl border border-[#E2C3D0] bg-white px-4 py-3 text-sm text-gray-900 outline-none transition placeholder:text-slate-500 hover:border-[#E99AB7] focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/25';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $star       = '<span class="text-[#C9517E]">*</span>';
    $selectedWilayah = old('wilayah_kerja_id', $posyandu->wilayah_kerja_id ?? '');
@endphp

<div class="space-y-4">
    <div>
        <label for="nama_posyandu" class="{{ $labelClass }}">Nama Posyandu {!! $star !!}</label>
        <input id="nama_posyandu" type="text" name="nama_posyandu" maxlength="100"
               value="{{ old('nama_posyandu', $posyandu->nama_posyandu ?? '') }}"
               placeholder="Posyandu Anggrek" class="{{ $inputClass }}">
        @error('nama_posyandu') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="wilayah_kerja_id" class="{{ $labelClass }}">Wilayah {!! $star !!}</label>
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
        <label for="alamat" class="{{ $labelClass }}">Alamat {!! $star !!}</label>
        <input id="alamat" type="text" name="alamat" maxlength="255"
               value="{{ old('alamat', $posyandu->alamat ?? '') }}"
               placeholder="Alamat / lokasi posyandu" class="{{ $inputClass }}">
        @error('alamat') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>
</div>

<div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-6 sm:flex-row sm:justify-end">
    <a href="{{ $cancelUrl }}"
       class="inline-flex justify-center rounded-xl border border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">Batal</a>
    <button type="submit"
            class="inline-flex justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">Simpan</button>
</div>