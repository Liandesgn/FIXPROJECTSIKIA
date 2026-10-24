@php
    $locked   = $locked ?? false;
    $selected = old('roles', isset($permission) ? $permission->roles->pluck('name')->all() : []);
@endphp

<div>
    <label for="name" class="mb-1 block text-sm font-semibold text-[#542D3D]">Nama Permission</label>
    <input id="name" type="text" name="name"
           value="{{ old('name', $permission->name ?? '') }}"
           @readonly($locked)
           placeholder="contoh: ibu.view"
           class="w-full rounded-xl border border-[#F1D5E0] px-3 py-2 text-sm {{ $locked ? 'bg-slate-50' : '' }}">
    @error('name')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
    @if ($locked)
        <p class="mt-1 text-xs text-slate-500">Permission inti, namanya tidak bisa diubah.</p>
    @endif
</div>

<div>
    <p class="mb-2 text-sm font-semibold text-[#542D3D]">Role yang boleh memakai permission ini</p>

    <div class="grid gap-2 sm:grid-cols-2">
        @foreach ($roles as $role)
            @php $wajib = $locked && $role->name === 'admin'; @endphp
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                       @checked($wajib || in_array($role->name, $selected, true))
                       @if ($wajib) onclick="return false" @endif
                       class="h-4 w-4 rounded border-slate-300 text-[#C9517E] focus:ring-[#C9517E]">
                {{ ucfirst($role->name) }}
                @if ($wajib)
                    <span class="text-xs text-slate-400">(wajib)</span>
                @endif
            </label>
        @endforeach
    </div>
    @error('roles.*')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="flex gap-2">
    <button type="submit" class="rounded-xl bg-[#C9517E] px-4 py-2 text-sm font-semibold text-white">Simpan</button>
    <a href="{{ route('admin.permissions.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:underline">Batal</a>
</div>