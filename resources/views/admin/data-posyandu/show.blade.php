@extends('layouts.app')

@section('title', 'Detail Posyandu')

@section('content')
@php
    $base = $base ?? 'admin';

    $daftar    = $tersedia ? collect($pemeriksaan) : collect();
    $jumlah    = $daftar->count();
    $normal    = $daftar->where('status_kesehatan', 'normal')->count();
    $perhatian = $daftar->filter(fn ($p) => ! empty($p->status_kesehatan) && $p->status_kesehatan !== 'normal')->count();

    $kartu = [
        ['Anak Diperiksa', $tersedia ? $jumlah : '-', 'M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6', 'bg-[#FCE7F0] text-[#C9517E]', 'text-[#C9517E]'],
        ['Normal', $tersedia ? $normal : '-', 'm5 12 5 5 9-10', 'bg-green-50 text-green-600', 'text-green-600'],
        ['Perlu Perhatian', $tersedia ? $perhatian : '-', 'M6 9a6 6 0 1 1 12 0c0 5 2 6.5 2 6.5H4S6 14 6 9Zm4 9.5a2 2 0 0 0 4 0', 'bg-red-50 text-red-600', 'text-red-600'],
    ];

    $info = [
        'Tanggal' => $kegiatan->tanggal?->format('d/m/Y'),
        'Waktu'   => $kegiatan->waktu ? substr($kegiatan->waktu, 0, 5) . ' WIB' : null,
        'Lokasi'  => $kegiatan->lokasi,
        'Wilayah' => $kegiatan->wilayah?->nama_wilayah,
    ];
@endphp

<div class="mx-auto w-full max-w-5xl space-y-5">

    {{-- Judul --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Detail Posyandu</h1>
            <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Detail Posyandu</p>
        </div>

        <a href="{{ route($base . '.data-posyandu.index') }}"
           class="inline-flex justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-5 py-2.5 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/30">
            Kembali
        </a>
    </div>

    {{-- Informasi kegiatan --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
            <h2 class="text-base font-bold text-[#542D3D]">{{ $kegiatan->jenis_kegiatan ?? 'Kegiatan Posyandu' }}</h2>
        </div>

        <dl class="grid grid-cols-2 gap-4 p-5 lg:grid-cols-4">
            @foreach ($info as $label => $nilai)
                <div class="min-w-0">
                    <dt class="text-xs font-semibold text-slate-600">{{ $label }}</dt>
                    <dd class="mt-0.5 break-words text-sm font-bold text-[#542D3D]">{{ $nilai ?: '-' }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach ($kartu as [$label, $nilai, $ikon, $warnaIkon, $warnaAngka])
            <div class="flex items-center gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $warnaIkon }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#542D3D]">{{ $label }}</p>
                    <p class="mt-0.5 text-2xl font-bold leading-none {{ $warnaAngka }}">{{ $nilai }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Anak yang diperiksa --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:flex-row sm:items-center sm:justify-between sm:p-4">
            <h2 class="text-base font-bold text-[#542D3D]">Anak yang Diperiksa</h2>

            @if ($tersedia)
                <div class="relative sm:w-72">
                    <label for="cariAnak" class="sr-only">Cari nama anak</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="cariAnak" data-cari-anak type="search" autocomplete="off" placeholder="Cari nama anak"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2 pl-10 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>
            @endif
        </div>

        @if (! $tersedia)
            <p class="px-5 py-10 text-center text-sm font-medium text-slate-500">Data pemeriksaan belum tersedia.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[34rem] text-left">
                    <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3">No</th>
                            <th class="whitespace-nowrap px-4 py-3">Nama Anak</th>
                            <th class="whitespace-nowrap px-4 py-3">NIK</th>
                            <th class="whitespace-nowrap px-4 py-3">Berat Badan</th>
                            <th class="whitespace-nowrap px-4 py-3">Tinggi Badan</th>
                            <th class="whitespace-nowrap px-4 py-3">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#F7E6ED]">
                        @forelse ($pemeriksaan as $p)
                            @php
                                $st    = (string) ($p->status_kesehatan ?? '');
                                $label = $st === '' ? '-' : ($st === 'normal' ? 'Normal' : ucwords(str_replace('_', ' ', $st)));
                                $warna = $st === '' ? 'bg-slate-100 text-slate-600' : ($st === 'normal' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700');
                            @endphp
                            <tr data-baris-anak data-nama="{{ mb_strtolower((string) $p->nama_anak) }}" class="transition hover:bg-[#FCE7F0]/50">
                                <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3.5 text-sm font-bold text-[#542D3D]">{{ $p->nama_anak }}</td>
                                <td class="whitespace-nowrap px-4 py-3.5 text-sm text-slate-800">{{ $p->nik_anak }}</td>
                                <td class="px-4 py-3.5 text-sm text-slate-800">{{ $p->berat_badan ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-sm text-slate-800">{{ $p->tinggi_badan ?? '-' }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold {{ $warna }}">{{ $label }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <p class="font-bold text-[#542D3D]">Belum ada anak yang diperiksa pada kegiatan ini.</p>
                                </td>
                            </tr>
                        @endforelse

                        {{-- Muncul saat pencarian nama tidak menemukan hasil --}}
                        <tr id="anakKosong" class="hidden">
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">Data tidak ditemukan.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Cari nama anak: tampilkan nama yang diawali huruf yang diketik, kosongkan untuk kembali ke semua
document.querySelector('[data-cari-anak]')?.addEventListener('input', function () {
    const kata = this.value.trim().toLowerCase();
    let tampil = 0;

    document.querySelectorAll('[data-baris-anak]').forEach(function (tr) {
        const cocok = kata === '' || (tr.dataset.nama || '').startsWith(kata);
        tr.classList.toggle('hidden', ! cocok);
        if (cocok) tampil++;
    });

    document.getElementById('anakKosong')?.classList.toggle('hidden', tampil > 0 || kata === '');
});
</script>
@endpush