@extends('layouts.app')

@section('title', 'Kelola Jadwal')

@section('content')
@php
    $jadwalPosyandu    = $jadwals->where('tipe_jadwal', 'posyandu');
    $jadwalPemeriksaan = $jadwals->where('tipe_jadwal', 'pemeriksaan');

    $thClass = 'px-4 py-3 text-xs font-bold uppercase tracking-wide text-[#542D3D]';
    $tdClass = 'px-4 py-3.5 text-sm text-slate-800';
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Jadwal</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Kelola Jadwal</p>
    </div>

    {{-- Tab + tombol Tambah (sejajar) --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex w-full gap-2 rounded-2xl border border-[#F1D5E0] bg-white p-1.5 shadow-sm sm:w-auto" role="tablist" aria-label="Jenis jadwal">
            <button type="button" id="tab-pemeriksaan" role="tab"
                    class="tab-jadwal flex-1 whitespace-nowrap rounded-xl bg-[#C9517E] px-4 py-2 text-sm font-semibold text-white shadow-sm transition sm:flex-none">
                Pemeriksaan Ibu Hamil
            </button>

            <button type="button" id="tab-posyandu" role="tab"
                    class="tab-jadwal flex-1 whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold text-[#542D3D] transition hover:bg-[#FCE7F0] sm:flex-none">
                Posyandu
            </button>
        </div>

        <a id="tambah-jadwal"
           href="{{ route('admin.jadwal.create', ['tipe' => 'posyandu']) }}"
           class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 sm:w-auto">
            <span class="text-lg leading-none">+</span> Tambah Jadwal
        </a>
    </div>

    {{-- Daftar --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Pencarian dan filter bulan --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <label for="search-jadwal" class="sr-only">Cari jadwal</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input type="search" id="search-jadwal" autocomplete="off" placeholder="Cari Pemeriksaan"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>

                <div class="sm:w-56">
                    <label for="filter-bulan" class="sr-only">Filter bulan</label>
                    <select id="filter-bulan"
                            class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                        <option value="">Semua Bulan</option>

                        @foreach (range(1, 12) as $bulan)
                            <option value="{{ sprintf('%02d', $bulan) }}">
                                {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ================================= --}}
        {{-- TABEL POSYANDU --}}
        {{-- ================================= --}}
        <div id="panel-posyandu" class="hidden overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0]">
                    <tr>
                        <th class="{{ $thClass }}">No</th>
                        <th class="{{ $thClass }}">Jenis Kegiatan</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Hari, Tanggal</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Waktu</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Lokasi</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Kelurahan/Desa</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Posyandu</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Kader</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Bidan</th>
                        <th class="{{ $thClass }} text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($jadwalPosyandu as $jadwal)
                        @php
                            $tanggal = \Carbon\Carbon::parse($jadwal->tanggal);
                            $namaKelurahan = $jadwal->rw?->kelurahanDesa?->nama_kelurahan_desa ?? '-';
                            $nomorRw = $jadwal->rw?->nomor_rw;
                            $hariTanggal = $tanggal->locale('id')->translatedFormat('l, d/m/Y');
                            $namaPosyandu = $nomorRw ? 'Posyandu RW '.$nomorRw : '-';
                            $namaKader = $jadwal->kader?->user?->name ?? '-';
                            $namaBidan = $jadwal->bidan?->user?->name ?? '-';
                        @endphp

                        <tr class="jadwal-row transition hover:bg-[#FCE7F0]/40"
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

                            <td class="{{ $tdClass }} whitespace-nowrap font-semibold text-slate-700">{{ $loop->iteration }}</td>

                            <td class="{{ $tdClass }}">
                                <p class="font-bold text-[#542D3D]">{{ $jadwal->jenis_kegiatan }}</p>

                                {{-- Ringkasan untuk layar kecil --}}
                                <div class="mt-1 space-y-0.5 text-xs text-slate-700 xl:hidden">
                                    <p>{{ $hariTanggal }}, {{ substr($jadwal->waktu, 0, 5) }}</p>
                                    <p>{{ $jadwal->lokasi }} · {{ $namaKelurahan }}</p>
                                    <p>{{ $namaPosyandu }}</p>
                                    <p>Kader: {{ $namaKader }} · Bidan: {{ $namaBidan }}</p>
                                </div>
                            </td>

                            <td class="{{ $tdClass }} hidden whitespace-nowrap xl:table-cell">{{ $hariTanggal }}</td>
                            <td class="{{ $tdClass }} hidden whitespace-nowrap xl:table-cell">{{ substr($jadwal->waktu, 0, 5) }}</td>
                            <td class="{{ $tdClass }} hidden xl:table-cell">{{ $jadwal->lokasi }}</td>
                            <td class="{{ $tdClass }} hidden xl:table-cell">{{ $namaKelurahan }}</td>
                            <td class="{{ $tdClass }} hidden whitespace-nowrap xl:table-cell">{{ $namaPosyandu }}</td>
                            <td class="{{ $tdClass }} hidden xl:table-cell">{{ $namaKader }}</td>
                            <td class="{{ $tdClass }} hidden xl:table-cell">{{ $namaBidan }}</td>

                            <td class="whitespace-nowrap px-4 py-3.5">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.jadwal.edit', $jadwal->id_jadwal) }}"
                                       class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-[#E99AB7] px-3 text-xs font-bold text-[#542D3D] shadow-sm transition hover:bg-[#DD87A8] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.jadwal.destroy', $jadwal->id_jadwal) }}" method="POST" class="m-0"
                                          data-confirm-delete
                                          data-name="{{ $jadwal->jenis_kegiatan }}"
                                          data-title="Hapus jadwal {{ $jadwal->jenis_kegiatan }}?"
                                          data-text="Jadwal akan dihapus permanen. Lanjutkan?"
                                          data-confirm-text="Ya, Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-red-600 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr class="empty-row">
                            <td colspan="10" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">Belum ada jadwal Posyandu.</p>
                            </td>
                        </tr>
                    @endforelse

                    <tr id="posyandu-no-result" class="hidden">
                        <td colspan="10" class="px-5 py-12 text-center">
                            <p class="font-bold text-[#542D3D]">Jadwal Posyandu tidak ditemukan.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- ================================= --}}
        {{-- TABEL PEMERIKSAAN IBU HAMIL --}}
        {{-- ================================= --}}
        <div id="panel-pemeriksaan" class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0]">
                    <tr>
                        <th class="{{ $thClass }}">No</th>
                        <th class="{{ $thClass }}">Jenis Kegiatan</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Hari, Tanggal</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Waktu</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Lokasi</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Kelurahan/Desa</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Bidan Pemeriksa</th>
                        <th class="{{ $thClass }} hidden xl:table-cell">Catatan</th>
                        <th class="{{ $thClass }} text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($jadwalPemeriksaan as $jadwal)
                        @php
                            $tanggal = \Carbon\Carbon::parse($jadwal->tanggal);

                            $namaKelurahan = $jadwal->wilayah?->kelurahanDesa?->nama_kelurahan_desa
                                ?? $jadwal->wilayah?->nama_wilayah
                                ?? '-';

                            $catatan = $jadwal->pemeriksaan?->catatan ?? '-';
                            $hariTanggal = $tanggal->locale('id')->translatedFormat('l, d/m/Y');
                            $namaBidan = $jadwal->bidan?->user?->name ?? '-';
                        @endphp

                        <tr class="jadwal-row transition hover:bg-[#FCE7F0]/40"
                            data-type="pemeriksaan"
                            data-month="{{ $tanggal->format('m') }}"
                            data-search="{{ strtolower(
                                $jadwal->jenis_kegiatan . ' ' .
                                $jadwal->lokasi . ' ' .
                                $namaKelurahan . ' ' .
                                ($jadwal->bidan?->user?->name ?? '') . ' ' .
                                $catatan
                            ) }}">

                            <td class="{{ $tdClass }} whitespace-nowrap font-semibold text-slate-700">{{ $loop->iteration }}</td>

                            <td class="{{ $tdClass }}">
                                <p class="font-bold text-[#542D3D]">{{ $jadwal->jenis_kegiatan }}</p>

                                {{-- Ringkasan untuk layar kecil --}}
                                <div class="mt-1 space-y-0.5 text-xs text-slate-700 xl:hidden">
                                    <p>{{ $hariTanggal }}, {{ substr($jadwal->waktu, 0, 5) }}</p>
                                    <p>{{ $jadwal->lokasi }} · {{ $namaKelurahan }}</p>
                                    <p>Bidan: {{ $namaBidan }}</p>
                                    @if ($catatan !== '-')
                                        <p>Catatan: {{ $catatan }}</p>
                                    @endif
                                </div>
                            </td>

                            <td class="{{ $tdClass }} hidden whitespace-nowrap xl:table-cell">{{ $hariTanggal }}</td>
                            <td class="{{ $tdClass }} hidden whitespace-nowrap xl:table-cell">{{ substr($jadwal->waktu, 0, 5) }}</td>
                            <td class="{{ $tdClass }} hidden xl:table-cell">{{ $jadwal->lokasi }}</td>
                            <td class="{{ $tdClass }} hidden xl:table-cell">{{ $namaKelurahan }}</td>
                            <td class="{{ $tdClass }} hidden xl:table-cell">{{ $namaBidan }}</td>
                            <td class="{{ $tdClass }} hidden xl:table-cell">{{ $catatan }}</td>

                            <td class="whitespace-nowrap px-4 py-3.5">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.jadwal.edit', $jadwal->id_jadwal) }}"
                                       class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-[#E99AB7] px-3 text-xs font-bold text-[#542D3D] shadow-sm transition hover:bg-[#DD87A8] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.jadwal.destroy', $jadwal->id_jadwal) }}" method="POST" class="m-0"
                                          data-confirm-delete
                                          data-name="{{ $jadwal->jenis_kegiatan }}"
                                          data-title="Hapus jadwal {{ $jadwal->jenis_kegiatan }}?"
                                          data-text="Jadwal akan dihapus permanen. Lanjutkan?"
                                          data-confirm-text="Ya, Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-red-600 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr class="empty-row">
                            <td colspan="9" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">Belum ada jadwal pemeriksaan ibu hamil.</p>
                            </td>
                        </tr>
                    @endforelse

                    <tr id="pemeriksaan-no-result" class="hidden">
                        <td colspan="9" class="px-5 py-12 text-center">
                            <p class="font-bold text-[#542D3D]">Jadwal pemeriksaan tidak ditemukan.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

@push('scripts')
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

    const kelasAktif = ['bg-[#C9517E]', 'text-white', 'shadow-sm'];
    const kelasNonaktif = ['text-[#542D3D]', 'hover:bg-[#FCE7F0]'];

    function aturTab(tombol, aktif) {
        kelasAktif.forEach(function (c) { tombol.classList.toggle(c, aktif); });
        kelasNonaktif.forEach(function (c) { tombol.classList.toggle(c, !aktif); });
        tombol.setAttribute('aria-selected', String(aktif));
    }

    function gantiTab(tipe) {
        tipeAktif = tipe;

        const posyanduAktif = tipe === 'posyandu';

        panelPosyandu.classList.toggle('hidden', !posyanduAktif);
        panelPemeriksaan.classList.toggle('hidden', posyanduAktif);

        aturTab(tabPosyandu, posyanduAktif);
        aturTab(tabPemeriksaan, !posyanduAktif);

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
@endpush