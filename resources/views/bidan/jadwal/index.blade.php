
@extends('layouts.app')

@section('title', 'Jadwal')

@section('content')
<div class="min-h-screen bg-[#FFF8FA] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-[#542D3D]">Jadwal</h1>
            <p class="mt-1 text-sm text-gray-500">
                Informasi jadwal pemeriksaan ibu hamil dan kegiatan Posyandu
                di wilayah kerja Anda.
            </p>
        </div>

        {{-- Tab --}}
        <div class="mb-5 flex gap-6 border-b border-pink-200">
            <button type="button"
                onclick="gantiTab('pemeriksaan')"
                id="tab-pemeriksaan"
                class="border-b-2 border-[#C9517E] px-1 pb-3 text-sm font-semibold text-[#C9517E]">
                Pemeriksaan Ibu Hamil
            </button>

            <button type="button"
                onclick="gantiTab('posyandu')"
                id="tab-posyandu"
                class="border-b-2 border-transparent px-1 pb-3 text-sm font-semibold text-gray-500">
                Posyandu
            </button>
        </div>

        {{-- TAB PEMERIKSAAN IBU HAMIL --}}
        <div id="konten-pemeriksaan">

            {{-- Filter Pemeriksaan --}}
            <div class="mb-5 flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <input type="text"
                        id="cari-pemeriksaan"
                        oninput="filterJadwal('pemeriksaan')"
                        placeholder="Cari pemeriksaan..."
                        class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]">

                    <span class="absolute left-3 top-2.5 text-gray-400">⌕</span>
                </div>

                <select id="bulan-pemeriksaan"
                    onchange="filterJadwal('pemeriksaan')"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-[#C9517E] focus:outline-none">
                    <option value="">Semua Bulan</option>
                    @foreach(range(1, 12) as $bulan)
                        <option value="{{ sprintf('%02d', $bulan) }}">
                            {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Daftar Jadwal Pemeriksaan --}}
            <div id="daftar-pemeriksaan" class="space-y-4">
                @forelse($jadwalPemeriksaan as $jadwal)
                    @php
                        $detail = $jadwal->jadwalPemeriksaan;
                        $namaBidan = $detail?->bidan?->user?->name ?? '-';
                        $tanggal = $jadwal->tanggal;
                    @endphp

                    <div class="item-jadwal-pemeriksaan rounded-xl border border-pink-100 bg-white p-5 shadow-sm"
                        data-cari="{{ strtolower(($jadwal->jenis_kegiatan ?? '') . ' ' . ($jadwal->lokasi ?? '') . ' ' . $namaBidan) }}"
                        data-bulan="{{ $tanggal ? $tanggal->format('m') : '' }}">

                        <h3 class="mb-4 font-semibold text-[#542D3D]">
                            {{ $jadwal->jenis_kegiatan ?? 'Pemeriksaan Ibu Hamil' }}
                        </h3>

                        <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                            <div>
                                <p class="text-gray-500">Tanggal</p>
                                <p class="font-medium text-gray-800">
                                    {{ $tanggal ? $tanggal->translatedFormat('d F Y') : '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Waktu</p>
                                <p class="font-medium text-gray-800">
                                    {{ $jadwal->waktu ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i') : '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Lokasi</p>
                                <p class="font-medium text-gray-800">
                                    {{ $jadwal->lokasi ?? '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Bidan Pemeriksa</p>
                                <p class="font-medium text-gray-800">
                                    {{ $namaBidan }}
                                </p>
                            </div>

                            <div class="sm:col-span-2">
                                <p class="text-gray-500">Catatan</p>
                                <p class="font-medium text-gray-800">
                                    {{ $detail?->catatan ?: '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-pink-200 bg-white px-4 py-10 text-center">
                        <p class="font-medium text-gray-600">
                            Belum ada jadwal pemeriksaan ibu hamil.
                        </p>
                    </div>
                @endforelse
            </div>

            <p id="kosong-pemeriksaan"
                class="hidden rounded-xl bg-white p-6 text-center text-sm text-gray-500">
                Jadwal yang dicari tidak ditemukan.
            </p>
        </div>

        {{-- TAB POSYANDU --}}
        <div id="konten-posyandu" class="hidden">

            {{-- Filter Posyandu --}}
            <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">

                {{-- Pencarian --}}
                <div class="relative">
                    <input type="text"
                        id="cari-posyandu"
                        oninput="filterJadwal('posyandu')"
                        placeholder="Cari Posyandu..."
                        class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]">

                    <span class="absolute left-3 top-2.5 text-gray-400">⌕</span>
                </div>

                {{-- Filter RW --}}
                <select id="rw-rt-posyandu"
                    onchange="filterJadwal('posyandu')"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-[#C9517E] focus:outline-none">

                    <option value="">Semua RW/RT</option>

                    @foreach($daftarRw ?? collect() as $rw)
                        <option value="{{ $rw->nomor_rw }}">
                            RW {{ $rw->nomor_rw }}
                        </option>
                    @endforeach
                </select>

                {{-- Filter Bulan --}}
                <select id="bulan-posyandu"
                    onchange="filterJadwal('posyandu')"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-[#C9517E] focus:outline-none">
                    <option value="">Semua Bulan</option>

                    @foreach(range(1, 12) as $bulan)
                        <option value="{{ sprintf('%02d', $bulan) }}">
                            {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Jadwal Posyandu Mendatang --}}
            <div id="daftar-posyandu" class="space-y-4">
                @forelse($jadwalPosyandu as $jadwal)
                    @php
                        $detail = $jadwal->jadwalPosyandu;
                        $kegiatan = $detail?->kegiatan;
                        $rw = $kegiatan?->rw;
                        $namaKader = $detail?->kader?->user?->name ?? '-';
                        $namaBidan = $detail?->bidan?->user?->name ?? '-';
                        $tanggal = $jadwal->tanggal;
                    @endphp

                    <div class="item-jadwal-posyandu rounded-xl border border-pink-100 bg-white p-5 shadow-sm"
                        data-cari="{{ strtolower(($jadwal->jenis_kegiatan ?? '') . ' ' . ($jadwal->lokasi ?? '') . ' ' . $namaKader . ' ' . $namaBidan) }}"
                        data-rw="{{ $rw?->nomor_rw ?? '' }}"
                        data-bulan="{{ $tanggal ? $tanggal->format('m') : '' }}">

                        <h3 class="mb-4 font-semibold text-[#542D3D]">
                            {{ $jadwal->jenis_kegiatan ?? 'Kegiatan Posyandu' }}
                        </h3>

                        <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                            <div>
                                <p class="text-gray-500">Tanggal</p>
                                <p class="font-medium text-gray-800">
                                    {{ $tanggal ? $tanggal->translatedFormat('d F Y') : '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Waktu</p>
                                <p class="font-medium text-gray-800">
                                    {{ $jadwal->waktu ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i') : '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Lokasi</p>
                                <p class="font-medium text-gray-800">
                                    {{ $jadwal->lokasi ?? '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Kader</p>
                                <p class="font-medium text-gray-800">
                                    {{ $namaKader }}
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-500">Bidan Pemeriksa</p>
                                <p class="font-medium text-gray-800">
                                    {{ $namaBidan }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-pink-200 bg-white px-4 py-10 text-center">
                        <p class="font-medium text-gray-600">
                            Belum ada jadwal Posyandu mendatang.
                        </p>
                    </div>
                @endforelse
            </div>

            <p id="kosong-posyandu"
                class="hidden rounded-xl bg-white p-6 text-center text-sm text-gray-500">
                Jadwal yang dicari tidak ditemukan.
            </p>

        </div>

    </div>
</div>

<script>
    function gantiTab(tab) {
        const pemeriksaan = document.getElementById('konten-pemeriksaan');
        const posyandu = document.getElementById('konten-posyandu');
        const tabPemeriksaan = document.getElementById('tab-pemeriksaan');
        const tabPosyandu = document.getElementById('tab-posyandu');

        pemeriksaan.classList.toggle('hidden', tab !== 'pemeriksaan');
        posyandu.classList.toggle('hidden', tab !== 'posyandu');

        tabPemeriksaan.classList.toggle('border-[#C9517E]', tab === 'pemeriksaan');
        tabPemeriksaan.classList.toggle('text-[#C9517E]', tab === 'pemeriksaan');
        tabPemeriksaan.classList.toggle('border-transparent', tab !== 'pemeriksaan');
        tabPemeriksaan.classList.toggle('text-gray-500', tab !== 'pemeriksaan');

        tabPosyandu.classList.toggle('border-[#C9517E]', tab === 'posyandu');
        tabPosyandu.classList.toggle('text-[#C9517E]', tab === 'posyandu');
        tabPosyandu.classList.toggle('border-transparent', tab !== 'posyandu');
        tabPosyandu.classList.toggle('text-gray-500', tab !== 'posyandu');
    }

    function filterJadwal(jenis) {
        const cari = document.getElementById('cari-' + jenis).value.toLowerCase();
        const bulan = document.getElementById('bulan-' + jenis).value;

        let rw = '';

        if (jenis === 'posyandu') {
            rw = document.getElementById('rw-rt-posyandu').value;
        }

        const items = document.querySelectorAll('.item-jadwal-' + jenis);
        let jumlahTampil = 0;

        items.forEach(item => {
            const cocokCari = item.dataset.cari.includes(cari);
            const cocokBulan = !bulan || item.dataset.bulan === bulan;
            const cocokRw = !rw || item.dataset.rw === rw;

            const tampil = cocokCari && cocokBulan && cocokRw;

            item.classList.toggle('hidden', !tampil);

            if (tampil) {
                jumlahTampil++;
            }
        });

        document.getElementById('kosong-' + jenis)
            .classList.toggle('hidden', jumlahTampil > 0);
    }
</script>
@endsection
