
@extends('layouts.app')

@section('title', 'Pendataan Anak')

@section('content')
@php
    use Carbon\Carbon;

    $sudahDiperiksa = $pemeriksaan->filter(
        fn ($anak) => !empty($anak->pemeriksaan_id)
    )->count();

    $belumDiperiksa = $pemeriksaan->count() - $sudahDiperiksa;

    $statusOptions = $pemeriksaan
        ->filter(fn ($anak) => !empty($anak->pemeriksaan_id))
        ->pluck('status_stunting')
        ->filter()
        ->unique()
        ->sort()
        ->values();

    if ($belumDiperiksa > 0) {
        $statusOptions->push('Belum diperiksa');
    }

    $periodeOptions = $pemeriksaan
        ->filter(fn ($anak) => !empty($anak->tgl_pemeriksaan))
        ->map(fn ($anak) => Carbon::parse($anak->tgl_pemeriksaan)->format('Y-m'))
        ->unique()
        ->sort()
        ->values();
@endphp

<div class="min-h-screen bg-white">

    {{-- HEADER HALAMAN --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            Pendataan Anak
        </h1>

        <p class="mt-1 text-sm text-gray-600">
            Pendataan Posyandu
        </p>
    </div>

    {{-- INFORMASI KEGIATAN --}}
    <div class="mb-5 rounded-xl border border-gray-200 bg-white px-5 py-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-sm font-bold text-gray-900">
                    {{ $kegiatan->jenis_kegiatan ?? 'Kegiatan Posyandu' }}
                    - {{ $kegiatan->wilayah->nama_wilayah ?? 'Wilayah Posyandu' }}
                </h2>

                <p class="mt-1 text-xs text-gray-500">
                    Kegiatan
                    {{ Carbon::parse($kegiatan->tanggal)->format('d/m/Y') }}
                    - {{ $kegiatan->lokasi ?? '-' }}
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full border border-green-300 bg-green-50 px-3 py-1 text-xs font-semibold text-green-600">
                <span class="mr-1.5 h-2 w-2 rounded-full bg-green-500"></span>
                Berlangsung
            </span>

        </div>
    </div>

    {{-- PENCARIAN DAN FILTER --}}
    <div class="mb-4 grid grid-cols-1 gap-2 sm:grid-cols-3">

        {{-- Pencarian --}}
        <div class="relative sm:col-span-1">
            <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.8">
                <circle cx="10.5" cy="10.5" r="6.5"/>
                <path d="m16 16 5 5"/>
            </svg>

            <input
                type="text"
                id="cariAnak"
                placeholder="Cari nama / NIK"
                class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]"
            >
        </div>

        {{-- Filter Status --}}
        <select
            id="filterStatus"
            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-600 focus:border-[#C9517E] focus:outline-none"
        >
            <option value="">Semua Status</option>

            @foreach($statusOptions as $status)
                <option value="{{ strtolower($status) }}">
                    {{ $status }}
                </option>
            @endforeach
        </select>

        {{-- Filter Periode --}}
        <select
            id="filterPeriode"
            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-600 focus:border-[#C9517E] focus:outline-none"
        >
            <option value="">Semua Periode</option>

            @foreach($periodeOptions as $periode)
                <option value="{{ $periode }}">
                    {{ Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}
                </option>
            @endforeach
        </select>

    </div>

    {{-- TABEL DATA ANAK --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

        <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

                <thead class="border-b border-gray-200 bg-white">
                    <tr class="text-left text-xs font-semibold uppercase text-gray-700">

                        <th class="whitespace-nowrap px-4 py-3">
                            No
                        </th>

                        <th class="whitespace-nowrap px-4 py-3">
                            Anak
                        </th>

                        <th class="whitespace-nowrap px-4 py-3">
                            NIK
                        </th>

                        <th class="whitespace-nowrap px-4 py-3">
                            Usia
                        </th>

                        <th class="whitespace-nowrap px-4 py-3">
                            Status
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-center">
                            Aksi
                        </th>

                    </tr>
                </thead>

                <tbody id="daftarAnak" class="divide-y divide-gray-100">

                    @forelse($pemeriksaan as $index => $anak)

                        @php
                            $tanggalLahir = $anak->tgl_lahir
                                ? Carbon::parse($anak->tgl_lahir)
                                : null;

                            $tanggalAcuan = !empty($anak->tgl_pemeriksaan)
                                ? Carbon::parse($anak->tgl_pemeriksaan)
                                : Carbon::parse($kegiatan->tanggal);

                            $usiaBulan = $tanggalLahir
                                ? (int) $tanggalLahir->diffInMonths($tanggalAcuan)
                                : null;

                            $sudah = !empty($anak->pemeriksaan_id);

                            $statusText = $sudah
                                ? ($anak->status_stunting ?? 'Belum ada hasil')
                                : 'Belum diperiksa';

                            $statusLower = strtolower($statusText);

                            $statusClass = str_contains($statusLower, 'normal')
                                ? 'bg-green-100 text-green-600'
                                : (str_contains($statusLower, 'stunting')
                                    ? 'bg-red-100 text-red-600'
                                    : 'bg-gray-100 text-gray-600');

                            $periode = !empty($anak->tgl_pemeriksaan)
                                ? Carbon::parse($anak->tgl_pemeriksaan)->format('Y-m')
                                : '';
                        @endphp

                        <tr
                            class="baris-anak transition hover:bg-gray-50"
                            data-cari="{{ strtolower(($anak->nama_anak ?? '') . ' ' . ($anak->nik_anak ?? '')) }}"
                            data-status="{{ strtolower($statusText) }}"
                            data-periode="{{ $periode }}"
                        >

                            <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                {{ $index + 1 }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-800">
                                {{ $anak->nama_anak }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                                {{ $anak->nik_anak ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                @if($usiaBulan !== null)
                                    @if(intdiv($usiaBulan, 12) > 0)
                                        {{ intdiv($usiaBulan, 12) }} th
                                    @endif

                                    {{ $usiaBulan % 12 }} bln
                                @else
                                    -
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">

                                <span class="inline-flex min-w-[76px] justify-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>

                            </td>

                            <td class="whitespace-nowrap px-4 py-3">

                                <div class="flex items-center justify-center gap-1.5">

                                    {{-- DETAIL --}}
                                    <a
                                        href="{{ route($routePrefix . '.data-anak.show', $anak->anak_id) }}"
                                        class="rounded-md bg-gray-200 px-2.5 py-1 text-xs font-medium text-gray-800 transition hover:bg-gray-300"
                                    >
                                        Detail
                                    </a>

                                    {{-- EDIT --}}
                                    @if($sudah)

                                        <a
                                            href="{{ route('kader.data-posyandu.pemeriksaan.edit', [
                                                'kegiatan' => $kegiatan->id,
                                                'anak' => $anak->anak_id,
                                                'pemeriksaan' => $anak->pemeriksaan_id,
                                            ]) }}"
                                            class="rounded-md bg-gray-200 px-2.5 py-1 text-xs font-medium text-gray-800 transition hover:bg-gray-300"
                                        >
                                            Edit
                                        </a>

                                    @else

                                        {{-- INPUT --}}
                                        <a
                                            href="{{ route('kader.data-posyandu.pemeriksaan.create', [
                                                'kegiatan' => $kegiatan->id,
                                                'anak' => $anak->anak_id,
                                            ]) }}"
                                            class="rounded-md bg-gray-200 px-2.5 py-1 text-xs font-medium text-gray-800 transition hover:bg-gray-300"
                                        >
                                            Input
                                        </a>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">
                                Belum ada data anak di wilayah kerja ini.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- HASIL PENCARIAN KOSONG --}}
        <div
            id="hasilPencarianKosong"
            class="hidden px-5 py-8 text-center text-sm text-gray-500"
        >
            Data anak yang dicari tidak ditemukan.
        </div>

    </div>

    {{-- INFORMASI DAN PAGINATION --}}
    <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">

        <p id="jumlahData" class="text-xs text-gray-400">
            Menampilkan data
        </p>

        <div id="pagination" class="flex items-center gap-1">

            <button
                type="button"
                id="halamanSebelumnya"
                class="rounded-md border border-gray-300 px-2 py-1 text-sm text-gray-600 hover:bg-gray-100"
            >
                ‹
            </button>

            <div id="nomorHalaman" class="flex items-center gap-1"></div>

            <button
                type="button"
                id="halamanBerikutnya"
                class="rounded-md border border-gray-300 px-2 py-1 text-sm text-gray-600 hover:bg-gray-100"
            >
                ›
            </button>

        </div>

    </div>

