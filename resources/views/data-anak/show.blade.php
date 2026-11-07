@extends('layouts.app')

@section('title', 'Detail Anak')

@section('content')
@php
    $ibu = $anak->ibu;

    $riwayatPemeriksaan = $riwayatPemeriksaan ?? collect();
    $pemeriksaanTerakhir = $pemeriksaanTerakhir ?? null;

    $formatTanggal = fn ($tanggal) => $tanggal
        ? \Carbon\Carbon::parse($tanggal)->format('d/m/Y')
        : '-';

    // Data grafik berat badan.
    $dataGrafik = $riwayatPemeriksaan
        ->filter(fn ($item) => $item->berat_badan !== null)
        ->values();

    $jumlahGrafik = $dataGrafik->count();

    $beratMinimum = $jumlahGrafik > 0
        ? (float) $dataGrafik->min('berat_badan')
        : 0;

    $beratMaksimum = $jumlahGrafik > 0
        ? (float) $dataGrafik->max('berat_badan')
        : 0;

    $rentangBerat = max($beratMaksimum - $beratMinimum, 1);

    $chartPoints = [];

    foreach ($dataGrafik as $index => $item) {
        $x = $jumlahGrafik > 1
            ? 50 + ($index * (500 / ($jumlahGrafik - 1)))
            : 300;

        $y = 180 - (
            ((float) $item->berat_badan - $beratMinimum)
            / $rentangBerat
        ) * 130;

        $chartPoints[] = [
            'x' => $x,
            'y' => $y,
            'berat' => $item->berat_badan,
            'tanggal' => $item->kegiatan?->tanggal,
        ];
    }

    $garisGrafik = collect($chartPoints)
        ->map(fn ($point) => $point['x'].','.$point['y'])
        ->implode(' ');

    $statusTerakhir = $pemeriksaanTerakhir?->status_kesehatan;
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul dan tombol --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
                Detail Anak
            </h1>
            <p class="mt-1 text-sm font-medium text-slate-600">
                Data Master / Data Anak / Detail
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route($base.'.data-anak.index') }}"
               class="rounded-xl border border-[#E99AB7] bg-white px-5 py-2.5 text-sm font-semibold text-[#C9517E] hover:bg-[#FCE7F0]">
                Kembali
            </a>

            @if ($base === 'admin')
                @can('anak.update')
                    <a href="{{ route('admin.data-anak.edit', $anak) }}"
                       class="rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#B7436D]">
                        Edit
                    </a>
                @endcan
            @endif
        </div>
    </div>

    {{-- Identitas Anak dan Status Terkini --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

        {{-- Identitas Anak --}}
        <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-base font-bold text-[#542D3D]">
                Identitas Anak
            </h2>

            @php
                $dataAnak = [
                    'NIK Anak' => $anak->nik_anak,
                    'Nama Anak' => $anak->nama_anak,
                    'Tempat Lahir' => $anak->tempat_lahir,
                    'Tanggal Lahir' => $formatTanggal($anak->tgl_lahir),
                    'Jenis Kelamin' => $anak->jenis_kelamin === 'L'
                        ? 'Laki-laki'
                        : ($anak->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                    'Nama Ayah' => $anak->nama_ayah,
                ];
            @endphp

            <div class="divide-y divide-[#F7E6ED]">
                @foreach ($dataAnak as $label => $nilai)
                    <div class="flex justify-between gap-4 py-3 text-sm">
                        <span class="font-semibold text-slate-600">
                            {{ $label }}
                        </span>
                        <span class="text-right font-semibold text-[#542D3D]">
                            {{ $nilai ?: '-' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Status Terkini --}}
        <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-base font-bold text-[#542D3D]">
                Status Terkini
            </h2>

            <div class="flex min-h-40 flex-col justify-center rounded-xl bg-[#FFF5F8] p-5">
                <p class="text-sm font-medium text-slate-600">
                    Status Kesehatan Terakhir
                </p>

                <div class="mt-3">
                    @if ($statusTerakhir === 'normal')
                        <span class="inline-flex rounded-full bg-green-100 px-4 py-2 text-sm font-bold text-green-700">
                            Normal
                        </span>
                    @elseif ($statusTerakhir === 'perlu_perhatian')
                        <span class="inline-flex rounded-full bg-red-100 px-4 py-2 text-sm font-bold text-red-700">
                            Perlu Perhatian
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-gray-100 px-4 py-2 text-sm font-bold text-gray-600">
                            Belum ada pemeriksaan
                        </span>
                    @endif
                </div>

                <div class="mt-5">
                    <p class="text-sm text-slate-600">
                        Tanggal Pemeriksaan Terakhir
                    </p>

                    <p class="mt-1 text-lg font-bold text-[#542D3D]">
                        {{ $formatTanggal($pemeriksaanTerakhir?->kegiatan?->tanggal) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Ibu --}}
    <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-base font-bold text-[#542D3D]">
            Data Ibu
        </h2>

        @if ($ibu)
            @php
                $dataIbu = [
                    'NIK Ibu' => $ibu->nik,
                    'Nama Ibu' => $ibu->user?->name,
                    'Tempat Lahir' => $ibu->tempat_lahir,
                    'Tanggal Lahir' => $formatTanggal($ibu->tgl_lahir),
                    'Nomor HP' => $ibu->user?->phone,
                    'Wilayah Kerja' => $ibu->wilayahKerja?->nama_wilayah,
                ];
            @endphp

            <div class="grid gap-x-8 md:grid-cols-2">
                @foreach ($dataIbu as $label => $nilai)
                    <div class="flex justify-between gap-4 border-b border-[#F7E6ED] py-3 text-sm">
                        <span class="font-semibold text-slate-600">
                            {{ $label }}
                        </span>
                        <span class="text-right font-semibold text-[#542D3D]">
                            {{ $nilai ?: '-' }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="py-6 text-center text-sm text-slate-500">
                Data ibu belum terhubung.
            </p>
        @endif
    </div>

    {{-- Grafik Pertumbuhan --}}
    <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
        <div class="mb-4">
            <h2 class="text-base font-bold text-[#542D3D]">
                Grafik Pertumbuhan Anak
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Perkembangan berat badan anak berdasarkan riwayat pemeriksaan Posyandu.
            </p>
        </div>

        @if ($jumlahGrafik > 0)
            <div class="w-full overflow-x-auto">
                <svg
                    viewBox="0 0 600 230"
                    class="w-full min-w-[320px]"
                    role="img"
                    aria-label="Grafik pertumbuhan berat badan anak"
                >
                    @foreach ([40, 85, 130, 180] as $y)
                        <line
                            x1="45"
                            y1="{{ $y }}"
                            x2="555"
                            y2="{{ $y }}"
                            stroke="#F1D5E0"
                            stroke-dasharray="4 4"
                        />
                    @endforeach

                    @if ($jumlahGrafik > 1)
                        <polyline
                            points="{{ $garisGrafik }}"
                            fill="none"
                            stroke="#C9517E"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    @endif

                    @foreach ($chartPoints as $point)
                        <circle
                            cx="{{ $point['x'] }}"
                            cy="{{ $point['y'] }}"
                            r="5"
                            fill="#C9517E"
                            stroke="white"
                            stroke-width="2"
                        >
                            <title>{{ $point['berat'] }} kg</title>
                        </circle>
                    @endforeach

                    <text x="5" y="43" font-size="11" fill="#64748B">
                        {{ number_format($beratMaksimum, 1) }} kg
                    </text>

                    <text x="5" y="183" font-size="11" fill="#64748B">
                        {{ number_format($beratMinimum, 1) }} kg
                    </text>
                </svg>
            </div>

            <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">
                <span class="h-2.5 w-2.5 rounded-full bg-[#C9517E]"></span>
                Berat badan (kg)
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($chartPoints as $point)
                    <span class="rounded-lg bg-[#FFF5F8] px-3 py-2 text-xs text-[#542D3D]">
                        {{ $formatTanggal($point['tanggal']) }}:
                        <strong>{{ $point['berat'] }} kg</strong>
                    </span>
                @endforeach
            </div>
        @else
            <div class="flex min-h-48 flex-col items-center justify-center rounded-xl bg-gray-50">
                <p class="text-sm font-semibold text-slate-600">
                    0 kg
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    Belum ada data berat badan untuk ditampilkan.
                </p>
            </div>
        @endif
    </div>

    {{-- Riwayat Pemeriksaan --}}
    <div
        id="riwayat-pemeriksaan"
        class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm"
    >
        <div class="border-b border-[#F1D5E0] px-5 py-4">
            <h2 class="text-base font-bold text-[#542D3D]">
                Riwayat Pemeriksaan Anak
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Riwayat hasil pemeriksaan berdasarkan kegiatan Posyandu.
            </p>
        </div>

        @if ($riwayatPemeriksaan->isNotEmpty())
            @php
                $riwayatTerbaru = $riwayatPemeriksaan
                    ->sortByDesc(fn ($item) => $item->kegiatan?->tanggal)
                    ->values();

                $riwayatAwal = $riwayatTerbaru->take(5);
                $riwayatLanjutan = $riwayatTerbaru->slice(5);
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left text-sm">
                    <thead class="bg-[#FFF5F8] text-xs uppercase text-[#542D3D]">
                        <tr>
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3 text-center">Berat Badan</th>
                            <th class="px-5 py-3 text-center">Tinggi Badan</th>
                            <th class="px-5 py-3 text-center">Lingkar Kepala</th>
                            <th class="px-5 py-3 text-center">Status Kesehatan</th>
                            <th class="px-5 py-3 text-center">Imunisasi/Vitamin</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach ($riwayatAwal as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-5 py-3">
                                    {{ $formatTanggal($item->kegiatan?->tanggal) }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    {{ $item->berat_badan !== null
                                        ? $item->berat_badan.' kg'
                                        : '0 kg' }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    {{ $item->tinggi_badan !== null
                                        ? $item->tinggi_badan.' cm'
                                        : '0 cm' }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    0 cm
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if ($item->status_kesehatan === 'normal')
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                            Normal
                                        </span>
                                    @elseif ($item->status_kesehatan === 'perlu_perhatian')
                                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                            Perlu Perhatian
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    0
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($riwayatLanjutan->isNotEmpty())
                <details class="border-t border-[#F1D5E0]">
                    <summary class="cursor-pointer px-5 py-4 text-center text-sm font-semibold text-[#C9517E] hover:bg-[#FFF5F8]">
                        Tampilkan Semua Riwayat
                    </summary>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[850px] text-left text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($riwayatLanjutan as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3">
                                            {{ $loop->iteration + 5 }}
                                        </td>
                                        <td class="px-5 py-3">
                                            {{ $formatTanggal($item->kegiatan?->tanggal) }}
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            {{ $item->berat_badan !== null
                                                ? $item->berat_badan.' kg'
                                                : '0 kg' }}
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            {{ $item->tinggi_badan !== null
                                                ? $item->tinggi_badan.' cm'
                                                : '0 cm' }}
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            0 cm
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            @if ($item->status_kesehatan === 'normal')
                                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                    Normal
                                                </span>
                                            @elseif ($item->status_kesehatan === 'perlu_perhatian')
                                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                                    Perlu Perhatian
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            0
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        @else
            <div class="px-5 py-12 text-center">
                <p class="text-sm font-semibold text-slate-600">
                    Tidak ada riwayat pemeriksaan anak.
                </p>
                <p class="mt-1 text-xs text-slate-400">
                    Data akan ditampilkan setelah pemeriksaan tersimpan.
                </p>
            </div>
        @endif
    </div>

</div>
@endsection