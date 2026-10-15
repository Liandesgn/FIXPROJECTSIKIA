@extends('layouts.app')

@section('title', 'Registrasi Orang Tua')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-lg font-semibold text-slate-800">Registrasi Orang Tua</h2>
            <p class="mb-6 text-sm text-slate-500">
                Lengkapi <span class="font-medium text-slate-700">Data Akun</span> lalu
                <span class="font-medium text-slate-700">Data Ibu</span>.
            </p>

            <form method="POST" action="{{ route('panel.kelola-orang-tua.store') }}" class="space-y-6">
                @csrf

                @include('panel.kelola-orang-tua.partials.fields', ['orangTua' => null])
            </form>
        </div>
    </div>
@endsection
