
@extends('layouts.app')

@section('title', 'Jadwal')

@section('content')
@php
    $kartu = 'overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm';

    $band = 'rounded-2xl border border-[#F1D5E0] bg-[#FCE7F0] p-3 sm:p-4';

    $cariClass = 'w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';

    $pilihClass = 'w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';

    $ikonCari = '<svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>';

    $ikonKosong = '<svg class="mx-auto mb-3 h-10 w-10 text-[#E99AB7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/></svg>';
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- JUDUL --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
            Jadwal
        </h1>

        <p class="mt-1 text-sm font-medium text-slate-700">
            Informasi jadwal pemeriksaan ibu hamil dan kegiatan Posyandu di wilayah kerja Anda.
        </p>
    </div>

    {{-- TAB --}}
    <div
        class="flex w-full gap-2 rounded-2xl border border-[#F1D5E0] bg-white p-1.5 shadow-sm sm:w-fit"
        role="tablist"
        aria-label="Jenis jadwal"
    >
        <button
            type="button"
            id="tab-pemeriksaan"
            role="tab"
            aria-selected="true"
            aria-controls="konten-pemeriksaan"
            onclick="gantiTab('pemeriksaan')"
            class="flex-1 whitespace-nowrap rounded-xl bg-[#C9517E] px-4 py-2 text-sm font-semibold text-white shadow-sm transition sm:flex-none"
        >
            Pemeriksaan Ibu Hamil
        </button>

        <button
            type="button"
            id="tab-posyandu"
            role="tab"
            aria-selected="false"
            aria-controls="konten-posyandu"
            onclick="gantiTab('posyandu')"
            class="flex-1 whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold text-[#542D3D] transition hover:bg-[#FCE7F0] sm:flex-none"
        >
            Posyandu
        </button>
    </div>

    {{-- ===================================================== --}}
    {{-- TAB PEMERIKSAAN IBU HAMIL --}}
    {{-- ===================================================== --}}

    <div
        id="konten-pemeriksaan"
        role="tabpanel"
        aria-labelledby="tab-pemeriksaan"
        class="space-y-4"
    >

        {{-- PENCARIAN DAN FILTER TANGGAL --}}
        <div class="{{ $band }}">
            <div class="flex flex-col gap-3 sm:flex-row">

                <div class="relative flex-1">
                    <label for="cari-pemeriksaan" class="sr-only">
                        Cari pemeriksaan
                    </label>

                    {!! $ikonCari !!}

                    <input
                        type="text"
                        id="cari-pemeriksaan"
                        oninput="filterPemeriksaan()"
                        autocomplete="off"
                        placeholder="Cari Pemeriksaan"
                        class="{{ $cariClass }}"
                    >
                </div>

                <div class="sm:w-48">
                    <label for="tanggal-pemeriksaan" class="sr-only">
                        Pilih tanggal pemeriksaan
                    </label>

                    <input
                        type="date"
                        id="tanggal-pemeriksaan"
                        onchange="filterPemeriksaan()"
                        class="{{ $pilihClass }}"
                        title="Pilih tanggal pemeriksaan"
                    >
                </div>

            </div>
        </div>

        {{-- DAFTAR PEMERIKSAAN --}}
        <div id="daftar-pemeriksaan" class="space-y-4">

            @foreach ($jadwalPemeriksaan ?? [] as $jadwal)

                @php
                    $detail = $jadwal->jadwalPemeriksaan;

                    $namaBidan = $detail?->bidan?->user?->name ?? '-';

                    $tanggal = $jadwal->tanggal
                        ? \Carbon\Carbon::parse($jadwal->tanggal)
                        : null;

                    $tanggalFormat = $tanggal?->format('Y-m-d') ?? '';

                    $baris = [
                        'Tanggal' => $tanggal
                            ? $tanggal->translatedFormat('d F Y')
                            : '-',

                        'Waktu' => $jadwal->waktu
                            ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i')
                            : '-',

                        'Lokasi' => $jadwal->lokasi ?? '-',

                        'Bidan Pemeriksa' => $namaBidan,

                        'Catatan' => $detail?->catatan ?: '-',
                    ];

                    $teksCari = strtolower(
                        ($jadwal->jenis_kegiatan ?? '') . ' '
                        . ($jadwal->lokasi ?? '') . ' '
                        . $namaBidan . ' '
                        . ($detail?->catatan ?? '')
                    );
                @endphp

                <div
                    class="item-jadwal-pemeriksaan {{ $kartu }}"
                    data-cari="{{ $teksCari }}"
                    data-tanggal="{{ $tanggalFormat }}"
                >

                    <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
                        <h3 class="text-base font-bold text-[#542D3D]">
                            {{ $jadwal->jenis_kegiatan ?? 'Pemeriksaan Ibu Hamil' }}
                        </h3>
                    </div>

                    <dl class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                        @foreach ($baris as $label => $nilai)

                            <div class="min-w-0 {{ $label === 'Catatan' ? 'sm:col-span-2' : '' }}">

                                <dt class="text-xs font-semibold text-slate-600">
                                    {{ $label }}
                                </dt>

                                <dd class="mt-1 break-words text-sm font-bold text-[#542D3D]">
                                    {{ $nilai }}
                                </dd>

                            </div>

                        @endforeach

                    </dl>
                </div>

            @endforeach

            {{-- KOSONG PEMERIKSAAN --}}
            <div
                id="kosong-pemeriksaan"
                class="{{ $kartu }} px-4 py-12 text-center"
                style="{{ ($jadwalPemeriksaan ?? collect())->isEmpty() ? '' : 'display: none;' }}"
            >
                {!! $ikonKosong !!}

                <p class="font-bold text-[#542D3D]">
                    Belum ada jadwal pemeriksaan ibu hamil atau jadwal tidak ditemukan.
                </p>
            </div>

        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- TAB POSYANDU --}}
    {{-- ===================================================== --}}

    <div
        id="konten-posyandu"
        role="tabpanel"
        aria-labelledby="tab-posyandu"
        class="hidden space-y-4"
    >

        {{-- PENCARIAN, FILTER RW, DAN KALENDER KECIL --}}
        <div class="{{ $band }}">
            <div class="flex flex-col gap-3 sm:flex-row">

                {{-- PENCARIAN --}}
                <div class="relative flex-1">
                    <label for="cari-posyandu" class="sr-only">
                        Cari Posyandu
                    </label>

                    {!! $ikonCari !!}

                    <input
                        type="text"
                        id="cari-posyandu"
                        oninput="filterPosyandu()"
                        autocomplete="off"
                        placeholder="Cari Posyandu"
                        class="{{ $cariClass }}"
                    >
                </div>

                {{-- FILTER RW --}}
                <div class="sm:w-40">
                    <label for="rw-posyandu" class="sr-only">
                        Filter RW
                    </label>

                    <select
                        id="rw-posyandu"
                        onchange="filterPosyandu()"
                        class="{{ $pilihClass }}"
                    >
                        <option value="">RW</option>

                        @foreach ($daftarRw ?? collect() as $rw)
                            <option value="{{ $rw->nomor_rw }}">
                                RW {{ $rw->nomor_rw }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- KALENDER KECIL POSYANDU --}}
                <div class="sm:w-48">
                    <label for="tanggal-posyandu" class="sr-only">
                        Pilih tanggal Posyandu
                    </label>

                    <input
                        type="date"
                        id="tanggal-posyandu"
                        onchange="filterPosyandu()"
                        class="{{ $pilihClass }}"
                        title="Pilih tanggal Posyandu"
                    >
                </div>

            </div>
        </div>

        {{-- DAFTAR JADWAL POSYANDU --}}
        <div id="daftar-posyandu" class="space-y-4">

            @foreach ($jadwalPosyandu ?? [] as $jadwal)

                @php
                    $detail = $jadwal->jadwalPosyandu;

                    $kegiatan = $detail?->kegiatan;

                    $rw = $kegiatan?->rw;

                    $namaKader = $detail?->kader?->user?->name ?? '-';

                    $namaBidan = $detail?->bidan?->user?->name ?? '-';

                    $tanggal = $jadwal->tanggal
                        ? \Carbon\Carbon::parse($jadwal->tanggal)
                        : null;

                    $tanggalFormat = $tanggal?->format('Y-m-d') ?? '';

                    $baris = [
                        'Tanggal' => $tanggal
                            ? $tanggal->translatedFormat('d F Y')
                            : '-',

                        'Waktu' => $jadwal->waktu
                            ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i')
                            : '-',

                        'Lokasi' => $jadwal->lokasi ?? '-',

                        'Kader' => $namaKader,

                        'Bidan' => $namaBidan,

                        'RW' => $rw?->nomor_rw
                            ? 'RW ' . $rw->nomor_rw
                            : '-',
                    ];

                    $teksCari = strtolower(
                        ($jadwal->jenis_kegiatan ?? '') . ' '
                        . ($jadwal->lokasi ?? '') . ' '
                        . $namaKader . ' '
                        . $namaBidan . ' '
                        . ($rw?->nomor_rw ?? '')
                    );
                @endphp

                <div
                    class="item-jadwal-posyandu {{ $kartu }}"
                    data-cari="{{ $teksCari }}"
                    data-rw="{{ $rw?->nomor_rw ?? '' }}"
                    data-tanggal="{{ $tanggalFormat }}"
                >

                    {{-- JUDUL KEGIATAN --}}
                    <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
                        <h3 class="text-base font-bold text-[#542D3D]">
                            {{ $jadwal->jenis_kegiatan ?? 'Kegiatan Posyandu' }}
                        </h3>
                    </div>

                    {{-- DETAIL KEGIATAN --}}
                    <dl class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

                        @foreach ($baris as $label => $nilai)

                            <div class="min-w-0">

                                <dt class="text-xs font-semibold text-slate-600">
                                    {{ $label }}
                                </dt>

                                <dd class="mt-1 break-words text-sm font-bold text-[#542D3D]">
                                    {{ $nilai }}
                                </dd>

                            </div>

                        @endforeach

                    </dl>
                </div>

            @endforeach

            {{-- KOSONG POSYANDU --}}
            <div
                id="kosong-posyandu"
                class="{{ $kartu }} px-4 py-12 text-center"
                style="{{ ($jadwalPosyandu ?? collect())->isEmpty() ? '' : 'display: none;' }}"
            >
                {!! $ikonKosong !!}

                <p class="font-bold text-[#542D3D]">
                    Belum ada jadwal Posyandu atau jadwal tidak ditemukan.
                </p>
            </div>

        </div>
    </div>

