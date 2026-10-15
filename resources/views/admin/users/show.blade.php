@extends('layouts.app')

@section('title', 'Detail User')

@section('content')
    <div class="mx-auto max-w-2xl space-y-4">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                </div>
                @include('admin.users.partials.status', ['status' => $user->status])
            </div>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">Role</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $user->primaryRole()?->label() ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">Nomor HP</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $user->phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">Wilayah Kerja</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $user->wilayahKerja?->nama ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">Dibuat</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $user->created_at?->format('d M Y H:i') ?? '-' }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-4">
                <a href="{{ route('admin.users.edit', $user) }}"
                   class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                    Edit
                </a>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Kembali</a>
            </div>
        </div>
    </div>
@endsection
