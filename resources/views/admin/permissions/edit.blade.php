@extends('layouts.app')

@section('title', 'Edit Permission')

@section('content')
@php
    $routePrefix = request()->routeIs('admin.*')
        ? 'admin'
        : 'superadmin';
@endphp

<div class="max-w-xl space-y-6">
    <div>
        <h1 class="text-xl font-bold text-[#542D3D]">
            Edit Permission
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Ubah nama permission atau role yang dapat menggunakannya.
        </p>
    </div>

    <form
        method="POST"
        action="{{ route($routePrefix . '.permissions.update', $permission) }}"
        class="space-y-5"
    >
        @csrf
        @method('PUT')

        @include('admin.permissions.partials.form')
    </form>
</div>
@endsection