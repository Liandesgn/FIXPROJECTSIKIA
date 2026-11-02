@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi')

@section('content')
@php
    // Awalan route: admin (default) atau bidan
    $base = $base ?? 'admin';
    

    $tabs = [
        'rekap-kesehatan'   => 'Rekap Kesehatan',
        'kegiatan-posyandu' => 'Kegiatan Posyandu',
        'data-risiko'       => 'Data Risiko',
        'rekap-pemeriksaan' => 'Rekap Pemeriksaan',
    ];

    $tabAktif = request('tab', 'rekap-kesehatan');

    if (! array_key_exists($tabAktif, $tabs)) {
        $tabAktif = 'rekap-kesehatan';
    }

    // Nilai bawaan supaya halaman tidak error bila data belum dikirim controller
    $wilayah          = $wilayah ?? collect();
    $ringkasan        = $ringkasan ?? ['ibu_hamil' => 0, 'anak' => 0, 'perlu_perhatian' => 0];
    $rekapWilayah     = $rekapWilayah ?? collect();
    $kegiatanPosyandu = $kegiatanPosyandu ?? collect();
    $dataRisiko       = $dataRisiko ?? collect();
    $rekapPemeriksaan = $rekapPemeriksaan ?? collect();

    $adaRutePdf = \Illuminate\Support\Facades\Route::has($base.'.laporan.pdf');
@endphp

