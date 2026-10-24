@extends('layouts.app')

@section('content')
<div class="max-w-4xl space-y-6">
    <h1 class="text-2xl font-bold text-[#542D3D]">Edit Hak Akses: {{ ucfirst($role->name) }}</h1>

    <form method="POST" action="{{ route('admin.permissions.role.update', $role) }}"
          class="space-y-4 rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        @php $selected = old('permissions', $role->permissions->pluck('name')->all()); @endphp

        <div class="grid gap-x-6 gap-y-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($permissions as $p)
                @php $wajib = $role->name === 'admin' && str_starts_with($p->name, 'user.') && $p->name !== 'user.assign-role'; @endphp
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="permissions[]" value="{{ $p->name }}"
                           @checked($wajib || in_array($p->name, $selected, true))
                           @if ($wajib) onclick="return false" @endif
                           class="h-4 w-4 rounded border-slate-300 text-[#C9517E] focus:ring-[#C9517E]">
                    {{ $p->name }}
                    @if ($wajib)
                        <span class="text-xs text-slate-400">(wajib)</span>
                    @endif
                </label>
            @endforeach
        </div>

        @error('permissions.*')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        <div class="flex gap-2">
            <button type="submit" class="rounded-xl bg-[#C9517E] px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            <a href="{{ route('admin.permissions.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:underline">Batal</a>
        </div>
    </form>
</div>
@endsection