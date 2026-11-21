@php
    $baris = [
        'NIK'           => $anak->nik_anak ?? '-',
        'Nama'          => $anak->nama_anak ?? '-',
        'Tanggal Lahir' => $anak->tgl_lahir?->format('d/m/Y') ?? '-',
        'Tempat Lahir'  => $anak->tempat_lahir ?? '-',
        'JK'            => $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
    ];

    $routeDetail = auth()->user()->isOrangTua()
        ? 'ibu.data-anak.show'
        : 'data-anak.show';
@endphp

<section class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-[#E99AB7] hover:shadow-md motion-reduce:transform-none motion-reduce:transition-none sm:p-6">
    <div class="flex items-center gap-3">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FCE7F0] text-[#C9517E]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6"/></svg>
        </span>
        <h2 class="text-base font-bold text-[#542D3D]">{{ $judul ?? 'Data Anak' }}</h2>
    </div>

    <dl class="mt-2">
        @foreach ($baris as $label => $nilai)
            <div class="flex items-start justify-between gap-4 border-b border-[#F7E6ED] py-3 last:border-0">
                <dt class="text-sm font-semibold text-slate-600">{{ $label }}</dt>
                <dd class="text-right text-sm font-bold text-[#542D3D]">{{ $nilai }}</dd>
            </div>
        @endforeach
    </dl>

    <div class="mt-4 flex justify-end">
        <a href="{{ route($routeDetail, $anak) }}"
           class="inline-flex items-center justify-center rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#AD416B] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.98]">
            Detail Anak
        </a>
    </div>
</section>