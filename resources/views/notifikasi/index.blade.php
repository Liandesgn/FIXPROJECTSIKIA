
@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="min-h-screen bg-[#FFF8FA] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">

        {{-- Header --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-[#542D3D]">
                    Notifikasi
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Informasi dan pemberitahuan terbaru.
                </p>
            </div>

            @if ($jumlahBelumDibaca > 0)
                <form action="{{ route('notifikasi.read-all') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                        class="rounded-xl bg-[#C9517E] px-4 py-2 text-sm font-semibold text-white hover:bg-[#ad416b]">
                        Tandai semua dibaca
                    </button>
                </form>
            @endif
        </div>

        {{-- Jumlah Belum Dibaca --}}
        <div class="mb-4 rounded-xl bg-white p-4 shadow-sm">
            <p class="text-sm text-gray-600">
                Belum dibaca:
                <span class="font-bold text-[#C9517E]">
                    {{ $jumlahBelumDibaca }}
                </span>
            </p>
        </div>

        {{-- Daftar Notifikasi --}}
        <div class="space-y-4">
            @forelse ($notifikasi as $item)
                @php
                    $data = $item->data ?? [];
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
                @endphp

                <div class="rounded-xl border border-pink-100 bg-white p-5 shadow-sm
                    {{ $item->read_at ? '' : 'border-l-4 border-l-[#C9517E]' }}">

                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">

                            {{-- Judul --}}
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-semibold text-[#542D3D]">
                                    {{ $data['judul'] ?? 'Notifikasi SI-KIA' }}
                                </h2>

                                @if (!$item->read_at)
                                    <span class="rounded-full bg-pink-100 px-2 py-1 text-xs font-semibold text-[#C9517E]">
                                        Baru
                                    </span>
                                @endif
                            </div>

                            {{-- Detail Jadwal --}}
                            @if ($kategori && $namaJadwal)
                                <div class="mt-4 rounded-xl border border-pink-100 bg-white p-4">

                                    <h3 class="mb-4 border-b border-pink-100 pb-3 font-bold text-[#542D3D]">
                                        {{ $namaJadwal }}
                                    </h3>

                                    <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">

                                        @foreach ($detail as $label => $value)
                                            <div class="{{ $label === 'Catatan' ? 'sm:col-span-2' : '' }}">
                                                <p class="mb-1 text-gray-500">
                                                    {{ $label }}
                                                </p>

                                                <p class="font-semibold text-gray-800">
                                                    {{ $value ?: '-' }}
                                                </p>
                                            </div>
                                        @endforeach

                                    </div>
                                </div>
                            @else
                                {{-- Notifikasi lama / notifikasi umum --}}
                                <p class="mt-2 text-sm leading-relaxed text-gray-600">
                                    {{ $data['isi'] ?? 'Ada informasi baru untuk Anda.' }}
                                </p>
                            @endif

                            {{-- Waktu Notifikasi --}}
                            <p class="mt-3 text-xs text-gray-400">
                                {{ $item->created_at?->translatedFormat('d F Y, H:i') }}
                            </p>
                        </div>

                        {{-- Status Dibaca --}}
                        @if (!$item->read_at)
                            <form action="{{ route('notifikasi.read', $item->id) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                    class="text-xs font-semibold text-[#C9517E] hover:underline">
                                    Tandai dibaca
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">
                                Sudah dibaca
                            </span>
                        @endif
                    </div>
                </div>

            @empty
                <div class="rounded-xl bg-white px-4 py-12 text-center shadow-sm">
                    <p class="font-semibold text-[#542D3D]">
                        Belum ada notifikasi
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        Notifikasi baru akan muncul di halaman ini.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-5">
            {{ $notifikasi->links() }}
        </div>

    </div>
</div>
@endsection
