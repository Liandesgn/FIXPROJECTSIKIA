@php
    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $lockedClass = str_replace('bg-white', 'cursor-not-allowed bg-slate-100 text-slate-700', $fieldClass);
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';

    // Wilayah otomatis: saat edit pakai wilayah akun itu, saat tambah pakai wilayah Admin yang login
    $akun        = $user?->bidan ?? $user?->kader;
    $wilayahAuto = $akun?->wilayahKerja
        ?? auth()->user()?->admin?->wilayahKerja
        ?? collect($wilayahs ?? [])->first();

    $wilayahTeks = $wilayahAuto
        ? 'Desa ' . $wilayahAuto->kelurahan . ($wilayahAuto->kecamatan ? ', Kec. ' . $wilayahAuto->kecamatan : '')
        : '';
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="{{ $labelClass }}">Nama Lengkap <span class="text-[#C9517E]">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $user?->name) }}"
               placeholder="Nama lengkap" required class="{{ $fieldClass }}">
        @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="{{ $labelClass }}">Email <span class="text-[#C9517E]">*</span></label>
        <input id="email" type="email" name="email" value="{{ old('email', $user?->email) }}"
               placeholder="nama@gmail.com" required class="{{ $fieldClass }}">
        @error('email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="phone" class="{{ $labelClass }}">Nomor HP</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $user?->phone) }}"
               inputmode="numeric" maxlength="13" pattern="[0-9]{10,13}" title="Hanya angka, 10-13 digit"
               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
               placeholder="08xxxxxxxxxx" class="{{ $fieldClass }}">
        @error('phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    {{-- Wilayah kerja: terisi otomatis, tidak bisa diubah --}}
    <div>
        <label for="wilayah_tampil" class="{{ $labelClass }}">Wilayah Kerja <span class="text-[#C9517E]">*</span></label>
        <input type="hidden" name="wilayah_kerja_id" value="{{ $wilayahAuto?->id }}">
        <input id="wilayah_tampil" type="text" value="{{ $wilayahTeks }}" readonly tabindex="-1"
               placeholder="Wilayah belum tersedia" class="{{ $lockedClass }}">
        <p class="mt-1 text-xs font-medium text-slate-600">Terisi otomatis sesuai wilayah Anda.</p>
        @error('wilayah_kerja_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="password" class="{{ $labelClass }}">
            Password @unless ($user) <span class="text-[#C9517E]">*</span> @endunless
            @if ($user) <span class="font-normal text-slate-500">(kosongkan bila tidak diubah)</span> @endif
        </label>
        <input id="password" type="password" name="password" placeholder="Minimal 8 karakter"
               @required(! $user) class="{{ $fieldClass }}">
        @error('password') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>
</div>

@once
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const eye = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
        const eyeOff = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6 0 9.5 7 9.5 7a15 15 0 0 1-3.1 3.9M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.5 7 9.5 7c1 0 1.9-.2 2.8-.5"/></svg>';

        document.querySelectorAll('input[type="password"]').forEach(function (input) {
            if (input.dataset.eye) return;
            input.dataset.eye = '1';

            const wrap = document.createElement('div');
            wrap.className = 'relative mt-1.5';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);
            input.classList.remove('mt-1.5');
            input.classList.add('pr-12');

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-500 transition hover:text-[#C9517E] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#C9517E]';
            btn.setAttribute('aria-label', 'Tampilkan password');
            btn.setAttribute('aria-pressed', 'false');
            btn.innerHTML = eye;
            btn.addEventListener('click', function () {
                const hidden = input.type === 'password';
                input.type = hidden ? 'text' : 'password';
                btn.innerHTML = hidden ? eyeOff : eye;
                btn.setAttribute('aria-pressed', String(hidden));
                btn.setAttribute('aria-label', hidden ? 'Sembunyikan password' : 'Tampilkan password');
            });
            wrap.appendChild(btn);
        });
    });
    </script>
    @endpush
@endonce