@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="w-full max-w-md">
        <div class="mb-6 text-center text-white">
            <h1 class="text-3xl font-bold tracking-wide">SI-KIA</h1>
            <p class="mt-1 text-sm text-white/80">Sistem Informasi Kesehatan Ibu &amp; Anak</p>
        </div>

        <div class="rounded-2xl bg-white p-8 shadow-2xl">
            <h2 class="text-xl font-semibold text-slate-800">Masuk ke SI-KIA</h2>
            <p class="mb-6 text-sm text-slate-500">Gunakan email dan password akun Anda.</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" type="password" name="password" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1"
                           class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    Ingat saya
                </label>

                <button type="submit"
                        class="w-full rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-700">
                    Masuk
                </button>
            </form>
        </div>
    </div>
@endsection
