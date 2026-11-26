
<div>
    <h2 class="mb-1 text-sm font-semibold text-[#542D3D]">
        Riwayat Imunisasi
    </h2>
    <p class="mb-3 text-xs text-gray-500">
        Centang vaksin yang diberikan kepada anak. Vaksin yang sudah tercatat akan otomatis terpilih.
    </p>

    @php
        $vaksinDipilih = old('vaksin', $vaksinTerpilih);
        $vaksinDipilih = array_map('intval', (array) $vaksinDipilih);
    @endphp

    <div class="mb-2 grid grid-cols-2 gap-3 px-1">
        <span class="text-xs font-medium text-gray-700">Jenis Vaksin</span>
        <span class="text-xs font-medium text-gray-700">Dosis</span>
    </div>

    <div class="space-y-2">
        @forelse ($jadwalImunisasi as $jadwal)
            <label class="grid cursor-pointer grid-cols-2 items-center gap-3 rounded-lg border border-gray-200 px-3 py-2 hover:border-[#C9517E] hover:bg-[#FFF8FA]">
                <span class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="vaksin[]"
                        value="{{ $jadwal->id_jadwal_imunisasi }}"
                        @checked(in_array((int) $jadwal->id_jadwal_imunisasi, $vaksinDipilih, true))
                        class="h-4 w-4 rounded border-gray-300 text-[#C9517E] focus:ring-[#C9517E]"
                    >

                    <span class="text-sm text-gray-800">
                        {{ $jadwal->jenis_vaksin }}
                    </span>
                </span>

                <span class="text-sm text-gray-600">
                    {{ $jadwal->dosis ?? '-' }}
                </span>

                @if ($jadwal->keterangan)
                    <span class="col-span-2 pl-6 text-xs text-gray-500">
                        {{ $jadwal->keterangan }}
                    </span>
                @endif
            </label>
        @empty
            <div class="rounded-lg bg-gray-50 p-3 text-sm text-gray-500">
                Tidak ada jadwal vaksin yang tersedia untuk umur anak ini.
            </div>
        @endforelse
    </div>

    @error('vaksin')
        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
    @enderror

    @error('vaksin.*')
        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
