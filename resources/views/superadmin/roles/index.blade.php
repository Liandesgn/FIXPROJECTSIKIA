@extends('layouts.app')

@section('title', 'Kelola Role')

@section('content')
@php
    $routePrefix  = request()->routeIs('admin.*') ? 'admin' : 'superadmin';
    $isSuperAdmin = $routePrefix === 'superadmin';

    $keyword    = mb_strtolower(trim((string) request('search')));
    $perHalaman = 10;

    if ($roles instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
        // Controller sudah memakai paginate(): pencarian dilakukan di controller
        $pg = $roles;
    } else {
        $semua = collect($roles)->values();

        // Pencarian: nama role diawali kata yang diketik
        if ($keyword !== '') {
            $semua = $semua->filter(

                fn ($r) => str_starts_with(mb_strtolower($r->name), $keyword)
            )->values();
        }

        $halamanTerakhir = max(1, (int) ceil($semua->count() / $perHalaman));
        $halaman         = min(max(1, (int) request('page', 1)), $halamanTerakhir);

        $pg = new \Illuminate\Pagination\LengthAwarePaginator(
            $semua->forPage($halaman, $perHalaman)->values(),
            $semua->count(),
            $perHalaman,
            $halaman,
            ['path' => url()->current()]
        );
    }

    $pg->appends(request()->except('page'));

    $roles     = $pg;
    $total     = $pg->total();
    $nomorAwal = $pg->firstItem() ?? 1;
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Role</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Akses &amp; Hak / Kelola Role</p>
    </div>

    {{-- Ringkasan + tombol Tambah --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="inline-flex items-center gap-3 rounded-xl border border-[#F1D5E0] bg-white px-4 py-2.5 shadow-sm">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#FCE7F0] text-[#C9517E]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3Z"/>
                </svg>
            </span>
            <div class="leading-tight">
                <p class="text-xs font-semibold text-slate-700">Total data role</p>
                <p class="text-xl font-bold text-[#C9517E]">{{ $total }}</p>
            </div>
        </div>

        @if ($isSuperAdmin)
            <a href="{{ route('superadmin.roles.create') }}"
               class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 sm:w-auto">
                <span class="text-lg leading-none">+</span> Tambah Role
            </a>
        @endif
    </div>

    {{-- Daftar --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Pencarian --}}
        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <label for="searchInput" class="sr-only">Cari role</label>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input id="searchInput" type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                           placeholder="Cari nama role"
                           class="w-full rounded-xl border-2 border-[#C9517E]/60 bg-white py-2.5 pl-11 pr-3 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-500 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">
                </div>

                <button type="submit"
                        class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                    Cari
                </button>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">No</th>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Role</th>
                        <th class="px-3 py-3 sm:px-4">Permission</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($roles as $role)
                        @php
                            $namaRole = $role->name;
                        @endphp

                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 sm:px-4">{{ $nomorAwal + $loop->index }}</td>

                            <td class="whitespace-nowrap px-3 py-3.5 text-sm font-bold text-[#542D3D] sm:px-4">{{ $namaRole }}</td>

                            <td class="px-3 py-3.5 sm:px-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($role->permissions->sortBy('name') as $permission)
                                        <span class="inline-flex rounded-full bg-[#FCE7F0] px-3 py-1 text-xs font-bold text-[#542D3D]">
                                            {{ $permission->name }}
                                        </span>
                                    @empty
                                        <span class="text-sm font-medium text-slate-600">-</span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-3 py-3.5 sm:px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('superadmin.roles.edit', $role) }}"
                                       class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-[#E99AB7] px-3 text-xs font-bold text-[#542D3D] shadow-sm transition hover:bg-[#DD87A8] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                                        Edit
                                    </a>

                                    {{-- Super Admin hanya punya Edit --}}
                                    @if ($role->name !== 'super_admin')
                                        <form action="{{ route($routePrefix . '.roles.destroy', $role) }}" method="POST" class="m-0"
                                              data-confirm-delete
                                              data-name="{{ $namaRole }}"
                                              data-title="Apakah akan menghapus role {{ $namaRole }}?"
                                              data-text="Role {{ $namaRole }} akan dihapus beserta pengaturan permission-nya. Role yang masih digunakan tidak dapat dihapus. Lanjutkan?"
                                              data-confirm-text="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex h-8 items-center justify-center whitespace-nowrap rounded-lg bg-red-600 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">{{ $keyword !== '' ? 'Data tidak ditemukan.' : 'Belum ada data role.' }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-[#F1D5E0] bg-white px-4 py-3">
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
        </div>
    </div>
</div>
@endsection
