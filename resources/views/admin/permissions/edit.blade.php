@extends('layouts.app')

@section('content')
<div class="max-w-xl space-y-6">
    <h1 class="text-2xl font-bold text-[#542D3D]">Edit Permission</h1>

    <form method="POST" action="{{ route('admin.permissions.update', $permission) }}"
          class="space-y-4 rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('admin.permissions.partials.form')
    </form>
</div>
@endsection