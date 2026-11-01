@extends('layouts.app')

@section('title', 'Kelola Role')

@section('content')
<div class="mx-auto w-full max-w-[96rem] space-y-5">

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Role</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Akses &amp; Hak / Kelola Role</p>
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
                    @foreach ($roles as $role)
                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">{{ $loop->iteration }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-sm font-bold text-[#542D3D]">{{ ucfirst($role->name) }}</td>
                            <td class="px-4 py-3.5">
                                @forelse ($role->permissions->sortBy('name') as $permission)
                                    <span class="mb-1 mr-1 inline-flex rounded-full bg-[#FCE7F0] px-3 py-1 text-xs font-semibold text-[#542D3D]">{{ $permission->name }}</span>
                                @empty
                                    <span class="text-sm text-slate-500">-</span>
                                @endforelse
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-center">
                                <a href="{{ route('admin.roles.edit', $role) }}"
                                   class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white shadow-sm transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection