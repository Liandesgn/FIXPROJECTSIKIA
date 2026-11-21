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

    // Tambahkan kata "Puskesmas" hanya jika namanya belum memuatnya
    $labelPuskesmas = function ($nama) {
        $nama = trim((string) $nama);

        return \Illuminate\Support\Str::startsWith(\Illuminate\Support\Str::lower($nama), 'puskesmas')
            ? $nama
            : 'Puskesmas ' . $nama;
    };

    // Tambahkan kata "Desa" / "Kelurahan" hanya jika namanya belum memuatnya
    $labelKelurahan = function ($kd) {
        $nama  = trim((string) $kd->nama_kelurahan_desa);
        $jenis = ucfirst(trim((string) $kd->jenis));

        if ($jenis === '' || \Illuminate\Support\Str::startsWith(\Illuminate\Support\Str::lower($nama), \Illuminate\Support\Str::lower($jenis))) {
            return $nama;
        }

        return $jenis . ' ' . $nama;
    };

    // Tingkat 1: daftar Puskesmas
    $daftarPuskesmas = $listWilayah
        ->map(fn ($w) => $w->puskesmas)
        ->filter()
        ->unique('id')
        ->sortBy('nama_puskesmas')
        ->values();

    $itemsPuskesmas = $daftarPuskesmas
        ->map(fn ($p) => ['value' => $p->id, 'label' => $labelPuskesmas($p->nama_puskesmas), 'parent' => ''])
        ->values()
        ->all();

    // Tingkat 2: Kelurahan/Desa per Puskesmas (nilai yang dikirim = wilayah_kerja_id)
    $itemsKelurahan = $listWilayah
        ->filter(fn ($w) => $w->puskesmas)
        ->map(function ($w) use ($labelKelurahan) {
            $kd = $w->kelurahanDesa;

            return [
                'value'  => $w->id,
                'label'  => $kd ? $labelKelurahan($kd) : 'Wilayah #' . $w->id,
                'parent' => $w->puskesmas->id,
            ];
        })
        ->sortBy('label')
        ->values()
        ->all();

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

    {{-- Wilayah Kerja: Puskesmas lalu Kelurahan/Desa (satu kolom) --}}
    <div class="sm:col-span-2">
        @include('partials.dropdown-bertingkat', [
            'ddId'          => 'wilayah_kerja',
            'ddLabel'       => 'Wilayah Kerja',
            'ddPlaceholder' => 'Pilih Puskesmas lalu Kelurahan/Desa',
            'ddHint'        => 'Pilih Puskesmas terlebih dahulu, lalu pilih Kelurahan/Desa penugasan.',
            'ddLevels'      => [
                ['title' => 'Pilih Puskesmas', 'items' => $itemsPuskesmas],
                ['title' => 'Pilih Kelurahan / Desa', 'items' => $itemsKelurahan],
            ],
            'ddHidden'      => [
                ['name' => 'wilayah_kerja_id', 'level' => 1, 'value' => $wilayahTerpilih],
            ],
            'ddSelected'    => [$puskesmasTerpilih, $wilayahTerpilih],
        ])
    </div>

</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Tampilkan/sembunyikan password
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