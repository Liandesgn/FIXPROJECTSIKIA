@extends('layouts.app')

@section('title', 'Admin Wilayah')

@section('content')
<div class="mx-auto w-full max-w-[96rem] space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Admin Wilayah</h1>
            <p class="mt-1 text-sm font-medium text-slate-700">Manajemen Admin / Admin Wilayah</p>
        </div>
        <a href="{{ route('superadmin.admins.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D]">
            <span class="text-lg leading-none">+</span> Tambah Admin
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Wilayah</th>
                        <th class="px-4 py-3">Telepon</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($admins as $admin)
                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">{{ $admins->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3.5 text-sm font-bold text-[#542D3D]">{{ $admin->user?->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $admin->user?->email ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $admin->wilayahKerja?->nama_wilayah ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $admin->user?->phone ?? '-' }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('superadmin.admins.edit', $admin) }}"
                                       class="inline-flex h-8 items-center rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white transition hover:bg-[#B7436D]">Edit</a>
                                    <form method="POST" action="{{ route('superadmin.admins.destroy', $admin) }}" class="m-0"
                                          data-confirm-delete
                                          data-name="{{ $admin->user?->name }}"
                                          data-title="Hapus admin {{ $admin->user?->name }}?"
                                          data-text="Akun admin akan dihapus permanen. Lanjutkan?"
                                          data-confirm-text="Ya, Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-8 items-center rounded-lg bg-red-600 px-3 text-xs font-bold text-white transition hover:bg-red-700">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">Belum ada akun admin wilayah.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#F1D5E0] bg-white px-4 py-3">
            {{ $admins->links() }}
        </div>
    </div>
</div>
@endsection
