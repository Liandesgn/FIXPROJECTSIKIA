
@extends('layouts.app')

@section('title', 'Laporan Super Admin')

@section('content')
@php
    $tabs = [
        'rekap-kesehatan' => 'Rekap Kesehatan Wilayah',
        'kegiatan-posyandu' => 'Kegiatan Posyandu',
        'data-risiko' => 'Data Risiko',
        'rekap-pemeriksaan' => 'Rekap Pemeriksaan',
    ];

    $tabAktif = $tab;
@endphp

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            Laporan & Rekapitulasi
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Pantau laporan kesehatan dan kegiatan dari seluruh wilayah kerja.
        </p>
    </div>

    {{-- Tab laporan --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex flex-wrap gap-6">
            @foreach ($tabs as $key => $label)
                <a
                    href="{{ route('superadmin.laporan.index', array_merge(request()->query(), ['tab' => $key])) }}"
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

    {{-- Filter --}}
    <form
        method="GET"
        action="{{ route('superadmin.laporan.index') }}"
        class="flex flex-col gap-3 lg:flex-row lg:items-center"
    >
        <input type="hidden" name="tab" value="{{ $tabAktif }}">

        <input
            type="month"
            name="periode"
            value="{{ $periode }}"
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm"
        >

        <select
            name="wilayah_id"
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm"
        >
            <option value="">Semua Wilayah</option>

            @foreach ($wilayah as $item)
                <option
                    value="{{ $item->id }}"
                    @selected((string) $wilayahId === (string) $item->id)
                >
                    {{ $item->nama_wilayah }}
                </option>
            @endforeach
        </select>

        <button
            type="submit"
            class="rounded-lg bg-gray-700 px-5 py-2 text-sm font-semibold text-white hover:bg-gray-800"
        >
            Tampilkan
        </button>

        <a
            href="{{ route('superadmin.laporan.pdf', request()->query()) }}"
            class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
            Unduh PDF
        </a>
    </form>

    <div class="text-sm text-gray-600">
        Periode: <strong>{{ $periode }}</strong>
        <span class="mx-1">|</span>
        Wilayah: <strong>{{ $namaWilayah }}</strong>
    </div>

    {{-- 1. Rekap Kesehatan --}}
    @if ($tabAktif === 'rekap-kesehatan')

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-lg border bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Ibu Hamil</p>
                <p class="mt-2 text-2xl font-bold text-gray-800">
                    {{ $ringkasan['ibu_hamil'] }}
                </p>
            </div>

            <div class="rounded-lg border bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Anak Terdaftar</p>
                <p class="mt-2 text-2xl font-bold text-gray-800">
                    {{ $ringkasan['anak'] }}
                </p>
            </div>

            <div class="rounded-lg border bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Perlu Perhatian</p>
                <p class="mt-2 text-2xl font-bold text-red-600">
                    {{ $ringkasan['perlu_perhatian'] }}
                </p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-5 py-3">Wilayah</th>
                        <th class="px-5 py-3 text-center">Ibu Hamil</th>
                        <th class="px-5 py-3 text-center">Anak</th>
                        <th class="px-5 py-3 text-center">Diperiksa</th>
                        <th class="px-5 py-3 text-center">Perlu Perhatian</th>
                        <th class="px-5 py-3 text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($rekapWilayah as $item)
                        <tr>
                            <td class="px-5 py-3 font-medium">
                                {{ $item->nama_wilayah }}
                            </td>
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

    {{-- 2. Kegiatan Posyandu --}}
    @elseif ($tabAktif === 'kegiatan-posyandu')

        <div class="overflow-x-auto rounded-lg border bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Wilayah</th>
                        <th class="px-5 py-3">Jenis Kegiatan</th>
                        <th class="px-5 py-3">Lokasi</th>
                        <th class="px-5 py-3 text-center">Anak Diperiksa</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($kegiatanPosyandu as $item)
                        <tr>
                            <td class="px-5 py-3">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3">
                                {{ $item->tanggal?->format('d-m-Y') ?? '-' }}
                            </td>
                            <td class="px-5 py-3">
                                {{ $item->wilayah?->nama_wilayah ?? '-' }}
                            </td>
                            <td class="px-5 py-3">
                                {{ $item->jenis_kegiatan ?? '-' }}
                            </td>
                            <td class="px-5 py-3">
                                {{ $item->lokasi ?? '-' }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                {{ $item->pemeriksaan_count }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500">
                                Belum ada kegiatan Posyandu pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    {{-- 3. Data Risiko --}}
    @elseif ($tabAktif === 'data-risiko')

        <div class="overflow-x-auto rounded-lg border bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600">
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
                <tbody class="divide-y">
                    @forelse ($dataRisiko as $item)
                        <tr>
                            <td class="px-5 py-3">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3 font-medium">{{ $item->nama }}</td>
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

    {{-- 4. Rekap Pemeriksaan --}}
    @elseif ($tabAktif === 'rekap-pemeriksaan')

        <div class="overflow-x-auto rounded-lg border bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3 text-center">Pemeriksaan Ibu</th>
                        <th class="px-5 py-3 text-center">Pemeriksaan Anak</th>
                        <th class="px-5 py-3 text-center">Kegiatan Posyandu</th>
                        <th class="px-5 py-3 text-center">Total Pemeriksaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($rekapPemeriksaan as $item)
                        <tr>
                            <td class="px-5 py-3 font-medium">{{ $item->bulan }}</td>
                            <td class="px-5 py-3 text-center">{{ $item->pemeriksaan_ibu }}</td>
                            <td class="px-5 py-3 text-center">{{ $item->pemeriksaan_anak }}</td>
                            <td class="px-5 py-3 text-center">{{ $item->kegiatan_posyandu }}</td>
                            <td class="px-5 py-3 text-center font-semibold">{{ $item->total }}</td>
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

    @endif
</div>
@endsection
