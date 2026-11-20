@php
    $admin = $admin ?? null;
    $isEdit = (bool) $admin;

    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $pwdClass = str_replace('mt-1.5 ', '', $fieldClass);
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $hintClass = 'mt-1 text-xs text-slate-500';

    $selectedPuskesmas = old('puskesmas_id', $admin?->puskesmas_id ?? '');
    $selectedKecamatan = old('kecamatan_id', $admin?->puskesmas?->kecamatan_id ?? '');

    $puskesmasTerpakai = collect($puskesmasTerpakai ?? []);

    // Dropdown Wilayah Kerja: Puskesmas dikelompokkan per Kecamatan
    $listKecamatan = collect($kecamatans ?? []);
    $listPuskesmas = collect($puskesmas ?? []);
    $idKecamatan   = $listKecamatan->pluck('id');

    $kelompok = $listKecamatan
        ->map(fn ($k) => [
            'label' => 'Kecamatan ' . $k->nama_kecamatan,
            'items' => $listPuskesmas->where('kecamatan_id', $k->id),
        ])
        ->filter(fn ($g) => $g['items']->isNotEmpty())
        ->values();

    $puskesmasLain = $listPuskesmas->reject(fn ($p) => $idKecamatan->contains($p->kecamatan_id));
    if ($puskesmasLain->isNotEmpty()) {
        $kelompok->push(['label' => 'Lainnya', 'items' => $puskesmasLain]);
    }
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

    {{-- Nama --}}
    <div>
        <label for="name" class="{{ $labelClass }}">
            Nama <span class="text-[#C9517E]">*</span>
        </label>

        <input
            id="name"
            name="name"
            type="text"
            required
            maxlength="255"
            placeholder="Nama akun"
            value="{{ old('name', $admin?->user?->name ?? '') }}"
            class="{{ $fieldClass }}"
        >

        @error('name')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror

        <p class="{{ $hintClass }}">
            Nama hanya boleh menggunakan huruf dan spasi.
        </p>
    </div>

    {{-- Email --}}
    <div>
        <label for="email" class="{{ $labelClass }}">
            Email <span class="text-[#C9517E]">*</span>
        </label>

        <input
            id="email"
            name="email"
            type="email"
            required
            maxlength="255"
            placeholder="nama@gmail.com"
            value="{{ old('email', $admin?->user?->email ?? '') }}"
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
            @unless ($isEdit)
                <span class="text-[#C9517E]">*</span>
            @else
                <span class="font-normal text-slate-500">
                    (kosongkan jika tidak diubah)
                </span>
            @endunless
        </label>

        <div class="relative mt-1.5">
            <input
                id="password"
                name="password"
                type="password"
                {{ $isEdit ? '' : 'required' }}
                minlength="8"
                maxlength="8"
                autocomplete="new-password"
                placeholder="Masukkan tepat 8 karakter"
                class="{{ $pwdClass }} pr-12"
            >

            <button
                type="button"
                data-toggle-password
                aria-label="Tampilkan password"
                aria-pressed="false"
                class="absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-500 transition hover:text-[#C9517E] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#C9517E]"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>

        @error('password')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror

        <p class="{{ $hintClass }}">
            Password harus tepat 8 karakter.
        </p>
    </div>

    {{-- Nomor HP --}}
    <div>
        <label for="phone" class="{{ $labelClass }}">
            Nomor HP <span class="text-[#C9517E]">*</span>
        </label>

        <input
            id="phone"
            name="phone"
            type="tel"
            required
            minlength="10"
            maxlength="13"
            inputmode="numeric"
            pattern="[0-9]{10,13}"
            placeholder="08xxxxxxxxxx"
            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13)"
            value="{{ old('phone', $admin?->user?->phone ?? '') }}"
            class="{{ $fieldClass }}"
        >

        @error('phone')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror

        <p class="{{ $hintClass }}">
            Wajib diisi, hanya angka, 10–13 digit.
        </p>
    </div>

    {{-- Wilayah Kerja: Puskesmas per Kecamatan --}}
    <div class="sm:col-span-2">
        <label for="puskesmas_id" class="{{ $labelClass }}">
            Wilayah Kerja <span class="text-[#C9517E]">*</span>
        </label>

        <select
            id="puskesmas_id"
            name="puskesmas_id"
            required
            class="{{ $fieldClass }}"
        >
            <option value="">Pilih Wilayah Kerja</option>

            @foreach ($kelompok as $grup)
                <optgroup label="{{ $grup['label'] }}">
                    @foreach ($grup['items'] as $item)
                        @php
                            $sudahMemilikiAdmin = $puskesmasTerpakai->contains($item->id);
                            $milikAdminIni = $isEdit && (int) $admin->puskesmas_id === (int) $item->id;
                        @endphp

                        <option
                            value="{{ $item->id }}"
                            data-kecamatan="{{ $item->kecamatan_id }}"
                            @disabled($sudahMemilikiAdmin && ! $milikAdminIni)
                            @selected((string) $selectedPuskesmas === (string) $item->id)
                        >
                            Puskesmas {{ $item->nama_puskesmas }}@if ($sudahMemilikiAdmin && ! $milikAdminIni) (Sudah memiliki Admin)@endif
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>

        {{-- Kecamatan ikut terkirim otomatis sesuai Puskesmas yang dipilih --}}
        <input type="hidden" id="kecamatan_id" name="kecamatan_id" value="{{ $selectedKecamatan }}">

        @error('puskesmas_id')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror

        @error('kecamatan_id')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror

        <p class="{{ $hintClass }}">
            Pilih Puskesmas sesuai kecamatannya. Satu Puskesmas hanya boleh memiliki satu Admin.
        </p>
    </div>
</div>

{{-- Tombol --}}
<div class="mt-6 flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
    <a
        href="{{ route('superadmin.admins.index') }}"
        class="inline-flex w-full justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] sm:w-auto"
    >
        Batal
    </a>

    <button
        type="submit"
        class="inline-flex w-full justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 sm:w-auto"
    >
        Simpan
    </button>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Kecamatan (tersembunyi) selalu mengikuti Puskesmas yang dipilih
    const puskesmasSelect = document.getElementById('puskesmas_id');
    const kecamatanHidden = document.getElementById('kecamatan_id');

    function sinkronKecamatan() {
        const opsi = puskesmasSelect.selectedOptions[0];
        kecamatanHidden.value = opsi && opsi.dataset.kecamatan ? opsi.dataset.kecamatan : '';
    }

    puskesmasSelect.addEventListener('change', sinkronKecamatan);
    sinkronKecamatan();

    // Tampilkan/sembunyikan password
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = btn.querySelector('svg');
            const eye = '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>';
            const hidden = input.type === 'password';

            input.type = hidden ? 'text' : 'password';
            icon.innerHTML = hidden
                ? eye + '<path d="m3 3 18 18"/>'
                : eye;

            btn.setAttribute('aria-pressed', String(hidden));
            btn.setAttribute(
                'aria-label',
                hidden ? 'Sembunyikan password' : 'Tampilkan password'
            );
        });
    });

    // Nama hanya boleh mengandung huruf dan spasi.
    const nameInput = document.getElementById('name');

    nameInput?.addEventListener('input', function () {
        this.value = this.value.replace(/[^\p{L}\s]/gu, '');
    });
});
</script>
@endpush