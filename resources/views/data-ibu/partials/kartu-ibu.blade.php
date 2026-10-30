{{-- Kartu ringkas data ibu. Parameter: $ibu --}}
@php
    $wk = $ibu->wilayahKerja;
    $wilayah = $wk
        ? trim(($wk->desa ? 'Desa '.$wk->desa : '').($wk->kecamatan ? ', Kec. '.$wk->kecamatan : ''), ', ')
        : '-';

    $baris = [
        'NIK'           => $ibu->nik ?? '-',
        'Nama'          => $ibu->user?->name ?? '-',
        'Tanggal Lahir' => $ibu->tanggal_lahir?->format('d/m/Y') ?? '-',
        'Nomor HP'      => $ibu->user?->phone ?? '-',
        'Wilayah'       => $wilayah ?: '-',
    ];
@endphp

<section class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
    <h2 class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-5 py-3 text-sm font-bold text-[#542D3D]">Data Ibu</h2>

    <dl class="px-5 py-2">
        @foreach ($baris as $label => $nilai)
            <div class="flex items-start justify-between gap-4 border-b border-[#F7E6ED] py-3 last:border-0">
                <dt class="text-sm font-semibold text-slate-700">{{ $label }}</dt>
                <dd class="text-right text-sm font-bold text-[#542D3D]">{{ $nilai }}</dd>
            </div>
        @endforeach
    </dl>
</section>