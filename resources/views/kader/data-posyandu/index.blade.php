
@extends('layouts.app')

@section('title', 'Pendataan Anak')

@section('content')
@php
    $routePrefix = match (true) {
        request()->routeIs('bidan.*') => 'bidan',
        request()->routeIs('kader.*') => 'kader',
        default => 'admin',
    };
@endphp

    <div class="min-h-screen bg-[#FFF8FA] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">

            {{-- Header --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-[#542D3D]">
                    Pendataan Anak
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Pendataan Posyandu
                </p>
            </div>

            {{-- Informasi Kegiatan Posyandu Hari Ini --}}
            <div class="mb-6 rounded-xl border border-[#F3D5E1] bg-white px-5 py-4 shadow-sm">
                @if($jadwalHariIni)
                    @php
                        $kegiatan = $jadwalHariIni->jadwalPosyandu?->kegiatan;
                        $namaKegiatan = $kegiatan?->jenis_kegiatan ?? 'Kegiatan Posyandu';
                        $lokasi = $jadwalHariIni->lokasi
                            ?? $kegiatan?->lokasi
                            ?? '-';
                    @endphp

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-semibold text-[#542D3D]">
                                {{ $namaKegiatan }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Kegiatan
                                {{ \Carbon\Carbon::parse($jadwalHariIni->tanggal)->format('d/m/Y') }}
                                - {{ $lokasi }}
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                Pukul {{ $jadwalHariIni->waktu }}
                            </p>
                        </div>

                        <span class="inline-flex w-fit items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                            Ada Kegiatan Hari Ini
                        </span>
                    </div>
                @else
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-semibold text-[#542D3D]">
                                Informasi Kegiatan Posyandu Hari Ini
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Tidak ada kegiatan Posyandu yang dijadwalkan hari ini.
                            </p>
                        </div>

                        <span class="inline-flex w-fit items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                            Tidak Ada Kegiatan
                        </span>
                    </div>
                @endif
            </div>

            {{-- Pencarian dan filter --}}
            <div class="mb-5 rounded-xl border border-[#F3D5E1] bg-white p-5">
                <form action="{{ route($routePrefix . '.data-posyandu.index') }}"
                      method="GET"
                      class="grid grid-cols-1 gap-3 md:grid-cols-4">

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama anak atau NIK..."
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm md:col-span-2"
                    >

                    <select name="status"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm">
                        <option value="">Semua Status</option>
                        <option value="belum_diperiksa" @selected(request('status') === 'belum_diperiksa')>
                            Belum Diperiksa
                        </option>
                        <option value="Normal" @selected(request('status') === 'Normal')>
                            Normal
                        </option>
                        <option value="Pendek" @selected(request('status') === 'Pendek')>
                            Pendek
                        </option>
                        <option value="Sangat Pendek" @selected(request('status') === 'Sangat Pendek')>
                            Sangat Pendek
                        </option>
                        <option value="Tinggi" @selected(request('status') === 'Tinggi')>
                            Tinggi
                        </option>
                    </select>

                    <input
                        type="month"
                        name="periode"
                        value="{{ request('periode') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm"
                    >

                    <div class="flex flex-wrap gap-2 md:col-span-4">
                        <button type="submit"
                                class="rounded-lg bg-[#C9517E] px-5 py-2 text-sm font-semibold text-white hover:bg-[#B74470]">
                            Cari Data
                        </button>

                        <a href="{{ route($routePrefix . '.data-posyandu.index') }}"
                           class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-600">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- Tabel anak --}}
            <div class="overflow-hidden rounded-xl border border-[#F3D5E1] bg-white shadow-sm">

                <div class="flex flex-col gap-2 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="font-semibold text-[#542D3D]">
                        Daftar Anak
                    </h2>

                    <span class="text-sm text-gray-500">
                        Menampilkan {{ $anakList->firstItem() ?? 0 }}–{{ $anakList->lastItem() ?? 0 }}
                        dari {{ $anakList->total() }} anak
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FFF1F5] text-[#542D3D]">
                            <tr>
                                <th class="px-5 py-4">No</th>
                                <th class="px-5 py-4">Anak</th>
                                <th class="px-5 py-4">NIK</th>
                                <th class="px-5 py-4">Usia</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse($anakList as $index => $anak)
                                @php
                                    $usia = $anak->tgl_lahir
                                        ? \Carbon\Carbon::parse($anak->tgl_lahir)->diff(now())
                                        : null;

                                    $status = $anak->status_stunting;
                                @endphp

                                <tr class="hover:bg-[#FFFAFC]">
                                    <td class="px-5 py-4 text-gray-600">
                                        {{ $anakList->firstItem() + $index }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-[#542D3D]">
                                            {{ $anak->nama_anak }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $anak->jenis_kelamin ?? '-' }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 text-gray-600">
                                        {{ $anak->nik_anak ?? '-' }}
                                    </td>

                                    <td class="px-5 py-4 text-gray-600">
                                        @if($usia)
                                            {{ $usia->y > 0 ? $usia->y . ' th ' : '' }}
                                            {{ $usia->m }} bln
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-5 py-4">
                                        @if(!$anak->pemeriksaan_id)
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                                Belum Diperiksa
                                            </span>
                                        @elseif($status === 'Normal')
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                Normal
                                            </span>
                                        @else
                                            <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">
                                                {{ $status ?? 'Sudah Diperiksa' }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap justify-center gap-2">

{{-- DETAIL --}}
<a
    href="{{ route($routePrefix . '.data-anak.show', $anak->anak_id) }}"
    class="rounded-md bg-gray-200 px-2.5 py-1 text-xs font-medium text-gray-800 transition hover:bg-gray-300"
>
    Detail
</a>

                                            @if($anak->pemeriksaan_id && $anak->kegiatan_posyandu_id)

                                                {{-- Edit Pemeriksaan --}}
                                                <a href="{{ route($routePrefix . '.data-posyandu.pemeriksaan.edit', [
                                                    'kegiatan' => $anak->kegiatan_posyandu_id,
                                                    'anak' => $anak->anak_id,
                                                    'pemeriksaan' => $anak->pemeriksaan_id,
                                                ]) }}"
                                                   class="rounded-lg border border-orange-200 px-3 py-1.5 text-xs font-semibold text-orange-600 hover:bg-orange-50">
                                                    Edit Pemeriksaan
                                                </a>

                                            @elseif(!$anak->pemeriksaan_id && $jadwalHariIni?->jadwalPosyandu?->kegiatan)

                                                @php
                                                    $kegiatanInput = $jadwalHariIni->jadwalPosyandu->kegiatan;
                                                @endphp

                                                {{-- Input Pemeriksaan --}}
                                                <a href="{{ route($routePrefix . '.data-posyandu.pemeriksaan.create', [
                                                    'kegiatan' => $kegiatanInput->id,
                                                    'anak' => $anak->anak_id,
                                                ]) }}"
                                                   class="rounded-lg bg-[#C9517E] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#B74470]">
                                                    Input Pemeriksaan
                                                </a>

                                            @else

                                                <span class="text-xs text-gray-400">
                                                    Belum Ada Kegiatan
                                                </span>

                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center">
                                        <div class="text-4xl">📋</div>
                                        <p class="mt-3 font-semibold text-[#542D3D]">
                                            Belum Ada Data Anak
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Belum terdapat anak yang terdaftar di wilayah kerja Anda.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($anakList->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">
                        {{ $anakList->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
