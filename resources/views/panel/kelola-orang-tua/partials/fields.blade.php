@php
    $inputClass = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200';
    $labelClass = 'mb-1 block text-sm font-medium text-slate-700';
    $errorClass = 'mt-1 text-xs text-red-600';

    $selectedKecamatan = old('kecamatan', $orangTua?->wilayahKerja?->kecamatan);
    $selectedWilayah = old('wilayah_kerja_id', $orangTua?->wilayah_kerja_id);
    $selectedStatus = old('status_kehamilan', $orangTua?->status_kehamilan?->value);

    $motherErrorKeys = ['nik', 'nama_ibu', 'tempat_lahir', 'tanggal_lahir', 'phone', 'wilayah_kerja_id', 'status_kehamilan', 'hpl', 'usia_kehamilan'];
    $motherHasError = collect($motherErrorKeys)->contains(fn ($key) => $errors->has($key));
@endphp

<div class="mb-6 flex items-center gap-3 text-sm">
    <span data-step-dot="1" class="flex h-7 w-7 items-center justify-center rounded-full bg-sky-600 font-semibold text-white">1</span>
    <span class="font-medium text-slate-700">Data Akun</span>
    <span class="h-px flex-1 bg-slate-200"></span>
    <span data-step-dot="2" class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 font-semibold text-slate-500">2</span>
    <span class="font-medium text-slate-500">Data Ibu</span>
</div>

