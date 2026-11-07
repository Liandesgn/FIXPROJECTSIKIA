@extends('layouts.app')

@section('title', 'Kelola Permission')

@section('content')
@php
    $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'superadmin';
@endphp

<div class="mx-auto w-full max-w-[96rem] space-y-5">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
                Kelola Permission
            </h1>

            <p class="mt-1 text-sm font-medium text-slate-700">
                Akses &amp; Hak / Kelola Permission
            </p>
        </div>

        @can('userpermission.create')
            <a
                href="{{ route($routePrefix . '.permissions.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40"
            >
                <span class="text-lg leading-none">+</span>
                Tambah Permission
            </a>
        @endcan

    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="overflow-x-auto">

            <table class="w-full text-left">

                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3">No</th>
                        <th class="whitespace-nowrap px-4 py-3">Permission</th>
                        <th class="px-4 py-3">Role yang memakai</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">

                    @forelse ($permissions as $permission)
                        <tr class="transition hover:bg-[#FCE7F0]/50">

                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">
                                {{ $loop->iteration }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5 text-sm font-bold text-[#542D3D]">
                                {{ $permission->name }}
                            </td>

                            <td class="px-4 py-3.5">
                                @forelse ($permission->roles->sortBy('name') as $r)
                                    <span class="mb-1 mr-1 inline-flex rounded-full bg-[#FCE7F0] px-3 py-1 text-xs font-semibold text-[#542D3D]">
                                        {{ ucfirst($r->name) }}
                                    </span>
                                @empty
                                    <span class="text-sm text-slate-500">-</span>
                                @endforelse
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5">
                                <div class="flex flex-nowrap items-center justify-center gap-2">

                                    @can('userpermission.update')
                                        <a
                                            href="{{ route($routePrefix . '.permissions.edit', $permission) }}"
                                            class="inline-flex h-8 items-center justify-center rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white shadow-sm transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40"
                                        >
                                            Edit
                                        </a>
                                    @endcan

                                    @can('userpermission.delete')
                                        <form
                                            method="POST"
                                            action="{{ route($routePrefix . '.permissions.destroy', $permission) }}"
                                            class="m-0"
                                            onsubmit="return confirm('Hapus permission {{ $permission->name }}?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="inline-flex h-8 items-center justify-center rounded-lg bg-red-600 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    @endcan

                                    @cannot('userpermission.update')
                                        @cannot('userpermission.delete')
                                            <span class="text-xs text-slate-400">
                                                Tidak ada aksi
                                            </span>
                                        @endcannot
                                    @endcannot

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                                Belum ada permission.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>
@endsection