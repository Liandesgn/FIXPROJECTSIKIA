@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    @php
        $inputClass = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200';
        $labelClass = 'mb-1 block text-sm font-medium text-slate-700';
        $errorClass = 'mt-1 text-xs text-red-600';
        $isOrangTua = $user->isOrangTua();
        $selectedStatus = old('status_kehamilan', $user->status_kehamilan?->value);
    @endphp

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Informasi Profil</h2>
                    <p class="mt-1 text-sm text-slate-500">Perbarui nama, email, dan nomor HP akun Anda.</p>
                </div>
                <div class="text-right text-sm">
                    <span class="inline-flex rounded-full bg-sky-50 px-3 py-1 font-medium text-sky-700">
                        {{ $user->primaryRole()?->label() ?? '-' }}
                    </span>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $user->wilayahKerja?->nama ?? 'Tanpa wilayah kerja' }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="{{ $labelClass }}">Nama Lengkap</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="{{ $inputClass }}">
                    @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="{{ $labelClass }}">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $inputClass }}">
                    @error('email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="{{ $labelClass }}">Nomor HP</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="{{ $inputClass }}">
                    @error('phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                    Simpan Profil
                </button>
            </form>
        </div>

        @if ($isOrangTua)
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-1 text-lg font-semibold text-slate-800">Data Ibu</h2>
                <p class="mb-6 text-sm text-slate-500">Data registrasi ibu Anda. Wajib milik Anda sendiri.</p>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="nik" class="{{ $labelClass }}">NIK</label>
                            <input id="nik" type="text" name="nik" maxlength="16" value="{{ old('nik', $user->nik) }}" class="{{ $inputClass }}">
                            @error('nik') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="tempat_lahir" class="{{ $labelClass }}">Tempat Lahir</label>
                            <input id="tempat_lahir" type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $user->tempat_lahir) }}" class="{{ $inputClass }}">
                            @error('tempat_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="tanggal_lahir" class="{{ $labelClass }}">Tanggal Lahir</label>
                            <input id="tanggal_lahir" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                            @error('tanggal_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="status_kehamilan" class="{{ $labelClass }}">Status Kehamilan</label>
                            <select id="status_kehamilan" name="status_kehamilan" class="{{ $inputClass }}" data-status-kehamilan>
                                <option value="">-- Pilih Status --</option>
                                @foreach (\App\Enums\StatusKehamilanEnum::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            @error('status_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div data-hamil-fields class="grid grid-cols-1 gap-4 md:grid-cols-2 {{ $selectedStatus === \App\Enums\StatusKehamilanEnum::Hamil->value ? '' : 'hidden' }}">
                        <div>
                            <label for="hpl" class="{{ $labelClass }}">HPL (Hari Perkiraan Lahir)</label>
                            <input id="hpl" type="date" name="hpl" value="{{ old('hpl', $user->hpl?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                            @error('hpl') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="usia_kehamilan" class="{{ $labelClass }}">Usia Kehamilan (minggu)</label>
                            <input id="usia_kehamilan" type="number" min="1" max="42" name="usia_kehamilan" value="{{ old('usia_kehamilan', $user->usia_kehamilan) }}" class="{{ $inputClass }}">
                            @error('usia_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 p-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Wilayah &amp; Petugas</p>
                        <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                            <div>
                                <p class="text-xs text-slate-400">Kabupaten</p>
                                <p class="font-medium text-slate-700">{{ $user->wilayahKerja?->kabupaten ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Kecamatan</p>
                                <p class="font-medium text-slate-700">{{ $user->wilayahKerja?->kecamatan ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Desa</p>
                                <p class="font-medium text-slate-700">{{ $user->wilayahKerja?->desa ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Bidan</p>
                                <p class="font-medium text-slate-700">{{ $user->bidanWilayah?->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Kader</p>
                                <p class="font-medium text-slate-700">{{ $user->kaderWilayah?->name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                            class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                        Simpan Data Ibu
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection

@if ($isOrangTua)
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
@endif
