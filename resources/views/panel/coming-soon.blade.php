@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-sky-100 text-2xl">🧩</div>
            <h2 class="text-xl font-semibold text-slate-800">{{ $title }}</h2>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">{{ $description }}</p>
            <p class="mx-auto mt-6 inline-block rounded-lg bg-amber-50 px-4 py-2 text-sm font-medium text-amber-700">
                Modul ini akan tersedia pada pengembangan berikutnya.
            </p>
        </div>
    </div>
@endsection
