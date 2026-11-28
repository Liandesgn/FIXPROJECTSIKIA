@extends('layouts.app')

@section('title', 'Jadwal Posyandu')

@section('content')
@php
    $kartu      = 'overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm';
    $pilihClass = 'w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';

    $daftarBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $sedangMencari = filled($cari) || filled($rwRt) || filled($bulan);
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Jadwal</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">
            Lihat jadwal kegiatan Posyandu di wilayah kerja Anda.
        </p>
    </div>

    {{-- Filter jadwal --}}
    <div class="rounded-2xl border border-[#F1D5E0] bg-[#FCE7F0] p-3 sm:p-4">
        <form action="{{ route('kader.jadwal.index') }}" method="GET"
              class="flex flex-col gap-3 lg:flex-row lg:items-center">

            {{-- Cari Posyandu --}}
            <div class="relative flex-1">
                <label for="cari" class="sr-only">Cari Posyandu</label>
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                <input type="text" id="cari" name="cari" value="{{ $cari }}" autocomplete="off"
                       placeholder="Cari nama atau lokasi Posyandu"
                       class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
            </div>

            {{-- Filter RW/RT --}}
            <div class="lg:w-52">
                <label for="rw_rt" class="sr-only">RW / RT</label>
                <select id="rw_rt" name="rw_rt" class="{{ $pilihClass }}">
                    <option value="">Semua RW / RT</option>

                    @foreach ($daftarRw as $rw)
                        @foreach ($rw->rt as $rt)
                            @php
                                $nilaiRwRt = $rw->id . ':' . $rt->id;
                                $nomorRw = str_pad($rw->nomor_rw, 3, '0', STR_PAD_LEFT);
                                $nomorRt = str_pad($rt->nomor_rt, 3, '0', STR_PAD_LEFT);
                            @endphp

                            <option value="{{ $nilaiRwRt }}" {{ $rwRt == $nilaiRwRt ? 'selected' : '' }}>
                                RW {{ $nomorRw }} / RT {{ $nomorRt }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            {{-- Filter Bulan --}}
            <div class="lg:w-52">
                <label for="bulan" class="sr-only">Bulan</label>
                <select id="bulan" name="bulan" class="{{ $pilihClass }}">
                    <option value="">Semua Bulan</option>

                    @foreach ($daftarBulan as $angka => $namaBulan)
                        <option value="{{ $angka }}" {{ (string) $bulan === (string) $angka ? 'selected' : '' }}>
                            {{ $namaBulan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                Cari
            </button>

            @if ($sedangMencari)
                <a href="{{ route('kader.jadwal.index') }}"
                   class="text-center text-sm font-semibold text-[#C9517E] hover:underline">Reset</a>
            @endif
        </form>
    </div>

    {{-- Daftar jadwal Posyandu --}}
    <div class="flex items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-[#542D3D]">Jadwal Posyandu</h2>

        <span class="rounded-full bg-[#FCE7F0] px-3 py-1 text-sm font-bold text-[#C9517E]">
            {{ $jadwalMendatang->count() }} Jadwal
        </span>
    </div>

    <div class="space-y-4">
        @forelse ($jadwalMendatang as $jadwal)
            @php
                $jadwalPosyandu = $jadwal->jadwalPosyandu;
                $kegiatan       = $jadwalPosyandu?->kegiatan;
                $kaderPetugas   = $jadwalPosyandu?->kader?->user;
                $bidanPetugas   = $jadwalPosyandu?->bidan?->user;

                $baris = [
                    'Tanggal'         => $jadwal->tanggal?->translatedFormat('d F Y') ?? '-',
                    'Waktu'           => $jadwal->waktu ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i') : '-',
                    'Lokasi'          => $jadwal->lokasi ?? $kegiatan?->lokasi ?? '-',
                    'Kader'           => $kaderPetugas?->name ?? '-',
                    'Bidan Pemeriksa' => $bidanPetugas?->name ?? '-',
                ];
            @endphp

            <div class="{{ $kartu }}">
                <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
                    <h3 class="text-base font-bold text-[#542D3D]">
                        {{ $kegiatan?->jenis_kegiatan ?? $jadwal->jenis_kegiatan ?? 'Kegiatan Posyandu' }}
                    </h3>
                </div>

                <dl class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($baris as $label => $nilai)
                        <div class="min-w-0">
                            <dt class="text-xs font-semibold text-slate-600">{{ $label }}</dt>
                            <dd class="mt-1 break-words text-sm font-bold text-[#542D3D]">{{ $nilai }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

        @empty
            <div class="{{ $kartu }} px-5 py-12 text-center">
                <svg class="mx-auto mb-3 h-10 w-10 text-[#E99AB7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/></svg>
                <p class="font-bold text-[#542D3D]">Belum ada jadwal</p>
                <p class="mt-1 text-sm font-medium text-slate-600">
                    Belum ada jadwal Posyandu yang sesuai dengan filter.
                </p>
            </div>
        @endforelse
    </div>

</div>
@endsection