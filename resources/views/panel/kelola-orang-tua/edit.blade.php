@extends('layouts.app')

@section('title', 'Edit Orang Tua')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-lg font-semibold text-slate-800">Edit Data Orang Tua</h2>
            <p class="mb-6 text-sm text-slate-500">Perbarui data akun dan data ibu {{ $orangTua->nama_ibu ?? $orangTua->name }}.</p>

            <form method="POST" action="{{ route('panel.kelola-orang-tua.update', $orangTua) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @include('panel.kelola-orang-tua.partials.fields', ['orangTua' => $orangTua])
            </form>
        </div>
    </div>
@endsection
