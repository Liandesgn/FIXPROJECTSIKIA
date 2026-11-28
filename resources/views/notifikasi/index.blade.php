@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
@php
    if (method_exists($notifikasi, 'appends')) {
        $notifikasi->appends(request()->query());
    }

    // Lencana jenis notifikasi (pengingat_h3, pengingat_hari_h, pesan_konsultasi, risiko_tinggi)
    $jenisNotifikasi = [
        'pengingat_h3'     => ['Pengingat H-3',     'bg-slate-100 text-slate-700'],
        'pengingat_hari_h' => ['Pengingat Hari H',  'bg-amber-100 text-amber-700'],
        'pesan_konsultasi' => ['Pesan Konsultasi',  'bg-pink-100 text-[#C9517E]'],
        'risiko_tinggi'    => ['Risiko Tinggi',     'bg-red-100 text-red-700'],
    ];
@endphp

<div class="mx-auto w-full max-w-5xl space-y-5">

    {{-- Judul + tombol tandai semua --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Notifikasi</h1>
            <p class="mt-1 text-sm font-medium text-slate-700">Informasi dan pemberitahuan terbaru.</p>
        </div>

        @if ($jumlahBelumDibaca > 0)
            <form action="{{ route('notifikasi.read-all') }}" method="POST" class="m-0">
                @csrf
                @method('PATCH')

                <button type="submit"
                        class="inline-flex justify-center rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                    Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    {{-- Jumlah belum dibaca --}}
    <div class="inline-flex items-center gap-3 rounded-xl border border-[#F1D5E0] bg-white px-4 py-2.5 shadow-sm">
        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#FCE7F0] text-[#C9517E]">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9a6 6 0 1 1 12 0c0 5 2 6.5 2 6.5H4S6 14 6 9Zm4 9.5a2 2 0 0 0 4 0"/>
            </svg>
        </span>
        <div class="leading-tight">
            <p class="text-xs font-semibold text-slate-700">Belum dibaca</p>
            <p class="text-xl font-bold text-[#C9517E]">{{ $jumlahBelumDibaca }}</p>
        </div>
    </div>

    {{-- Daftar notifikasi --}}
    <div class="space-y-4">
        @forelse ($notifikasi as $item)
            @php
                $data     = $item->data ?? [];
                $kategori = $data['kategori_jadwal'] ?? null;

                $tanggal = !empty($data['tanggal'])
                    ? \Carbon\Carbon::parse($data['tanggal'])->translatedFormat('d F Y')
                    : '-';

                $waktu = !empty($data['waktu'])
                    ? \Carbon\Carbon::parse($data['waktu'])->format('H:i')
                    : '-';

                if ($kategori === 'pemeriksaan') {
                    $detail = [
                        'Tanggal' => $tanggal,
                        'Waktu' => $waktu,
                        'Lokasi' => $data['lokasi'] ?? '-',
                        'Bidan Pemeriksa' => $data['bidan'] ?? '-',
                        'Catatan' => $data['catatan'] ?? '-',
                    ];

                    $namaJadwal = $data['jenis_pemeriksaan']
                        ?? 'Pemeriksaan Ibu Hamil';

                } elseif ($kategori === 'posyandu') {
                    $detail = [
                        'Tanggal' => $tanggal,
                        'Waktu' => $waktu,
                        'Lokasi' => $data['lokasi'] ?? '-',
                        'Kader' => $data['kader'] ?? '-',
                        'Bidan Pemeriksa' => $data['bidan'] ?? '-',
                    ];

                    $namaJadwal = $data['jenis_kegiatan']
                        ?? 'Kegiatan Posyandu';

                } else {
                    $detail = [];
                    $namaJadwal = null;
                }

                // Jenis notifikasi: dicoba dari beberapa kemungkinan kunci data
                $kunciJenis = $data['jenis'] ?? $data['tipe'] ?? $data['jenis_notifikasi'] ?? null;
                $lencana    = $jenisNotifikasi[$kunciJenis] ?? null;
            @endphp

            <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm
                {{ $item->read_at ? '' : 'border-l-4 border-l-[#C9517E]' }}">

                <div class="flex flex-wrap items-start justify-between gap-3 p-5">
                    <div class="min-w-0 flex-1">

                        {{-- Judul --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-bold text-[#542D3D]">
                                {{ $data['judul'] ?? 'Notifikasi SI-KIA' }}
                            </h2>

                            @if (!$item->read_at)
                                <span class="rounded-full bg-[#C9517E] px-2.5 py-0.5 text-xs font-bold text-white">
                                    Baru
                                </span>
                            @endif

                            @if ($lencana)
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $lencana[1] }}">
                                    {{ $lencana[0] }}
                                </span>
                            @endif
                        </div>

                        {{-- Detail jadwal --}}
                        @if ($kategori && $namaJadwal)
                            <div class="mt-4 overflow-hidden rounded-xl border border-[#F1D5E0] bg-white">

                                <h3 class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-4 py-3 text-sm font-bold text-[#542D3D]">
                                    {{ $namaJadwal }}
                                </h3>

                                <dl class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2">
                                    @foreach ($detail as $label => $value)
                                        <div class="min-w-0 {{ $label === 'Catatan' ? 'sm:col-span-2' : '' }}">
                                            <dt class="text-xs font-semibold text-slate-600">{{ $label }}</dt>
                                            <dd class="mt-1 break-words text-sm font-bold text-[#542D3D]">
                                                {{ $value ?: '-' }}
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        @else
                            {{-- Notifikasi lama / notifikasi umum --}}
                            <p class="mt-2 text-sm font-medium leading-relaxed text-slate-700">
                                {{ $data['isi'] ?? 'Ada informasi baru untuk Anda.' }}
                            </p>
                        @endif

                        {{-- Waktu notifikasi --}}
                        <p class="mt-3 text-xs font-medium text-slate-500">
                            {{ $item->created_at?->translatedFormat('d F Y, H:i') }}
                        </p>
                    </div>

                    {{-- Status dibaca --}}
                    @if (!$item->read_at)
                        <form action="{{ route('notifikasi.read', $item->id) }}" method="POST" class="m-0">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg border-2 border-[#E99AB7] bg-white px-3 text-xs font-bold text-[#C9517E] transition hover:bg-[#FCE7F0] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/30">
                                Tandai dibaca
                            </button>
                        </form>
                    @else
                        <span class="text-xs font-semibold text-slate-500">Sudah dibaca</span>
                    @endif
                </div>
            </div>

        @empty
            <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white px-4 py-12 text-center shadow-sm">
                <svg class="mx-auto mb-3 h-10 w-10 text-[#E99AB7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9a6 6 0 1 1 12 0c0 5 2 6.5 2 6.5H4S6 14 6 9Zm4 9.5a2 2 0 0 0 4 0"/></svg>
                <p class="font-bold text-[#542D3D]">Belum ada notifikasi</p>
                <p class="mt-1 text-sm font-medium text-slate-600">Notifikasi baru akan muncul di halaman ini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($notifikasi->hasPages())
        @php
            $pg     = $notifikasi;
            $awal   = max($pg->currentPage() - 1, 1);
            $akhir  = min($pg->currentPage() + 1, $pg->lastPage());
            $btn    = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-[#F1D5E0] bg-white px-2 text-sm font-semibold text-[#542D3D] transition hover:bg-[#FCE7F0]';
            $btnOff = 'inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-[#F1D5E0] bg-white text-lg text-slate-300';
        @endphp

        <nav class="flex flex-col items-center justify-between gap-3 sm:flex-row" aria-label="Navigasi halaman">
            <p class="text-xs font-medium text-slate-600">
                Menampilkan {{ $pg->firstItem() ?? 0 }}–{{ $pg->lastItem() ?? 0 }} dari {{ $pg->total() }} notifikasi
            </p>

            <div class="inline-flex items-center gap-1">
                @if ($pg->onFirstPage())
                    <span class="{{ $btnOff }}" aria-disabled="true">‹</span>
                @else
                    <a href="{{ $pg->previousPageUrl() }}" aria-label="Halaman sebelumnya" class="{{ $btn }} text-lg text-[#C9517E]">‹</a>
                @endif

                @if ($awal > 1)
                    <a href="{{ $pg->url(1) }}" class="{{ $btn }}">1</a>
                    @if ($awal > 2) <span class="px-1 text-slate-400">…</span> @endif
                @endif

                @for ($i = $awal; $i <= $akhir; $i++)
                    @if ($i === $pg->currentPage())
                        <span aria-current="page" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-[#C9517E] px-2 text-sm font-bold text-white shadow-sm">{{ $i }}</span>
                    @else
                        <a href="{{ $pg->url($i) }}" class="{{ $btn }}">{{ $i }}</a>
                    @endif
                @endfor

                @if ($akhir < $pg->lastPage())
                    @if ($akhir < $pg->lastPage() - 1) <span class="px-1 text-slate-400">…</span> @endif
                    <a href="{{ $pg->url($pg->lastPage()) }}" class="{{ $btn }}">{{ $pg->lastPage() }}</a>
                @endif

                @if ($pg->hasMorePages())
                    <a href="{{ $pg->nextPageUrl() }}" aria-label="Halaman berikutnya" class="{{ $btn }} text-lg text-[#C9517E]">›</a>
                @else
                    <span class="{{ $btnOff }}" aria-disabled="true">›</span>
                @endif
            </div>
        </nav>
    @endif

</div>
@endsection