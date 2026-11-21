@php
    $profil = $ibu?->ibu;

    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $lockedClass = str_replace('bg-white', 'cursor-not-allowed bg-slate-100 text-slate-700', $fieldClass);
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $star = '<span class="text-[#C9517E]">*</span>';

    $selectedStatus = old('status_kehamilan', $profil?->status_kehamilan?->value);
    $isHamilNow = $selectedStatus === \App\Enums\StatusKehamilanEnum::Hamil->value;

    // ===== Data alamat bertingkat =====
    $kecamatan = $kecamatan ?? null;
    $listKecamatan = isset($kecamatans)
        ? collect($kecamatans)
        : collect([$kecamatan])->filter();

    $daftarKelurahan = collect($kelurahans ?? []);
    $kelurahanAwal   = $selectedKelurahanId ?? '';
    $rwAwal          = $selectedRwId ?? '';
    $rtAwal          = old('rt_id', $selectedRtId ?? '');

    // Kecamatan terpilih: ikut kelurahan yang tersimpan, kalau tidak ada pakai kecamatan wilayah Admin
    $kelurahanDipilih  = $daftarKelurahan->first(fn ($k) => (string) $k->id === (string) $kelurahanAwal);
    $kecamatanTerpilih = ($kelurahanDipilih?->kecamatan_id ?: null)
        ?? $kecamatan?->id
        ?? $listKecamatan->first()?->id
        ?? '';

    $kecamatanTunggal = $listKecamatan->count() === 1 ? (string) $listKecamatan->first()->id : '';

    $itemsKecamatan = $listKecamatan
        ->map(fn ($k) => ['value' => $k->id, 'label' => $k->nama_kecamatan, 'parent' => ''])
        ->values()
        ->all();

    $itemsKelurahan = $daftarKelurahan
        ->map(fn ($k) => [
            'value'  => $k->id,
            'label'  => $k->nama_kelurahan_desa,
            'parent' => ($k->kecamatan_id ?? null) ?: $kecamatanTunggal,
        ])
        ->values()
        ->all();
@endphp

