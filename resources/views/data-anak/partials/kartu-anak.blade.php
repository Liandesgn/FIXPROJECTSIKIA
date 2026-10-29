@php
    $baris = [
        'NIK' => $anak->nik_anak ?? '-',
        'Nama' => $anak->nama_anak ?? '-',
        'Tanggal Lahir' => $anak->tanggal_lahir?->format('d/m/Y') ?? '-',
        'Tempat Lahir' => $anak->tempat_lahir ?? '-',
        'JK' => $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
    ];
@endphp

<section class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm sm:p-6">
    <h2 class="text-base font-bold text-[#542D3D]">{{ $judul ?? 'Data Anak' }}</h2>

    <dl class="mt-2">
        @foreach ($baris as $label => $nilai)
            <div class="flex items-start justify-between gap-4 border-b border-[#F7E6ED] py-3 last:border-0">
                <dt class="text-sm font-semibold text-slate-600">{{ $label }}</dt>
                <dd class="text-right text-sm font-bold text-[#542D3D]">{{ $nilai }}</dd>
            </div>
        @endforeach
    </dl>
</section>