@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email..."
                       class="w-52 rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200">

                <select name="role"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                    <option value="">Semua Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>
                            {{ \App\Enums\RoleEnum::from($role)->label() }}
                        </option>
                    @endforeach
                </select>

                <select name="status"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                        class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">
                    Filter
                </button>
                @if (request()->filled('search') || request()->filled('role') || request()->filled('status'))
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </form>

            <a href="{{ route('admin.users.create') }}"
               class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                + Tambah User
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Wilayah</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <div class="font-medium text-slate-800">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $user->phone ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $user->email }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $user->primaryRole()?->label() ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $user->wilayahKerja?->nama ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    @include('admin.users.partials.status', ['status' => $user->status])
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-3 text-xs font-medium">
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-slate-600 hover:text-slate-900">Lihat</a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-sky-600 hover:text-sky-700">Edit</a>

                                        @if ($user->status === \App\Enums\UserStatusEnum::Active)
                                            <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-amber-600 hover:text-amber-700">Nonaktifkan</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-emerald-600 hover:text-emerald-700">Aktifkan</button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-500">Tidak ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $users->links() }}
        </div>
    </div>
@endsection
