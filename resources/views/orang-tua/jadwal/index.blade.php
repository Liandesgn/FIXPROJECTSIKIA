
@extends('layouts.app')

@section('title', 'Jadwal')

@section('content')
<div class="min-h-screen bg-white px-4 py-6 sm:px-6">

    <div class="mx-auto max-w-7xl">

        {{-- Judul --}}
        <div class="mb-7">
            <h1 class="text-2xl font-bold text-gray-800">
                Jadwal
            </h1>
        </div>

        {{-- Tab Navigasi --}}
        <div class="border-b border-gray-200">
            <div class="flex gap-6" role="tablist">

                <button
                    type="button"
                    id="tab-pemeriksaan"
                    role="tab"
                    aria-selected="true"
                    aria-controls="panel-pemeriksaan"
                    onclick="ubahTabJadwal('pemeriksaan')"
                    class="tab-jadwal border-b-2 border-[#C9517E] px-3 py-3 text-sm font-semibold text-[#542D3D]"
                >
                    Pemeriksaan Ibu Hamil
                </button>

                <button
                    type="button"
                    id="tab-posyandu"
                    role="tab"
                    aria-selected="false"
                    aria-controls="panel-posyandu"
                    onclick="ubahTabJadwal('posyandu')"
                    class="tab-jadwal border-b-2 border-transparent px-3 py-3 text-sm font-semibold text-gray-500 hover:text-[#C9517E]"
                >
                    Posyandu
                </button>

            </div>
        </div>

        {{-- Konten Pemeriksaan Ibu Hamil --}}
        <div
            id="panel-pemeriksaan"
            role="tabpanel"
            aria-labelledby="tab-pemeriksaan"
            class="tab-panel py-5"
        >

            @forelse ($jadwalPemeriksaan as $jadwal)
                <div class="mb-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-base font-semibold text-[#542D3D]">
                            {{ $jadwal->jenis_kegiatan }}
                        </h3>

                        <span class="rounded-full bg-pink-50 px-3 py-1 text-xs font-medium text-[#C9517E]">
                            Pemeriksaan Ibu Hamil
                        </span>
                    </div>

                    <div class="grid gap-3 text-sm text-gray-600 sm:grid-cols-2">

                        <p>
                            <span class="font-medium text-gray-700">Tanggal:</span>
                            {{ $jadwal->tanggal?->translatedFormat('d F Y') }}
                        </p>

                        <p>
                            <span class="font-medium text-gray-700">Waktu:</span>
                            {{ \Carbon\Carbon::parse($jadwal->waktu)->format('H:i') }} WIB
                        </p>

                        <p>
                            <span class="font-medium text-gray-700">Lokasi:</span>
                            {{ $jadwal->lokasi }}
                        </p>

                        <p>
                            <span class="font-medium text-gray-700">Bidan Pemeriksa:</span>
                            {{ $jadwal->jadwalPemeriksaan?->bidan?->user?->name ?? 'Belum ditentukan' }}
                        </p>

                    </div>

                    @if ($jadwal->jadwalPemeriksaan?->catatan)
                        <div class="mt-4 border-t border-gray-100 pt-3 text-sm text-gray-600">
                            <span class="font-medium text-gray-700">Catatan:</span>
                            {{ $jadwal->jadwalPemeriksaan->catatan }}
                        </div>
                    @endif

                </div>
            @empty

                <div class="flex min-h-[230px] flex-col items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-10">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="mb-3 h-10 w-10 text-slate-300"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
                    </svg>

                    <p class="text-sm text-slate-400">
                        Belum ada jadwal
                    </p>

                </div>

            @endforelse

        </div>

        {{-- Konten Posyandu --}}
        <div
            id="panel-posyandu"
            role="tabpanel"
            aria-labelledby="tab-posyandu"
            class="tab-panel hidden py-5"
        >

            @forelse ($jadwalPosyandu as $jadwal)
                <div class="mb-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-base font-semibold text-[#542D3D]">
                            {{ $jadwal->jenis_kegiatan }}
                        </h3>

                        <span class="rounded-full bg-pink-50 px-3 py-1 text-xs font-medium text-[#C9517E]">
                            Posyandu
                        </span>
                    </div>

                    <div class="grid gap-3 text-sm text-gray-600 sm:grid-cols-2">

                        <p>
                            <span class="font-medium text-gray-700">Tanggal:</span>
                            {{ $jadwal->tanggal?->translatedFormat('d F Y') }}
                        </p>

                        <p>
                            <span class="font-medium text-gray-700">Waktu:</span>
                            {{ \Carbon\Carbon::parse($jadwal->waktu)->format('H:i') }} WIB
                        </p>

                        <p>
                            <span class="font-medium text-gray-700">Lokasi:</span>
                            {{ $jadwal->lokasi }}
                        </p>

                        <p>
                            <span class="font-medium text-gray-700">RW:</span>
                            {{ $jadwal->jadwalPosyandu?->kegiatan?->rw?->nomor_rw ?? '-' }}
                        </p>

                        <p>
                            <span class="font-medium text-gray-700">Bidan:</span>
                            {{ $jadwal->jadwalPosyandu?->bidan?->user?->name ?? 'Belum ditentukan' }}
                        </p>

                        <p>
                            <span class="font-medium text-gray-700">Kader:</span>
                            {{ $jadwal->jadwalPosyandu?->kader?->user?->name ?? 'Belum ditentukan' }}
                        </p>

                    </div>

                </div>
            @empty

                <div class="flex min-h-[230px] flex-col items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-10">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="mb-3 h-10 w-10 text-slate-300"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
                    </svg>

                    <p class="text-sm text-slate-400">
                        Belum ada jadwal
                    </p>

                </div>

            @endforelse

        </div>

    </div>
</div>

{{-- Script Tab --}}
<script>
    function ubahTabJadwal(tab) {
        const tabs = ['pemeriksaan', 'posyandu'];

        tabs.forEach(function (nama) {
            const tombol = document.getElementById('tab-' + nama);
            const panel = document.getElementById('panel-' + nama);
            const aktif = nama === tab;

            tombol.setAttribute('aria-selected', aktif ? 'true' : 'false');

            tombol.classList.toggle('border-[#C9517E]', aktif);
            tombol.classList.toggle('text-[#542D3D]', aktif);

            tombol.classList.toggle('border-transparent', !aktif);
            tombol.classList.toggle('text-gray-500', !aktif);

            panel.classList.toggle('hidden', !aktif);
        });
    }
</script>
@endsection
