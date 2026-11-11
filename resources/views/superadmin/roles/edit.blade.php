@extends('layouts.app')

@section('title', 'Edit Hak Akses Role')

@section('content')
@php
    $routePrefix  = request()->routeIs('admin.*') ? 'admin' : 'superadmin';
    $isSuperAdmin = $routePrefix === 'superadmin';
    $namaRole     = ucwords(str_replace('_', ' ', $role->name));

    $selected   = old('permissions', $role->permissions->pluck('name')->all());
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
@endphp

<div class="mx-auto w-full max-w-3xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Role</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Akses &amp; Hak / Kelola Role / Edit</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-6 py-4">
            <h2 class="text-lg font-bold text-[#542D3D]">Edit Hak Akses: {{ $namaRole }}</h2>
            <p class="mt-0.5 text-sm text-slate-600">Atur permission yang dimiliki role ini.</p>
        </div>

        <form method="POST" action="{{ route($routePrefix . '.roles.update', $role) }}" class="space-y-5 p-4 sm:p-6">
            @csrf
            @method('PUT')

            @if ($role->name === 'super_admin')
                <div class="flex gap-3 rounded-xl border border-[#E99AB7] bg-[#FCE7F0] p-4">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 11v5m0-8h.01"/></svg>
                    <p class="text-sm font-medium leading-6 text-[#542D3D]">
                        Role Super Admin memiliki seluruh permission dan tidak dapat dikurangi.
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                @foreach ($permissions as $p)
                    @php
                        $wajibAdmin      = $role->name === 'admin' && str_starts_with($p->name, 'user.');
                        $wajibSuperAdmin = $role->name === 'super_admin';
                        $wajib           = $wajibAdmin || $wajibSuperAdmin;
                        $terpilih        = $wajib || in_array($p->name, $selected, true);
                    @endphp

                    <label class="flex items-center gap-3 rounded-xl border border-[#E99AB7] bg-white px-3.5 py-3 text-sm font-semibold text-[#542D3D] transition has-[:checked]:border-[#C9517E] has-[:checked]:bg-[#FCE7F0] {{ $wajib ? '' : 'cursor-pointer hover:bg-[#FCE7F0]' }}">
                        <input type="checkbox" name="permissions[]" value="{{ $p->name }}"
                               @checked($terpilih)
                               @disabled($wajib)
                               class="h-5 w-5 shrink-0 rounded border-slate-400 text-[#C9517E] focus:ring-[#C9517E]">

                        <span class="break-all">{{ $p->label ?? $p->name }}</span>

                        @if ($wajib)
                            <span class="shrink-0 text-xs font-medium text-slate-600">(wajib)</span>
                        @endif
                    </label>
                @endforeach
            </div>

            @error('permissions')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
            @error('permissions.*')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror

            <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('superadmin.roles.index') }}"
                   class="inline-flex w-full justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] sm:w-auto">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex w-full justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 sm:w-auto">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection