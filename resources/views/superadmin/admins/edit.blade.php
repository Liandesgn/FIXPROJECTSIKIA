@extends('layouts.app')

@section('title', 'Edit Admin Wilayah')

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-[#542D3D]">Edit Admin Wilayah</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Manajemen Admin / Admin Wilayah / Edit</p>
    </div>

    <form method="POST" action="{{ route('superadmin.admins.update', $admin) }}"
          class="space-y-5 rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('superadmin.admins.partials.form', ['admin' => $admin])
    </form>
</div>
@endsection
