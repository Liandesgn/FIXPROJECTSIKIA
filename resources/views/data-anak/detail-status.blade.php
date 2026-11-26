
@extends('layouts.app')

@section('title', 'Detail Status Gizi')

@section('content')

@php
    $tanggal = $pemeriksaan?->kegiatan?->tanggal
        ?? $pemeriksaan?->tgl_pemeriksaan;

    $formatAngka = fn ($nilai, $desimal = 1) =>
        $nilai !== null
            ? number_format((float) $nilai, $desimal, ',', '.')
            : '-';

    $formatTanggal = fn ($nilai) =>
        $nilai
            ? \Carbon\Carbon::parse($nilai)->format('d/m/Y')
            : '-';

    // Label status stunting.
    $labelStunting = match ($pemeriksaan?->status_stunting) {
        'normal' => 'Normal',
        'pendek' => 'Pendek (stunting)',
        'sangat_pendek' => 'Sangat Pendek (stunting)',
        default => 'Belum diperiksa',
    };

    // Label tingkat risiko.
    $labelRisiko = match ($pemeriksaan?->tingkat_risiko) {
        'rendah' => 'Risiko Rendah',
        'sedang' => 'Risiko Sedang',
        'tinggi' => 'Risiko Tinggi',
        default => 'Belum diketahui',
    };

    $statusTersedia = $pemeriksaan
        && $pemeriksaan->z_score_tbu !== null
        && $pemeriksaan->status_stunting !== null;

    // Menentukan rumus sesuai posisi tinggi badan terhadap median.
    $tinggi = $pemeriksaan?->tinggi_badan;
    $median = $pemeriksaan?->median_tbu;
    $sdMinus = $pemeriksaan?->sd_minus_1_tbu;
    $sdPlus = $pemeriksaan?->sd_plus_1_tbu;
    $zScore = $pemeriksaan?->z_score_tbu;

    $rumus = null;

    if (
        $statusTersedia
        && $tinggi !== null
        && $median !== null
    ) {
        if ((float) $tinggi < (float) $median && $sdMinus !== null) {
            $pembilang = (float) $tinggi - (float) $median;
            $penyebut = (float) $median - (float) $sdMinus;

            $rumus = '('
                . $formatAngka($tinggi)
                . ' - '
                . $formatAngka($median)
                . ') / ('
                . $formatAngka($median)
                . ' - '
                . $formatAngka($sdMinus)
                . ') = '
                . $formatAngka($zScore, 2);
        } elseif (
            (float) $tinggi >= (float) $median
            && $sdPlus !== null
        ) {
            $rumus = '('
                . $formatAngka($tinggi)
                . ' - '
                . $formatAngka($median)
                . ') / ('
                . $formatAngka($sdPlus)
                . ' - '
                . $formatAngka($median)
                . ') = '
                . $formatAngka($zScore, 2);
        }
    }

    // Saran berdasarkan hasil status.
    $saran = match ($pemeriksaan?->status_stunting) {
        'sangat_pendek' =>
            'Segera konsultasikan hasil pertumbuhan anak dengan bidan atau tenaga kesehatan untuk mendapatkan pemeriksaan dan tindak lanjut.',

        'pendek' =>
            'Konsultasikan hasil pertumbuhan anak dengan bidan dan lakukan pemantauan pertumbuhan secara berkala.',

        'normal' =>
            'Tetap jaga asupan gizi seimbang dan lakukan pemantauan pertumbuhan anak secara rutin.',

        default =>
            'Belum ada saran karena data pemeriksaan status gizi belum tersedia.',
    };
@endphp

