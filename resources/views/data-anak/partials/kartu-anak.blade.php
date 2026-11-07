{{-- Kartu ringkas data anak. Parameter: $anak, $judul (opsional) --}}
@php
    $baris = [
<<<<<<< HEAD
        'NIK'           => $anak->nik_anak ?? '-',
        'Nama'          => $anak->nama_anak ?? '-',
        'Tanggal Lahir' => $anak->tanggal_lahir?->format('d/m/Y') ?? '-',
        'Tempat Lahir'  => $anak->tempat_lahir ?? '-',
        'JK'            => $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
=======
        'NIK' => $anak->nik_anak ?? '-',
        'Nama' => $anak->nama_anak ?? '-',
        'Tanggal Lahir' => $anak->tgl_lahir?->format('d/m/Y') ?? '-',
        'Tempat Lahir' => $anak->tempat_lahir ?? '-',
        'JK' => $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
>>>>>>> c2c3578db03f43e9582063b4e1b16d459ea2148e
    ];
@endphp

<section class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
    <h2 class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-5 py-3 text-sm font-bold text-[#542D3D]">{{ $judul ?? 'Data Anak' }}</h2>

    <dl class="px-5 py-2">
        @foreach ($baris as $label => $nilai)
            <div class="flex items-start justify-between gap-4 border-b border-[#F7E6ED] py-3 last:border-0">
                <dt class="text-sm font-semibold text-slate-700">{{ $label }}</dt>
                <dd class="text-right text-sm font-bold text-[#542D3D]">{{ $nilai }}</dd>
            </div>
        @endforeach
    </dl>
</section>