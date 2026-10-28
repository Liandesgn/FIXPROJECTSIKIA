@extends('layouts.app')

@section('title', 'Edit Data Anak')

@section('content')
<div class="mx-auto w-full max-w-4xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-[#542D3D]">Edit Data Anak</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Anak / Edit Data Anak</p>
    </div>

    <form method="POST" action="{{ route('admin.data-anak.update', $anak) }}"
          class="space-y-5 rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('data-anak.partials.form')
    </form>
</div>
@endsection