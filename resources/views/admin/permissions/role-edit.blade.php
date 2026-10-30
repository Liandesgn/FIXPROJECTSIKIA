@extends('layouts.app')

@section('title', 'Edit Hak Akses Role')

@section('content')
@php
    $selected = old('permissions', $role->permissions->pluck('name')->all());
    $indexUrl = route('admin.permissions.index', ['tampilan' => 'role']);   // kembali ke daftar Role
@endphp

<div class="mx-auto w-full max-w-5xl space-y-4">

    <div>
        <a href="{{ $indexUrl }}"
           class="inline-flex items-center gap-2 rounded-xl border-2 border-[#C9517E] bg-white px-4 py-2 text-sm font-semibold text-[#C9517E] shadow-sm transition hover:bg-[#C9517E] hover:text-white focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"/></svg>
            Kembali ke Kelola Role
        </a>
        <p class="mt-3 text-sm font-medium text-slate-700">Akses &amp; Hak / Kelola Role / Edit</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-6 py-5 sm:px-8">
            <h1 class="text-xl font-bold text-[#542D3D] sm:text-2xl">Edit Hak Akses: {{ ucfirst($role->name) }}</h1>
            <p class="mt-1 text-sm text-slate-700">Centang permission yang boleh dipakai role ini.</p>
        </div>

        <form method="POST" action="{{ route('admin.permissions.role.update', $role) }}" class="space-y-6 p-6 sm:p-8">
            @csrf
            @method('PUT')

            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($permissions as $p)
                    @php $wajib = $role->name === 'admin' && str_starts_with($p->name, 'user.'); @endphp
                    <label class="flex items-center gap-2.5 rounded-xl border border-[#F1D5E0] bg-[#FFF7FA] px-3 py-2.5 text-sm font-semibold text-[#542D3D] {{ $wajib ? '' : 'cursor-pointer hover:bg-[#FCE7F0]' }}">
                        <input type="checkbox" name="permissions[]" value="{{ $p->name }}"
                               @checked($wajib || in_array($p->name, $selected, true))
                               @if ($wajib) onclick="return false" @endif
                               class="h-4 w-4 rounded border-slate-400 text-[#C9517E] focus:ring-[#C9517E]">
                        <span class="break-all">{{ $p->name }}</span>
                        @if ($wajib)
                            <span class="text-xs font-medium text-slate-600">(wajib)</span>
                        @endif
                    </label>
                @endforeach
            </div>

            @error('permissions.*')
                <p class="text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-6 sm:flex-row sm:justify-end">
                <a href="{{ $indexUrl }}"
                   class="inline-flex justify-center rounded-xl border border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">Batal</a>
                <button type="submit"
                        class="inline-flex justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection