
@php
    $admin  = $admin ?? null;
    $isEdit = (bool) $admin;

    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $pwdClass   = str_replace('mt-1.5 ', '', $fieldClass);
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $hintClass  = 'mt-1 text-xs text-slate-500';
@endphp

{{-- Pesan gagal ditampilkan sebagai popup SweetAlert dari layout --}}

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="{{ $labelClass }}">Nama <span class="text-[#C9517E]">*</span></label>
        <input id="name" name="name" type="text" required maxlength="255" placeholder="Nama akun"
               value="{{ old('name', $admin?->user?->name ?? '') }}" class="{{ $fieldClass }}">
        @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="{{ $labelClass }}">Email <span class="text-[#C9517E]">*</span></label>
        <input id="email" name="email" type="email" required maxlength="255" placeholder="nama@gmail.com"
               value="{{ old('email', $admin?->user?->email ?? '') }}" class="{{ $fieldClass }}">
        @error('email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="phone" class="{{ $labelClass }}">Nomor HP <span class="text-[#C9517E]">*</span></label>
        <input id="phone" name="phone" type="tel" required
               minlength="10" maxlength="13"
               inputmode="numeric" pattern="[0-9]{10,13}"
               placeholder="08xxxxxxxxxx"
               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13)"
               value="{{ old('phone', $admin?->user?->phone ?? '') }}"
               class="{{ $fieldClass }}">
        @error('phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        <p class="{{ $hintClass }}">Wajib diisi, hanya angka, 10–13 digit.</p>
    </div>

    <div>
        <label for="id_wilayah" class="{{ $labelClass }}">Wilayah Kerja <span class="text-[#C9517E]">*</span></label>
        <select id="id_wilayah" name="id_wilayah" required class="{{ $fieldClass }}">
            <option value="">Pilih wilayah</option>
            @foreach ($wilayahs as $wilayah)
                <option value="{{ $wilayah->id }}" @selected((string) old('id_wilayah', $admin?->id_wilayah ?? '') === (string) $wilayah->id)>
                    {{ $wilayah->nama_wilayah }} - {{ $wilayah->kelurahan }} ({{ $wilayah->kecamatan }})
                </option>
            @endforeach
        </select>
        @error('id_wilayah') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        <p class="{{ $hintClass }}">Satu wilayah hanya boleh memiliki satu Admin.</p>
    </div>

    <div class="sm:col-span-2">
        <label for="password" class="{{ $labelClass }}">
            Password @unless ($isEdit)<span class="text-[#C9517E]">*</span>@endunless
            @if ($isEdit) <span class="font-normal text-slate-500">(kosongkan jika tidak diubah)</span> @endif
        </label>

        <div class="relative mt-1.5">
            <input id="password" name="password" type="password"
                   {{ $isEdit ? '' : 'required' }}
                   minlength="8" maxlength="8"
                   autocomplete="new-password"
                   placeholder="Masukkan tepat 8 karakter"
                   class="{{ $pwdClass }} pr-12">

            <button type="button" data-toggle-password aria-label="Tampilkan password" aria-pressed="false"
                    class="absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-500 transition hover:text-[#C9517E] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#C9517E]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>
        @error('password') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        <p class="{{ $hintClass }}">Password harus tepat 8 karakter.</p>
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
    <a href="{{ route('superadmin.admins.index') }}"
       class="inline-flex w-full justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] sm:w-auto">
        Batal
    </a>
    <button type="submit"
            class="inline-flex w-full justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 sm:w-auto">
        Simpan
    </button>
</div>

@push('scripts')
<script>
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon  = btn.querySelector('svg');
            const eye   = '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>';
            const hidden = input.type === 'password';

            input.type = hidden ? 'text' : 'password';
            icon.innerHTML = hidden ? eye + '<path d="m3 3 18 18"/>' : eye;
            btn.setAttribute('aria-pressed', String(hidden));
            btn.setAttribute('aria-label', hidden ? 'Sembunyikan password' : 'Tampilkan password');
        });
    });
</script>
@endpush
