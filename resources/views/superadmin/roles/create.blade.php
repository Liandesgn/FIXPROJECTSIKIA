@extends('layouts.app')

@section('title', 'Tambah Role')

@section('content')
@php
    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
@endphp

<div class="mx-auto w-full max-w-3xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Role</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Akses &amp; Hak / Kelola Role / Tambah</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-6 py-4">
            <h2 class="text-lg font-bold text-[#542D3D]">Tambah Role</h2>
            <p class="mt-0.5 text-sm text-slate-600">Isi nama role dan pilih permission yang dimilikinya.</p>
        </div>

        <form action="{{ route('superadmin.roles.store') }}" method="POST" class="space-y-5 p-4 sm:p-6">
            @csrf

            <div>
                <label for="name" class="{{ $labelClass }}">Nama Role <span class="text-[#C9517E]">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: bidan" required
                       class="{{ $fieldClass }}">
                @error('name')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <p class="{{ $labelClass }}">Pilih Permission</p>

                <div class="mt-2 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[#E99AB7] bg-white px-3.5 py-3 text-sm font-semibold text-[#542D3D] transition hover:bg-[#FCE7F0] has-[:checked]:border-[#C9517E] has-[:checked]:bg-[#FCE7F0]">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                   @checked(in_array($permission->name, old('permissions', [])))
                                   class="h-5 w-5 shrink-0 rounded border-slate-400 text-[#C9517E] focus:ring-[#C9517E]">
                            <span class="break-all">{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>

                @error('permissions')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
                @error('permissions.*')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('superadmin.roles.index') }}"
                   class="inline-flex w-full justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] sm:w-auto">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex w-full justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 sm:w-auto">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
