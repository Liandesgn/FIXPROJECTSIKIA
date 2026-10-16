@extends('layouts.app')

@section('title', 'Tambah Kader')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-lg font-semibold text-slate-800">Tambah Kader</h2>
            <p class="mb-6 text-sm text-slate-500">Lengkapi data akun dan wilayah kerja kader.</p>

            <form method="POST" action="{{ route('admin.kader.store') }}" class="space-y-6">
                @csrf

                @include('admin.petugas.partials.fields', ['user' => null])

                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                        Simpan
                    </button>
                    <a href="{{ route('admin.kader.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
