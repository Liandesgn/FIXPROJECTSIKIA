{{-- Isi form penugasan.
     Parameter: $wilayah (model|null), $wilayahs, $bidans, $kaders,
                $bidanSekarang, $kaderSekarang, $cancelUrl (opsional). --}}
@php
    $wilayah       = $wilayah ?? null;
    $wilayahs      = $wilayahs ?? collect();
    $bidans        = $bidans ?? collect();
    $kaders        = $kaders ?? collect();
    $cancelUrl     = $cancelUrl ?? route('admin.penugasan.index');

    $inputClass = 'w-full rounded-xl border border-[#E2C3D0] bg-white px-4 py-3 text-sm text-gray-900 outline-none transition placeholder:text-slate-500 hover:border-[#E99AB7] focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/25';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $hintClass  = 'mt-1.5 text-xs font-medium text-slate-600';
    $star       = '<span class="text-[#C9517E]">*</span>';

    $edit          = $wilayah !== null;
    $bidanDipilih  = old('bidan_id', $bidanSekarang ?? '');
    $kaderDipilih  = old('kader_ids', $kaderSekarang ?? []);
    $kaderTerkunci = $kaderSekarang ?? [];
@endphp

<div class="space-y-6">

    {{-- ===== Bidan Penanggung Jawab ===== --}}
    <section class="space-y-4 rounded-2xl border border-[#F1D5E0] bg-[#FFF7FA] p-5 sm:p-6">
        <h2 class="text-base font-bold text-[#542D3D]">Bidan Penanggung Jawab</h2>

        <div>
            <label for="wilayah_kerja_id" class="{{ $labelClass }}">Wilayah {!! $star !!}</label>
            @if ($edit)
                <p class="rounded-xl border border-[#E2C3D0] bg-white px-4 py-3 text-sm font-bold text-[#542D3D]">{{ $wilayah->nama_wilayah }}</p>
            @else
                <select id="wilayah_kerja_id" name="wilayah_kerja_id" class="{{ $inputClass }}">
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
            <label for="bidan_id" class="{{ $labelClass }}">Bidan Penanggung Jawab {!! $star !!}</label>
            <select id="bidan_id" name="bidan_id" class="{{ $inputClass }}">
                <option value="">-- Pilih Bidan --</option>
                @foreach ($bidans as $b)
                    <option value="{{ $b->id }}" @selected((string) $bidanDipilih === (string) $b->id)>
                        {{ $b->name }} (saat ini: {{ $b->bidan?->wilayahKerja?->desa ?? '-' }})
                    </option>
                @endforeach
            </select>
            <p class="{{ $hintClass }}">Jika wilayah sudah punya bidan, kedua bidan saling bertukar wilayah.</p>
            @error('bidan_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>
    </section>

    {{-- ===== Kader Wilayah ===== --}}
    <section class="rounded-2xl border border-[#F1D5E0] bg-[#FFF7FA] p-5 sm:p-6">
        <h2 class="text-base font-bold text-[#542D3D]">Kader Wilayah</h2>

        <div class="mt-4 max-h-64 divide-y divide-[#F7E6ED] overflow-y-auto rounded-xl border border-[#E2C3D0] bg-white">
            @foreach ($kaders as $k)
                @php $terkunci = in_array($k->id, $kaderTerkunci, true); @endphp
                <label class="flex items-center gap-3 px-4 py-2.5 text-sm {{ $terkunci ? 'bg-[#FCE7F0]/60' : 'cursor-pointer hover:bg-[#FCE7F0]/40' }}">
                    <input type="checkbox" name="kader_ids[]" value="{{ $k->id }}"
                           @checked($terkunci || in_array($k->id, (array) $kaderDipilih))
                           @disabled($terkunci)
                           class="h-4 w-4 rounded border-slate-400 text-[#C9517E] focus:ring-[#C9517E]">
                    <span class="font-bold text-[#542D3D]">{{ $k->name }}</span>
                    <span class="text-xs font-medium text-slate-600">
                        {{ $terkunci ? '(sudah bertugas di wilayah ini)' : '(saat ini: '.($k->kader?->wilayahKerja?->desa ?? '-').')' }}
                    </span>
                </label>
            @endforeach
        </div>
        <p class="{{ $hintClass }}">Centang kader untuk dipindahkan ke wilayah ini. Kader yang sudah bertugas di sini dipindah lewat penugasan wilayah lain.</p>
        @error('kader_ids.*') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </section>
</div>

<div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-6 sm:flex-row sm:justify-end">
    <a href="{{ $cancelUrl }}"
       class="inline-flex justify-center rounded-xl border border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">Batal</a>
    <button type="submit"
            class="inline-flex justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">Simpan</button>
</div>