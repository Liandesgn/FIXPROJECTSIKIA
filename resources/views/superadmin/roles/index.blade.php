
@extends('layouts.app')

@section('title', 'Kelola Role')

@section('content')
@php
    $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'superadmin';
    $isSuperAdmin = $routePrefix === 'superadmin';
@endphp


<div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
            Kelola Role
        </h1>
        <p class="mt-1 text-sm font-medium text-slate-700">
            Akses &amp; Hak / Kelola Role
        </p>
    </div>

    @if ($isSuperAdmin)
        <a
            href="{{ route('superadmin.roles.create') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#B7436D]"
        >
            <span class="text-lg">+</span>
            Tambah Role
        </a>
    @endif
</div>


    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">

                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3">No</th>
                        <th class="whitespace-nowrap px-4 py-3">Role</th>
                        <th class="px-4 py-3">Permission</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($roles as $role)
                        <tr class="transition hover:bg-[#FCE7F0]/50">

                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">
                                {{ $loop->iteration }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5 text-sm font-bold text-[#542D3D]">
                                {{ ucfirst($role->name) }}
                            </td>

                            <td class="px-4 py-3.5">
                                @forelse ($role->permissions->sortBy('name') as $permission)
                                    <span class="mb-1 mr-1 inline-flex rounded-full bg-[#FCE7F0] px-3 py-1 text-xs font-semibold text-[#542D3D]">
                                        {{ $permission->name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-slate-500">-</span>
                                @endforelse
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5">
                                <div class="flex items-center justify-center gap-2">

                                   <a
                                        href="{{ route('superadmin.roles.edit', $role) }}"
                                        class="inline-flex h-8 items-center justify-center rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white transition hover:bg-[#B7436D]"
                                    >
                                        Edit
                                    </a>

                                    @if ($role->name !== 'super_admin')
                                        <form
                                            action="{{ route($routePrefix . '.roles.destroy', $role) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus role {{ ucfirst($role->name) }}?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="inline-flex h-8 items-center justify-center rounded-lg bg-red-500 px-3 text-xs font-bold text-white transition hover:bg-red-600"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                                Belum ada data Role.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>
</div>
@endsection
