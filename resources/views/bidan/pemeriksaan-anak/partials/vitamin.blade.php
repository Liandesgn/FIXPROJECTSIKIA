
<div>
    <h2 class="mb-1 text-sm font-semibold text-[#542D3D]">
        Pemberian Vitamin
    </h2>

    <div class="mb-2 grid grid-cols-2 gap-3 px-1">
        <span class="text-xs font-medium text-gray-700">Jenis Vitamin</span>
        <span class="text-xs font-medium text-gray-700">Dosis</span>
    </div>

    @php
        $vitaminDipilih = old('id_jadwal_vitamin', $vitaminTerpilih);
    @endphp

    <div class="space-y-2">
        <label class="grid cursor-pointer grid-cols-2 items-center gap-3 rounded-lg border border-gray-200 px-3 py-2 hover:border-[#C9517E]">
            <span class="flex items-center gap-2">
                <input
                    type="radio"
                    name="id_jadwal_vitamin"
                    value=""
                    @checked($vitaminDipilih === null || $vitaminDipilih === '')
                    class="h-4 w-4 border-gray-300 text-[#C9517E] focus:ring-[#C9517E]"
                >
                <span class="text-sm text-gray-800">Tidak diberikan</span>
            </span>

            <span class="text-sm text-gray-500">-</span>
        </label>

        @forelse ($jadwalVitamin as $vitamin)
            <label class="grid cursor-pointer grid-cols-2 items-center gap-3 rounded-lg border border-gray-200 px-3 py-2 hover:border-[#C9517E] hover:bg-[#FFF8FA]">
                <span class="flex items-center gap-2">
                    <input
                        type="radio"
                        name="id_jadwal_vitamin"
                        value="{{ $vitamin->id_jadwal_vitamin }}"
                        @checked((string) $vitaminDipilih === (string) $vitamin->id_jadwal_vitamin)
                        class="h-4 w-4 border-gray-300 text-[#C9517E] focus:ring-[#C9517E]"
                    >

                    <span class="text-sm text-gray-800">
                        {{ $vitamin->jenis_vitamin }}
                    </span>
                </span>

                <span class="text-sm text-gray-600">
                    {{ $vitamin->dosis ?? '-' }}
                </span>

                @if ($vitamin->frekuensi)
                    <span class="col-span-2 pl-6 text-xs text-gray-500">
                        Frekuensi: {{ $vitamin->frekuensi }}
                    </span>
                @endif
            </label>
        @empty
            <p class="rounded-lg bg-gray-50 p-3 text-sm text-gray-500">
                Tidak ada jadwal vitamin yang tersedia untuk umur anak ini.
            </p>
        @endforelse
    </div>

    @error('id_jadwal_vitamin')
        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
