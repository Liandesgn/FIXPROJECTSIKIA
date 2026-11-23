 @extends('layouts.app')

@section('title', 'Jadwal Posyandu')

@section('content')
<div class="min-h-screen bg-[#FFF8FA] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-[#542D3D]">
                Jadwal Posyandu
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Lihat jadwal kegiatan Posyandu di wilayah kerja Anda.
            </p>
        </div>

        {{-- Filter Jadwal --}}
        <div class="mb-6 rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
            <form action="{{ route('kader.jadwal.index') }}"
                  method="GET"
                  class="grid grid-cols-1 gap-4 md:grid-cols-3">

                {{-- Cari Posyandu --}}
                <div>
                    <label for="cari" class="mb-2 block text-sm font-medium text-gray-700">
                        Cari Posyandu
                    </label>
                    <input
                        type="text"
                        id="cari"
                        name="cari"
                        value="{{ $cari }}"
                        placeholder="Cari nama atau lokasi Posyandu..."
                        class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-pink-400 focus:outline-none focus:ring-2 focus:ring-pink-100">
                </div>

                {{-- Filter RW/RT --}}
                <div>
                    <label for="rw_rt" class="mb-2 block text-sm font-medium text-gray-700">
                        RW / RT
                    </label>
                    <select
                        id="rw_rt"
                        name="rw_rt"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-pink-400 focus:outline-none focus:ring-2 focus:ring-pink-100">

                        <option value="">Semua RW / RT</option>

                        @foreach($daftarRw as $rw)
                            @foreach($rw->rt as $rt)
                                @php
                                    $nilaiRwRt = $rw->id . ':' . $rt->id;
                                    $nomorRw = str_pad($rw->nomor_rw, 3, '0', STR_PAD_LEFT);
                                    $nomorRt = str_pad($rt->nomor_rt, 3, '0', STR_PAD_LEFT);
                                @endphp

                                <option value="{{ $nilaiRwRt }}"
                                    {{ $rwRt == $nilaiRwRt ? 'selected' : '' }}>
                                    RW {{ $nomorRw }} / RT {{ $nomorRt }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                {{-- Filter Bulan --}}
                <div>
                    <label for="bulan" class="mb-2 block text-sm font-medium text-gray-700">
                        Bulan
                    </label>
                    <select
                        id="bulan"
                        name="bulan"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-pink-400 focus:outline-none focus:ring-2 focus:ring-pink-100">

                        <option value="">Semua Bulan</option>

                        @foreach([
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ] as $angka => $namaBulan)
                            <option value="{{ $angka }}"
                                {{ (string) $bulan === (string) $angka ? 'selected' : '' }}>
                                {{ $namaBulan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Filter --}}
                <div class="flex flex-wrap gap-2 md:col-span-3">
                    <button type="submit"
                            class="rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#AD416B]">
                        Cari Jadwal
                    </button>

                    <a href="{{ route('kader.jadwal.index') }}"
                       class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Daftar Jadwal Posyandu --}}

            <span class="rounded-full bg-pink-100 px-3 py-1 text-sm font-semibold text-[#C9517E]">
                {{ $jadwalMendatang->count() }} Jadwal
            </span>
        </div>

        @forelse($jadwalMendatang as $jadwal)
            @php
                $jadwalPosyandu = $jadwal->jadwalPosyandu;
                $kegiatan = $jadwalPosyandu?->kegiatan;
                $kaderPetugas = $jadwalPosyandu?->kader?->user;
                $bidanPetugas = $jadwalPosyandu?->bidan?->user;
            @endphp

            <div class="mb-4 rounded-2xl border border-pink-100 bg-white p-5 shadow-sm transition hover:shadow-md">

                {{-- Judul Kegiatan --}}
                <div class="mb-4 border-b border-pink-100 pb-3">

                    <h3 class="mt-2 text-lg font-bold text-[#542D3D]">
                        {{ $kegiatan?->jenis_kegiatan ?? $jadwal->jenis_kegiatan ?? 'Kegiatan Posyandu' }}
                    </h3>
                </div>

                {{-- Informasi Jadwal --}}
                <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2 lg:grid-cols-3">

                    <div>
                        <p class="mb-1 text-gray-500">Tanggal</p>
                        <p class="font-semibold text-gray-800">
                            {{ $jadwal->tanggal?->translatedFormat('d F Y') ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-gray-500">Waktu</p>
                        <p class="font-semibold text-gray-800">
                            {{ $jadwal->waktu ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-gray-500">Lokasi</p>
                        <p class="font-semibold text-gray-800">
                            {{ $jadwal->lokasi ?? $kegiatan?->lokasi ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-gray-500">Kader</p>
                        <p class="font-semibold text-gray-800">
                            {{ $kaderPetugas?->name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-1 text-gray-500">Bidan Pemeriksa</p>
                        <p class="font-semibold text-gray-800">
                            {{ $bidanPetugas?->name ?? '-' }}
                        </p>
                    </div>

                </div>
            </div>

        @empty
            <div class="rounded-2xl border border-dashed border-pink-200 bg-white px-5 py-12 text-center">
                <div class="mb-3 text-4xl">📅</div>

                <h3 class="font-semibold text-[#542D3D]">
                    Belum ada jadwal
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Belum ada jadwal Posyandu yang sesuai dengan filter.
                </p>
            </div>
        @endforelse

    </div>
</div>
@endsection