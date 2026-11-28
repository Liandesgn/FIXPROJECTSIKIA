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

    $pilihClass = 'w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $btnAksi    = 'inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg px-3 text-xs font-bold shadow-sm transition focus:outline-none focus:ring-4';
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Pendataan Anak</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Pendataan Posyandu</p>
    </div>

    {{-- Informasi kegiatan --}}
    <div class="rounded-2xl border border-[#F1D5E0] bg-white px-5 py-4 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-bold text-[#542D3D]">
                    {{ $kegiatan->jenis_kegiatan ?? 'Kegiatan Posyandu' }}
                    - {{ $kegiatan->wilayah->nama_wilayah ?? 'Wilayah Posyandu' }}
                </h2>

                <p class="mt-1 text-sm font-medium text-slate-700">
                    Kegiatan
                    {{ Carbon::parse($kegiatan->tanggal)->format('d/m/Y') }}
                    - {{ $kegiatan->lokasi ?? '-' }}
                </p>
            </div>

            <span class="inline-flex w-fit items-center gap-1.5 rounded-full border border-green-300 bg-green-50 px-3 py-1 text-xs font-bold text-green-700">
                <span class="h-2 w-2 rounded-full bg-green-500" aria-hidden="true"></span>
                Berlangsung
            </span>
        </div>
    </div>

    {{-- Daftar --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Pencarian dan filter --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">

                <div class="relative flex-1">
                    <label for="cariAnak" class="sr-only">Cari nama atau NIK</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input type="text" id="cariAnak" autocomplete="off" placeholder="Cari nama / NIK"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>

                <div class="lg:w-48">
                    <label for="filterStatus" class="sr-only">Filter status</label>
                    <select id="filterStatus" class="{{ $pilihClass }}">
                        <option value="">Semua Status</option>

                        @foreach ($statusOptions as $status)
                            <option value="{{ strtolower($status) }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:w-48">
                    <label for="filterPeriode" class="sr-only">Filter periode</label>
                    <select id="filterPeriode" class="{{ $pilihClass }}">
                        <option value="">Semua Periode</option>

                        @foreach ($periodeOptions as $periode)
                            <option value="{{ $periode }}">
                                {{ Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">No</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Anak</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">NIK</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Usia</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Status</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Aksi</th>
                    </tr>
                </thead>

                <tbody id="daftarAnak" class="divide-y divide-[#F7E6ED]">

                    @forelse ($pemeriksaan as $index => $anak)

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
                                ? 'bg-green-100 text-green-700'
                                : (str_contains($statusLower, 'stunting') || str_contains($statusLower, 'pendek')
                                    ? 'bg-red-100 text-red-700'
                                    : 'bg-slate-100 text-slate-600');

                            $periode = !empty($anak->tgl_pemeriksaan)
                                ? Carbon::parse($anak->tgl_pemeriksaan)->format('Y-m')
                                : '';
                        @endphp

                        <tr class="baris-anak transition hover:bg-[#FCE7F0]/50"
                            data-cari="{{ strtolower(($anak->nama_anak ?? '') . ' ' . ($anak->nik_anak ?? '')) }}"
                            data-status="{{ strtolower($statusText) }}"
                            data-periode="{{ $periode }}">

                            <td class="whitespace-nowrap px-3 py-3.5 text-sm font-semibold text-slate-700 sm:px-4">
                                {{ $index + 1 }}
                            </td>

                            <td class="px-3 py-3.5 sm:px-4">
                                <p class="break-words text-sm font-bold text-[#542D3D]">{{ $anak->nama_anak }}</p>
                                <p class="text-xs text-slate-700 md:hidden">{{ $anak->nik_anak ?? '-' }}</p>
                            </td>

                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 md:table-cell">
                                {{ $anak->nik_anak ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3.5 text-sm text-slate-800 sm:px-4">
                                @if ($usiaBulan !== null)
                                    @if (intdiv($usiaBulan, 12) > 0)
                                        {{ intdiv($usiaBulan, 12) }} th
                                    @endif

                                    {{ $usiaBulan % 12 }} bln
                                @else
                                    -
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-3 py-3.5 text-center sm:px-4">
                                <span class="inline-flex min-w-[5rem] justify-center rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-3 py-3.5 sm:px-4">
                                <div class="flex items-center justify-center gap-2">

                                    {{-- Detail --}}
                                    <a href="{{ route($routePrefix . '.data-anak.show', $anak->anak_id) }}"
                                       class="{{ $btnAksi }} border-2 border-[#E99AB7] bg-white text-[#C9517E] hover:bg-[#FCE7F0] focus:ring-[#E99AB7]/30">
                                        Detail
                                    </a>

                                    @if ($sudah)

                                        {{-- Edit --}}
                                        <a href="{{ route('kader.data-posyandu.pemeriksaan.edit', [
                                                'kegiatan' => $kegiatan->id,
                                                'anak' => $anak->anak_id,
                                                'pemeriksaan' => $anak->pemeriksaan_id,
                                            ]) }}"
                                           class="{{ $btnAksi }} bg-[#E99AB7] text-[#542D3D] hover:bg-[#DD87A8] focus:ring-[#E99AB7]/40">
                                            Edit
                                        </a>

                                    @else

                                        {{-- Input --}}
                                        <a href="{{ route('kader.data-posyandu.pemeriksaan.create', [
                                                'kegiatan' => $kegiatan->id,
                                                'anak' => $anak->anak_id,
                                            ]) }}"
                                           class="{{ $btnAksi }} bg-[#C9517E] text-white hover:bg-[#B7436D] focus:ring-[#E99AB7]/40">
                                            Input
                                        </a>

                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">Belum ada data anak di wilayah kerja ini.</p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Hasil pencarian kosong --}}
        <div id="hasilPencarianKosong" class="hidden px-5 py-12 text-center">
            <p class="font-bold text-[#542D3D]">Data anak yang dicari tidak ditemukan.</p>
        </div>

        {{-- Informasi dan pagination --}}
        <div class="flex flex-col items-center justify-between gap-3 border-t border-[#F1D5E0] bg-white px-4 py-3 sm:flex-row">
            <p id="jumlahData" class="text-xs font-medium text-slate-600">
                Menampilkan data
            </p>

            <div id="pagination" class="flex items-center gap-1">

                <button type="button" id="halamanSebelumnya" aria-label="Halaman sebelumnya"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#F1D5E0] bg-white text-lg font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] disabled:cursor-not-allowed disabled:text-slate-300 disabled:hover:bg-white">
                    ‹
                </button>

                <div id="nomorHalaman" class="flex items-center gap-1"></div>

                <button type="button" id="halamanBerikutnya" aria-label="Halaman berikutnya"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#F1D5E0] bg-white text-lg font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] disabled:cursor-not-allowed disabled:text-slate-300 disabled:hover:bg-white">
                    ›
                </button>

            </div>
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

        nomorHalaman.innerHTML = '';

        for (let i = 1; i <= totalHalaman; i++) {

            const tombol = document.createElement('button');

            tombol.type = 'button';
            tombol.textContent = i;

            tombol.className = i === halamanAktif
                ? 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-[#C9517E] px-2 text-sm font-bold text-white shadow-sm'
                : 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-[#F1D5E0] bg-white px-2 text-sm font-semibold text-[#542D3D] transition hover:bg-[#FCE7F0]';

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