{{-- A. Data akun --}}
<div>
    <h3 class="mb-3 border-b border-[#F1D5E0] pb-2 text-sm font-bold uppercase tracking-wide text-[#C9517E]">
        A. Data Akun Ibu
    </h3>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label for="name" class="{{ $labelClass }}">Nama Lengkap {!! $star !!}</label>
            <input id="name" type="text" name="name"
                value="{{ old('name', $ibu?->name) }}" required
                placeholder="Nama lengkap ibu" class="{{ $fieldClass }}">
            @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="{{ $labelClass }}">Email {!! $star !!}</label>
            <input id="email" type="email" name="email"
                value="{{ old('email', $ibu?->email) }}" required
                placeholder="nama@gmail.com" class="{{ $fieldClass }}">
            @error('email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="{{ $labelClass }}">Nomor HP {!! $star !!}</label>
            <input id="phone" type="tel" name="phone"
                value="{{ old('phone', $ibu?->phone) }}"
                required inputmode="numeric" minlength="10" maxlength="13"
                pattern="[0-9]{10,13}"
                title="Nomor HP harus berupa angka dengan panjang 10–13 digit"
                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13)"
                placeholder="08xxxxxxxxxx" class="{{ $fieldClass }}">
            @error('phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="{{ $labelClass }}">
                Password @unless ($ibu) {!! $star !!} @endunless
                @if ($ibu)
                    <span class="font-normal text-slate-500">(kosongkan bila tidak diubah)</span>
                @endif
            </label>
            <input id="password" type="password" name="password"
                @required(!$ibu) minlength="8" maxlength="8"
                placeholder="Masukkan tepat 8 karakter" class="{{ $fieldClass }}">
            @error('password') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-slate-500">
                @if ($ibu)
                    Kosongkan jika tidak ingin mengganti password. Jika diisi, harus tepat 8 karakter.
                @else
                    Password harus terdiri dari tepat 8 karakter.
                @endif
            </p>
        </div>
    </div>
</div>

{{-- B. Data ibu --}}
<div>
    <h3 class="mb-3 border-b border-[#F1D5E0] pb-2 text-sm font-bold uppercase tracking-wide text-[#C9517E]">
        B. Data Ibu
    </h3>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label for="nik" class="{{ $labelClass }}">NIK {!! $star !!}</label>
            <input id="nik" type="text" name="nik"
                value="{{ old('nik', $profil?->nik) }}"
                maxlength="16" inputmode="numeric" required
                placeholder="16 digit NIK" class="{{ $fieldClass }}">
            @error('nik') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="tempat_lahir" class="{{ $labelClass }}">Tempat Lahir {!! $star !!}</label>
            <input id="tempat_lahir" type="text" name="tempat_lahir"
                value="{{ old('tempat_lahir', $profil?->tempat_lahir) }}"
                required placeholder="Kota / kabupaten kelahiran"
                class="{{ $fieldClass }}">
            @error('tempat_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="tgl_lahir" class="{{ $labelClass }}">Tanggal Lahir {!! $star !!}</label>
            <input id="tgl_lahir" type="date" name="tgl_lahir"
                value="{{ old('tgl_lahir', $profil?->tgl_lahir?->format('Y-m-d')) }}"
                required class="{{ $fieldClass }}">
            @error('tgl_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="status_kehamilan" class="{{ $labelClass }}">
                Status Kehamilan {!! $star !!}
            </label>
            <select id="status_kehamilan" name="status_kehamilan"
                required class="{{ $fieldClass }}">
                <option value="">-- Pilih Status --</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}"
                        @selected($selectedStatus === $status->value)>
                        {{ $status->value === 'hamil' ? 'Hamil' : 'Melahirkan' }}
                    </option>
                @endforeach
            </select>
            @error('status_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        {{-- Usia kehamilan --}}
        <div data-hamil-fields class="{{ $isHamilNow ? '' : 'hidden' }}">
            <label for="usia_kehamilan" class="{{ $labelClass }}">
                Usia Kehamilan (minggu) {!! $star !!}
            </label>
            <input id="usia_kehamilan" type="number" name="usia_kehamilan"
                min="1" max="42"
                value="{{ old('usia_kehamilan', $profil?->usia_kehamilan) }}"
                placeholder="Masukkan usia kehamilan (minggu)"
                class="{{ $fieldClass }}">
            @error('usia_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        {{-- HPL otomatis, tidak ada kalender --}}
        <div data-hamil-fields class="{{ $isHamilNow ? '' : 'hidden' }}">

<div data-hamil>
    <label for="hpl" class="{{ $labelClass }}">
        HPL <span class="text-[#C9517E]">*</span>
    </label>

    <input
        id="hpl"
        type="text"
        name="hpl"
        value="{{ old('hpl', $ibu?->hpl?->format('Y-m-d')) }}"
        placeholder="HPL dihitung otomatis"
        readonly
        class="{{ $lockedClass }}"
    >

    <p class="mt-1 text-xs text-slate-500">
        HPL otomatis dihitung berdasarkan usia kehamilan.
    </p>

    @error('hpl')
        <p class="{{ $errorClass }}">{{ $message }}</p>
    @enderror
</div>


        </div>
    </div>
</div>


{{-- C. Alamat Domisili Ibu: satu kolom dropdown bertingkat --}}
<div>
    <h3 class="mb-3 border-b border-[#F1D5E0] pb-2 text-sm font-bold uppercase tracking-wide text-[#C9517E]">
        C. Alamat Domisili Ibu
    </h3>

    @include('partials.dropdown-bertingkat', [
        'ddId'          => 'alamat_ibu',
        'ddLabel'       => 'Alamat',
        'ddPlaceholder' => 'Pilih Kecamatan, Kelurahan/Desa, RW, lalu RT',
        'ddLevels'      => [
            ['title' => 'Pilih Kecamatan', 'items' => $itemsKecamatan],
            ['title' => 'Pilih Kelurahan / Desa', 'items' => $itemsKelurahan],
            [
                'title' => 'Pilih RW',
                'url'   => route('admin.ibu.alamat.rw'),
                'param' => 'kelurahan_desa_id',
                'map'   => ['value' => 'id', 'label' => 'nomor_rw', 'prefix' => 'RW '],
            ],
            [
                'title' => 'Pilih RT',
                'url'   => route('admin.ibu.alamat.rt'),
                'param' => 'rw_id',
                'map'   => ['value' => 'id', 'label' => 'nomor_rt', 'prefix' => 'RT '],
            ],
        ],
        'ddHidden'      => [
            ['name' => 'rt_id', 'level' => 3, 'value' => $rtAwal],
        ],
        'ddSelected'    => [$kecamatanTerpilih, $kelurahanAwal, $rwAwal, $rtAwal],
    ])
</div>


@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Nama hanya boleh mengandung huruf dan spasi.
                const nameInput = document.getElementById('name');

                if (nameInput) {
                    nameInput.value = nameInput.value.replace(/[^\p{L} ]/gu, '');
                    nameInput.addEventListener('input', function () {
                        this.value = this.value.replace(/[^\p{L} ]/gu, '');
                    });
                }

                // ========================================
                // PERHITUNGAN HPL OTOMATIS
                // ========================================
                const statusKehamilan = document.getElementById('status_kehamilan');
                const usiaKehamilan = document.getElementById('usia_kehamilan');
                const hpl = document.getElementById('hpl');
                const TARGET_KEHAMILAN = 40;

                function hitungHPL() {
                    if (!usiaKehamilan || !hpl) return;

                    const usia = Number(usiaKehamilan.value);

                    if (!Number.isInteger(usia) || usia < 1 || usia > 42) {
                        hpl.value = '';
                        return;
                    }

                    const sisaHari = (TARGET_KEHAMILAN - usia) * 7;
                    const tanggal = new Date();
                    tanggal.setHours(12, 0, 0, 0);
                    tanggal.setDate(tanggal.getDate() + sisaHari);

                    const tahun = tanggal.getFullYear();
                    const bulan = String(tanggal.getMonth() + 1).padStart(2, '0');
                    const hari = String(tanggal.getDate()).padStart(2, '0');

                    hpl.value = `${hari}-${bulan}-${tahun}`;
                }

                function toggleHamilFields() {
                    const isHamil = statusKehamilan?.value === 'hamil';

                    document.querySelectorAll('[data-hamil-fields]').forEach(wrapper => {
                        wrapper.classList.toggle('hidden', !isHamil);
                    });

                    if (usiaKehamilan) {
                        usiaKehamilan.required = isHamil;
                    }

                    if (!isHamil && hpl) {
                        hpl.value = '';
                    }

                    if (isHamil) {
                        hitungHPL();
                    }
                }

                statusKehamilan?.addEventListener('change', toggleHamilFields);
                usiaKehamilan?.addEventListener('input', hitungHPL);
                usiaKehamilan?.addEventListener('change', hitungHPL);

                toggleHamilFields();
            });
        </script>
    @endpush
@endonce