@php
    $inputClass = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200';
    $labelClass = 'mb-1 block text-sm font-medium text-slate-700';
    $errorClass = 'mt-1 text-xs text-red-600';
    $selectedWilayah = old('wilayah_kerja_id', $ibu?->wilayah_kerja_id);
    $selectedStatus = old('status_kehamilan', $ibu?->status_kehamilan?->value);
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <p class="text-sm font-semibold text-slate-800">Data Akun &amp; Identitas Ibu</p>
        <p class="text-xs text-slate-500">Isi data akun untuk masuk dan identitas ibu.</p>
    </div>

    <div>
        <label for="name" class="{{ $labelClass }}">Nama Lengkap</label>
        <input id="name" type="text" name="name" value="{{ old('name', $ibu?->name) }}" required class="{{ $inputClass }}">
        @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="{{ $labelClass }}">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $ibu?->email) }}" required class="{{ $inputClass }}">
        @error('email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="nik" class="{{ $labelClass }}">NIK</label>
        <input id="nik" type="text" name="nik" value="{{ old('nik', $ibu?->nik) }}" maxlength="16" inputmode="numeric" required class="{{ $inputClass }}">
        @error('nik') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="phone" class="{{ $labelClass }}">No. HP</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $ibu?->phone) }}" class="{{ $inputClass }}">
        @error('phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="tempat_lahir" class="{{ $labelClass }}">Tempat Lahir</label>
        <input id="tempat_lahir" type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $ibu?->tempat_lahir) }}" required class="{{ $inputClass }}">
        @error('tempat_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="tanggal_lahir" class="{{ $labelClass }}">Tanggal Lahir</label>
        <input id="tanggal_lahir" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $ibu?->tanggal_lahir?->format('Y-m-d')) }}" required class="{{ $inputClass }}">
        @error('tanggal_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="status_kehamilan" class="{{ $labelClass }}">Status Kehamilan</label>
        <select id="status_kehamilan" name="status_kehamilan" required class="{{ $inputClass }}" data-status-kehamilan>
            <option value="">-- Pilih Status --</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="wilayah_kerja_id" class="{{ $labelClass }}">Wilayah (Desa)</label>
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

    <div>
        <label for="password" class="{{ $labelClass }}">
            Password @if ($ibu) <span class="font-normal text-slate-400">(kosongkan bila tidak diubah)</span> @endif
        </label>
        <input id="password" type="password" name="password" @required(! $ibu) class="{{ $inputClass }}">
        @error('password') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password_confirmation" class="{{ $labelClass }}">Konfirmasi Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" @required(! $ibu) class="{{ $inputClass }}">
    </div>

    <div data-hamil-fields class="md:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2 {{ $selectedStatus === \App\Enums\StatusKehamilanEnum::Hamil->value ? '' : 'hidden' }}">
        <div>
            <label for="hpl" class="{{ $labelClass }}">HPL (Hari Perkiraan Lahir)</label>
            <input id="hpl" type="date" name="hpl" value="{{ old('hpl', $ibu?->hpl?->format('Y-m-d')) }}" class="{{ $inputClass }}">
            @error('hpl') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="usia_kehamilan" class="{{ $labelClass }}">Usia Kehamilan (minggu)</label>
            <input id="usia_kehamilan" type="number" name="usia_kehamilan" min="1" max="42" value="{{ old('usia_kehamilan', $ibu?->usia_kehamilan) }}" class="{{ $inputClass }}">
            @error('usia_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const statusSelect = document.querySelector('[data-status-kehamilan]');
                const hamilFields = document.querySelector('[data-hamil-fields]');
                const hplInput = document.querySelector('#hpl');
                const usiaInput = document.querySelector('#usia_kehamilan');

                function toggleHamil() {
                    const isHamil = statusSelect?.value === 'hamil';
                    hamilFields?.classList.toggle('hidden', !isHamil);
                    hplInput?.toggleAttribute('required', isHamil);
                    usiaInput?.toggleAttribute('required', isHamil);
                }
                statusSelect?.addEventListener('change', toggleHamil);
                toggleHamil();
            });
        </script>
    @endpush
@endonce
