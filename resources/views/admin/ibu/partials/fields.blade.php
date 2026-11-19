
@php
    $profil = $ibu?->ibu;

    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $lockedClass = str_replace('bg-white', 'cursor-not-allowed bg-slate-100 text-slate-700', $fieldClass);
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $star = '<span class="text-[#C9517E]">*</span>';

    $selectedStatus = old('status_kehamilan', $profil?->status_kehamilan?->value);
    $isHamilNow = $selectedStatus === \App\Enums\StatusKehamilanEnum::Hamil->value;
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


{{-- C. Alamat Domisili Ibu --}}
<div>
    <h3 class="mb-3 border-b border-[#F1D5E0] pb-2 text-sm font-bold uppercase tracking-wide text-[#C9517E]">
        C. Alamat Domisili Ibu
    </h3>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="{{ $labelClass }}">Kecamatan</label>
            <input
                type="text"
                value="{{ $kecamatan?->nama_kecamatan }}"
                readonly
                class="{{ $lockedClass }}"
            >
        </div>

        <div>
            <label for="kelurahan_desa_id" class="{{ $labelClass }}">
                Kelurahan / Desa {!! $star !!}
            </label>
            <select
                id="kelurahan_desa_id"
                class="{{ $fieldClass }}"
                data-selected="{{ $selectedKelurahanId }}"
            >
                <option value="">-- Pilih Kelurahan / Desa --</option>
                @foreach ($kelurahans as $kelurahan)
                    <option
                        value="{{ $kelurahan->id }}"
                        @selected((string) $selectedKelurahanId === (string) $kelurahan->id)
                    >
                        {{ $kelurahan->nama_kelurahan_desa }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="rw_id" class="{{ $labelClass }}">RW {!! $star !!}</label>
            <select
                id="rw_id"
                class="{{ $fieldClass }}"
                data-selected="{{ $selectedRwId }}"
            >
                <option value="">-- Pilih RW --</option>
            </select>
        </div>

        <div>
            <label for="rt_id" class="{{ $labelClass }}">RT {!! $star !!}</label>
            <select
                id="rt_id"
                name="rt_id"
                required
                class="{{ $fieldClass }}"
                data-selected="{{ $selectedRtId }}"
            >
                <option value="">-- Pilih RT --</option>
            </select>
            @error('rt_id')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>
    </div>
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

                // ========================================
                // DROPDOWN ALAMAT BERTINGKAT
                // ========================================
                const kelurahanSelect = document.getElementById('kelurahan_desa_id');
                const rwSelect = document.getElementById('rw_id');
                const rtSelect = document.getElementById('rt_id');

                const selectedKelurahan = kelurahanSelect?.dataset.selected || '';
                const selectedRw = rwSelect?.dataset.selected || '';
                const selectedRt = rtSelect?.dataset.selected || '';

                const rwUrl = @json(route('admin.ibu.alamat.rw'));
                const rtUrl = @json(route('admin.ibu.alamat.rt'));

                function resetSelect(select, placeholder) {
                    if (!select) return;
                    select.innerHTML = '';
                    select.add(new Option(placeholder, ''));
                }

                async function loadRt(rwId, selectedId = '') {
                    resetSelect(rtSelect, '-- Pilih RT --');
                    if (!rwId || !rtSelect) return;

                    const response = await fetch(
                        `${rtUrl}?rw_id=${encodeURIComponent(rwId)}`,
                        { headers: { Accept: 'application/json' } }
                    );

                    if (!response.ok) throw new Error('Gagal mengambil data RT.');

                    const data = await response.json();
                    data.forEach(item => {
                        rtSelect.add(new Option(`RT ${item.nomor_rt}`, item.id));
                    });

                    rtSelect.value = selectedId;
                }

                async function loadRw(kelurahanId, selectedId = '') {
                    resetSelect(rwSelect, '-- Pilih RW --');
                    resetSelect(rtSelect, '-- Pilih RT --');

                    if (!kelurahanId || !rwSelect) return;

                    const response = await fetch(
                        `${rwUrl}?kelurahan_desa_id=${encodeURIComponent(kelurahanId)}`,
                        { headers: { Accept: 'application/json' } }
                    );

                    if (!response.ok) throw new Error('Gagal mengambil data RW.');

                    const data = await response.json();
                    data.forEach(item => {
                        rwSelect.add(new Option(`RW ${item.nomor_rw}`, item.id));
                    });

                    rwSelect.value = selectedId;

                    if (rwSelect.value) {
                        await loadRt(rwSelect.value, selectedRt);
                    }
                }

                kelurahanSelect?.addEventListener('change', async function () {
                    try {
                        await loadRw(this.value);
                    } catch (error) {
                        console.error(error);
                        alert('Gagal memuat pilihan RW.');
                    }
                });

                rwSelect?.addEventListener('change', async function () {
                    try {
                        await loadRt(this.value);
                    } catch (error) {
                        console.error(error);
                        alert('Gagal memuat pilihan RT.');
                    }
                });

                (async function initializeAddress() {
                    try {
                        if (selectedKelurahan) {
                            await loadRw(selectedKelurahan, selectedRw);
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Gagal memuat alamat yang tersimpan.');
                    }
                })();
            });
        </script>
    @endpush
@endonce
