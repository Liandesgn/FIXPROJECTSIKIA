{{-- Isi form permission. Parameter: $permission (model|null), $roles, $locked (opsional), $cancelUrl (opsional) --}}
@php
    $permission = $permission ?? null;
    $roles      = $roles ?? collect();
    $locked     = $locked ?? false;
    $cancelUrl  = $cancelUrl ?? route('admin.permissions.index');
    $selected   = old('roles', $permission ? $permission->roles->pluck('name')->all() : []);

    $inputClass = 'w-full rounded-xl border border-[#E2C3D0] px-4 py-3 text-sm text-gray-900 outline-none transition placeholder:text-slate-500 hover:border-[#E99AB7] focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/25';
@endphp

<div class="space-y-6">
    <div>
        <label for="name" class="mb-1.5 block text-sm font-semibold text-[#542D3D]">Nama Permission <span class="text-[#C9517E]">*</span></label>
        <input id="name" type="text" name="name"
               value="{{ old('name', $permission->name ?? '') }}"
               @readonly($locked)
               placeholder="contoh: ibu.view"
               class="{{ $inputClass }} {{ $locked ? 'bg-slate-100' : 'bg-white' }}">
        @error('name')
            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
        @if ($locked)
            <p class="mt-1.5 text-xs font-medium text-slate-600">Permission inti, namanya tidak bisa diubah.</p>
        @endif
    </div>

    <div>
        <p class="mb-2 text-sm font-semibold text-[#542D3D]">Role yang boleh memakai permission ini</p>

        <div class="grid gap-2 sm:grid-cols-2">
            @foreach ($roles as $role)
                @php $wajib = $locked && $role->name === 'admin'; @endphp
                <label class="flex items-center gap-2.5 rounded-xl border border-[#F1D5E0] bg-[#FFF7FA] px-3 py-2.5 text-sm font-semibold text-[#542D3D] {{ $wajib ? '' : 'cursor-pointer hover:bg-[#FCE7F0]' }}">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                           @checked($wajib || in_array($role->name, $selected, true))
                           @if ($wajib) onclick="return false" @endif
                           class="h-4 w-4 rounded border-slate-400 text-[#C9517E] focus:ring-[#C9517E]">
                    {{ ucfirst($role->name) }}
                    @if ($wajib)
                        <span class="text-xs font-medium text-slate-600">(wajib)</span>
                    @endif
                </label>
            @endforeach
        </div>
        @error('roles.*')
            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-6 sm:flex-row sm:justify-end">
    <a href="{{ $cancelUrl }}"
       class="inline-flex justify-center rounded-xl border border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">Batal</a>
    <button type="submit"
            class="inline-flex justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">Simpan</button>
</div>