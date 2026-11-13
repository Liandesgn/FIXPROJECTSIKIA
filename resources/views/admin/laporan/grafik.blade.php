{{-- Parameter: $tipe = 'status' (donat) | 'pemeriksaan' (batang). --}}
@if ($tipe === 'status')
    {{-- Parameter: $donat (daftar: judul, normal, perhatian, total, persen) --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @foreach ($donat as $d)
            <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-lg font-bold text-[#542D3D]">{{ $d['judul'] }}</h2>

                @if ($d['total'] > 0)
                    <div class="flex flex-col items-center gap-5 sm:flex-row sm:justify-center">
                        <div class="relative h-40 w-40 shrink-0 rounded-full"
                             style="background: conic-gradient(#16a34a 0% {{ $d['persen'] }}%, #f59e0b {{ $d['persen'] }}% 100%);">
                            <div class="absolute inset-5 flex flex-col items-center justify-center rounded-full bg-white">
                                <span class="text-3xl font-bold text-[#542D3D]">{{ $d['total'] }}</span>
                                <span class="text-xs text-slate-600">Pemeriksaan</span>
                            </div>
                        </div>

                        <div class="w-full space-y-3 sm:w-auto">
                            <div class="flex items-center justify-between gap-6">
                                <span class="inline-flex items-center gap-2 text-sm text-slate-700"><span class="h-3 w-3 rounded-full bg-green-600"></span>Normal</span>
                                <span class="text-sm font-bold text-[#542D3D]">{{ $d['normal'] }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-6">
                                <span class="inline-flex items-center gap-2 text-sm text-slate-700"><span class="h-3 w-3 rounded-full bg-amber-500"></span>Perlu Perhatian</span>
                                <span class="text-sm font-bold text-[#542D3D]">{{ $d['perhatian'] }}</span>
                            </div>
                            <p class="text-xs text-slate-600">Normal: {{ $d['persen'] }}%</p>
                        </div>
                    </div>
                @else
                    <div class="flex h-40 items-center justify-center rounded-xl bg-[#FCE7F0]/60">
                        <p class="px-4 text-center text-sm text-slate-600">Belum ada data pemeriksaan pada periode ini.</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    {{-- Parameter: $rekapPemeriksaan (bulan, pemeriksaan_ibu, pemeriksaan_anak) --}}
    @php
        $rekap = collect($rekapPemeriksaan);
        $maks  = max((int) $rekap->max('pemeriksaan_ibu'), (int) $rekap->max('pemeriksaan_anak'), 1);
        $tinggiMaks = 150; // px
    @endphp

    <div class="rounded-2xl border border-[#F1D5E0] bg-white p-4 shadow-sm sm:p-5">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-[#542D3D]">Grafik Pemeriksaan</h2>
                <p class="mt-1 text-sm text-slate-600">Perbandingan jumlah pemeriksaan Ibu dan Anak per bulan.</p>
            </div>

            <div class="flex items-center gap-4 text-xs font-semibold text-[#542D3D]">
                <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm bg-[#C9517E]"></span>Pemeriksaan Ibu</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm bg-[#E99AB7]"></span>Pemeriksaan Anak</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <div class="flex min-w-[18rem] items-end justify-around gap-4 border-b border-[#F1D5E0] px-2 pt-2">
                @foreach ($rekap as $r)
                    @php
                        $ibu  = (int) $r->pemeriksaan_ibu;
                        $anak = (int) $r->pemeriksaan_anak;
                    @endphp
                    <div class="flex flex-col items-center">
                        <div class="flex items-end gap-1.5" style="height: {{ $tinggiMaks + 20 }}px;">
                            <div class="flex flex-col items-center justify-end">
                                <span class="mb-1 text-xs font-bold text-[#542D3D]">{{ $ibu }}</span>
                                <div class="w-5 rounded-t-md bg-[#C9517E] sm:w-7" style="height: {{ max(4, round(($ibu / $maks) * $tinggiMaks)) }}px;"></div>
                            </div>
                            <div class="flex flex-col items-center justify-end">
                                <span class="mb-1 text-xs font-bold text-[#542D3D]">{{ $anak }}</span>
                                <div class="w-5 rounded-t-md bg-[#E99AB7] sm:w-7" style="height: {{ max(4, round(($anak / $maks) * $tinggiMaks)) }}px;"></div>
                            </div>
                        </div>
                        <span class="py-2 text-xs font-semibold text-slate-700">{{ $r->bulan }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif