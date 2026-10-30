@php
    $inputClass = 'w-full rounded-xl border-2 border-[#F1D5E0] bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $labelClass = 'mb-1 block text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';

    $bidanDipilih = old('bidan_id', '');
    $kaderDipilih = (array) old('kader_ids', []);
    $adaOld       = old('wilayah_kerja_id') !== null;
@endphp

<div>
    <label for="wilayah_kerja_id" class="{{ $labelClass }}">Wilayah <span class="text-red-600">*</span></label>
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
    @error('bidan_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
</div>

<div>
    <p class="{{ $labelClass }}">Kader Wilayah</p>
    <div class="max-h-64 divide-y divide-[#F7E6ED] overflow-y-auto rounded-xl border-2 border-[#F1D5E0]">
        @foreach ($kaders as $k)
            <label class="flex items-center gap-3 px-3 py-2 text-sm text-slate-700">
                <input type="checkbox" name="kader_ids[]" value="{{ $k->id }}"
                       @checked(in_array($k->id, $kaderDipilih))
                       class="kader-cb h-4 w-4 rounded border-slate-300 text-[#C9517E] focus:ring-[#C9517E]">
                <span class="font-semibold text-[#542D3D]">{{ $k->name }}</span>
                <span class="text-xs text-slate-500">(saat ini: {{ $k->kader?->wilayahKerja?->desa ?? '-' }})</span>
            </label>
        @endforeach
    </div>
    @error('kader_ids.*') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
</div>

<div class="flex justify-end gap-3 pt-2">
    <a href="{{ route('admin.penugasan.index') }}"
       class="rounded-xl border border-[#E99AB7] bg-white px-6 py-2.5 text-sm font-semibold text-[#C9517E] hover:bg-[#FCE7F0]">Batal</a>
    <button type="submit"
            class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#C9517E]/25 hover:bg-[#B7436D]">Simpan</button>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const peta = @json($penugasanMap ?? []);
    const wilayah = document.getElementById('wilayah_kerja_id');
    const bidan = document.getElementById('bidan_id');
    const kaderCb = document.querySelectorAll('.kader-cb');

    // Saat wilayah dipilih: bidan & kader yang sudah bertugas langsung terpilih/tercentang
    function terapkan() {
        const p = peta[wilayah.value];
        bidan.value = (p && p.bidan) ? String(p.bidan) : '';
        kaderCb.forEach(function (cb) {
            cb.checked = !!(p && p.kader.indexOf(Number(cb.value)) !== -1);
        });
    }

    wilayah.addEventListener('change', terapkan);
    if (wilayah.value && !@json($adaOld)) terapkan();
});
</script>
@endpush