</div>

{{-- ========================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ========================================================= --}}

<script>
    /*
    |--------------------------------------------------------------------------
    | TAB PEMERIKSAAN DAN POSYANDU
    |--------------------------------------------------------------------------
    */

    const kelasTabAktif = [
        'bg-[#C9517E]',
        'text-white',
        'shadow-sm'
    ];

    const kelasTabNonaktif = [
        'text-[#542D3D]',
        'hover:bg-[#FCE7F0]'
    ];

    function aturTabJadwal(tombol, aktif) {
        kelasTabAktif.forEach(function (kelas) {
            tombol.classList.toggle(kelas, aktif);
        });

        kelasTabNonaktif.forEach(function (kelas) {
            tombol.classList.toggle(kelas, !aktif);
        });

        tombol.setAttribute('aria-selected', String(aktif));
    }

    function gantiTab(tab) {
        document.getElementById('konten-pemeriksaan')
            .classList.toggle('hidden', tab !== 'pemeriksaan');

        document.getElementById('konten-posyandu')
            .classList.toggle('hidden', tab !== 'posyandu');

        aturTabJadwal(
            document.getElementById('tab-pemeriksaan'),
            tab === 'pemeriksaan'
        );

        aturTabJadwal(
            document.getElementById('tab-posyandu'),
            tab === 'posyandu'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER PEMERIKSAAN IBU HAMIL
    |--------------------------------------------------------------------------
    */

    function filterPemeriksaan() {
        const input = document.getElementById('cari-pemeriksaan');

        const filterTanggal = document.getElementById('tanggal-pemeriksaan');

        const kataKunci = input.value.toLowerCase().trim();

        const tanggalDipilih = filterTanggal.value;

        const daftar = document.querySelectorAll(
            '.item-jadwal-pemeriksaan'
        );

        let jumlahTampil = 0;

        daftar.forEach(item => {
            const teks = item.dataset.cari || '';

            const tanggal = item.dataset.tanggal || '';

            const cocokCari = teks.includes(kataKunci);

            const cocokTanggal =
                !tanggalDipilih || tanggal === tanggalDipilih;

            const tampil = cocokCari && cocokTanggal;

            item.classList.toggle('hidden', !tampil);

            if (tampil) {
                jumlahTampil++;
            }
        });

        const kosong = document.getElementById('kosong-pemeriksaan');

        if (kosong) {
            kosong.style.display = jumlahTampil === 0 ? '' : 'none';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER POSYANDU
    |--------------------------------------------------------------------------
    */

    function filterPosyandu() {
        const input = document.getElementById('cari-posyandu');

        const filterRw = document.getElementById('rw-posyandu');

        const filterTanggal = document.getElementById('tanggal-posyandu');

        const kataKunci = input.value.toLowerCase().trim();

        const rwDipilih = filterRw.value;

        const tanggalDipilih = filterTanggal.value;

        const daftar = document.querySelectorAll(
            '.item-jadwal-posyandu'
        );

        let jumlahTampil = 0;

        daftar.forEach(item => {
            const teks = item.dataset.cari || '';

            const rw = item.dataset.rw || '';

            const tanggal = item.dataset.tanggal || '';

            const cocokCari = teks.includes(kataKunci);

            const cocokRw = !rwDipilih || rw === rwDipilih;

            const cocokTanggal =
                !tanggalDipilih || tanggal === tanggalDipilih;

            const tampil = cocokCari && cocokRw && cocokTanggal;

            item.classList.toggle('hidden', !tampil);

            if (tampil) {
                jumlahTampil++;
            }
        });

        const kosong = document.getElementById('kosong-posyandu');

        if (kosong) {
            kosong.style.display = jumlahTampil === 0 ? '' : 'none';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INISIALISASI HALAMAN
    |--------------------------------------------------------------------------
    | Tab Pemeriksaan menjadi tab pertama yang tampil.
    | Kalender besar Posyandu sudah tidak digunakan.
    */

    document.addEventListener('DOMContentLoaded', function () {
        gantiTab('pemeriksaan');

        filterPemeriksaan();

        filterPosyandu();
    });
</script>

@endsection
