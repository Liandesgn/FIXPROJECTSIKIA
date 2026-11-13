{{-- Isi form permission. Parameter: $permission (model|null), $roles, $locked (opsional) --}}
@php
    $permission = $permission ?? null;
    $roles      = $roles ?? collect();
    $locked     = $locked ?? false;
    $selected   = old('roles', $permission ? $permission->roles->pluck('name')->all() : []);

    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
@endphp

<div class="space-y-5">

    <div>
        <label for="name" class="{{ $labelClass }}">Nama Permission <span class="text-[#C9517E]">*</span></label>
        <input id="name" type="text" name="name"
               value="{{ old('name', $permission->name ?? '') }}"
               @readonly($locked)
               required placeholder="contoh: ibu.view"
               class="{{ $fieldClass }} {{ $locked ? 'cursor-not-allowed bg-slate-100' : 'bg-white' }}">

        @error('name')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror

        @if ($locked)
            <p class="mt-1.5 text-xs font-medium text-slate-600">Permission inti, namanya tidak bisa diubah.</p>
        @endif
    </div>

    <div>
        <p class="{{ $labelClass }}">Role yang boleh memakai permission ini</p>

        <div class="mt-2 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
            @foreach ($roles as $role)
                @php $wajib = $locked && $role->name === 'admin'; @endphp

                <label class="flex items-center gap-3 rounded-xl border border-[#E99AB7] bg-white px-3.5 py-3 text-sm font-semibold text-[#542D3D] transition has-[:checked]:border-[#C9517E] has-[:checked]:bg-[#FCE7F0] {{ $wajib ? '' : 'cursor-pointer hover:bg-[#FCE7F0]' }}">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                           @checked($wajib || in_array($role->name, $selected, true))
                           @if ($wajib) onclick="return false" @endif
                           class="h-5 w-5 rounded border-slate-400 text-[#C9517E] focus:ring-[#C9517E]">

                    <span>{{ ucwords(str_replace('_', ' ', $role->name)) }}</span>

                    @if ($wajib)
                        <span class="text-xs font-medium text-slate-600">(wajib)</span>
                    @endif
                </label>
            @endforeach
        </div>

        @error('roles')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
        @error('roles.*')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
    <a href="{{ route('superadmin.permissions.index') }}"
       class="inline-flex w-full justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] sm:w-auto">
        Batal
    </a>
    <button type="submit"
            class="inline-flex w-full justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 sm:w-auto">
        Simpan
    </button>
</div>