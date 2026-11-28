<div>
    <h2 class="mb-4 text-base font-bold text-[#542D3D]">
        Pemberian Vitamin
    </h2>

    <div class="mb-2 grid grid-cols-2 gap-3 px-4">
        <span class="text-xs font-bold uppercase tracking-wide text-[#542D3D]">Jenis Vitamin</span>
        <span class="text-xs font-bold uppercase tracking-wide text-[#542D3D]">Dosis</span>
    </div>

    @php
        $vitaminDipilih = old('id_jadwal_vitamin', $vitaminTerpilih);
    @endphp

    <div class="space-y-2">
        <label class="grid cursor-pointer grid-cols-2 items-center gap-3 rounded-xl border border-[#F1D5E0] bg-white px-4 py-3 transition hover:border-[#E99AB7] hover:bg-[#FCE7F0]/40">
            <span class="flex items-center gap-2.5">
                <input
                    type="radio"
                    name="id_jadwal_vitamin"
                    value=""
                    @checked($vitaminDipilih === null || $vitaminDipilih === '')
                    class="h-4 w-4 border-[#E99AB7] text-[#C9517E] focus:ring-[#C9517E]"
                >
                <span class="text-sm font-semibold text-[#542D3D]">Tidak diberikan</span>
            </span>

            <span class="text-sm text-slate-500">-</span>
        </label>

        @forelse ($jadwalVitamin as $vitamin)
            <label class="grid cursor-pointer grid-cols-2 items-center gap-3 rounded-xl border border-[#F1D5E0] bg-white px-4 py-3 transition hover:border-[#E99AB7] hover:bg-[#FCE7F0]/40">
                <span class="flex items-center gap-2.5">
                    <input
                        type="radio"
                        name="id_jadwal_vitamin"
                        value="{{ $vitamin->id_jadwal_vitamin }}"
                        @checked((string) $vitaminDipilih === (string) $vitamin->id_jadwal_vitamin)
                        class="h-4 w-4 border-[#E99AB7] text-[#C9517E] focus:ring-[#C9517E]"
                    >

                    <span class="text-sm font-semibold text-[#542D3D]">
                        {{ $vitamin->jenis_vitamin }}
                    </span>
                </span>

                <span class="text-sm text-slate-700">
                    {{ $vitamin->dosis ?? '-' }}
                </span>

                @if ($vitamin->frekuensi)
                    <span class="col-span-2 pl-6 text-xs text-slate-600">
                        Frekuensi: {{ $vitamin->frekuensi }}
                    </span>
                @endif
            </label>
        @empty
            <p class="rounded-xl bg-[#FCE7F0]/50 px-4 py-3 text-sm font-medium text-slate-700">
                Tidak ada jadwal vitamin yang tersedia untuk umur anak ini.
            </p>
        @endforelse
    </div>

    @error('id_jadwal_vitamin')
        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>