@extends('layouts.app')

@section('title', 'Jadwal')

@section('content')
@php
    $kartu = 'overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm';
    $ikonKosong = '<svg class="mx-auto mb-3 h-10 w-10 text-[#E99AB7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/></svg>';
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Jadwal</h1>
    </div>

    {{-- Tab navigasi --}}
    <div class="flex w-full gap-2 rounded-2xl border border-[#F1D5E0] bg-white p-1.5 shadow-sm sm:w-fit" role="tablist" aria-label="Jenis jadwal">
        <button type="button" id="tab-pemeriksaan" role="tab" aria-selected="true"
                aria-controls="panel-pemeriksaan" onclick="ubahTabJadwal('pemeriksaan')"
                class="flex-1 whitespace-nowrap rounded-xl bg-[#C9517E] px-4 py-2 text-sm font-semibold text-white shadow-sm transition sm:flex-none">
            Pemeriksaan Ibu Hamil
        </button>

        <button type="button" id="tab-posyandu" role="tab" aria-selected="false"
                aria-controls="panel-posyandu" onclick="ubahTabJadwal('posyandu')"
                class="flex-1 whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold text-[#542D3D] transition hover:bg-[#FCE7F0] sm:flex-none">
            Posyandu
        </button>
    </div>

    {{-- Konten Pemeriksaan Ibu Hamil --}}
    <div id="panel-pemeriksaan" role="tabpanel" aria-labelledby="tab-pemeriksaan" class="tab-panel space-y-4">

        @forelse ($jadwalPemeriksaan as $jadwal)
            @php
                $baris = [
                    'Tanggal'         => $jadwal->tanggal?->translatedFormat('d F Y') ?? '-',
                    'Waktu'           => $jadwal->waktu ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i') . ' WIB' : '-',
                    'Lokasi'          => $jadwal->lokasi ?: '-',
                    'Bidan Pemeriksa' => $jadwal->jadwalPemeriksaan?->bidan?->user?->name ?? 'Belum ditentukan',
                ];

                $catatan = $jadwal->jadwalPemeriksaan?->catatan;
            @endphp

            <div class="{{ $kartu }}">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
                    <h3 class="text-base font-bold text-[#542D3D]">{{ $jadwal->jenis_kegiatan }}</h3>

                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-[#C9517E] ring-1 ring-[#E99AB7]">
                        Pemeriksaan Ibu Hamil
                    </span>
                </div>

                <dl class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                    @foreach ($baris as $label => $nilai)
                        <div class="min-w-0">
                            <dt class="text-xs font-semibold text-slate-600">{{ $label }}</dt>
                            <dd class="mt-1 break-words text-sm font-bold text-[#542D3D]">{{ $nilai }}</dd>
                        </div>
                    @endforeach

                    @if ($catatan)
                        <div class="min-w-0 border-t border-[#F7E6ED] pt-4 sm:col-span-2">
                            <dt class="text-xs font-semibold text-slate-600">Catatan</dt>
                            <dd class="mt-1 break-words text-sm font-bold text-[#542D3D]">{{ $catatan }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @empty
            <div class="{{ $kartu }} px-4 py-12 text-center">
                {!! $ikonKosong !!}
                <p class="font-bold text-[#542D3D]">Belum ada jadwal</p>
            </div>
        @endforelse

    </div>

    {{-- Konten Posyandu --}}
    <div id="panel-posyandu" role="tabpanel" aria-labelledby="tab-posyandu" class="tab-panel hidden space-y-4">

        @forelse ($jadwalPosyandu as $jadwal)
            @php
                $baris = [
                    'Tanggal'         => $jadwal->tanggal?->translatedFormat('d F Y') ?? '-',
                    'Waktu'           => $jadwal->waktu ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i') . ' WIB' : '-',
                    'Lokasi'          => $jadwal->lokasi ?: '-',
                    'RW'              => $jadwal->jadwalPosyandu?->kegiatan?->rw?->nomor_rw ?? '-',
                    'Bidan'           => $jadwal->jadwalPosyandu?->bidan?->user?->name ?? 'Belum ditentukan',
                    'Kader'           => $jadwal->jadwalPosyandu?->kader?->user?->name ?? 'Belum ditentukan',
                ];
            @endphp

            <div class="{{ $kartu }}">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
                    <h3 class="text-base font-bold text-[#542D3D]">{{ $jadwal->jenis_kegiatan }}</h3>

                    <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-[#C9517E] ring-1 ring-[#E99AB7]">
                        Posyandu
                    </span>
                </div>

                <dl class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                    @foreach ($baris as $label => $nilai)
                        <div class="min-w-0">
                            <dt class="text-xs font-semibold text-slate-600">{{ $label }}</dt>
                            <dd class="mt-1 break-words text-sm font-bold text-[#542D3D]">{{ $nilai }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @empty
            <div class="{{ $kartu }} px-4 py-12 text-center">
                {!! $ikonKosong !!}
                <p class="font-bold text-[#542D3D]">Belum ada jadwal</p>
            </div>
        @endforelse

    </div>

</div>

{{-- Script Tab --}}
<script>
    function ubahTabJadwal(tab) {
        const tabs = ['pemeriksaan', 'posyandu'];

        const kelasAktif = ['bg-[#C9517E]', 'text-white', 'shadow-sm'];
        const kelasNonaktif = ['text-[#542D3D]', 'hover:bg-[#FCE7F0]'];

        tabs.forEach(function (nama) {
            const tombol = document.getElementById('tab-' + nama);
            const panel = document.getElementById('panel-' + nama);
            const aktif = nama === tab;

            tombol.setAttribute('aria-selected', aktif ? 'true' : 'false');

            kelasAktif.forEach(function (c) { tombol.classList.toggle(c, aktif); });
            kelasNonaktif.forEach(function (c) { tombol.classList.toggle(c, !aktif); });

            panel.classList.toggle('hidden', !aktif);
        });
    }
</script>
@endsection