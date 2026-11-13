@extends('layouts.app')

@section('title', 'Tambah Permission')

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Permission</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Akses &amp; Hak / Kelola Permission / Tambah</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-6 py-4">
            <h2 class="text-lg font-bold text-[#542D3D]">Tambah Permission</h2>
            <p class="mt-0.5 text-sm text-slate-600">Tambahkan hak akses baru ke sistem.</p>
        </div>

        <form method="POST" action="{{ route('superadmin.permissions.store') }}" class="space-y-5 p-4 sm:p-6">
            @csrf

            @include('superadmin.permissions.partials.form', [
                'permission' => null,
                'roles'      => $roles ?? collect(),
                'locked'     => false,
            ])
        </form>
    </div>
</div>
@endsection