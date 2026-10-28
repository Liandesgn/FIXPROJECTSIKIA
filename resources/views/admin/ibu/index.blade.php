@extends('layouts.app')

@section('title', 'Kelola Ibu')

@section('content')
@php
    // Bawa filter aktif ke halaman berikutnya (untuk virtual scrolling)
    if (method_exists($ibus, 'appends')) {
        $ibus->appends(request()->query());
    }
    $total   = method_exists($ibus, 'total') ? $ibus->total() : $ibus->count();
    $nextUrl = method_exists($ibus, 'nextPageUrl') ? ($ibus->nextPageUrl() ?? '') : '';

    // Jika controller sudah mengirim pesan flash, popup hasil dari layout yang dipakai
    $hasFlash = session()->has('success') || session()->has('status') || session()->has('message') || session()->has('error');
@endphp

<div class="mx-auto w-full max-w-[96rem] space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Ibu</h1>
            <p class="mt-1 text-sm font-medium text-slate-700">Manajemen User / Kelola Ibu</p>
        </div>

        @can('user.create')
        <a href="{{ route('admin.ibu.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
            <span class="text-lg leading-none">+</span> Registrasi Ibu
        </a>
        @endcan
    </div>

    {{-- Pesan berhasil / gagal ditampilkan sebagai popup SweetAlert --}}

    {{-- Ringkasan (ringkas) --}}
    <div class="inline-flex items-center gap-3 rounded-xl border border-[#F1D5E0] bg-white px-4 py-2.5 shadow-sm">
        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#FCE7F0] text-[#C9517E]">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11a3 3 0 1 0 0-6m-8 6a3 3 0 1 0 0-6m-6 13a6 6 0 0 1 12 0m2-5a6 6 0 0 1 6 5"/>
            </svg>
        </span>
        <div class="leading-tight">
            <p class="text-xs font-semibold text-slate-700">Total data ibu</p>
            <p class="text-xl font-bold text-[#C9517E]">{{ $total }}</p>
        </div>
    </div>

    {{-- Daftar --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Bar cari + filter (dikirim ke controller seperti sebelumnya) --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <p class="mb-2.5 text-sm font-semibold text-[#542D3D]">Temukan ibu lewat nama, wilayah, atau status akun</p>

            <form method="GET" action="{{ route('admin.ibu.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="relative md:col-span-5">
                    <label for="searchIbu" class="sr-only">Cari nama ibu</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="searchIbu" type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                           placeholder="Cari nama ibu"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>

                <div class="md:col-span-3">
                    <label for="filterWilayah" class="sr-only">Filter wilayah</label>
                    <select id="filterWilayah" name="wilayah_kerja_id" onchange="this.form.submit()"
                            class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                        <option value="">Semua wilayah</option>
                        @foreach ($wilayahs->groupBy('kecamatan') as $kecamatan => $desas)
                            <optgroup label="Kec. {{ $kecamatan ?: '-' }}">
                                @foreach ($desas as $wilayah)
                                    <option value="{{ $wilayah->id }}" @selected((string) request('wilayah_kerja_id') === (string) $wilayah->id)>
                                        Desa {{ $wilayah->desa }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label for="filterStatus" class="sr-only">Filter status akun</label>
                    <select id="filterStatus" name="status" onchange="this.form.submit()"
                            class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                        <option value="">Semua status akun</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-1">
                    <button type="submit"
                            class="w-full rounded-xl bg-[#C9517E] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabel (virtual scrolling) --}}
        <div id="listWrap" data-next="{{ $nextUrl }}">
            <div id="listScroll" class="max-h-[28rem] overflow-y-auto overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="sticky top-0 z-10 bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                        <tr>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">No</th>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">Nama Ibu</th>
                            <th class="hidden whitespace-nowrap bg-[#FCE7F0] px-4 py-3 2xl:table-cell">Kontak</th>
                            <th class="hidden whitespace-nowrap bg-[#FCE7F0] px-4 py-3 lg:table-cell">Wilayah</th>
                            <th class="hidden whitespace-nowrap bg-[#FCE7F0] px-6 py-3 lg:table-cell">Status Akun</th>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 text-center lg:px-4">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="listBody" class="divide-y divide-[#F7E6ED]">
                        @forelse ($ibus as $ibu)
                            @php
                                $isActive = $ibu->status === \App\Enums\UserStatusEnum::Active;
                                $desa     = $ibu->ibu?->wilayahKerja?->desa;
                                $kec      = $ibu->ibu?->wilayahKerja?->kecamatan;
                            @endphp
                            <tr class="row-item transition hover:bg-[#FCE7F0]/50">
                                <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 lg:px-4"><span class="row-no">{{ $loop->iteration }}</span></td>

                                {{-- Nama + NIK (+ kontak, wilayah, status ditumpuk hanya di layar kecil) --}}
                                <td class="px-3 py-3.5 lg:px-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#C9517E] text-sm font-bold text-white">
                                            {{ strtoupper(substr($ibu->name ?? 'I', 0, 1)) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="break-words text-sm font-bold text-[#542D3D] lg:whitespace-nowrap">{{ $ibu->name ?? '-' }}</p>
                                            <p class="whitespace-nowrap text-xs font-medium text-slate-600">NIK {{ $ibu->ibu?->nik ?? '-' }}</p>

                                            <p class="mt-0.5 break-all text-xs text-slate-700 lg:break-normal lg:whitespace-nowrap 2xl:hidden">{{ $ibu->email ?? '-' }}</p>
                                            <p class="text-xs text-slate-700 2xl:hidden">{{ $ibu->phone ?? '-' }}</p>
                                            <p class="text-xs font-semibold text-slate-800 lg:hidden">{{ $desa ? 'Desa ' . $desa : '-' }}{{ $kec ? ', Kec. ' . $kec : '' }}</p>
                                            <div class="mt-1.5 lg:hidden">@include('admin.partials.status', ['status' => $ibu->status])</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kontak: email di atas, nomor HP di bawah --}}
                                <td class="hidden whitespace-nowrap px-4 py-3.5 2xl:table-cell">
                                    <p class="text-sm text-slate-800">{{ $ibu->email ?? '-' }}</p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-600">{{ $ibu->phone ?? '-' }}</p>
                                </td>

                                {{-- Wilayah --}}
                                <td class="hidden whitespace-nowrap px-4 py-3.5 lg:table-cell">
                                    <p class="text-sm font-semibold text-slate-800">{{ $desa ?? '-' }}</p>
                                    @if ($kec) <p class="mt-0.5 text-xs font-medium text-slate-600">Kec. {{ $kec }}</p> @endif
                                </td>

                                {{-- Status Akun (kolom tersendiri) --}}
                                <td class="hidden whitespace-nowrap px-6 py-3.5 lg:table-cell">
                                    @include('admin.partials.status', ['status' => $ibu->status])
                                </td>

                                {{-- Aksi --}}
                                <td class="whitespace-nowrap px-3 py-3.5 lg:px-4">
                                    <div class="flex flex-nowrap items-center justify-center gap-2">
                                

                                        {{-- Tidak ada tombol Hapus: akun ibu hanya bisa diaktifkan / dinonaktifkan.
                                             NONAKTIFKAN: amber solid | AKTIFKAN: hijau solid.
                                             Konfirmasi + popup hasil memakai SweetAlert (script di bawah) --}}
                                        @can('user.activate')
                                        <form method="POST" action="{{ route('admin.ibu.toggle-status', $ibu) }}" class="m-0"
                                              data-ibu-toggle
                                              data-active="{{ $isActive ? '1' : '0' }}"
                                              data-name="{{ $ibu->name }}">
                                            @csrf
                                            @method('PATCH')
                                            @if ($isActive)
                                                <button type="submit"
                                                        class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-amber-500 px-3 text-xs font-bold text-amber-950 shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-4 focus:ring-amber-200 sm:px-3.5">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5.6 5.6l12.8 12.8M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/></svg>
                                                    Aktifkan
                                                </button>
                                            @else
                                                <button type="submit"
                                                        class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-emerald-700 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-200 sm:px-3.5">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5 9-10"/></svg>
                                                    Nonaktifkan
                                                </button>
                                            @endif
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <p class="font-bold text-[#542D3D]">Belum ada data ibu.</p>
                                    <p class="mt-1 text-sm text-slate-700">Tambahkan data ibu melalui tombol Registrasi Ibu.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[#F1D5E0] bg-white px-4 py-3">
                <span id="listInfo" class="text-xs font-semibold text-slate-700"></span>
                <span class="text-xs text-slate-600">Gulir ke bawah untuk melihat data lainnya</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.getElementById('listBody');
    const wrap = document.getElementById('listWrap');
    const scroller = document.getElementById('listScroll');
    const info = document.getElementById('listInfo');

    const CHUNK = 20;
    let limit = CHUNK;
    let next = wrap.dataset.next || '';
    let loading = false;

    const rows = () => Array.from(body.querySelectorAll('tr.row-item'));

    // Virtual scrolling: tampilkan 20 baris pertama, tambah 20 setiap kali mendekati dasar daftar
    function apply() {
        const all = rows();
        let shown = 0;
        all.forEach((r, i) => {
            const show = i < limit;
            r.classList.toggle('hidden', !show);
            if (show) {
                shown++;
                const no = r.querySelector('.row-no');
                if (no) no.textContent = i + 1;
            }
        });
        info.textContent = all.length
            ? 'Menampilkan ' + shown + ' dari ' + all.length + ' data' + (loading ? ' · memuat data...' : '')
            : '';
    }

    // Muat sisa halaman di latar belakang (filter yang aktif ikut terbawa)
    async function loadAll() {
        if (loading) return;
        loading = true; apply();
        try {
            while (next) {
                const res = await fetch(next, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                if (!res.ok) break;
                const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                doc.querySelectorAll('tr.row-item').forEach(r => body.appendChild(document.importNode(r, true)));
                const w = doc.getElementById('listWrap');
                next = (w && w.dataset.next) || '';
            }
        } catch (e) { /* abaikan: data yang sudah termuat tetap tampil */ }
        loading = false;
        apply();
    }

    scroller.addEventListener('scroll', function () {
        const nearBottom = scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 80;
        if (nearBottom && limit < rows().length) { limit += CHUNK; apply(); }
    });

    apply();
    if (next) loadAll();

    // ===== SweetAlert: popup hasil setelah Aktifkan / Nonaktifkan =====
    const serverHasFlash = @json($hasFlash);
    try {
        const raw = sessionStorage.getItem('sikia_ibu_toggle');
        if (raw) {
            sessionStorage.removeItem('sikia_ibu_toggle');
            if (!serverHasFlash && window.SikiaSwal) {
                const d = JSON.parse(raw);
                SikiaSwal.fire({
                    icon: 'success',
                    iconColor: '#C9517E',
                    title: 'Status akun berhasil diperbarui',
                    text: 'Akun ' + d.name + ' sekarang ' + (d.active ? 'nonaktif.' : 'aktif.'),
                    confirmButtonText: 'Kembali'
                });
            }
        }
    } catch (e) { /* abaikan */ }
});

// ===== SweetAlert: konfirmasi sebelum Aktifkan / Nonaktifkan =====
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-ibu-toggle') || form.dataset.confirmed) return;

    e.preventDefault();
    const active = form.dataset.active === '1';
    const name = form.dataset.name || '';

    SikiaConfirm({
        title: (active ? 'Apakah akan menonaktifkan akun ' : 'Apakah akan mengaktifkan akun ') + name + '?',
        text: active
            ? 'Akun aktif  dapat login ke sistem. Lanjutkan?'
            : 'Akun nonaktif tidak dapat login ke sistem. Lanjutkan?',
        confirmText: active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'
    }, function () {
        try { sessionStorage.setItem('sikia_ibu_toggle', JSON.stringify({ name: name, active: active })); } catch (err) {}
        form.dataset.confirmed = '1';
        form.submit();
    });
});
</script>
@endsection