<div class="mx-auto w-full max-w-5xl space-y-5">

    {{-- Judul halaman --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#222222] sm:text-3xl">
            Detail {{ $anak->nama_anak }} Status Gizi
        </h1>

        <p class="mt-1 text-xs text-[#777777] sm:text-sm">
            Pendataan Posyandu / Detail / Detail Status
        </p>
    </div>

    {{-- Kartu status anak --}}
    <section class="rounded-xl border border-[#D9D9D9] bg-white p-5 shadow-sm sm:p-6">

        <h2 class="mb-3 text-sm font-semibold text-[#333333]">
            Status Anak - {{ $anak->nama_anak }}
        </h2>

        @if ($statusTersedia)

            {{-- Badge status --}}
            <div class="mb-5 flex flex-wrap gap-2">

                @if ($pemeriksaan->status_stunting === 'normal')
                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                        {{ $labelStunting }}
                    </span>
                @elseif ($pemeriksaan->status_stunting === 'pendek')
                    <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">
                        {{ $labelStunting }}
                    </span>
                @elseif ($pemeriksaan->status_stunting === 'sangat_pendek')
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                        {{ $labelStunting }}
                    </span>
                @endif

                @if ($pemeriksaan->tingkat_risiko === 'tinggi')
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                        {{ $labelRisiko }}
                    </span>
                @elseif ($pemeriksaan->tingkat_risiko === 'sedang')
                    <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">
                        {{ $labelRisiko }}
                    </span>
                @elseif ($pemeriksaan->tingkat_risiko === 'rendah')
                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                        {{ $labelRisiko }}
                    </span>
                @endif

            </div>

            {{-- Informasi pemeriksaan --}}
            <div class="space-y-3 text-sm">

                <div class="grid grid-cols-[125px_1fr] gap-3 sm:grid-cols-[190px_1fr]">
                    <span class="text-[#666666]">
                        Pemeriksaan terakhir
                    </span>
                    <span class="font-medium text-[#333333]">
                        {{ $formatTanggal($tanggal) }}
                    </span>
                </div>

                <div class="grid grid-cols-[125px_1fr] gap-3 sm:grid-cols-[190px_1fr]">
                    <span class="text-[#666666]">
                        Umur / TB
                    </span>
                    <span class="font-medium text-[#333333]">
                        {{ $pemeriksaan->umur_bulan ?? '-' }} bulan
                        /
                        {{ $formatAngka($tinggi) }} cm
                    </span>
                </div>

                <div class="grid grid-cols-[125px_1fr] gap-3 sm:grid-cols-[190px_1fr]">
                    <span class="text-[#666666]">
                        Median / -1 SD
                        <span class="block text-xs text-[#999999]">
                            (tabel standar)
                        </span>
                    </span>

                    <span class="font-medium text-[#333333]">
                        {{ $formatAngka($median) }} /
                        {{ $formatAngka($sdMinus) }} cm
                    </span>
                </div>

                <div class="grid grid-cols-[125px_1fr] gap-3 sm:grid-cols-[190px_1fr]">
                    <span class="text-[#666666]">
                        Rumus
                    </span>

                    <span class="break-words font-medium text-[#333333]">
                        {{ $rumus ?? 'Data rumus belum lengkap.' }}
                    </span>
                </div>

                <div class="grid grid-cols-[125px_1fr] gap-3 sm:grid-cols-[190px_1fr]">
                    <span class="text-[#666666]">
                        Saran
                    </span>

                    <span class="font-medium text-[#333333]">
                        {{ $saran }}
                    </span>
                </div>

            </div>

        @else

            <div class="rounded-lg bg-[#F8FAFC] px-4 py-8 text-center">
                <p class="text-sm font-medium text-[#64748B]">
                    Belum ada data status gizi anak.
                </p>

                <p class="mt-1 text-xs text-[#94A3B8]">
                    Detail status akan ditampilkan setelah hasil pemeriksaan tersimpan.
                </p>
            </div>

        @endif

    </section>

    {{-- Tombol kembali --}}
    <div class="flex justify-end pb-3">
        <a
            href="{{ route($base . '.data-anak.show', $anak) }}"
            class="inline-flex items-center justify-center rounded-lg border border-[#D1D5DB] bg-white px-5 py-2.5 text-sm font-medium text-[#333333] transition hover:bg-[#F8FAFC] focus:outline-none focus:ring-2 focus:ring-[#E99AB7]/40"
        >
            Kembali
        </a>
    </div>

</div>

@endsection