</div>

{{-- JAVASCRIPT PENCARIAN, FILTER, DAN PAGINATION --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('cariAnak');
    const filterStatus = document.getElementById('filterStatus');
    const filterPeriode = document.getElementById('filterPeriode');

    const baris = Array.from(document.querySelectorAll('.baris-anak'));

    const pesanKosong = document.getElementById('hasilPencarianKosong');
    const jumlahData = document.getElementById('jumlahData');

    const pagination = document.getElementById('pagination');
    const nomorHalaman = document.getElementById('nomorHalaman');

    const sebelumnya = document.getElementById('halamanSebelumnya');
    const berikutnya = document.getElementById('halamanBerikutnya');

    const perHalaman = 10;
    let halamanAktif = 1;

    function tampilkanData() {

        const kataKunci = input.value.toLowerCase().trim();
        const statusDipilih = filterStatus.value;
        const periodeDipilih = filterPeriode.value;

        const dataCocok = baris.filter(function (row) {

            const nama = row.dataset.cari || '';
            const status = row.dataset.status || '';
            const periode = row.dataset.periode || '';

            return nama.includes(kataKunci)
                && (!statusDipilih || status === statusDipilih)
                && (!periodeDipilih || periode === periodeDipilih);

        });

        const totalData = dataCocok.length;
        const totalHalaman = Math.ceil(totalData / perHalaman);

        if (halamanAktif > totalHalaman) {
            halamanAktif = Math.max(1, totalHalaman);
        }

        baris.forEach(row => row.classList.add('hidden'));

        const mulai = (halamanAktif - 1) * perHalaman;
        const akhir = mulai + perHalaman;

        dataCocok.slice(mulai, akhir).forEach(function (row) {
            row.classList.remove('hidden');
        });

        pesanKosong.classList.toggle('hidden', totalData > 0);

        if (totalData > 0) {
            jumlahData.textContent =
                `Menampilkan ${mulai + 1}-${Math.min(akhir, totalData)} dari ${totalData} data`;
        } else {
            jumlahData.textContent = 'Menampilkan 0 dari 0 data';
        }

        pagination.classList.toggle('hidden', totalData <= perHalaman);

        sebelumnya.disabled = halamanAktif <= 1;
        berikutnya.disabled = halamanAktif >= totalHalaman;

        sebelumnya.classList.toggle('opacity-40', sebelumnya.disabled);
        berikutnya.classList.toggle('opacity-40', berikutnya.disabled);

        nomorHalaman.innerHTML = '';

        for (let i = 1; i <= totalHalaman; i++) {

            const tombol = document.createElement('button');

            tombol.type = 'button';
            tombol.textContent = i;

            tombol.className = i === halamanAktif
                ? 'min-w-7 rounded-md border border-gray-400 bg-gray-200 px-2 py-1 text-xs font-semibold text-gray-800'
                : 'min-w-7 rounded-md border border-gray-300 px-2 py-1 text-xs text-gray-600 hover:bg-gray-100';

            tombol.addEventListener('click', function () {
                halamanAktif = i;
                tampilkanData();
            });

            nomorHalaman.appendChild(tombol);
        }

    }

    input.addEventListener('input', function () {
        halamanAktif = 1;
        tampilkanData();
    });

    filterStatus.addEventListener('change', function () {
        halamanAktif = 1;
        tampilkanData();
    });

    filterPeriode.addEventListener('change', function () {
        halamanAktif = 1;
        tampilkanData();
    });

    sebelumnya.addEventListener('click', function () {
        if (halamanAktif > 1) {
            halamanAktif--;
            tampilkanData();
        }
    });

    berikutnya.addEventListener('click', function () {
        halamanAktif++;
        tampilkanData();
    });

    tampilkanData();

});
</script>

@endsection
