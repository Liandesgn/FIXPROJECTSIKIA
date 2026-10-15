@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Ringkasan Sistem</h2>
            <p class="text-sm text-slate-500">Selamat datang kembali, {{ auth()->user()->name }}.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Akun Bidan</p>
                <p class="mt-2 text-3xl font-bold text-sky-600">{{ $stats['bidan'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Akun Kader</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['kader'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Akun Orang Tua</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ $stats['orang_tua'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Wilayah Kerja</p>
                <p class="mt-2 text-3xl font-bold text-violet-600">{{ $stats['wilayah'] }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h3 class="font-semibold text-slate-800">User Terbaru</h3>
                <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-sky-600 hover:text-sky-700">
                    Lihat semua
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($latestUsers as $user)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-800">{{ $user->name }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $user->email }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $user->primaryRole()?->label() ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    @include('admin.users.partials.status', ['status' => $user->status])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-slate-500">Belum ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
