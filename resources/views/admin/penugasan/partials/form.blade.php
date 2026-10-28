@php
    $inputClass = 'w-full rounded-xl border-2 border-[#F1D5E0] bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $labelClass = 'mb-1 block text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';

    $edit          = isset($wilayah);
    $bidanDipilih  = old('bidan_id', $bidanSekarang ?? '');
    $kaderDipilih  = old('kader_ids', $kaderSekarang ?? []);
    $kaderTerkunci = $kaderSekarang ?? [];
@endphp

<div>
    <label class="{{ $labelClass }}">Wilayah <span class="text-red-600">*</span></label>
    @if ($edit)
        <p class="rounded-xl border-2 border-[#F1D5E0] bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700">{{ $wilayah->nama_wilayah }}</p>
    @else
        <select name="wilayah_kerja_id" class="{{ $inputClass }}">
            <option value="">-- Pilih Wilayah --</option>
            @foreach ($wilayahs->groupBy('kecamatan') as $kecamatan => $desas)
                <optgroup label="Kec. {{ $kecamatan ?: '-' }}">
                    @foreach ($desas as $w)
                        <option value="{{ $w->id }}" @selected((string) old('wilayah_kerja_id') === (string) $w->id)>Desa {{ $w->desa }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('wilayah_kerja_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    @endif
</div>

<div>
    <label for="bidan_id" class="{{ $labelClass }}">Bidan Penanggung Jawab <span class="text-red-600">*</span></label>
    <select id="bidan_id" name="bidan_id" class="{{ $inputClass }}">
        <option value="">-- Pilih Bidan --</option>
        @foreach ($bidans as $b)
            <option value="{{ $b->id }}" @selected((string) $bidanDipilih === (string) $b->id)>
                {{ $b->name }} (saat ini: {{ $b->bidan?->wilayahKerja?->desa ?? '-' }})
            </option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-slate-500">Jika wilayah sudah punya bidan, kedua bidan saling bertukar wilayah.</p>
    @error('bidan_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
</div>

<div>
    <p class="{{ $labelClass }}">Kader Wilayah</p>
    <div class="max-h-64 divide-y divide-[#F7E6ED] overflow-y-auto rounded-xl border-2 border-[#F1D5E0]">
        @foreach ($kaders as $k)
            @php $terkunci = in_array($k->id, $kaderTerkunci, true); @endphp
            <label class="flex items-center gap-3 px-3 py-2 text-sm text-slate-700">
                <input type="checkbox" name="kader_ids[]" value="{{ $k->id }}"
                       @checked($terkunci || in_array($k->id, (array) $kaderDipilih))
                       @disabled($terkunci)
                       class="h-4 w-4 rounded border-slate-300 text-[#C9517E] focus:ring-[#C9517E]">
                <span class="font-semibold text-[#542D3D]">{{ $k->name }}</span>
                <span class="text-xs text-slate-500">
                    {{ $terkunci ? '(sudah bertugas di wilayah ini)' : '(saat ini: '.($k->kader?->wilayahKerja?->desa ?? '-').')' }}
                </span>
            </label>
        @endforeach
    </div>
    <p class="mt-1 text-xs text-slate-500">Centang kader untuk dipindahkan ke wilayah ini. Kader yang sudah bertugas di sini dipindah lewat penugasan wilayah lain.</p>
    @error('kader_ids.*') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
</div>

<div class="flex justify-end gap-3 pt-2">
    <a href="{{ route('admin.penugasan.index') }}"
       class="rounded-xl border border-[#E99AB7] bg-white px-6 py-2.5 text-sm font-semibold text-[#C9517E] hover:bg-[#FCE7F0]">Batal</a>
    <button type="submit"
            class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#C9517E]/25 hover:bg-[#B7436D]">Simpan</button>
</div>