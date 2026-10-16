@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
            <h2 class="text-xl font-semibold text-slate-800">{{ $title }}</h2>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">{{ $description }}</p>
        </div>
    </div>
@endsection
