@extends('layouts.app')

@section('title', 'Kelola Ibu')

@section('content')
@php
    if (method_exists($ibus, 'appends')) {
        $ibus->appends(request()->query());
    }

    $pg        = $ibus instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? $ibus : null;
    $total     = $pg ? $pg->total() : $ibus->count();
    $nomorAwal = $pg ? ($pg->firstItem() ?? 1) : 1;
    $readOnly  = $readOnly ?? false;
    $listWilayah = collect($wilayahs ?? []);

    $hasFlash = session()->has('success') || session()->has('status') || session()->has('message') || session()->has('error');

    // Lebar kolom form pencarian: ada filter wilayah hanya untuk Super Admin
    $colSearch = $readOnly ? 'md:col-span-5' : 'md:col-span-6';
    $colStatus = $readOnly ? 'md:col-span-3' : 'md:col-span-4';
    $colBtn    = $readOnly ? 'md:col-span-1' : 'md:col-span-2';
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Ibu</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Manajemen User / Kelola Ibu</p>
    </div>

    {{-- Ringkasan + tombol Registrasi --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
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

        @if (!$readOnly)
            @can('user.create')
                <a href="{{ route('admin.ibu.create') }}"
                   class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 sm:w-auto">
                    <span class="text-lg leading-none">+</span> Registrasi Ibu
                </a>
            @endcan
        @endif
    </div>

    {{-- Daftar --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Pencarian + filter status (+ filter wilayah khusus Super Admin) --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <p class="mb-2.5 text-sm font-semibold text-[#542D3D]">
                {{ $readOnly ? 'Temukan ibu lewat nama, wilayah, atau status akun' : 'Temukan ibu lewat nama, NIK, atau status akun' }}
            </p>

            <form method="GET" action="{{ url()->current() }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="relative {{ $colSearch }}">
                    <label for="searchIbu" class="sr-only">Cari nama ibu</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="searchIbu" type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                           placeholder="Cari nama / NIK"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>

                @if ($readOnly)
                    <div class="md:col-span-3">
                        <label for="filterWilayah" class="sr-only">Filter wilayah</label>
                        <select id="filterWilayah" name="wilayah_kerja_id" onchange="this.form.submit()"
                                class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                            <option value="">Semua wilayah</option>
                            @foreach ($listWilayah->groupBy('kecamatan') as $kecamatan => $desas)
                                <optgroup label="Kec. {{ $kecamatan ?: '-' }}">
                                    @foreach ($desas as $wilayah)
                                        <option value="{{ $wilayah->id }}" @selected((string) request('wilayah_kerja_id') === (string) $wilayah->id)>
                                            Desa {{ $wilayah->kelurahan }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="{{ $colStatus }}">
                    <label for="filterStatus" class="sr-only">Filter status akun</label>
                    <select id="filterStatus" name="status" onchange="this.form.submit()"
                            class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                        <option value="">Semua status akun</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="{{ $colBtn }}">
                    <button type="submit"
                            class="w-full rounded-xl bg-[#C9517E] px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">No</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Nama Ibu</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">Email</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 lg:table-cell">No. HP</th>
                        @if ($readOnly)
                            <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">Wilayah</th>
                        @endif
                        <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">Status Akun</th>
                        @if (!$readOnly)
                            <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Aksi</th>
                        @endif
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($ibus as $ibu)
                        @php
                            $isActive = $ibu->status === \App\Enums\UserStatusEnum::Active;
                            $desa     = $ibu->ibu?->wilayahKerja?->kelurahan;
                            $kec      = $ibu->ibu?->wilayahKerja?->kecamatan;
                        @endphp

                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 sm:px-4">{{ $nomorAwal + $loop->index }}</td>

                            {{-- Nama + NIK (+ data lain di layar kecil) --}}
                            <td class="px-3 py-3.5 sm:px-4">
                                <p class="break-words text-sm font-bold text-[#542D3D]">{{ $ibu->name ?? '-' }}</p>
                                <p class="whitespace-nowrap text-xs font-medium text-slate-600">NIK {{ $ibu->ibu?->nik ?? '-' }}</p>
                                <p class="mt-0.5 break-all text-xs text-slate-700 md:hidden">{{ $ibu->email ?? '-' }}</p>
                                <p class="text-xs text-slate-700 lg:hidden">{{ $ibu->phone ?? '-' }}</p>
                                @if ($readOnly)
                                    <p class="text-xs font-semibold text-slate-800 md:hidden">{{ $desa ? 'Desa ' . $desa : '-' }}{{ $kec ? ', Kec. ' . $kec : '' }}</p>
                                @endif
                                <div class="mt-1.5 md:hidden">
                                    @include('admin.partials.status', ['status' => $ibu->status])
                                </div>
                            </td>

                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 md:table-cell">{{ $ibu->email ?? '-' }}</td>
                            <td class="hidden whitespace-nowrap px-4 py-3.5 text-sm text-slate-800 lg:table-cell">{{ $ibu->phone ?? '-' }}</td>

                            @if ($readOnly)
                                <td class="hidden whitespace-nowrap px-4 py-3.5 md:table-cell">
                                    <p class="text-sm font-semibold text-slate-800">{{ $desa ?? '-' }}</p>
                                    @if ($kec)
                                        <p class="mt-0.5 text-xs font-medium text-slate-600">Kec. {{ $kec }}</p>
                                    @endif
                                </td>
                            @endif

                            <td class="hidden whitespace-nowrap px-4 py-3.5 md:table-cell">
                                @include('admin.partials.status', ['status' => $ibu->status])
                            </td>

                            {{-- Aktifkan / Nonaktifkan hanya untuk Admin --}}
                            @if (!$readOnly)
                                <td class="whitespace-nowrap px-3 py-3.5 sm:px-4">
                                    <div class="flex items-center justify-center gap-2">
                                        @can('user.activate')
                                            <form method="POST" action="{{ route('admin.ibu.toggle-status', $ibu) }}" class="m-0"
                                                  data-ibu-toggle
                                                  data-active="{{ $isActive ? '1' : '0' }}"
                                                  data-name="{{ $ibu->name }}">
                                                @csrf
                                                @method('PATCH')

                                                @if ($isActive)
                                                    <button type="submit"
                                                            class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-amber-500 px-3 text-xs font-bold text-amber-950 shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-4 focus:ring-amber-200">
                                                        Nonaktifkan
                                                    </button>
                                                @else
                                                    <button type="submit"
                                                            class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-emerald-700 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                                                        Aktifkan
                                                    </button>
                                                @endif
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">{{ request('search') ? 'Data tidak ditemukan.' : 'Belum ada data ibu.' }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-[#F1D5E0] bg-white px-4 py-3">
            @if ($pg)
                @php
                    $awal   = max($pg->currentPage() - 1, 1);
                    $akhir  = min($pg->currentPage() + 1, $pg->lastPage());
                    $btn    = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-[#F1D5E0] bg-white px-2 text-sm font-semibold text-[#542D3D] transition hover:bg-[#FCE7F0]';
                    $btnOff = 'inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-lg border border-[#F1D5E0] bg-white text-lg text-slate-300';
                @endphp
                <nav class="flex flex-col items-center justify-between gap-3 sm:flex-row" aria-label="Navigasi halaman">
                    <p class="text-xs font-medium text-slate-600">
                        Menampilkan {{ $pg->firstItem() ?? 0 }}–{{ $pg->lastItem() ?? 0 }} dari {{ $pg->total() }} data
                    </p>

                    @if ($pg->hasPages())
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
                    @endif
                </nav>
            @else
                <p class="text-xs font-medium text-slate-600">Total {{ $total }} data</p>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Popup hasil setelah perubahan status, hanya untuk Admin
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
    } catch (e) {
        /* Abaikan */
    }
});

// Konfirmasi perubahan status akun, hanya tersedia untuk Admin
document.addEventListener('submit', function (e) {
    const form = e.target;

    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-ibu-toggle') || form.dataset.confirmed) return;

    e.preventDefault();

    const active = form.dataset.active === '1';
    const name = form.dataset.name || '';

    SikiaConfirm({
        title: (active ? 'Apakah akan menonaktifkan akun ' : 'Apakah akan mengaktifkan akun ') + name + '?',
        text: active
            ? 'Akun aktif dapat login ke sistem. Lanjutkan?'
            : 'Akun nonaktif tidak dapat login ke sistem. Lanjutkan?',
        confirmText: active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'
    }, function () {
        try {
            sessionStorage.setItem('sikia_ibu_toggle', JSON.stringify({ name: name, active: active }));
        } catch (err) {}

        form.dataset.confirmed = '1';
        form.submit();
    });
});
</script>
@endsection