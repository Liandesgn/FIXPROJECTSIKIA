@php
    $selectedRole = old('role', $user?->primaryRole()?->value);
    $selectedStatus = old('status', $user?->status?->value ?? \App\Enums\UserStatusEnum::Active->value);
    $selectedWilayah = old('wilayah_kerja_id', $user?->wilayah_kerja_id);
    $inputClass = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200';
    $labelClass = 'mb-1 block text-sm font-medium text-slate-700';
    $errorClass = 'mt-1 text-xs text-red-600';
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="name" class="{{ $labelClass }}">Nama Lengkap</label>
        <input id="name" type="text" name="name" value="{{ old('name', $user?->name) }}" required class="{{ $inputClass }}">
        @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="{{ $labelClass }}">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $user?->email) }}" required class="{{ $inputClass }}">
        @error('email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="phone" class="{{ $labelClass }}">Nomor HP</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $user?->phone) }}" class="{{ $inputClass }}">
        @error('phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="role" class="{{ $labelClass }}">Role</label>
        <select id="role" name="role" required class="{{ $inputClass }}">
            <option value="">-- Pilih Role --</option>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected($selectedRole === $role)>
                    {{ \App\Enums\RoleEnum::from($role)->label() }}
                </option>
            @endforeach
        </select>
        @error('role') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="wilayah_kerja_id" class="{{ $labelClass }}">
            Wilayah Kerja <span class="font-normal text-slate-400">(wajib untuk Bidan/Kader)</span>
        </label>
        <select id="wilayah_kerja_id" name="wilayah_kerja_id" class="{{ $inputClass }}">
            <option value="">-- Tidak ada --</option>
            @foreach ($wilayahs as $wilayah)
                <option value="{{ $wilayah->id }}" @selected((string) $selectedWilayah === (string) $wilayah->id)>
                    {{ $wilayah->nama }}
                </option>
            @endforeach
        </select>
        @error('wilayah_kerja_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="status" class="{{ $labelClass }}">Status</label>
        <select id="status" name="status" class="{{ $inputClass }}">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        @error('status') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password" class="{{ $labelClass }}">
            Password @if ($user) <span class="font-normal text-slate-400">(kosongkan bila tidak diubah)</span> @endif
        </label>
        <input id="password" type="password" name="password" @required(! $user) class="{{ $inputClass }}">
        @error('password') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password_confirmation" class="{{ $labelClass }}">Konfirmasi Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" @required(! $user) class="{{ $inputClass }}">
    </div>
</div>
