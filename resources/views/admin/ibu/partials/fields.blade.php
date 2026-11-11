@php
    $profil = $ibu?->ibu;

    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $lockedClass = str_replace('bg-white', 'cursor-not-allowed bg-slate-100 text-slate-700', $fieldClass);
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $star       = '<span class="text-[#C9517E]">*</span>';
    $selectedStatus = old('status_kehamilan', $profil?->status_kehamilan?->value);
    $isHamilNow     = $selectedStatus === \App\Enums\StatusKehamilanEnum::Hamil->value;

    // Wilayah otomatis: saat edit pakai wilayah ibu itu, saat registrasi pakai wilayah Admin yang login
    $wilayahAuto = $profil?->wilayahKerja
        ?? auth()->user()?->admin?->wilayahKerja
        ?? collect($wilayahs ?? [])->first();

    $wilayahTeks = $wilayahAuto
        ? 'Desa ' . $wilayahAuto->kelurahan . ($wilayahAuto->kecamatan ? ', Kec. ' . $wilayahAuto->kecamatan : '')
        : '';
@endphp

{{-- A. Data akun --}}
<div>
    <h3 class="mb-3 border-b border-[#F1D5E0] pb-2 text-sm font-bold uppercase tracking-wide text-[#C9517E]">A. Data Akun Ibu</h3>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label for="name" class="{{ $labelClass }}">Nama Lengkap {!! $star !!}</label>
            <input id="name" type="text" name="name" value="{{ old('name', $ibu?->name) }}" required
                   placeholder="Nama lengkap ibu" class="{{ $fieldClass }}">
            @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="{{ $labelClass }}">Email {!! $star !!}</label>
            <input id="email" type="email" name="email" value="{{ old('email', $ibu?->email) }}" required
                   placeholder="nama@gmail.com" class="{{ $fieldClass }}">
            @error('email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="{{ $labelClass }}">Nomor HP</label>
            <input id="phone" type="text" name="phone" value="{{ old('phone', $ibu?->phone) }}"
                   inputmode="numeric" maxlength="13" pattern="[0-9]{10,13}" title="Hanya angka, 10-13 digit"
                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                   placeholder="08xxxxxxxxxx" class="{{ $fieldClass }}">
            @error('phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="{{ $labelClass }}">
                Password @unless ($ibu) {!! $star !!} @endunless
                @if ($ibu) <span class="font-normal text-slate-500">(kosongkan bila tidak diubah)</span> @endif
            </label>
            <input id="password" type="password" name="password" @required(! $ibu)
                   placeholder="Minimal 8 karakter" class="{{ $fieldClass }}">
            @error('password') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

{{-- B. Data ibu --}}
<div>
    <h3 class="mb-3 border-b border-[#F1D5E0] pb-2 text-sm font-bold uppercase tracking-wide text-[#C9517E]">B. Data Ibu</h3>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label for="nik" class="{{ $labelClass }}">NIK {!! $star !!}</label>
            <input id="nik" type="text" name="nik" value="{{ old('nik', $profil?->nik) }}" maxlength="16"
                   inputmode="numeric" required placeholder="16 digit NIK" class="{{ $fieldClass }}">
            @error('nik') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="tempat_lahir" class="{{ $labelClass }}">Tempat Lahir {!! $star !!}</label>
            <input id="tempat_lahir" type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $profil?->tempat_lahir) }}"
                   required placeholder="Kota / kabupaten kelahiran" class="{{ $fieldClass }}">
            @error('tempat_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="tgl_lahir" class="{{ $labelClass }}">Tanggal Lahir {!! $star !!}</label>
            <input id="tgl_lahir" type="date" name="tgl_lahir" value="{{ old('tgl_lahir', $profil?->tgl_lahir?->format('Y-m-d')) }}"
                   required class="{{ $fieldClass }}">
            @error('tgl_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        {{-- Wilayah: terisi otomatis, tidak bisa diubah --}}
        <div>
            <label for="wilayah_tampil" class="{{ $labelClass }}">Wilayah (Desa) {!! $star !!}</label>
            <input type="hidden" name="wilayah_kerja_id" value="{{ $wilayahAuto?->id }}">
            <input id="wilayah_tampil" type="text" value="{{ $wilayahTeks }}" readonly tabindex="-1"
                   placeholder="Wilayah belum tersedia" class="{{ $lockedClass }}">
            <p class="mt-1 text-xs font-medium text-slate-600">Terisi otomatis sesuai wilayah Anda.</p>
            @error('wilayah_kerja_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="status_kehamilan" class="{{ $labelClass }}">Status Kehamilan {!! $star !!}</label>
            <select id="status_kehamilan" name="status_kehamilan" required class="{{ $fieldClass }}" data-status-kehamilan>
                <option value="">-- Pilih Status --</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            @error('status_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        {{-- Tampil hanya jika status = Hamil --}}
        <div data-hamil-fields class="{{ $isHamilNow ? '' : 'hidden' }}">
            <label for="usia_kehamilan" class="{{ $labelClass }}">Usia Kehamilan (minggu) {!! $star !!}</label>
            <input id="usia_kehamilan" type="number" name="usia_kehamilan" min="1" max="42"
                   value="{{ old('usia_kehamilan', $profil?->usia_kehamilan) }}" placeholder="Minggu" class="{{ $fieldClass }}">
            @error('usia_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div data-hamil-fields class="{{ $isHamilNow ? '' : 'hidden' }}">
            <label for="hpl" class="{{ $labelClass }}">HPL (Hari Perkiraan Lahir) {!! $star !!}</label>
            <input id="hpl" type="date" name="hpl" value="{{ old('hpl', $profil?->hpl?->format('Y-m-d')) }}" class="{{ $fieldClass }}">
            @error('hpl') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Tampilkan / sembunyikan kolom usia kehamilan & HPL
                const statusSelect = document.querySelector('[data-status-kehamilan]');
                const hamilFields = document.querySelectorAll('[data-hamil-fields]');
                const hplInput = document.querySelector('#hpl');
                const usiaInput = document.querySelector('#usia_kehamilan');

                function toggleHamil() {
                    const isHamil = statusSelect?.value === 'hamil';
                    hamilFields.forEach(el => el.classList.toggle('hidden', !isHamil));
                    hplInput?.toggleAttribute('required', isHamil);
                    usiaInput?.toggleAttribute('required', isHamil);
                }
                statusSelect?.addEventListener('change', toggleHamil);
                toggleHamil();

                // Ikon mata (tampilkan/sembunyikan) untuk kolom password
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