@php
    $admin = $admin ?? null;
    $isEdit = (bool) $admin;
    $fieldClass = 'mt-1.5 w-full rounded-xl border-2 border-[#C9517E]/50 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
@endphp

@if ($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="text-sm font-semibold text-[#542D3D]">Nama</label>
        <input id="name" name="name" type="text" required maxlength="255"
               value="{{ old('name', $admin?->user?->name ?? '') }}" class="{{ $fieldClass }}">
    </div>

    <div>
        <label for="email" class="text-sm font-semibold text-[#542D3D]">Email</label>
        <input id="email" name="email" type="email" required maxlength="255"
               value="{{ old('email', $admin?->user?->email ?? '') }}" class="{{ $fieldClass }}">
    </div>

   <div>
    <label for="phone" class="text-sm font-semibold text-[#542D3D]">Telepon</label>

    <input
        id="phone"
        name="phone"
        type="text"
        maxlength="13"
        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
        value="{{ old('phone', $admin?->user?->phone ?? '') }}"
        class="{{ $fieldClass }}"
    >

    <p class="mt-1 text-xs text-slate-500">Hanya angka, 10-13 digit.</p>
</div>

    <div>
        <label for="id_wilayah" class="text-sm font-semibold text-[#542D3D]">Wilayah Kerja</label>
        <select id="id_wilayah" name="id_wilayah" required class="{{ $fieldClass }}">
            <option value="">Pilih wilayah</option>
            @foreach ($wilayahs as $wilayah)
                <option value="{{ $wilayah->id }}" @selected((string) old('id_wilayah', $admin?->id_wilayah ?? '') === (string) $wilayah->id)>
                    {{ $wilayah->nama_wilayah }} - {{ $wilayah->kelurahan }} ({{ $wilayah->kecamatan }})
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">Satu wilayah hanya boleh memiliki satu Admin.</p>
    </div>

   <div class="sm:col-span-2">
    <label for="password" class="text-sm font-semibold text-[#542D3D]">
        Password {{ $isEdit ? '(kosongkan jika tidak diubah)' : '' }}
    </label>

    <div class="relative mt-1.5">
        <input
            id="password"
            name="password"
            type="password"
            {{ $isEdit ? '' : 'required' }}
            minlength="8"
            autocomplete="new-password"
            class="{{ $fieldClass }} pr-12"
        >

        <button
            type="button"
            onclick="
                const password = document.getElementById('password');
                const icon = this.querySelector('svg');

                if (password.type === 'password') {
                    password.type = 'text';
                    icon.innerHTML = '<path d=\'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z\'/><circle cx=\'12\' cy=\'12\' r=\'3\'/><path d=\'m3 3 18 18\'/>';
                } else {
                    password.type = 'password';
                    icon.innerHTML = '<path d=\'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z\'/><circle cx=\'12\' cy=\'12\' r=\'3\'/>';
                }
            "
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-[#C9517E]"
            aria-label="Tampilkan atau sembunyikan password"
        >
            <svg
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        </button>
    </div>
</div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
    <a href="{{ route('superadmin.admins.index') }}"
       class="inline-flex justify-center rounded-xl border border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">Batal</a>
    <button type="submit"
            class="inline-flex justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D]">
        Simpan
    </button>
</div>
