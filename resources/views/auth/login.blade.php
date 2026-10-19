@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
<div class="relative min-h-screen overflow-hidden bg-[#FCE7F0] px-4 py-6 sm:px-6 lg:px-10">

    {{-- Dekorasi latar belakang --}}
    <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-[#E99AB7]/25 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-20 h-80 w-80 rounded-full bg-[#C9517E]/15 blur-3xl"></div>

    <div class="relative mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-5xl items-center justify-center">
        <div class="grid w-full overflow-hidden rounded-[2rem] border border-white/80 bg-white shadow-[0_25px_80px_rgba(201,81,126,0.15)] lg:grid-cols-2">

            {{-- PANEL IDENTITAS SI-KIA --}}
            <div class="relative flex flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-[#C9517E] via-[#D86F96] to-[#E99AB7] px-6 py-10 text-center text-white sm:px-10 lg:min-h-[540px]">
                <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full border border-white/20"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-16 h-64 w-64 rounded-full bg-white/10"></div>

                <div class="relative z-10 flex w-full max-w-sm flex-col items-center">
                    {{-- Logo: simpan gambar di public/images/logo-si-kia.png --}}
                    <div class="flex h-36 w-36 items-center justify-center overflow-hidden rounded-[2rem] bg-white p-4 shadow-xl sm:h-44 sm:w-44">
                        <img src="{{ asset('images/logo-si-kia.png') }}" alt="Logo SI-KIA" class="h-full w-full object-contain">
                    </div>

                    <h1 class="mt-6 text-4xl font-bold tracking-wide sm:text-5xl">SI-KIA</h1>
                    <p class="mt-2 text-base font-medium text-white sm:text-lg">Sistem Monitoring Ibu &amp; Anak</p>

                    {{-- Poin singkat --}}
                    <ul class="mt-7 w-full space-y-2.5 text-left text-sm">
                        <li class="flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-3 backdrop-blur-sm">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-4.4-9-9a5 5 0 0 1 9-3 5 5 0 0 1 9 3c-2 4.6-9 9-9 9Z"/></svg>
                            </span>
                            <span class="font-semibold">Pantau kesehatan ibu hamil</span>
                        </li>
                        <li class="flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-3 backdrop-blur-sm">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6"/></svg>
                            </span>
                            <span class="font-semibold">Catat tumbuh kembang bayi &amp; balita</span>
                        </li>
                        <li class="flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-3 backdrop-blur-sm">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/></svg>
                            </span>
                            <span class="font-semibold">Jadwal &amp; pengingat Posyandu</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- PANEL FORM LOGIN --}}
            <div class="flex items-center justify-center px-5 py-10 sm:px-10 sm:py-12 lg:px-12">
                <div class="w-full max-w-sm">

                    <div class="mb-8">
                        <h2 class="text-3xl font-bold tracking-tight text-[#542D3D]">Login</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Silakan login dengan akun yang telah terdaftar.</p>
                    </div>

                    @if ($errors->any())
                        <div role="alert" aria-live="assertive"
                             class="mb-6 flex gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path stroke-linecap="round" d="M12 8v4m0 4h.01"></path>
                            </svg>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-[#542D3D]">Email</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#C9517E]/70">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8 6 8-6"></path>
                                    </svg>
                                </div>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                       placeholder="nama@gmail.com"
                                       class="w-full rounded-xl border border-[#E2C3D0] bg-white py-3.5 pl-12 pr-4 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 hover:border-[#E99AB7] focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/20">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-[#542D3D]">Password</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#C9517E]/70">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <rect x="4" y="10" width="16" height="11" rx="2"></rect>
                                        <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                                    </svg>
                                </div>
                                <input id="password" type="password" name="password" required
                                       placeholder="Masukkan password"
                                       class="w-full rounded-xl border border-[#E2C3D0] bg-white py-3.5 pl-12 pr-12 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 hover:border-[#E99AB7] focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/20">

                                {{-- Tombol tampil/sembunyikan password --}}
                                <button type="button" id="togglePassword" aria-label="Tampilkan password" aria-pressed="false"
                                        class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 transition hover:text-[#C9517E] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#C9517E]">
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
                        </div>

                        <button type="submit"
                                class="flex w-full items-center justify-center rounded-xl bg-[#C9517E] px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#C9517E]/20 transition duration-200 hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                            Masuk
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
        if (!input || !btn) return;

        btn.addEventListener('click', function () {
            const hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            eye.classList.toggle('hidden', hidden);
            eyeOff.classList.toggle('hidden', !hidden);
            btn.setAttribute('aria-pressed', String(hidden));
            btn.setAttribute('aria-label', hidden ? 'Sembunyikan password' : 'Tampilkan password');
        });
    });
</script>
@endsection