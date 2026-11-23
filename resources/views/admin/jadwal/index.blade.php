
@extends('layouts.app')

@section('title', 'Kelola Jadwal')

@section('content')
<div class="min-h-screen bg-white px-4 py-5 sm:px-6">

    <div class="mx-auto max-w-7xl">

        {{-- Judul --}}
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-gray-900">
                Kelola Jadwal
            </h1>
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Tab Jadwal --}}
        <div class="flex items-center gap-8 border-b border-gray-300">
            <button type="button"
                    id="tab-pemeriksaan"
                    class="tab-jadwal border-b-2 border-gray-900 px-3 py-3 text-sm font-semibold text-gray-900">
                Pemeriksaan Ibu Hamil
            </button>

            <button type="button"
                    id="tab-posyandu"
                    class="tab-jadwal border-b-2 border-transparent px-3 py-3 text-sm font-semibold text-gray-500">
                Posyandu
            </button>
        </div>

        {{-- Pencarian dan Filter --}}
        <div class="my-4 flex flex-col gap-3 sm:flex-row sm:items-center">

            <div class="relative w-full sm:max-w-xs">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                    🔍
                </span>

                <input type="text"
                       id="search-jadwal"
                       placeholder="Cari Posyandu"
                       class="w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-3 text-sm focus:border-pink-400 focus:outline-none focus:ring-1 focus:ring-pink-400">
            </div>

            <select id="filter-bulan"
                    class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-700 focus:border-pink-400 focus:outline-none">
                <option value="">Semua Bulan</option>

                @foreach (range(1, 12) as $bulan)
                    <option value="{{ sprintf('%02d', $bulan) }}">
                        {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>

        <a id="tambah-jadwal"
        href="{{ route('admin.jadwal.create', ['tipe' => 'posyandu']) }}"
        class="sm:ml-auto inline-flex items-center justify-center rounded-lg bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#B7436D]">
            + Tambah Jadwal
        </a>

        </div>

        {{-- ================================= --}}
        {{-- TABEL POSYANDU --}}
        {{-- ================================= --}}
        <div id="panel-posyandu" class="hidden overflow-x-auto">

            <table class="min-w-full border-collapse text-left text-xs">

                <thead class="bg-[#F3F6FA] text-gray-600">
                    <tr>
                        <th class="px-3 py-3">NO</th>
                        <th class="px-3 py-3">JENIS KEGIATAN</th>
                        <th class="px-3 py-3">HARI, TANGGAL</th>
                        <th class="px-3 py-3">WAKTU</th>
                        <th class="px-3 py-3">LOKASI</th>
                        <th class="px-3 py-3">KELURAHAN/DESA</th>
                        <th class="px-3 py-3">POSYANDU</th>
                        <th class="px-3 py-3">KADER</th>
                        <th class="px-3 py-3">BIDAN</th>
                        <th class="px-3 py-3 text-center">AKSI</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @php
                        $jadwalPosyandu = $jadwals->where('tipe_jadwal', 'posyandu');
                    @endphp

                    @forelse ($jadwalPosyandu as $jadwal)
                        @php
                            $tanggal = \Carbon\Carbon::parse($jadwal->tanggal);
                            $namaKelurahan = $jadwal->rw?->kelurahanDesa?->nama_kelurahan_desa ?? '-';
                            $nomorRw = $jadwal->rw?->nomor_rw;
                        @endphp

                        <tr class="jadwal-row hover:bg-gray-50"
                            data-type="posyandu"
                            data-month="{{ $tanggal->format('m') }}"
                            data-search="{{ strtolower(
                                $jadwal->jenis_kegiatan . ' ' .
                                $jadwal->lokasi . ' ' .
                                $namaKelurahan . ' ' .
                                ($nomorRw ? 'posyandu rw '.$nomorRw : '') . ' ' .
                                ($jadwal->kader?->user?->name ?? '') . ' ' .
                                ($jadwal->bidan?->user?->name ?? '')
                            ) }}">

                            <td class="whitespace-nowrap px-3 py-4">
                                {{ $loop->iteration }}
                            </td>

                            <td class="min-w-32 px-3 py-4 font-semibold text-gray-800">
                                {{ $jadwal->jenis_kegiatan }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-4">
                                {{ $tanggal->locale('id')->translatedFormat('l, d/m/Y') }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-4">
                                {{ substr($jadwal->waktu, 0, 5) }}
                            </td>

                            <td class="min-w-28 px-3 py-4">
                                {{ $jadwal->lokasi }}
                            </td>

                            <td class="px-3 py-4">
                                {{ $namaKelurahan }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-4">
                                {{ $nomorRw ? 'Posyandu RW '.$nomorRw : '-' }}
                            </td>

                            <td class="px-3 py-4">
                                {{ $jadwal->kader?->user?->name ?? '-' }}
                            </td>

                            <td class="px-3 py-4">
                                {{ $jadwal->bidan?->user?->name ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-4">
                                <div class="flex items-center justify-center gap-1.5">

                                    <a href="{{ route('admin.jadwal.edit', $jadwal->id_jadwal) }}"
                                       class="rounded-md border border-teal-300 px-2 py-1 text-xs font-semibold text-teal-700 hover:bg-teal-50">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.jadwal.destroy', $jadwal->id_jadwal) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="rounded-md border border-red-300 px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr class="empty-row">
                            <td colspan="10" class="px-4 py-10 text-center text-gray-500">
                                Belum ada jadwal Posyandu.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="posyandu-no-result" class="hidden">
                        <td colspan="10" class="px-4 py-10 text-center text-gray-500">
                            Jadwal Posyandu tidak ditemukan.
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        {{-- ================================= --}}
        {{-- TABEL PEMERIKSAAN IBU HAMIL --}}
        {{-- ================================= --}}
        <div id="panel-pemeriksaan" class="overflow-x-auto">

            <table class="min-w-full border-collapse text-left text-xs">

                <thead class="bg-[#F3F6FA] text-gray-600">
                    <tr>
                        <th class="px-3 py-3">NO</th>
                        <th class="px-3 py-3">JENIS KEGIATAN</th>
                        <th class="px-3 py-3">HARI, TANGGAL</th>
                        <th class="px-3 py-3">WAKTU</th>
                        <th class="px-3 py-3">LOKASI</th>
                        <th class="px-3 py-3">KELURAHAN/DESA</th>
                        <th class="px-3 py-3">BIDAN PEMERIKSA</th>
                        <th class="px-3 py-3">CATATAN</th>
                        <th class="px-3 py-3 text-center">AKSI</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @php
                        $jadwalPemeriksaan = $jadwals->where('tipe_jadwal', 'pemeriksaan');
                    @endphp

                    @forelse ($jadwalPemeriksaan as $jadwal)
                        @php
                            $tanggal = \Carbon\Carbon::parse($jadwal->tanggal);

                            $namaKelurahan = $jadwal->wilayah?->kelurahanDesa?->nama_kelurahan_desa
                                ?? $jadwal->wilayah?->nama_wilayah
                                ?? '-';

                            $catatan = $jadwal->pemeriksaan?->catatan ?? '-';
                        @endphp

                        <tr class="jadwal-row hover:bg-gray-50"
                            data-type="pemeriksaan"
                            data-month="{{ $tanggal->format('m') }}"
                            data-search="{{ strtolower(
                                $jadwal->jenis_kegiatan . ' ' .
                                $jadwal->lokasi . ' ' .
                                $namaKelurahan . ' ' .
                                ($jadwal->bidan?->user?->name ?? '') . ' ' .
                                $catatan
                            ) }}">

                            <td class="px-3 py-4">
                                {{ $loop->iteration }}
                            </td>

                            <td class="min-w-32 px-3 py-4 font-semibold text-gray-800">
                                {{ $jadwal->jenis_kegiatan }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-4">
                                {{ $tanggal->locale('id')->translatedFormat('l, d/m/Y') }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-4">
                                {{ substr($jadwal->waktu, 0, 5) }}
                            </td>

                            <td class="min-w-28 px-3 py-4">
                                {{ $jadwal->lokasi }}
                            </td>

                            <td class="px-3 py-4">
                                {{ $namaKelurahan }}
                            </td>

                            <td class="px-3 py-4">
                                {{ $jadwal->bidan?->user?->name ?? '-' }}
                            </td>

                            {{-- Kolom Catatan --}}
                            <td class="min-w-32 px-3 py-4">
                                {{ $catatan }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-4">
                                <div class="flex items-center justify-center gap-1.5">

                                    <a href="{{ route('admin.jadwal.edit', $jadwal->id_jadwal) }}"
                                       class="rounded-md border border-teal-300 px-2 py-1 text-xs font-semibold text-teal-700 hover:bg-teal-50">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.jadwal.destroy', $jadwal->id_jadwal) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="rounded-md border border-red-300 px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr class="empty-row">
                            <td colspan="9" class="px-4 py-10 text-center text-gray-500">
                                Belum ada jadwal pemeriksaan ibu hamil.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="pemeriksaan-no-result" class="hidden">
                        <td colspan="9" class="px-4 py-10 text-center text-gray-500">
                            Jadwal pemeriksaan tidak ditemukan.
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- Script Tab, Pencarian, dan Filter Bulan --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const tabPosyandu = document.getElementById('tab-posyandu');
    const tabPemeriksaan = document.getElementById('tab-pemeriksaan');

    const panelPosyandu = document.getElementById('panel-posyandu');
    const panelPemeriksaan = document.getElementById('panel-pemeriksaan');

    const searchInput = document.getElementById('search-jadwal');
    const filterBulan = document.getElementById('filter-bulan');

    const params = new URLSearchParams(window.location.search);
    let tipeAktif = params.get('tab') === 'posyandu'
        ? 'posyandu'
        : 'pemeriksaan';

    function gantiTab(tipe) {
        tipeAktif = tipe;

        const posyanduAktif = tipe === 'posyandu';

        panelPosyandu.classList.toggle('hidden', !posyanduAktif);
        panelPemeriksaan.classList.toggle('hidden', posyanduAktif);

        tabPosyandu.classList.toggle('border-gray-900', posyanduAktif);
        tabPosyandu.classList.toggle('text-gray-900', posyanduAktif);
        tabPosyandu.classList.toggle('border-transparent', !posyanduAktif);
        tabPosyandu.classList.toggle('text-gray-500', !posyanduAktif);

        tabPemeriksaan.classList.toggle('border-gray-900', !posyanduAktif);
        tabPemeriksaan.classList.toggle('text-gray-900', !posyanduAktif);
        tabPemeriksaan.classList.toggle('border-transparent', posyanduAktif);
        tabPemeriksaan.classList.toggle('text-gray-500', posyanduAktif);

        document.getElementById('tambah-jadwal').href =
        `{{ route('admin.jadwal.create') }}?tipe=${tipe}`;

        searchInput.placeholder = posyanduAktif
            ? 'Cari Posyandu'
            : 'Cari Pemeriksaan';

        searchInput.value = '';
        filterBulan.value = '';

        filterJadwal();
    }

    function filterJadwal() {
        const kataKunci = searchInput.value.toLowerCase().trim();
        const bulan = filterBulan.value;

        const rows = document.querySelectorAll(
            `.jadwal-row[data-type="${tipeAktif}"]`
        );

        let jumlahTampil = 0;

        rows.forEach(row => {
            const cocokKata = row.dataset.search.includes(kataKunci);
            const cocokBulan = bulan === '' || row.dataset.month === bulan;

            const tampil = cocokKata && cocokBulan;

            row.classList.toggle('hidden', !tampil);

            if (tampil) {
                jumlahTampil++;
            }
        });

        const noResult = document.getElementById(
            tipeAktif + '-no-result'
        );

        if (noResult) {
            noResult.classList.toggle('hidden', jumlahTampil > 0);
        }
    }

    tabPosyandu.addEventListener('click', () => gantiTab('posyandu'));
    tabPemeriksaan.addEventListener('click', () => gantiTab('pemeriksaan'));

    searchInput.addEventListener('input', filterJadwal);
    filterBulan.addEventListener('change', filterJadwal);

gantiTab(tipeAktif);

});
</script>
@endsection
