@php
    $inputClass = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200';
    $labelClass = 'mb-1 block text-sm font-medium text-slate-700';
    $errorClass = 'mt-1 text-xs text-red-600';
    $selectedWilayah = old('wilayah_kerja_id', $user?->wilayah_kerja_id);
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
        <label for="wilayah_kerja_id" class="{{ $labelClass }}">Wilayah Kerja</label>
        <select id="wilayah_kerja_id" name="wilayah_kerja_id" required class="{{ $inputClass }}">
            <option value="">-- Pilih Wilayah --</option>
            @foreach ($wilayahs->groupBy('kecamatan') as $kecamatan => $desas)
                <optgroup label="Kec. {{ $kecamatan ?: '-' }}">
                    @foreach ($desas as $wilayah)
                        <option value="{{ $wilayah->id }}" @selected((string) $selectedWilayah === (string) $wilayah->id)>
                            Desa {{ $wilayah->desa }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('wilayah_kerja_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
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