{{-- Form 1 — Data Akun --}}
<div data-step="1" class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <p class="text-sm font-semibold text-slate-800">Data Akun</p>
        <p class="text-xs text-slate-500">Informasi akun untuk masuk ke sistem.</p>
    </div>

    <div>
        <label for="name" class="{{ $labelClass }}">Nama Lengkap</label>
        <input id="name" type="text" name="name" value="{{ old('name', $orangTua?->name) }}" required class="{{ $inputClass }}">
        @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="{{ $labelClass }}">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $orangTua?->email) }}" required class="{{ $inputClass }}">
        @error('email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password" class="{{ $labelClass }}">
            Password @if ($orangTua) <span class="font-normal text-slate-400">(kosongkan bila tidak diubah)</span> @endif
        </label>
        <input id="password" type="password" name="password" @required(! $orangTua) class="{{ $inputClass }}">
        @error('password') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password_confirmation" class="{{ $labelClass }}">Konfirmasi Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" @required(! $orangTua) class="{{ $inputClass }}">
    </div>
</div>

{{-- Form 2 — Data Ibu --}}
<div data-step="2" class="grid grid-cols-1 gap-4 md:grid-cols-2 {{ $motherHasError ? '' : 'hidden' }}">
    <div class="md:col-span-2">
        <p class="text-sm font-semibold text-slate-800">Data Ibu</p>
        <p class="text-xs text-slate-500">Data identitas ibu dan wilayah tempat tinggal.</p>
    </div>

    <div>
        <label for="nik" class="{{ $labelClass }}">NIK</label>
        <input id="nik" type="text" name="nik" value="{{ old('nik', $orangTua?->nik) }}" maxlength="16" inputmode="numeric" class="{{ $inputClass }}">
        @error('nik') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="nama_ibu" class="{{ $labelClass }}">Nama Ibu</label>
        <input id="nama_ibu" type="text" name="nama_ibu" value="{{ old('nama_ibu', $orangTua?->nama_ibu) }}" class="{{ $inputClass }}">
        @error('nama_ibu') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="tempat_lahir" class="{{ $labelClass }}">Tempat Lahir</label>
        <input id="tempat_lahir" type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $orangTua?->tempat_lahir) }}" class="{{ $inputClass }}">
        @error('tempat_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="tanggal_lahir" class="{{ $labelClass }}">Tanggal Lahir</label>
        <input id="tanggal_lahir" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $orangTua?->tanggal_lahir?->format('Y-m-d')) }}" class="{{ $inputClass }}">
        @error('tanggal_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="phone" class="{{ $labelClass }}">No. HP</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $orangTua?->phone) }}" class="{{ $inputClass }}">
        @error('phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="status_kehamilan" class="{{ $labelClass }}">Status Kehamilan</label>
        <select id="status_kehamilan" name="status_kehamilan" class="{{ $inputClass }}" data-status-kehamilan>
            <option value="">-- Pilih Status --</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Wilayah</p>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="kabupaten" class="{{ $labelClass }}">Kabupaten</label>
                <input id="kabupaten" type="text" value="{{ $kabupaten }}" readonly class="{{ $inputClass }} bg-slate-50 text-slate-500">
            </div>

            <div>
                <label for="kecamatan" class="{{ $labelClass }}">Kecamatan</label>
                <select id="kecamatan" name="kecamatan" class="{{ $inputClass }}" data-kecamatan>
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach ($kecamatans as $kecamatan)
                        <option value="{{ $kecamatan }}" @selected($selectedKecamatan === $kecamatan)>{{ $kecamatan }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="wilayah_kerja_id" class="{{ $labelClass }}">Desa</label>
                <select id="wilayah_kerja_id" name="wilayah_kerja_id" class="{{ $inputClass }}" data-desa>
                    <option value="">-- Pilih Desa --</option>
                    @foreach ($wilayahs as $wilayah)
                        <option value="{{ $wilayah->id }}" data-kecamatan="{{ $wilayah->kecamatan }}" @selected((string) $selectedWilayah === (string) $wilayah->id)>
                            {{ $wilayah->desa }}
                        </option>
                    @endforeach
                </select>
                @error('wilayah_kerja_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div data-hamil-fields class="md:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2 {{ $selectedStatus === \App\Enums\StatusKehamilanEnum::Hamil->value ? '' : 'hidden' }}">
        <div>
            <label for="hpl" class="{{ $labelClass }}">HPL (Hari Perkiraan Lahir)</label>
            <input id="hpl" type="date" name="hpl" value="{{ old('hpl', $orangTua?->hpl?->format('Y-m-d')) }}" class="{{ $inputClass }}">
            @error('hpl') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="usia_kehamilan" class="{{ $labelClass }}">Usia Kehamilan (minggu)</label>
            <input id="usia_kehamilan" type="number" name="usia_kehamilan" min="1" max="42" value="{{ old('usia_kehamilan', $orangTua?->usia_kehamilan) }}" class="{{ $inputClass }}">
            @error('usia_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="button" data-step-back class="hidden rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
        Kembali
    </button>
    <button type="button" data-step-next class="{{ $motherHasError ? 'hidden' : '' }} rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">
        Lanjut ke Data Ibu
    </button>
    <button type="submit" data-step-submit class="{{ $motherHasError ? '' : 'hidden' }} rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
        {{ $orangTua ? 'Simpan Perubahan' : 'Simpan' }}
    </button>
    <a href="{{ route('panel.kelola-orang-tua.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Batal</a>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.currentScript.closest('form');
                const root = form || document;
                const steps = root.querySelectorAll('[data-step]');
                const stepsWrap = steps[0] ? steps[0].parentElement : document;
                const stepDots = root.querySelectorAll('[data-step-dot]');
                const nextBtn = root.querySelector('[data-step-next]');
                const backBtn = root.querySelector('[data-step-back]');
                const submitBtn = root.querySelector('[data-step-submit]');

                function showStep(step) {
                    steps.forEach((el) => el.classList.toggle('hidden', el.dataset.step !== String(step)));
                    stepDots.forEach((dot) => {
                        const active = dot.dataset.stepDot === String(step);
                        dot.classList.toggle('bg-sky-600', active);
                        dot.classList.toggle('text-white', active);
                        dot.classList.toggle('bg-slate-200', !active);
                        dot.classList.toggle('text-slate-500', !active);
                    });
                    nextBtn.classList.toggle('hidden', step !== 1);
                    backBtn.classList.toggle('hidden', step !== 2);
                    submitBtn.classList.toggle('hidden', step !== 2);
                }

                nextBtn?.addEventListener('click', function () {
                    const email = root.querySelector('#email');
                    const name = root.querySelector('#name');
                    const password = root.querySelector('#password');
                    const accountFields = [name, email, password].filter(Boolean);
                    const invalid = accountFields.find((el) => !el.checkValidity());
                    if (invalid) { invalid.reportValidity(); return; }
                    showStep(2);
                });

                backBtn?.addEventListener('click', function () { showStep(1); });

                const statusSelect = root.querySelector('[data-status-kehamilan]');
                const hamilFields = root.querySelector('[data-hamil-fields]');
                const hplInput = root.querySelector('#hpl');
                const usiaInput = root.querySelector('#usia_kehamilan');

                function toggleHamil() {
                    const isHamil = statusSelect?.value === 'hamil';
                    hamilFields?.classList.toggle('hidden', !isHamil);
                    hplInput?.toggleAttribute('required', isHamil);
                    usiaInput?.toggleAttribute('required', isHamil);
                }
                statusSelect?.addEventListener('change', toggleHamil);
                toggleHamil();

                const kecamatanSelect = root.querySelector('[data-kecamatan]');
                const desaSelect = root.querySelector('[data-desa]');
                const desaOptions = desaSelect ? Array.from(desaSelect.options) : [];

                function filterDesa() {
                    const selected = kecamatanSelect?.value ?? '';
                    const current = desaSelect.value;
                    desaOptions.forEach((option) => {
                        if (! option.value) return;
                        const match = option.dataset.kecamatan === selected;
                        option.hidden = ! match;
                        option.disabled = ! match;
                    });
                    const currentOption = Array.from(desaSelect.options).find((o) => o.value === current);
                    if (currentOption && (currentOption.hidden || currentOption.disabled)) {
                        desaSelect.value = '';
                    }
                }
                kecamatanSelect?.addEventListener('change', filterDesa);
                filterDesa();
            });
        </script>
    @endpush
@endonce
