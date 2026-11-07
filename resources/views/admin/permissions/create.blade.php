@extends('layouts.app')

@section('title', 'Tambah Permission')

@section('content')
@php
    $routePrefix = request()->routeIs('admin.*')
        ? 'admin'
        : 'superadmin';
@endphp

<div class="max-w-xl space-y-6">
    <div>
        <h1 class="text-xl font-bold text-[#542D3D]">
            Tambah Permission
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Tambahkan hak akses baru ke sistem.
        </p>
    </div>

    <form
        method="POST"
        action="{{ route($routePrefix . '.permissions.store') }}"
        class="space-y-5"
    >
        @csrf

        @include('admin.permissions.partials.form')
    </form>
</div>
@endsection