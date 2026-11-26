
@extends('layouts.app')

@section('title', 'Detail Status Kehamilan')

@section('content')
@php
    $tingkatRisiko = strtolower($pemeriksaan->tingkat_risiko ?? '');

    $tinggi = $tingkatRisiko === 'tinggi';
    $sedang = $tingkatRisiko === 'sedang';

    $labelRisiko = $tinggi
        ? 'Tinggi'
        : ($sedang ? 'Sedang' : ($tingkatRisiko === 'rendah' ? 'Rendah' : 'Belum Diklasifikasikan'));

    $warnaRisiko = $tinggi
        ? 'bg-red-100 text-red-700'
        : ($sedang
            ? 'bg-yellow-100 text-yellow-700'
            : 'bg-green-100 text-green-700');

    $faktorRisiko = collect(explode(';', $pemeriksaan->faktor_risiko ?? ''))
        ->map(fn ($faktor) => trim($faktor))
        ->filter()
        ->values();

    $jumlahFaktor = $faktorRisiko->count();

    $teksFaktor = $faktorRisiko->isNotEmpty()
        ? $faktorRisiko->implode(', ')
        : 'Tidak ada faktor risiko yang tercatat.';

    $formatTanggal = fn ($tanggal) => $tanggal
        ? \Carbon\Carbon::parse($tanggal)->format('d/m/Y')
        : '-';

    $namaIbu = $ibu->user?->name ?? 'Ibu';

    if (str_contains(strtolower($teksFaktor), 'preeklampsia')) {
        $aturan = 'Preeklampsia → otomatis risiko Tinggi';
    } elseif ($tinggi) {
        $aturan = 'Faktor risiko yang ditemukan → klasifikasi risiko Tinggi';
    } elseif ($sedang) {
        $aturan = 'Faktor risiko yang ditemukan → klasifikasi risiko Sedang';
    } elseif ($tingkatRisiko === 'rendah') {
        $aturan = 'Tidak ditemukan faktor risiko berat → klasifikasi risiko Rendah';
    } else {
        $aturan = 'Belum ada aturan klasifikasi yang tersedia.';
    }

    $saran = $tinggi
        ? 'Segera hubungi Bidan / datang ke Puskesmas'
        : ($sedang
            ? 'Konsultasikan hasil pemeriksaan kepada Bidan'
            : ($tingkatRisiko === 'rendah'
                ? 'Lanjutkan pemeriksaan kehamilan secara rutin'
                : 'Lakukan pemeriksaan lebih lanjut kepada Bidan'));
@endphp

<div class="mx-auto w-full max-w-5xl space-y-6">

    {{-- Judul halaman --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
            Detail Status {{ $namaIbu }}
        </h1>

        <p class="mt-1 text-sm text-slate-600">
            Pemeriksaan / Detail / Detail Status
        </p>
    </div>

    {{-- Kartu ringkasan status risiko --}}
    <div class="rounded-xl border border-[#D9D9D9] bg-white px-5 py-5 shadow-sm sm:px-6">

        <h2 class="text-base font-bold text-[#542D3D]">
            Status Risiko Kehamilan - {{ $namaIbu }}
        </h2>

        {{-- Badge risiko dan skor --}}
        <div class="mt-3 flex flex-wrap items-center gap-2">

            <span class="inline-flex min-w-[105px] justify-center rounded-full px-4 py-1 text-xs font-bold {{ $warnaRisiko }}">
                {{ $labelRisiko }}
            </span>

            <span class="inline-flex rounded-full bg-slate-100 px-4 py-1 text-xs font-semibold text-slate-700">
                Skor {{ $pemeriksaan->skor_risiko ?? 0 }} • {{ $jumlahFaktor }} faktor
            </span>

        </div>

        {{-- Informasi status --}}
        <div class="mt-5 space-y-3 text-sm">

            <div class="grid grid-cols-1 gap-1 sm:grid-cols-[155px_1fr] sm:gap-3">
                <span class="font-medium text-slate-600">
                    Pemeriksaan terakhir
                </span>

                <span class="text-[#542D3D]">
                    {{ $formatTanggal($pemeriksaan->tgl_pemeriksaan) }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-1 sm:grid-cols-[155px_1fr] sm:gap-3">
                <span class="font-medium text-slate-600">
                    Faktor Risiko
                </span>

                <span class="text-[#542D3D]">
                    {{ $teksFaktor }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-1 sm:grid-cols-[155px_1fr] sm:gap-3">
                <span class="font-medium text-slate-600">
                    Aturan
                </span>

                <span class="text-[#542D3D]">
                    {{ $aturan }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-1 sm:grid-cols-[155px_1fr] sm:gap-3">
                <span class="font-medium text-slate-600">
                    Saran
                </span>

                <span class="font-medium text-[#542D3D]">
                    {{ $saran }}
                </span>
            </div>

        </div>
    </div>

    {{-- Tombol kembali --}}
    <div class="flex justify-end">
        <a href="{{ route('bidan.pemeriksaan-ibu.show', $ibu->id) }}"
           class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200">
            Kembali
        </a>
    </div>

</div>
@endsection
