@php
    $inputClass = 'w-full rounded-xl border-2 border-[#F1D5E0] bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $labelClass = 'mb-1 block text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $jk = old('jenis_kelamin', $anak->jenis_kelamin ?? '');
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="ibu_id" class="{{ $labelClass }}">Ibu <span class="text-red-600">*</span></label>
        <select id="ibu_id" name="ibu_id" class="{{ $inputClass }}">
            <option value="">-- Pilih Ibu --</option>
            @foreach ($ibus as $i)
                <option value="{{ $i->id }}" @selected((string) old('ibu_id', $anak->ibu_id ?? '') === (string) $i->id)>
                    {{ $i->user?->name }} ({{ $i->nik ?? 'tanpa NIK' }})
                </option>
            @endforeach
        </select>
        @error('ibu_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="nik_anak" class="{{ $labelClass }}">NIK Anak <span class="text-red-600">*</span></label>
        <input id="nik_anak" type="text" name="nik_anak" value="{{ old('nik_anak', $anak->nik_anak ?? '') }}"
               maxlength="16" inputmode="numeric" pattern="[0-9]{16}" title="NIK harus 16 digit angka"
               oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="16 digit NIK" class="{{ $inputClass }}">
        @error('nik_anak') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="nama_anak" class="{{ $labelClass }}">Nama Anak <span class="text-red-600">*</span></label>
        <input id="nama_anak" type="text" name="nama_anak" maxlength="100"
               value="{{ old('nama_anak', $anak->nama_anak ?? '') }}" class="{{ $inputClass }}">
        @error('nama_anak') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="tempat_lahir" class="{{ $labelClass }}">Tempat Lahir <span class="text-red-600">*</span></label>
        <input id="tempat_lahir" type="text" name="tempat_lahir" maxlength="100"
               value="{{ old('tempat_lahir', $anak->tempat_lahir ?? '') }}" placeholder="Kota/Kabupaten" class="{{ $inputClass }}">
        @error('tempat_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="tanggal_lahir" class="{{ $labelClass }}">Tanggal Lahir <span class="text-red-600">*</span></label>
        <input id="tanggal_lahir" type="date" name="tanggal_lahir" max="{{ now()->format('Y-m-d') }}"
               value="{{ old('tanggal_lahir', isset($anak) ? $anak->tanggal_lahir?->format('Y-m-d') : '') }}" class="{{ $inputClass }}">
        @error('tanggal_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="jenis_kelamin" class="{{ $labelClass }}">Jenis Kelamin <span class="text-red-600">*</span></label>
        <select id="jenis_kelamin" name="jenis_kelamin" class="{{ $inputClass }}">
            <option value="">-- Pilih --</option>
            <option value="L" @selected($jk === 'L')>Laki-laki</option>
            <option value="P" @selected($jk === 'P')>Perempuan</option>
        </select>
        @error('jenis_kelamin') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="nama_ayah" class="{{ $labelClass }}">Nama Ayah <span class="text-red-600">*</span></label>
        <input id="nama_ayah" type="text" name="nama_ayah" maxlength="100"
               value="{{ old('nama_ayah', $anak->nama_ayah ?? '') }}" placeholder="Nama ayah" class="{{ $inputClass }}">
        @error('nama_ayah') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>
</div>

<div class="flex justify-end gap-3 border-t border-[#F1D5E0] pt-5">
    <a href="{{ route('admin.data-anak.index') }}"
       class="rounded-xl border border-[#E99AB7] bg-white px-6 py-2.5 text-sm font-semibold text-[#C9517E] hover:bg-[#FCE7F0]">Batal</a>
    <button type="submit"
            class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#C9517E]/25 hover:bg-[#B7436D]">Simpan</button>
</div>