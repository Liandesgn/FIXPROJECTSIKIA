
@extends('layouts.app')

@section('title', 'Data Posyandu')

@section('content')
@php
    $base = $base ?? 'admin';

    $inputClass = 'rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none focus:border-[#C9517E] focus:ring-2 focus:ring-[#E99AB7]/30';
@endphp

<div class="mx-auto w-full max-w-[96rem] space-y-6">

    {{-- Judul halaman --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">
            Data Posyandu
        </h1>

        <p class="mt-1 text-sm text-gray-600">
            Data Master / Data Posyandu
        </p>
    </div>

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

        <div class="rounded-2xl bg-[#E1E1E1] p-4">
            <p class="text-sm font-medium text-gray-700">
                Jumlah Kegiatan
            </p>

            <p class="mt-2 text-3xl font-semibold text-gray-900">
                {{ number_format($jumlahKegiatan ?? 0, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-2xl bg-[#E1E1E1] p-4">
            <p class="text-sm font-medium text-gray-700">
                Anak Diperiksa
            </p>

            <p class="mt-2 text-3xl font-semibold text-gray-900">
                {{ number_format($jumlahAnakDiperiksa ?? 0, 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-2xl bg-[#E1E1E1] p-4">
            <p class="text-sm font-medium text-gray-700">
                Posyandu Aktif
            </p>

            <p class="mt-2 text-3xl font-semibold text-gray-900">
                {{ number_format($posyanduAktif ?? 0, 0, ',', '.') }}
            </p>
        </div>

    </div>

    {{-- Pencarian dan filter periode --}}
    <div class="pt-2">
        <form
            method="GET"
            action="{{ route($base . '.data-posyandu.index') }}"
            class="flex flex-wrap items-center gap-3"
        >

            <div class="relative w-full sm:max-w-xs">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    autocomplete="off"
                    placeholder="Cari Posyandu"
                    class="{{ $inputClass }} w-full pl-10"
                >

                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="11" cy="11" r="7"></circle>
                        <path stroke-linecap="round" d="m16 16 4 4"></path>
                    </svg>
                </span>
            </div>

            <select
                name="periode"
                onchange="this.form.submit()"
                class="{{ $inputClass }} min-w-[150px] font-semibold"
            >
                <option value="">Semua Periode</option>

                @foreach ($periodes as $p)
                    <option
                        value="{{ $p }}"
                        @selected(request('periode') === $p)
                    >
                        {{ \Illuminate\Support\Carbon::createFromFormat('!Y-m', $p)->translatedFormat('F Y') }}
                    </option>
                @endforeach
            </select>

            <button
                type="submit"
                class="rounded-lg bg-[#C9517E] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#B7436D]"
            >
                Cari
            </button>

            @if (request()->hasAny(['search', 'periode']))
                <a
                    href="{{ route($base . '.data-posyandu.index') }}"
                    class="px-2 py-2 text-sm font-medium text-gray-600 hover:underline"
                >
                    Reset
                </a>
            @endif

        </form>
    </div>

    {{-- Tabel data kegiatan --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left">

                <thead class="bg-[#E5E7EB] text-xs font-bold text-gray-700">
                    <tr>
                        <th class="w-14 px-3 py-3 text-center">
                            No
                        </th>

                        <th class="px-3 py-3">
                            Tanggal
                        </th>

                        <th class="px-3 py-3">
                            Jenis Kegiatan
                        </th>

                        <th class="px-3 py-3">
                            Lokasi
                        </th>

                        <th class="px-3 py-3">
                            Anak Diperiksa
                        </th>

                        <th class="w-24 px-3 py-3 text-center">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse ($kegiatans as $k)
                        <tr class="transition hover:bg-gray-50">

                            <td class="px-3 py-3 text-center text-xs text-gray-600">
                                {{ $kegiatans->firstItem() + $loop->index }}
                            </td>

                            <td class="whitespace-nowrap px-3 py-3 text-xs text-gray-700">
                                {{ $k->tanggal?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td class="px-3 py-3 text-xs font-medium text-gray-800">
                                {{ $k->jenis_kegiatan ?? '-' }}
                            </td>

                            <td class="px-3 py-3 text-xs text-gray-700">
                                {{ $k->lokasi ?: '-' }}
                            </td>

                            <td class="px-3 py-3 text-xs text-gray-700">
                                {{ ($jumlahAnak[$k->id] ?? 0) ?: '-' }}
                            </td>

                            <td class="px-3 py-3 text-center">
                                <a
                                    href="{{ route($base . '.data-posyandu.show', $k) }}"
                                    class="inline-flex items-center justify-center rounded-md border border-gray-400 bg-white px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100"
                                >
                                    Detail
                                </a>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <p class="text-sm font-semibold text-gray-700">
                                    Belum ada data kegiatan Posyandu.
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Data kegiatan akan ditampilkan setelah tersedia.
                                </p>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Informasi jumlah data dan pagination --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-4 py-3">

            <span class="text-xs text-gray-500">
                Menampilkan
                {{ $kegiatans->firstItem() ?? 0 }}-{{ $kegiatans->lastItem() ?? 0 }}
                dari {{ $kegiatans->total() }} data
            </span>

            <div>
                {{ $kegiatans->links() }}
            </div>

        </div>

    </div>

</div>
@endsection
