
@extends('layouts.app')

@section('title', 'Tambah Role')

@section('content')
<div class="mx-auto w-full max-w-4xl space-y-5">

    <div>
        <h1 class="text-2xl font-bold text-[#542D3D]">Tambah Role</h1>
        <p class="mt-1 text-sm text-slate-600">Akses &amp; Hak / Kelola Role / Tambah</p>
    </div>

    <div class="rounded-2xl border border-[#F1D5E0] bg-white p-6 shadow-sm">

        <form action="{{ route('superadmin.roles.store') }}" method="POST">
            @csrf

            <div class="mb-5">
                <label class="mb-2 block text-sm font-bold text-[#542D3D]">
                    Nama Role
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Contoh: bidan"
                    class="w-full rounded-xl border border-[#E8C9D5] px-4 py-3 text-sm focus:border-[#C9517E] focus:outline-none"
                    required
                >

                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="mb-3 block text-sm font-bold text-[#542D3D]">
                    Pilih Permission
                </label>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-2 rounded-lg border border-[#F1D5E0] p-3 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->name }}"
                                @checked(in_array($permission->name, old('permissions', [])))
                                class="rounded border-[#C9517E] text-[#C9517E] focus:ring-[#C9517E]"
                            >
                            {{ $permission->name }}
                        </label>
                    @endforeach
                </div>

                @error('permissions')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a
                    href="{{ route('superadmin.roles.index') }}"
                    class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-bold text-slate-600"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#B7436D]"
                >
                    Simpan Role
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