<div class="space-y-6">

    {{-- Judul halaman --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            Laporan & Rekapitulasi
        </h1>
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

    {{-- Filter periode dan wilayah --}}
    <form
        method="GET"
        action="{{ route($base.'.laporan.index') }}"
        class="flex flex-col gap-3 lg:flex-row lg:items-center"
    >
        <input type="hidden" name="tab" value="{{ $tabAktif }}">

        <div class="w-full lg:w-52">
            <input
                type="month"
                name="periode"
                value="{{ request('periode', now()->format('Y-m')) }}"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            >
        </div>

        <div class="w-full lg:w-52">
            <select
                name="wilayah_id"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
            >
                @if ($base === 'admin')
                    <option value="">Semua Wilayah</option>
                @endif

                @foreach ($wilayah as $item)
                    <option
                        value="{{ $item->id }}"
                        @selected(request('wilayah_id') == $item->id)
                    >
                        {{ $item->nama_wilayah }}
                    </option>
                @endforeach
            </select>
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

    {{-- ================= ISI LAPORAN PER TAB ================= --}}
    @if ($tabAktif === 'rekap-kesehatan')

        @php
            $kartu = [
                ['Ibu Hamil', $ringkasan['ibu_hamil'], 'bg-blue-50 text-blue-600', 'M12 12a4 4 0 100-8 4 4 0 000 8zm-7 9a7 7 0 0114 0'],
                ['Anak Terdaftar', $ringkasan['anak'], 'bg-green-50 text-green-600', 'M12 3v18m-7-9h14M7 7h10M7 17h10'],
                ['Perlu Perhatian', $ringkasan['perlu_perhatian'], 'bg-red-50 text-red-600', 'M12 9v4m0 4h.01M10.3 3.9L2.8 17a2 2 0 001.7 3h15a2 2 0 001.7-3l-7.5-13.1a2 2 0 00-3.4 0z'],
            ];
        @endphp

        {{-- Kartu ringkasan --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach ($kartu as [$labelKartu, $nilai, $warna, $ikon])
                <div class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg {{ $warna }}">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $ikon }}" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ $labelKartu }}</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $nilai }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tabel rekap kesehatan per wilayah --}}
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-5 py-3">Wilayah</th>
                            <th class="px-5 py-3 text-center">Ibu Hamil</th>
                            <th class="px-5 py-3 text-center">Anak</th>
                            <th class="px-5 py-3 text-center">Diperiksa</th>
                            <th class="px-5 py-3 text-center">Perlu Perhatian</th>
                            <th class="px-5 py-3 text-center">Persentase</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($rekapWilayah as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $item->nama_wilayah }}</td>
                                <td class="px-5 py-3 text-center">{{ $item->ibu_hamil }}</td>
                                <td class="px-5 py-3 text-center">{{ $item->anak }}</td>
                                <td class="px-5 py-3 text-center">{{ $item->diperiksa }}</td>
                                <td class="px-5 py-3 text-center">{{ $item->perlu_perhatian }}</td>
                                <td class="px-5 py-3 text-center">{{ $item->persentase }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-gray-500">
                                    Data rekap belum tersedia pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif ($tabAktif === 'kegiatan-posyandu')

        {{-- Tabel kegiatan Posyandu --}}
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="font-semibold text-gray-800">Laporan Kegiatan Posyandu</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Daftar kegiatan posyandu yang telah dilaksanakan pada periode terpilih.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">Nama Posyandu</th>
                            <th class="px-5 py-3">Tanggal Kegiatan</th>
                            <th class="px-5 py-3">Jumlah Peserta</th>
                            <th class="px-5 py-3">Keterangan</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($kegiatanPosyandu as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $item->nama_posyandu }}</td>
                                <td class="px-5 py-3">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td class="px-5 py-3">{{ $item->jumlah_peserta }}</td>
                                <td class="px-5 py-3">{{ $item->keterangan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-gray-500">
                                    Belum ada kegiatan posyandu pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif ($tabAktif === 'data-risiko')

        {{-- Tabel data risiko --}}
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="font-semibold text-gray-800">Laporan Data Risiko</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Daftar ibu dan anak yang hasil pemeriksaannya memerlukan perhatian.
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
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $item->nama }}</td>
                                <td class="px-5 py-3">{{ $item->jenis }}</td>
                                <td class="px-5 py-3">{{ $item->wilayah }}</td>
                                <td class="px-5 py-3">
                                    {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : '-' }}
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

    @elseif ($tabAktif === 'rekap-pemeriksaan')

        @php
            $totalIbu       = $rekapPemeriksaan->sum('pemeriksaan_ibu');
            $totalAnak      = $rekapPemeriksaan->sum('pemeriksaan_anak');
            $maksimumGrafik = max($totalIbu, $totalAnak, 1);
        @endphp

        {{-- Grafik rekap pemeriksaan --}}
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h2 class="font-semibold text-gray-800">Grafik Pemeriksaan</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Perbandingan pemeriksaan ibu hamil dan anak pada periode terpilih.
                </p>
            </div>

            @if ($totalIbu == 0 && $totalAnak == 0)

                <div class="flex h-48 items-center justify-center rounded-lg bg-gray-50">
                    <p class="text-sm text-gray-500">Belum ada data pemeriksaan pada periode ini.</p>
                </div>

            @else

                <div class="flex h-64 items-end justify-center gap-12 border-b border-gray-200 px-5">

                    {{-- Batang pemeriksaan ibu --}}
                    <div class="flex h-full w-24 flex-col items-center justify-end gap-2">
                        <span class="text-sm font-semibold text-gray-700">{{ $totalIbu }}</span>
                        <div
                            class="w-14 rounded-t-md bg-green-500 transition-all duration-500"
                            style="height: {{ ($totalIbu / $maksimumGrafik) * 80 }}%; min-height: 4px;"
                        ></div>
                        <span class="mb-2 text-xs font-medium text-gray-600">Ibu Hamil</span>
                    </div>

                    {{-- Batang pemeriksaan anak --}}
                    <div class="flex h-full w-24 flex-col items-center justify-end gap-2">
                        <span class="text-sm font-semibold text-gray-700">{{ $totalAnak }}</span>
                        <div
                            class="w-14 rounded-t-md bg-yellow-400 transition-all duration-500"
                            style="height: {{ ($totalAnak / $maksimumGrafik) * 80 }}%; min-height: 4px;"
                        ></div>
                        <span class="mb-2 text-xs font-medium text-gray-600">Anak</span>
                    </div>

                </div>

            @endif
        </div>

        {{-- Tabel rekap pemeriksaan --}}
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="font-semibold text-gray-800">Rekap Pemeriksaan</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Ringkasan pemeriksaan ibu, anak, dan kegiatan Posyandu.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-5 py-3">Periode</th>
                            <th class="px-5 py-3 text-center">Pemeriksaan Ibu</th>
                            <th class="px-5 py-3 text-center">Pemeriksaan Anak</th>
                            <th class="px-5 py-3 text-center">Kegiatan Posyandu</th>
                            <th class="px-5 py-3 text-center">Total Pemeriksaan</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse ($rekapPemeriksaan as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $item->bulan }}</td>
                                <td class="px-5 py-3 text-center">{{ $item->pemeriksaan_ibu }}</td>
                                <td class="px-5 py-3 text-center">{{ $item->pemeriksaan_anak }}</td>
                                <td class="px-5 py-3 text-center">{{ $item->kegiatan_posyandu }}</td>
                                <td class="px-5 py-3 text-center font-semibold">
                                    {{ $item->pemeriksaan_ibu + $item->pemeriksaan_anak }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-gray-500">
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