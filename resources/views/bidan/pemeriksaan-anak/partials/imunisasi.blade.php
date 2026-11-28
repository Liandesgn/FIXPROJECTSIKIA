<div>
    <h2 class="text-base font-bold text-[#542D3D]">
        Riwayat Imunisasi
    </h2>
    <p class="mb-4 mt-0.5 text-sm text-slate-600">
        Centang vaksin yang diberikan kepada anak. Vaksin yang sudah tercatat akan otomatis terpilih.
    </p>

    @php
        $vaksinDipilih = old('vaksin', $vaksinTerpilih);
        $vaksinDipilih = array_map('intval', (array) $vaksinDipilih);
    @endphp

    <div class="mb-2 grid grid-cols-2 gap-3 px-4">
        <span class="text-xs font-bold uppercase tracking-wide text-[#542D3D]">Jenis Vaksin</span>
        <span class="text-xs font-bold uppercase tracking-wide text-[#542D3D]">Dosis</span>
    </div>

    <div class="space-y-2">
        @forelse ($jadwalImunisasi as $jadwal)
            <label class="grid cursor-pointer grid-cols-2 items-center gap-3 rounded-xl border border-[#F1D5E0] bg-white px-4 py-3 transition hover:border-[#E99AB7] hover:bg-[#FCE7F0]/40">
                <span class="flex items-center gap-2.5">
                    <input
                        type="checkbox"
                        name="vaksin[]"
                        value="{{ $jadwal->id_jadwal_imunisasi }}"
                        @checked(in_array((int) $jadwal->id_jadwal_imunisasi, $vaksinDipilih, true))
                        class="h-4 w-4 rounded border-[#E99AB7] text-[#C9517E] focus:ring-[#C9517E]"
                    >

                    <span class="text-sm font-semibold text-[#542D3D]">
                        {{ $jadwal->jenis_vaksin }}
                    </span>
                </span>

                <span class="text-sm text-slate-700">
                    {{ $jadwal->dosis ?? '-' }}
                </span>

                @if ($jadwal->keterangan)
                    <span class="col-span-2 pl-6 text-xs text-slate-600">
                        {{ $jadwal->keterangan }}
                    </span>
                @endif
            </label>
        @empty
            <div class="rounded-xl bg-[#FCE7F0]/50 px-4 py-3 text-sm font-medium text-slate-700">
                Tidak ada jadwal vaksin yang tersedia untuk umur anak ini.
            </div>
        @endforelse
    </div>

    @error('vaksin')
        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror

    @error('vaksin.*')
        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>