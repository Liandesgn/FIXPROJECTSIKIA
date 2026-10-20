@php
    $inputClass = 'w-full rounded-xl border border-[#E2C3D0] bg-white px-4 py-3 text-sm text-gray-900 outline-none transition placeholder:text-slate-500 hover:border-[#E99AB7] focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/25';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $star       = '<span class="text-[#C9517E]">*</span>';
    $selectedWilayah = old('wilayah_kerja_id', $ibu?->wilayah_kerja_id);
    $selectedStatus  = old('status_kehamilan', $ibu?->status_kehamilan?->value);
    $isHamilNow      = $selectedStatus === \App\Enums\StatusKehamilanEnum::Hamil->value;
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">

    <div>
        <label for="name" class="{{ $labelClass }}">Nama Lengkap {!! $star !!}</label>
        <input id="name" type="text" name="name" value="{{ old('name', $ibu?->name) }}" required placeholder="Nama lengkap ibu" class="{{ $inputClass }}">
        @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="{{ $labelClass }}">Email {!! $star !!}</label>
        <input id="email" type="email" name="email" value="{{ old('email', $ibu?->email) }}" required placeholder="nama@gmail.com" class="{{ $inputClass }}">
        @error('email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password" class="{{ $labelClass }}">
            {{ $ibu ? 'Password' : 'Password Awal' }}
            @unless ($ibu) {!! $star !!} @endunless
            @if ($ibu) <span class="font-normal text-slate-600">(kosongkan bila tidak diubah)</span> @endif
        </label>
        <input id="password" type="password" name="password" @required(! $ibu) placeholder="{{ $ibu ? '' : 'Minimal 8 karakter' }}" class="{{ $inputClass }}">
        @error('password') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="nik" class="{{ $labelClass }}">NIK {!! $star !!}</label>
        <input id="nik" type="text" name="nik" value="{{ old('nik', $ibu?->nik) }}" maxlength="16" inputmode="numeric" required placeholder="16 digit NIK" class="{{ $inputClass }}">
        @error('nik') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="phone" class="{{ $labelClass }}">Nomor HP</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $ibu?->phone) }}" inputmode="tel" placeholder="08xxxxxxxxxx" class="{{ $inputClass }}">
        @error('phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="tempat_lahir" class="{{ $labelClass }}">Tempat Lahir {!! $star !!}</label>
        <input id="tempat_lahir" type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $ibu?->tempat_lahir) }}" required placeholder="Kota / kabupaten kelahiran" class="{{ $inputClass }}">
        @error('tempat_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="tanggal_lahir" class="{{ $labelClass }}">Tanggal Lahir {!! $star !!}</label>
        <input id="tanggal_lahir" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $ibu?->tanggal_lahir?->format('Y-m-d')) }}" required class="{{ $inputClass }}">
        @error('tanggal_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="status_kehamilan" class="{{ $labelClass }}">Status Kehamilan {!! $star !!}</label>
        <select id="status_kehamilan" name="status_kehamilan" required class="{{ $inputClass }}" data-status-kehamilan>
            <option value="">-- Pilih Status --</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="wilayah_kerja_id" class="{{ $labelClass }}">Wilayah (Desa) {!! $star !!}</label>
        <select id="wilayah_kerja_id" name="wilayah_kerja_id" required class="{{ $inputClass }}">
            <option value="">-- Pilih Wilayah --</option>
            @foreach ($wilayahs->groupBy('kecamatan') as $kecamatan => $desas)
                <optgroup label="Kec. {{ $kecamatan ?: '-' }}">
                    @foreach ($desas as $wilayah)
                        <option value="{{ $wilayah->id }}" @selected((string) $selectedWilayah === (string) $wilayah->id)>
                            Desa {{ $wilayah->desa }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('wilayah_kerja_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    {{-- Tampil hanya jika status = Hamil --}}
    <div data-hamil-fields class="{{ $isHamilNow ? '' : 'hidden' }}">
        <label for="usia_kehamilan" class="{{ $labelClass }}">Usia Kehamilan (minggu) {!! $star !!}</label>
        <input id="usia_kehamilan" type="number" name="usia_kehamilan" min="1" max="42" value="{{ old('usia_kehamilan', $ibu?->usia_kehamilan) }}" placeholder="Minggu" class="{{ $inputClass }}">
        @error('usia_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div data-hamil-fields class="{{ $isHamilNow ? '' : 'hidden' }}">
        <label for="hpl" class="{{ $labelClass }}">HPL (Hari Perkiraan Lahir) {!! $star !!}</label>
        <input id="hpl" type="date" name="hpl" value="{{ old('hpl', $ibu?->hpl?->format('Y-m-d')) }}" class="{{ $inputClass }}">
        @error('hpl') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
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
            });
        </script>
    @endpush
@endonce