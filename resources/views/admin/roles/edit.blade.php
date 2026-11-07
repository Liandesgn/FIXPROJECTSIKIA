
@extends('layouts.app')

@section('title', 'Edit Hak Akses Role')

@section('content')
@php
    $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'superadmin';

    $isSuperAdmin = $routePrefix === 'superadmin';

    $selected = old(
        'permissions',
        $role->permissions->pluck('name')->all()
    );
@endphp

<div class="mx-auto max-w-4xl space-y-6">

    <div>
        <h1 class="text-xl font-bold text-[#542D3D]">
            Edit Hak Akses: {{ ucfirst($role->name) }}
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Atur permission yang dimiliki role ini.
        </p>
    </div>

    @if ($role->name === 'super_admin')
        <div class="rounded-xl border border-[#F1D5E0] bg-[#FCE7F0] p-4 text-sm text-[#542D3D]">
            Role Super Admin memiliki seluruh permission dan tidak dapat dikurangi.
        </div>
    @endif

    <form
        method="POST"
        action="{{ route($routePrefix . '.roles.update', $role) }}"
        class="space-y-6"
    >
        @csrf
        @method('PUT')

        <div class="grid gap-3 sm:grid-cols-2">

            @foreach ($permissions as $p)
                @php
                    $wajibAdmin = $role->name === 'admin'
                        && str_starts_with($p->name, 'user.');

                    $wajibSuperAdmin = $role->name === 'super_admin';

                    $wajib = $wajibAdmin || $wajibSuperAdmin;

                    $terpilih = $wajib || in_array($p->name, $selected, true);
                @endphp

                <label class="flex items-center gap-2 rounded-xl border border-[#F1D5E0] p-3 text-sm text-slate-700">

                    <input
                        type="checkbox"
                        name="permissions[]"
                        value="{{ $p->name }}"
                        @checked($terpilih)
                        @disabled($wajib)
                        class="h-4 w-4 rounded border-slate-300 text-[#C9517E] focus:ring-[#C9517E]"
                    >

                    <span>
                        {{ $p->label ?? $p->name }}
                    </span>

                    @if ($wajibSuperAdmin)
                        <span class="text-xs text-slate-400">(wajib)</span>
                    @elseif ($wajibAdmin)
                        <span class="text-xs text-slate-400">(wajib)</span>
                    @endif

                </label>
            @endforeach

        </div>

        @error('permissions')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        @error('permissions.*')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <div class="flex gap-2">

            <button
                type="submit"
                class="rounded-xl bg-[#C9517E] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#B7436D]"
            >
                Simpan Perubahan
            </button>

            <a
                href="{{ route($routePrefix . '.roles.index') }}"
                class="rounded-xl px-4 py-2 text-sm text-slate-500 hover:underline"
            >
                Batal
            </a>

        </div>
    </form>
</div>
@endsection
