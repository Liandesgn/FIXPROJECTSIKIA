
@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi')

@section('content')
@php
    $base = $base ?? 'admin';

    $tabs = [
        'rekap-kesehatan' => 'Rekap Kesehatan',
        'kegiatan-posyandu' => 'Kegiatan Posyandu',
        'data-risiko' => 'Data Risiko',
        'rekap-pemeriksaan' => 'Rekap Pemeriksaan',
    ];

    $tabAktif = request('tab', 'rekap-kesehatan');

    if (! array_key_exists($tabAktif, $tabs)) {
        $tabAktif = 'rekap-kesehatan';
    }

    $wilayah = $wilayah ?? collect();
    $ringkasan = $ringkasan ?? [
        'ibu_hamil' => 0,
        'anak' => 0,
        'perlu_perhatian' => 0,
    ];

    $rekapWilayah = $rekapWilayah ?? collect();
    $grafikStatus = $grafikStatus ?? [
        'ibu_normal' => 0,
        'ibu_perhatian' => 0,
        'anak_normal' => 0,
        'anak_perhatian' => 0,
    ];

    $kegiatanPosyandu = $kegiatanPosyandu ?? collect();
    $dataRisiko = $dataRisiko ?? collect();
    $rekapPemeriksaan = $rekapPemeriksaan ?? collect();

    $adaRutePdf = \Illuminate\Support\Facades\Route::has($base.'.laporan.pdf');

    // Data grafik Ibu.
    $ibuNormal = (int) ($grafikStatus['ibu_normal'] ?? 0);
    $ibuPerhatian = (int) ($grafikStatus['ibu_perhatian'] ?? 0);
    $totalGrafikIbu = $ibuNormal + $ibuPerhatian;

    $persenIbuNormal = $totalGrafikIbu > 0
        ? round(($ibuNormal / $totalGrafikIbu) * 100, 1)
        : 0;

    // Data grafik Anak.
    $anakNormal = (int) ($grafikStatus['anak_normal'] ?? 0);
    $anakPerhatian = (int) ($grafikStatus['anak_perhatian'] ?? 0);
    $totalGrafikAnak = $anakNormal + $anakPerhatian;

    $persenAnakNormal = $totalGrafikAnak > 0
        ? round(($anakNormal / $totalGrafikAnak) * 100, 1)
        : 0;
@endphp

