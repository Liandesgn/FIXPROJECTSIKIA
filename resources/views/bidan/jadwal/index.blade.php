@extends('layouts.app')

@section('title', 'Jadwal')

@section('content')
@php
    $kartu     = 'overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm';
    $band      = 'rounded-2xl border border-[#F1D5E0] bg-[#FCE7F0] p-3 sm:p-4';
    $cariClass = 'w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $pilihClass = 'w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $ikonCari  = '<svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>';
    $ikonKosong = '<svg class="mx-auto mb-3 h-10 w-10 text-[#E99AB7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/></svg>';

    $daftarBulan = collect(range(1, 12))->mapWithKeys(fn ($b) => [
        sprintf('%02d', $b) => \Carbon\Carbon::create(2000, $b, 1)->translatedFormat('F'),
    ]);
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Jadwal</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">
            Informasi jadwal pemeriksaan ibu hamil dan kegiatan Posyandu di wilayah kerja Anda.
        </p>
    </div>

    {{-- Tab --}}
    <div class="flex w-full gap-2 rounded-2xl border border-[#F1D5E0] bg-white p-1.5 shadow-sm sm:w-fit" role="tablist" aria-label="Jenis jadwal">
        <button type="button" id="tab-pemeriksaan" role="tab" onclick="gantiTab('pemeriksaan')"
                class="flex-1 whitespace-nowrap rounded-xl bg-[#C9517E] px-4 py-2 text-sm font-semibold text-white shadow-sm transition sm:flex-none">
            Pemeriksaan Ibu Hamil
        </button>

        <button type="button" id="tab-posyandu" role="tab" onclick="gantiTab('posyandu')"
                class="flex-1 whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold text-[#542D3D] transition hover:bg-[#FCE7F0] sm:flex-none">
            Posyandu
        </button>
    </div>

    {{-- ================= TAB PEMERIKSAAN IBU HAMIL ================= --}}
    <div id="konten-pemeriksaan" class="space-y-4">

        <div class="{{ $band }}">
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <label for="cari-pemeriksaan" class="sr-only">Cari pemeriksaan</label>
                    {!! $ikonCari !!}
                    <input type="text" id="cari-pemeriksaan" oninput="filterJadwal('pemeriksaan')"
                           autocomplete="off" placeholder="Cari Pemeriksaan" class="{{ $cariClass }}">
                </div>

                <div class="sm:w-56">
                    <label for="bulan-pemeriksaan" class="sr-only">Filter bulan</label>
                    <select id="bulan-pemeriksaan" onchange="filterJadwal('pemeriksaan')" class="{{ $pilihClass }}">
                        <option value="">Semua Bulan</option>
                        @foreach ($daftarBulan as $kode => $nama)
                            <option value="{{ $kode }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div id="daftar-pemeriksaan" class="space-y-4">
            @forelse ($jadwalPemeriksaan as $jadwal)
                @php
                    $detail    = $jadwal->jadwalPemeriksaan;
                    $namaBidan = $detail?->bidan?->user?->name ?? '-';
                    $tanggal   = $jadwal->tanggal;

                    $baris = [
                        'Tanggal'         => $tanggal ? $tanggal->translatedFormat('d F Y') : '-',
                        'Waktu'           => $jadwal->waktu ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i') : '-',
                        'Lokasi'          => $jadwal->lokasi ?? '-',
                        'Bidan Pemeriksa' => $namaBidan,
                        'Catatan'         => $detail?->catatan ?: '-',
                    ];
                @endphp

                <div class="item-jadwal-pemeriksaan {{ $kartu }}"
                     data-cari="{{ strtolower(($jadwal->jenis_kegiatan ?? '') . ' ' . ($jadwal->lokasi ?? '') . ' ' . $namaBidan) }}"
                     data-bulan="{{ $tanggal ? $tanggal->format('m') : '' }}">

                    <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
                        <h3 class="text-base font-bold text-[#542D3D]">
                            {{ $jadwal->jenis_kegiatan ?? 'Pemeriksaan Ibu Hamil' }}
                        </h3>
                    </div>

                    <dl class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                        @foreach ($baris as $label => $nilai)
                            <div class="min-w-0 {{ $label === 'Catatan' ? 'sm:col-span-2' : '' }}">
                                <dt class="text-xs font-semibold text-slate-600">{{ $label }}</dt>
                                <dd class="mt-1 break-words text-sm font-bold text-[#542D3D]">{{ $nilai }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @empty
                <div class="{{ $kartu }} px-4 py-12 text-center">
                    {!! $ikonKosong !!}
                    <p class="font-bold text-[#542D3D]">Belum ada jadwal pemeriksaan ibu hamil.</p>
                </div>
            @endforelse
        </div>

        <p id="kosong-pemeriksaan" class="{{ $kartu }} hidden px-4 py-12 text-center font-bold text-[#542D3D]">
            Jadwal yang dicari tidak ditemukan.
        </p>
    </div>

    {{-- ================= TAB POSYANDU ================= --}}
    <div id="konten-posyandu" class="hidden space-y-4">

        <div class="{{ $band }}">
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <label for="cari-posyandu" class="sr-only">Cari Posyandu</label>
                    {!! $ikonCari !!}
                    <input type="text" id="cari-posyandu" oninput="filterJadwal('posyandu')"
                           autocomplete="off" placeholder="Cari Posyandu" class="{{ $cariClass }}">
                </div>

                <div class="sm:w-40">
                    <label for="rw-rt-posyandu" class="sr-only">Filter RW/RT</label>
                    <select id="rw-rt-posyandu" onchange="filterJadwal('posyandu')" class="{{ $pilihClass }}">
                        <option value="">RW/RT</option>
                        @foreach ($daftarRw ?? collect() as $rw)
                            <option value="{{ $rw->nomor_rw }}">RW {{ $rw->nomor_rw }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:w-56">
                    <label for="bulan-posyandu" class="sr-only">Filter bulan</label>
                    <select id="bulan-posyandu" onchange="filterJadwal('posyandu')" class="{{ $pilihClass }}">
                        <option value="">Semua Bulan</option>
                        @foreach ($daftarBulan as $kode => $nama)
                            <option value="{{ $kode }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div id="daftar-posyandu" class="space-y-4">
            @forelse ($jadwalPosyandu as $jadwal)
                @php
                    $detail    = $jadwal->jadwalPosyandu;
                    $kegiatan  = $detail?->kegiatan;
                    $rw        = $kegiatan?->rw;
                    $namaKader = $detail?->kader?->user?->name ?? '-';
                    $namaBidan = $detail?->bidan?->user?->name ?? '-';
                    $tanggal   = $jadwal->tanggal;

                    $baris = [
                        'Tanggal'         => $tanggal ? $tanggal->translatedFormat('d F Y') : '-',
                        'Waktu'           => $jadwal->waktu ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i') : '-',
                        'Lokasi'          => $jadwal->lokasi ?? '-',
                        'Kader'           => $namaKader,
                        'Bidan Pemeriksa' => $namaBidan,
                    ];
                @endphp

                <div class="item-jadwal-posyandu {{ $kartu }}"
                     data-cari="{{ strtolower(($jadwal->jenis_kegiatan ?? '') . ' ' . ($jadwal->lokasi ?? '') . ' ' . $namaKader . ' ' . $namaBidan) }}"
                     data-rw="{{ $rw?->nomor_rw ?? '' }}"
                     data-bulan="{{ $tanggal ? $tanggal->format('m') : '' }}">

                    <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-5 py-3.5">
                        <h3 class="text-base font-bold text-[#542D3D]">
                            {{ $jadwal->jenis_kegiatan ?? 'Kegiatan Posyandu' }}
                        </h3>
                    </div>

                    <dl class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                        @foreach ($baris as $label => $nilai)
                            <div class="min-w-0">
                                <dt class="text-xs font-semibold text-slate-600">{{ $label }}</dt>
                                <dd class="mt-1 break-words text-sm font-bold text-[#542D3D]">{{ $nilai }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @empty
                <div class="{{ $kartu }} px-4 py-12 text-center">
                    {!! $ikonKosong !!}
                    <p class="font-bold text-[#542D3D]">Belum ada jadwal Posyandu mendatang.</p>
                </div>
            @endforelse
        </div>

        <p id="kosong-posyandu" class="{{ $kartu }} hidden px-4 py-12 text-center font-bold text-[#542D3D]">
            Jadwal yang dicari tidak ditemukan.
        </p>
    </div>

</div>

<script>
    const kelasTabAktif = ['bg-[#C9517E]', 'text-white', 'shadow-sm'];
    const kelasTabNonaktif = ['text-[#542D3D]', 'hover:bg-[#FCE7F0]'];

    function aturTabJadwal(tombol, aktif) {
        kelasTabAktif.forEach(function (c) { tombol.classList.toggle(c, aktif); });
        kelasTabNonaktif.forEach(function (c) { tombol.classList.toggle(c, !aktif); });
        tombol.setAttribute('aria-selected', String(aktif));
    }

    function gantiTab(tab) {
        document.getElementById('konten-pemeriksaan').classList.toggle('hidden', tab !== 'pemeriksaan');
        document.getElementById('konten-posyandu').classList.toggle('hidden', tab !== 'posyandu');

        aturTabJadwal(document.getElementById('tab-pemeriksaan'), tab === 'pemeriksaan');
        aturTabJadwal(document.getElementById('tab-posyandu'), tab === 'posyandu');
    }

    function filterJadwal(jenis) {
        const cari = document.getElementById('cari-' + jenis).value.toLowerCase();
        const bulan = document.getElementById('bulan-' + jenis).value;

        let rw = '';

        if (jenis === 'posyandu') {
            rw = document.getElementById('rw-rt-posyandu').value;
        }

        const items = document.querySelectorAll('.item-jadwal-' + jenis);
        let jumlahTampil = 0;

        items.forEach(item => {
            const cocokCari = item.dataset.cari.includes(cari);
            const cocokBulan = !bulan || item.dataset.bulan === bulan;
            const cocokRw = !rw || item.dataset.rw === rw;

            const tampil = cocokCari && cocokBulan && cocokRw;

            item.classList.toggle('hidden', !tampil);

            if (tampil) {
                jumlahTampil++;
            }
        });

        document.getElementById('kosong-' + jenis)
            .classList.toggle('hidden', jumlahTampil > 0);
    }

    gantiTab('pemeriksaan');
</script>
@endsection