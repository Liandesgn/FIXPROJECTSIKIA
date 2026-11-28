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
            ? 'bg-amber-100 text-amber-700'
            : ($tingkatRisiko === 'rendah' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'));

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

    $baris = [
        'Pemeriksaan terakhir' => $formatTanggal($pemeriksaan->tgl_pemeriksaan),
        'Faktor Risiko'        => $teksFaktor,
        'Aturan'               => $aturan,
        'Saran'                => $saran,
    ];
@endphp

<div class="mx-auto w-full max-w-5xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
            Detail Status {{ $namaIbu }}
        </h1>
        <p class="mt-1 text-sm font-medium text-slate-700">
            Pemeriksaan / Detail / Detail Status
        </p>
    </div>

    {{-- Kartu status risiko --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-4 sm:px-6">
            <h2 class="text-lg font-bold text-[#542D3D]">
                Status Risiko Kehamilan - {{ $namaIbu }}
            </h2>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="inline-flex min-w-[6.5rem] justify-center rounded-full px-4 py-1 text-xs font-bold {{ $warnaRisiko }}">
                    {{ $labelRisiko }}
                </span>

                <span class="inline-flex rounded-full bg-white px-4 py-1 text-xs font-bold text-[#542D3D] ring-1 ring-[#E99AB7]">
                    Skor {{ $pemeriksaan->skor_risiko ?? 0 }} • {{ $jumlahFaktor }} faktor
                </span>
            </div>
        </div>

        <dl class="divide-y divide-[#F7E6ED] px-5 sm:px-6">
            @foreach ($baris as $label => $nilai)
                <div class="grid grid-cols-1 gap-1 py-3.5 text-sm sm:grid-cols-[11rem_1fr] sm:gap-4">
                    <dt class="font-semibold text-slate-600">{{ $label }}</dt>
                    <dd class="break-words font-bold text-[#542D3D]">{{ $nilai }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    {{-- Tombol kembali --}}
    <div class="flex justify-end">
        <a href="{{ route('bidan.pemeriksaan-ibu.show', $ibu->id) }}"
           class="inline-flex justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-2.5 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/30">
            Kembali
        </a>
    </div>

</div>
@endsection