<div class="space-y-6">

    {{-- Judul halaman --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            Laporan & Rekapitulasi
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Ringkasan data kesehatan dan kegiatan Posyandu.
        </p>
    </div>

    {{-- Tab laporan --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex flex-wrap gap-6">
            @foreach ($tabs as $key => $label)
                <a
                    href="{{ route($base.'.laporan.index', array_merge(request()->query(), ['tab' => $key])) }}"
                    class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium
                    {{ $tabAktif === $key
                        ? 'border-blue-600 text-blue-600'
                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

   {{-- Filter periode --}}
<form
    method="GET"
    action="{{ route($base.'.laporan.index') }}"
    class="flex flex-col gap-3 lg:flex-row lg:items-center"
>
    <input type="hidden" name="tab" value="{{ $tabAktif }}">

    {{-- Filter bulan --}}
    <div class="w-full lg:w-52">
        <input
            type="month"
            name="periode"
            value="{{ $periode ?? now()->format('Y-m') }}"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
        >
    </div>

    {{-- Wilayah Admin (hanya informasi, bukan dropdown) --}}
    <div class="flex items-center rounded-lg border border-gray-200 bg-gray-50 px-4 py-2">
        <span class="text-sm text-gray-500">
            Wilayah:
        </span>

        <span class="ml-2 text-sm font-semibold text-gray-800">
            {{ $wilayah->first()?->nama_wilayah ?? 'Wilayah belum tersedia' }}
        </span>
    </div>

    <button
        type="submit"
        class="rounded-lg bg-gray-700 px-5 py-2 text-sm font-semibold text-white hover:bg-gray-800"
    >
        Tampilkan
    </button>

    @if ($adaRutePdf)
        <a
            href="{{ route($base.'.laporan.pdf', request()->query()) }}"
            target="_blank"
            class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
            Unduh PDF
        </a>
    @endif
</form>

    {{-- TAB 1: REKAP KESEHATAN --}}
    @if ($tabAktif === 'rekap-kesehatan')

        @php
            $kartu = [
                [
                    'Ibu Hamil',
                    $ringkasan['ibu_hamil'],
                    'bg-blue-50 text-blue-600',
                    'M12 12a4 4 0 100-8 4 4 0 000 8zm-7 9a7 7 0 0114 0'
                ],
                [
                    'Anak Terdaftar',
                    $ringkasan['anak'],
                    'bg-green-50 text-green-600',
                    'M12 3v18m-7-9h14M7 7h10M7 17h10'
                ],
                [
                    'Perlu Perhatian',
                    $ringkasan['perlu_perhatian'],
                    'bg-red-50 text-red-600',
                    'M12 9v4m0 4h.01M10.3 3.9L2.8 17a2 2 0 001.7 3h15a2 2 0 001.7-3l-7.5-13.1a2 2 0 00-3.4 0z'
                ],
            ];
        @endphp

        {{-- Kartu ringkasan --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach ($kartu as [$labelKartu, $nilai, $warna, $ikon])
                <div class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg {{ $warna }}">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="{{ $ikon }}"
                            />
                        </svg>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">{{ $labelKartu }}</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $nilai }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Grafik status pemeriksaan --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

            {{-- Grafik Ibu --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-5">
                    <h2 class="font-semibold text-gray-800">
                        Status Pemeriksaan Ibu
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Grafik hasil pemeriksaan Ibu pada periode terpilih.
                    </p>
                </div>

                @if ($totalGrafikIbu > 0)
                    <div class="flex flex-col items-center gap-5 sm:flex-row sm:justify-center">
                        <div
                            class="relative h-44 w-44 shrink-0 rounded-full"
                            style="background: conic-gradient(#16a34a 0% {{ $persenIbuNormal }}%, #facc15 {{ $persenIbuNormal }}% 100%);"
                        >
                            <div class="absolute inset-5 flex flex-col items-center justify-center rounded-full bg-white">
                                <span class="text-3xl font-bold text-gray-800">
                                    {{ $totalGrafikIbu }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    Total pemeriksaan
                                </span>
                            </div>
                        </div>

                        <div class="w-full space-y-4 sm:w-auto">
                            <div class="flex items-center justify-between gap-6">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-green-600"></span>
                                    <span class="text-sm text-gray-600">Normal</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-800">
                                    {{ $ibuNormal }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-6">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                                    <span class="text-sm text-gray-600">Perlu Perhatian</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-800">
                                    {{ $ibuPerhatian }}
                                </span>
                            </div>

                            <p class="text-xs text-gray-400">
                                Normal: {{ $persenIbuNormal }}%
                            </p>
                        </div>
                    </div>
                @else
                    <div class="flex h-44 items-center justify-center rounded-lg bg-gray-50">
                        <p class="text-sm text-gray-500">
                            Belum ada data pemeriksaan Ibu pada periode ini.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Grafik Anak --}}
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-5">
                    <h2 class="font-semibold text-gray-800">
                        Status Pemeriksaan Anak
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Grafik hasil pemeriksaan Anak pada periode terpilih.
                    </p>
                </div>

                @if ($totalGrafikAnak > 0)
                    <div class="flex flex-col items-center gap-5 sm:flex-row sm:justify-center">
                        <div
                            class="relative h-44 w-44 shrink-0 rounded-full"
                            style="background: conic-gradient(#16a34a 0% {{ $persenAnakNormal }}%, #facc15 {{ $persenAnakNormal }}% 100%);"
                        >
                            <div class="absolute inset-5 flex flex-col items-center justify-center rounded-full bg-white">
                                <span class="text-3xl font-bold text-gray-800">
                                    {{ $totalGrafikAnak }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    Total pemeriksaan
                                </span>
                            </div>
                        </div>

                        <div class="w-full space-y-4 sm:w-auto">
                            <div class="flex items-center justify-between gap-6">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-green-600"></span>
                                    <span class="text-sm text-gray-600">Normal</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-800">
                                    {{ $anakNormal }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-6">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                                    <span class="text-sm text-gray-600">Perlu Perhatian</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-800">
                                    {{ $anakPerhatian }}
                                </span>
                            </div>

                            <p class="text-xs text-gray-400">
                                Normal: {{ $persenAnakNormal }}%
                            </p>
                        </div>
                    </div>
                @else
                    <div class="flex h-44 items-center justify-center rounded-lg bg-gray-50">
                        <p class="text-sm text-gray-500">
                            Belum ada data pemeriksaan Anak pada periode ini.
                        </p>
                    </div>
                @endif
            </div>
        </div>

      
                        
  {{-- TAB 2: KEGIATAN POSYANDU --}}
@elseif ($tabAktif === 'kegiatan-posyandu')

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-800">
                Laporan Kegiatan Posyandu
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Daftar kegiatan Posyandu yang telah dilaksanakan pada periode terpilih.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Jenis Kegiatan</th>
                        <th class="px-5 py-3">Lokasi</th>
                        <th class="px-5 py-3">Anak Diperiksa</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($kegiatanPosyandu as $item)
                        <tr class="hover:bg-gray-50">

                            <td class="px-5 py-3">
                                {{ $loop->iteration }}
                            </td>

                            <td class="px-5 py-3">
                                {{ $item->tanggal?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td class="px-5 py-3 font-medium text-gray-800">
                                {{ $item->jenis_kegiatan ?? '-' }}
                            </td>

                            <td class="px-5 py-3">
                                {{ $item->lokasi ?? '-' }}
                            </td>

                            <td class="px-5 py-3">
                                {{ $item->anak_diperiksa ?? 0 }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500">
                                Belum ada kegiatan Posyandu pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

    {{-- TAB 3: DATA RISIKO --}}
    @elseif ($tabAktif === 'data-risiko')

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="font-semibold text-gray-800">
                    Laporan Data Risiko
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Daftar Ibu dan Anak yang hasil pemeriksaannya memerlukan perhatian.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Jenis</th>
                            <th class="px-5 py-3">Wilayah</th>
                            <th class="px-5 py-3">Tanggal Pemeriksaan</th>
                            <th class="px-5 py-3">Indikator</th>
                            <th class="px-5 py-3">Penanda</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($dataRisiko as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3 font-medium text-gray-800">
                                    {{ $item->nama }}
                                </td>
                                <td class="px-5 py-3">{{ $item->jenis }}</td>
                                <td class="px-5 py-3">{{ $item->wilayah }}</td>
                                <td class="px-5 py-3">
                                    {{ $item->tanggal
                                        ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y')
                                        : '-' }}
                                </td>
                                <td class="px-5 py-3">{{ $item->indikator }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                        {{ $item->penanda }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                                    Belum ada data risiko pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- TAB 4: REKAP PEMERIKSAAN --}}
    @elseif ($tabAktif === 'rekap-pemeriksaan')

        @php
            $totalIbu = $rekapPemeriksaan->sum('pemeriksaan_ibu');
            $totalAnak = $rekapPemeriksaan->sum('pemeriksaan_anak');
            $maksimumGrafik = max($totalIbu, $totalAnak, 1);
        @endphp

        {{-- Grafik pemeriksaan --}}
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h2 class="font-semibold text-gray-800">
                    Grafik Pemeriksaan
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Perbandingan jumlah pemeriksaan Ibu dan Anak pada periode terpilih.
                </p>
            </div>

            @if ($totalIbu == 0 && $totalAnak == 0)
                <div class="flex h-48 items-center justify-center rounded-lg bg-gray-50">
                    <p class="text-sm text-gray-500">
                        Belum ada data pemeriksaan pada periode ini.
                    </p>
                </div>
            @else
                <div class="flex h-64 items-end justify-center gap-12 border-b border-gray-200 px-5">

                    {{-- Batang pemeriksaan Ibu --}}
                    <div class="flex h-full w-24 flex-col items-center justify-end gap-2">
                        <span class="text-sm font-semibold text-gray-700">
                            {{ $totalIbu }}
                        </span>
                        <div
                            class="w-14 rounded-t-md bg-green-500 transition-all duration-500"
                            style="height: {{ ($totalIbu / $maksimumGrafik) * 80 }}%; min-height: 4px;"
                        ></div>
                        <span class="mb-2 text-xs font-medium text-gray-600">
                            Ibu Hamil
                        </span>
                    </div>

                    {{-- Batang pemeriksaan Anak --}}
                    <div class="flex h-full w-24 flex-col items-center justify-end gap-2">
                        <span class="text-sm font-semibold text-gray-700">
                            {{ $totalAnak }}
                        </span>
                        <div
                            class="w-14 rounded-t-md bg-yellow-400 transition-all duration-500"
                            style="height: {{ ($totalAnak / $maksimumGrafik) * 80 }}%; min-height: 4px;"
                        ></div>
                        <span class="mb-2 text-xs font-medium text-gray-600">
                            Anak
                        </span>
                    </div>

                </div>
            @endif
        </div>

{{-- Tabel rekap pemeriksaan --}}
<div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 px-5 py-4">
        <h2 class="font-semibold text-gray-800">
            Rekap Pemeriksaan
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Ringkasan pemeriksaan Ibu, Anak, dan kegiatan Posyandu.
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                <tr>
                    <th class="px-5 py-3">Periode</th>
                    <th class="px-5 py-3 text-center">Pemeriksaan Ibu Hamil</th>
                    <th class="px-5 py-3 text-center">Pemeriksaan Anak</th>
                    <th class="px-5 py-3 text-center">Kegiatan Posyandu</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($rekapPemeriksaan as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">
                            {{ $item->bulan }}
                        </td>

                        <td class="px-5 py-3 text-center">
                            {{ $item->pemeriksaan_ibu }}
                        </td>

                        <td class="px-5 py-3 text-center">
                            {{ $item->pemeriksaan_anak }}
                        </td>

                        <td class="px-5 py-3 text-center">
                            {{ $item->kegiatan_posyandu }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-gray-500">
                            Belum ada data pemeriksaan pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif

</div>
@endsection