@extends('layouts.app')

@section('content')
<div class="space-y-8">

    @if (session('success'))
        <div class="rounded-xl bg-green-50 p-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- KELOLA ROLE --}}
    <div class="space-y-3">
        <h1 class="text-2xl font-bold text-[#542D3D]">Kelola Role</h1>

        <div class="overflow-x-auto rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-[#F1D5E0] bg-[#FCE7F0] text-xs font-bold uppercase text-[#542D3D]">
                    <tr>
                        <th class="p-3">No.</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Permission</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr class="border-b border-slate-100">
                            <td class="p-3">{{ $loop->iteration }}</td>
                            <td class="p-3 font-semibold text-[#542D3D]">{{ ucfirst($role->name) }}</td>
                            <td class="p-3 text-xs leading-relaxed">
                                {{ $role->permissions->pluck('name')->sort()->join(', ') ?: '-' }}
                            </td>
                            <td class="p-3">
                                <a href="{{ route('admin.permissions.role.edit', $role) }}"
                                   class="rounded-lg bg-slate-800 px-3 py-1 text-xs font-semibold text-white">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- KELOLA PERMISSION --}}
    <div class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-2xl font-bold text-[#542D3D]">Kelola Permission</h2>
            <a href="{{ route('admin.permissions.create') }}"
               class="rounded-xl bg-[#C9517E] px-4 py-2 text-sm font-semibold text-white">+ Tambah Permission</a>
        </div>


            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-[#F1D5E0] bg-[#FCE7F0] text-xs font-bold uppercase text-[#542D3D]">
                    <tr>
                        <th class="p-3">No.</th>
                        <th class="p-3">Permission</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permissions as $permission)
                        <tr class="border-b border-slate-100">
                            <td class="p-3">{{ $loop->iteration }}</td>
                            <td class="p-3 font-semibold text-[#542D3D]">{{ $permission->name }}</td>
                            <td class="p-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('admin.permissions.edit', $permission) }}"
                                       class="rounded-lg bg-slate-800 px-3 py-1 text-xs font-semibold text-white">Edit</a>

                                    <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}"
                                          onsubmit="return confirm('Hapus permission {{ $permission->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg bg-red-600 px-3 py-1 text-xs font-semibold text-white">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-6 text-center text-slate-400">Belum ada permission.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
  

</div>
@endsection