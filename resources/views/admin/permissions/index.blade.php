@extends('layouts.app')

@section('title', request('tampilan') === 'role' ? 'Kelola Role' : 'Kelola Permission')

@section('content')
@php
    $lihatRole   = request('tampilan') === 'role';
    $roles       = $roles ?? collect();
    $permissions = $permissions ?? collect();

    // Cadangan: pakai route role terpisah jika BE sudah membuatnya
    $editRoleUrl = fn ($role) => \Illuminate\Support\Facades\Route::has('admin.roles.edit')
        ? route('admin.roles.edit', $role)
        : route('admin.permissions.role.edit', $role);
@endphp

<div class="mx-auto w-full max-w-[96rem] space-y-5">

    @if ($lihatRole)
        {{-- ================= KELOLA ROLE ================= --}}
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Role</h1>
            <p class="mt-1 text-sm font-medium text-slate-700">Akses &amp; Hak / Kelola Role</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <div class="max-h-[32rem] overflow-x-auto overflow-y-auto">
                <table class="w-full text-left">
                    <thead class="sticky top-0 z-10 text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                        <tr>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">No</th>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">Role</th>
                            <th class="bg-[#FCE7F0] px-3 py-3 lg:px-4">Permission</th>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 text-center lg:px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F7E6ED]">
                        @forelse ($roles as $role)
                            <tr class="align-top transition hover:bg-[#FCE7F0]/50">
                                <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 lg:px-4">{{ $loop->iteration }}</td>
                                <td class="whitespace-nowrap px-3 py-3.5 text-sm font-bold text-[#542D3D] lg:px-4">{{ ucfirst($role->name) }}</td>
                                <td class="px-3 py-3.5 text-xs font-medium leading-relaxed text-slate-800 lg:px-4">
                                    {{ $role->permissions->pluck('name')->sort()->join(', ') ?: '-' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3.5 lg:px-4">
                                    <div class="flex items-center justify-center">
                                        {{-- EDIT: pink tua solid --}}
                                        <a href="{{ $editRoleUrl($role) }}"
                                           class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white shadow-sm transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 20h4L19 9l-4-4L4 16v4Zm9-13 4 4"/></svg>
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <p class="font-bold text-[#542D3D]">Belum ada role.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[#F1D5E0] bg-white px-4 py-3 text-xs font-semibold text-slate-700">
                Menampilkan {{ $roles->count() }} data
            </div>
        </div>

    @else
        {{-- ================= KELOLA PERMISSION ================= --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Permission</h1>
                <p class="mt-1 text-sm font-medium text-slate-700">Akses &amp; Hak / Kelola Permission</p>
            </div>

            <a href="{{ route('admin.permissions.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                <span class="text-lg leading-none">+</span> Tambah Permission
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
            <div class="max-h-[32rem] overflow-x-auto overflow-y-auto">
                <table class="w-full text-left">
                    <thead class="sticky top-0 z-10 text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                        <tr>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 lg:px-4">No</th>
                            <th class="bg-[#FCE7F0] px-3 py-3 lg:px-4">Permission</th>
                            <th class="whitespace-nowrap bg-[#FCE7F0] px-3 py-3 text-center lg:px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F7E6ED]">
                        @forelse ($permissions as $permission)
                            <tr class="transition hover:bg-[#FCE7F0]/50">
                                <td class="px-3 py-3.5 text-sm font-semibold text-slate-700 lg:px-4">{{ $loop->iteration }}</td>
                                <td class="break-all px-3 py-3.5 text-sm font-bold text-[#542D3D] lg:px-4">{{ $permission->name }}</td>
                                <td class="whitespace-nowrap px-3 py-3.5 lg:px-4">
                                    <div class="flex flex-nowrap items-center justify-center gap-2">
                                        {{-- EDIT: pink tua solid --}}
                                        <a href="{{ route('admin.permissions.edit', $permission) }}"
                                           class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white shadow-sm transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 20h4L19 9l-4-4L4 16v4Zm9-13 4 4"/></svg>
                                            Edit
                                        </a>

                                        {{-- HAPUS: merah solid. Konfirmasi memakai SweetAlert (diatur di layout) --}}
                                        <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" class="m-0"
                                              data-confirm-delete
                                              data-name="{{ $permission->name }}"
                                              data-title="Apakah akan menghapus permission {{ $permission->name }}?"
                                              data-text="Permission akan dihapus secara permanen dan tidak dapat dipulihkan. Lanjutkan?"
                                              data-confirm-text="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-red-600 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M10 11v6m4-6v6M6 7l1 12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-12M9 7V4h6v3"/></svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-12 text-center">
                                    <p class="font-bold text-[#542D3D]">Belum ada permission.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[#F1D5E0] bg-white px-4 py-3 text-xs font-semibold text-slate-700">
                Menampilkan {{ $permissions->count() }} data
            </div>
        </div>
    @endif
</div>
@endsection