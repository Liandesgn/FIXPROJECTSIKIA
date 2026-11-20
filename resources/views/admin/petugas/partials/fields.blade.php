@php
    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';

    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';

    $akun = $user?->bidan ?? $user?->kader;

    // Saat edit, tampilkan wilayah lama.
    // Saat tambah, Admin harus memilih wilayah sendiri.
    $wilayahTerpilih = old(
        'wilayah_kerja_id',
        $akun?->wilayah_kerja_id ?? ''
    );

    $listWilayah = collect($wilayahs ?? []);

    // Daftar Puskesmas (dropdown pertama)
    $daftarPuskesmas = $listWilayah
        ->map(fn ($w) => $w->puskesmas)
        ->filter()
        ->unique('id')
        ->sortBy('nama_puskesmas')
        ->values();

    // Puskesmas milik wilayah yang sedang dipilih (untuk Edit atau validasi gagal)
    $wilayahDipilih    = $listWilayah->first(fn ($w) => (string) $w->id === (string) $wilayahTerpilih);
    $puskesmasTerpilih = $wilayahDipilih?->puskesmas?->id ?? '';
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

    {{-- Nama --}}
    <div>
        <label for="name" class="{{ $labelClass }}">
            Nama Lengkap <span class="text-[#C9517E]">*</span>
        </label>

        <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $user?->name) }}"
            placeholder="Nama lengkap"
            required
            maxlength="255"
            class="{{ $fieldClass }}"
        >

        @error('name')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    {{-- Email --}}
    <div>
        <label for="email" class="{{ $labelClass }}">
            Email <span class="text-[#C9517E]">*</span>
        </label>

        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email', $user?->email) }}"
            placeholder="nama@gmail.com"
            required
            maxlength="255"
            class="{{ $fieldClass }}"
        >

        @error('email')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    {{-- Password --}}
    <div>
        <label for="password" class="{{ $labelClass }}">
            Password

            @unless ($user)
                <span class="text-[#C9517E]">*</span>
            @endunless

            @if ($user)
                <span class="font-normal text-slate-500">
                    (kosongkan bila tidak diubah)
                </span>
            @endif
        </label>

        <input
            id="password"
            type="password"
            name="password"
            placeholder="Masukkan tepat 8 karakter"
            {{ $user ? '' : 'required' }}
            minlength="8"
            maxlength="8"
            autocomplete="new-password"
            class="{{ $fieldClass }}"
        >

        <p class="mt-1 text-xs font-medium text-slate-600">
            Password harus tepat 8 karakter.
        </p>

        @error('password')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    {{-- Nomor HP --}}
    <div>
        <label for="phone" class="{{ $labelClass }}">
            Nomor HP <span class="text-[#C9517E]">*</span>
        </label>

        <input
            id="phone"
            type="tel"
            name="phone"
            value="{{ old('phone', $user?->phone) }}"
            required
            minlength="10"
            maxlength="13"
            inputmode="numeric"
            pattern="[0-9]{10,13}"
            title="Nomor HP wajib berupa angka sebanyak 10 sampai 13 digit"
            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13)"
            placeholder="08xxxxxxxxxx"
            class="{{ $fieldClass }}"
        >

        <p class="mt-1 text-xs font-medium text-slate-600">
            Wajib diisi, hanya angka, 10–13 digit.
        </p>

        @error('phone')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

    {{-- Wilayah Kerja: pilih Puskesmas dulu --}}
    <div class="sm:col-span-2">
        <label for="puskesmas_pilih" class="{{ $labelClass }}">
            Wilayah Kerja <span class="text-[#C9517E]">*</span>
        </label>

        <select
            id="puskesmas_pilih"
            required
            class="{{ $fieldClass }}"
        >
            <option value="">-- Pilih Puskesmas --</option>

            @foreach ($daftarPuskesmas as $item)
                <option
                    value="{{ $item->id }}"
                    @selected((string) $puskesmasTerpilih === (string) $item->id)
                >
                    Puskesmas {{ $item->nama_puskesmas }}
                </option>
            @endforeach
        </select>

        <p class="mt-1 text-xs font-medium text-slate-600">
            Pilih Puskesmas terlebih dahulu, lalu pilih Kelurahan/Desa penugasan.
        </p>
    </div>

    {{-- Kelurahan/Desa: muncul setelah Puskesmas dipilih --}}
    <div id="kelurahanWrap" class="sm:col-span-2 {{ $puskesmasTerpilih ? '' : 'hidden' }}">
        <label for="wilayah_kerja_id" class="{{ $labelClass }}">
            Kelurahan / Desa <span class="text-[#C9517E]">*</span>
        </label>

        <select
            id="wilayah_kerja_id"
            name="wilayah_kerja_id"
            class="{{ $fieldClass }}"
        >
            <option value="">-- Pilih Kelurahan / Desa --</option>

            @foreach ($listWilayah as $wilayah)
                @php
                    $kelurahanDesa = $wilayah->kelurahanDesa;
                    $labelKelurahan = $kelurahanDesa
                        ? ucfirst($kelurahanDesa->jenis) . ' ' . $kelurahanDesa->nama_kelurahan_desa
                        : 'Wilayah #' . $wilayah->id;
                @endphp

                <option
                    value="{{ $wilayah->id }}"
                    data-puskesmas="{{ $wilayah->puskesmas?->id }}"
                    @selected((string) $wilayahTerpilih === (string) $wilayah->id)
                >
                    {{ $labelKelurahan }}
                </option>
            @endforeach
        </select>

        @error('wilayah_kerja_id')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>

</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // ===== Wilayah Kerja: Puskesmas lalu Kelurahan/Desa =====
                const puskesmasSelect  = document.getElementById('puskesmas_pilih');
                const kelurahanSelect  = document.getElementById('wilayah_kerja_id');
                const kelurahanWrap    = document.getElementById('kelurahanWrap');

                if (puskesmasSelect && kelurahanSelect && kelurahanWrap) {
                    const semuaKelurahan = Array.from(kelurahanSelect.options)
                        .filter(function (o) { return o.value; })
                        .map(function (o) {
                            return { value: o.value, label: o.textContent.trim(), puskesmas: o.dataset.puskesmas || '' };
                        });

                    const dipilihAwal = kelurahanSelect.value;

                    function isiKelurahan(puskesmasId, dipilih) {
                        kelurahanSelect.innerHTML = '';
                        kelurahanSelect.add(new Option('-- Pilih Kelurahan / Desa --', ''));

                        semuaKelurahan
                            .filter(function (k) { return k.puskesmas === String(puskesmasId); })
                            .forEach(function (k) { kelurahanSelect.add(new Option(k.label, k.value)); });

                        kelurahanSelect.value = dipilih || '';
                    }

                    function tampilkan() {
                        const ada = puskesmasSelect.value !== '';
                        kelurahanWrap.classList.toggle('hidden', !ada);
                        kelurahanSelect.disabled = !ada;
                        kelurahanSelect.required = ada;
                    }

                    puskesmasSelect.addEventListener('change', function () {
                        isiKelurahan(this.value, '');
                        tampilkan();
                    });

                    isiKelurahan(puskesmasSelect.value, dipilihAwal);
                    tampilkan();
                }

                // ===== Tampilkan/sembunyikan password =====
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
                        btn.setAttribute(
                            'aria-label',
                            hidden ? 'Sembunyikan password' : 'Tampilkan password'
                        );
                    });

                    wrap.appendChild(btn);
                });
            });
        </script>
    @endpush
@endonce