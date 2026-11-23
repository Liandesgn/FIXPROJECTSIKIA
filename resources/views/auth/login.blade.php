@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
<style>
    @keyframes sikia-naik {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes sikia-apung {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-10px); }
    }

    .sikia-masuk { animation: sikia-naik .6s cubic-bezier(.22, .61, .36, 1) both; }
    .sikia-apung { animation: sikia-apung 9s ease-in-out infinite; }

    @media (prefers-reduced-motion: reduce) {
        .sikia-masuk,
        .sikia-apung { animation: none; }
    }
</style>

<div class="relative min-h-screen overflow-hidden bg-[#FCE7F0] px-4 py-4 sm:px-6 sm:py-6 lg:px-10">

    {{-- Dekorasi latar belakang --}}
    <div class="sikia-apung pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-[#E99AB7]/25 blur-3xl"></div>
    <div class="sikia-apung pointer-events-none absolute -bottom-24 -right-20 h-80 w-80 rounded-full bg-[#C9517E]/15 blur-3xl" style="animation-delay: -4s;"></div>

    <div class="relative mx-auto flex min-h-[calc(100vh-2rem)] w-full max-w-5xl items-center justify-center sm:min-h-[calc(100vh-3rem)]">
        <div class="sikia-masuk grid w-full overflow-hidden rounded-[2rem] border border-white/80 bg-white shadow-[0_25px_80px_rgba(201,81,126,0.15)] lg:grid-cols-2">

            {{-- PANEL IDENTITAS SI-KIA --}}
            <div class="relative flex flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-[#C9517E] via-[#D86F96] to-[#E99AB7] px-6 py-8 text-center text-white sm:px-10 sm:py-10 lg:min-h-[540px]">
                <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full border border-white/20"></div>
                <div class="sikia-apung pointer-events-none absolute -bottom-24 -left-16 h-64 w-64 rounded-full bg-white/10" style="animation-delay: -2s;"></div>

                <div class="relative z-10 flex w-full max-w-sm flex-col items-center">
                    {{-- Logo: simpan gambar di public/images/logo-si-kia.png --}}
                    <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-[1.75rem] bg-white p-3 shadow-xl ring-8 ring-white/20 sm:h-36 sm:w-36 sm:rounded-[2rem] sm:p-4 lg:h-44 lg:w-44">
                        <img src="{{ asset('images/logo-si-kia.png') }}" alt="Logo SI-KIA" class="h-full w-full object-contain">
                    </div>

                    <h1 class="mt-5 text-3xl font-bold tracking-wide sm:mt-6 sm:text-5xl">SI-KIA</h1>
                    <p class="mt-2 text-sm font-medium text-white sm:text-lg">Sistem Monitoring Kesehatan Ibu & Anak</p>

                    {{-- Poin singkat --}}
                    <ul class="mt-5 w-full space-y-2 text-left text-sm sm:mt-7 sm:space-y-2.5">
                        <li class="sikia-masuk flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-2.5 backdrop-blur-sm transition duration-200 hover:translate-x-1 hover:bg-white/25 motion-reduce:transition-none motion-reduce:hover:transform-none sm:py-3" style="animation-delay: .2s;">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-4.4-9-9a5 5 0 0 1 9-3 5 5 0 0 1 9 3c-2 4.6-9 9-9 9Z"/></svg>
                            </span>
                            <span class="font-semibold">Pantau kesehatan ibu hamil</span>
                        </li>
                        <li class="sikia-masuk flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-2.5 backdrop-blur-sm transition duration-200 hover:translate-x-1 hover:bg-white/25 motion-reduce:transition-none motion-reduce:hover:transform-none sm:py-3" style="animation-delay: .3s;">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6"/></svg>
                            </span>
                            <span class="font-semibold">Catat tumbuh kembang bayi & balita</span>
                        </li>
                        <li class="sikia-masuk flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-2.5 backdrop-blur-sm transition duration-200 hover:translate-x-1 hover:bg-white/25 motion-reduce:transition-none motion-reduce:hover:transform-none sm:py-3" style="animation-delay: .4s;">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/></svg>
                            </span>
                            <span class="font-semibold">Jadwal &amp; pengingat Posyandu</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- PANEL FORM LOGIN --}}
            <div class="flex items-center justify-center px-5 py-8 sm:px-10 sm:py-12 lg:px-12">
                <div class="sikia-masuk w-full max-w-sm" style="animation-delay: .15s;">

                    <div class="mb-7 sm:mb-8">
                        <h2 class="text-3xl font-bold tracking-tight text-[#542D3D]">Login</h2>
                        <span class="mt-3 block h-1 w-10 rounded-full bg-[#E99AB7]"></span>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Silakan login dengan akun yang telah terdaftar.</p>
                    </div>

                    @if ($errors->any())
                        <div role="alert" aria-live="assertive"
                             class="sikia-masuk mb-6 flex gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path stroke-linecap="round" d="M12 8v4m0 4h.01"></path>
                            </svg>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    @endif

                    <form id="formLogin" method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-[#542D3D]">Email</label>
                            <div class="group relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#C9517E]/70 transition-colors group-focus-within:text-[#C9517E]">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8 6 8-6"></path>
                                    </svg>
                                </div>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                       autocomplete="email" inputmode="email" enterkeyhint="next"
                                       placeholder="nama@gmail.com"
                                       class="w-full rounded-xl border border-[#E2C3D0] bg-white py-3.5 pl-12 pr-4 text-base text-gray-800 shadow-sm outline-none transition placeholder:text-gray-400 hover:border-[#E99AB7] focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/20 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-[#542D3D]">Password</label>
                            <div class="group relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#C9517E]/70 transition-colors group-focus-within:text-[#C9517E]">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <rect x="4" y="10" width="16" height="11" rx="2"></rect>
                                        <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                                    </svg>
                                </div>
                                <input id="password" type="password" name="password" required
                                       autocomplete="current-password" enterkeyhint="go"
                                       placeholder="Masukkan password"
                                       class="w-full rounded-xl border border-[#E2C3D0] bg-white py-3.5 pl-12 pr-12 text-base text-gray-800 shadow-sm outline-none transition placeholder:text-gray-400 hover:border-[#E99AB7] focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/20 sm:text-sm">

                                {{-- Tombol tampil/sembunyikan password --}}
                                <button type="button" id="togglePassword" aria-label="Tampilkan password" aria-pressed="false"
                                        class="absolute inset-y-0 right-0 flex items-center rounded-r-xl px-4 text-gray-500 transition hover:text-[#C9517E] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#C9517E]">
                                    <svg id="eyeIcon" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg id="eyeOffIcon" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6 0 9.5 7 9.5 7a15 15 0 0 1-3.1 3.9M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.5 7 9.5 7c1 0 1.9-.2 2.8-.5"></path>
                                    </svg>
                                </button>
                            </div>

                            {{-- Peringatan Caps Lock --}}
                            <p id="capsHint" role="status"
                               class="mt-2 hidden items-center gap-1.5 text-xs font-medium text-amber-700">
                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4 5 12h4v4h6v-4h4L12 4Zm-3 16h6"/></svg>
                                Caps Lock sedang aktif
                            </p>
                        </div>

                        <button type="submit" id="btnMasuk"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#C9517E] to-[#D86F96] px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#C9517E]/25 transition duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-[#C9517E]/30 focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:translate-y-0 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-80 disabled:hover:translate-y-0 motion-reduce:transition-none motion-reduce:hover:transform-none">
                            <svg id="spinnerMasuk" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"></circle>
                                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                            </svg>
                            <span id="teksMasuk">Masuk</span>
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('password');
        const btn = document.getElementById('togglePassword');
        const eye = document.getElementById('eyeIcon');
        const eyeOff = document.getElementById('eyeOffIcon');
        const caps = document.getElementById('capsHint');
        const form = document.getElementById('formLogin');
        const btnMasuk = document.getElementById('btnMasuk');
        const spinner = document.getElementById('spinnerMasuk');
        const teksMasuk = document.getElementById('teksMasuk');

        // Tampil / sembunyikan password
        if (input && btn) {
            btn.addEventListener('click', function () {
                const hidden = input.type === 'password';
                input.type = hidden ? 'text' : 'password';
                eye.classList.toggle('hidden', hidden);
                eyeOff.classList.toggle('hidden', !hidden);
                btn.setAttribute('aria-pressed', String(hidden));
                btn.setAttribute('aria-label', hidden ? 'Sembunyikan password' : 'Tampilkan password');
            });
        }

        // Peringatan Caps Lock
        if (input && caps) {
            const cekCaps = function (e) {
                if (typeof e.getModifierState !== 'function') return;
                const aktif = e.getModifierState('CapsLock');
                caps.classList.toggle('hidden', !aktif);
                caps.classList.toggle('flex', aktif);
            };

            input.addEventListener('keydown', cekCaps);
            input.addEventListener('keyup', cekCaps);
            input.addEventListener('blur', function () {
                caps.classList.add('hidden');
                caps.classList.remove('flex');
            });
        }

        // Tombol Masuk: tampilkan "Memproses..." agar tidak terkirim dua kali
        const pulihkanTombol = function () {
            if (!btnMasuk) return;
            btnMasuk.disabled = false;
            spinner.classList.add('hidden');
            teksMasuk.textContent = 'Masuk';
        };

        if (form && btnMasuk) {
            form.addEventListener('submit', function () {
                btnMasuk.disabled = true;
                spinner.classList.remove('hidden');
                teksMasuk.textContent = 'Memproses...';
            });

            // Bila pengguna kembali ke halaman ini lewat tombol "kembali" browser
            window.addEventListener('pageshow', function (e) {
                if (e.persisted) pulihkanTombol();
            });
        }
    });
</script>
@endsection