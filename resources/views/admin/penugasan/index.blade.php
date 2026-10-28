@extends('layouts.app')

@section('title', 'Penugasan Wilayah')

@section('content')
<div class="mx-auto w-full max-w-[96rem] space-y-5">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
            Kelola Wilayah Penugasan
        </h1>
        <p class="mt-1 text-sm font-medium text-slate-700">
            Data Master / Kelola Wilayah Kerja
        </p>
    </div>

    {{-- Navigation Tabs --}}
    @include('admin.wilayah.partials.tabs', ['active' => 'penugasan'])

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        {{-- Search and Add Button --}}
        <div class="flex flex-col gap-3 border-b border-[#F1D5E0] bg-[#FCE7F0] p-3 sm:flex-row sm:items-center sm:justify-between sm:p-4">

            <form method="GET"
                  action="{{ route('admin.penugasan.index') }}"
                  class="flex flex-1 gap-2">

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    autocomplete="off"
                    placeholder="Cari wilayah"
                    class="w-full max-w-md rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-medium outline-none focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40"
                >

                <button
                    type="submit"
                    class="rounded-xl bg-[#C9517E] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#B7436D]"
                >
                    Cari
                </button>

                @if (request()->filled('search'))
                    <a
                        href="{{ route('admin.penugasan.index') }}"
                        class="px-3 py-2.5 text-sm text-slate-600 hover:underline"
                    >
                        Reset
                    </a>
                @endif
            </form>

            @can('wilayah.update')
                <a
                    href="{{ route('admin.penugasan.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 hover:bg-[#B7436D]"
                >
                    <span class="text-lg leading-none">+</span>
                    Tambah Penugasan Bidan dan Kader
                </a>
            @endcan
        </div>

        {{-- Assignment Table --}}
        <div class="max-h-[28rem] overflow-x-auto overflow-y-auto">
            <table class="w-full text-left">

                <thead class="sticky top-0 z-10 bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="bg-[#FCE7F0] px-4 py-3">No</th>
                        <th class="bg-[#FCE7F0] px-4 py-3">Wilayah</th>
                        <th class="bg-[#FCE7F0] px-4 py-3">Bidan Penanggung Jawab</th>
                        <th class="bg-[#FCE7F0] px-4 py-3">Kader Wilayah</th>
                        <th class="bg-[#FCE7F0] px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($wilayahs as $w)

                        @php
                            $kaders = $kaderMap->get($w->id, collect());
                        @endphp

                        <tr class="align-top hover:bg-[#FCE7F0]/50">

                            {{-- Number --}}
                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">
                                {{ $loop->iteration }}
                            </td>

                            {{-- Region --}}
                            <td class="px-4 py-3.5 text-sm font-bold text-[#542D3D]">
                                {{ $w->nama_wilayah }}
                            </td>

                            {{-- Assigned Midwife --}}
                            <td class="px-4 py-3.5 text-sm text-slate-800">
                                {{ $bidanMap->get($w->id)?->user?->name ?? '-' }}
                            </td>

                            {{-- Assigned Cadres --}}
                            <td class="px-4 py-3.5 text-sm text-slate-800">
                                @forelse ($kaders as $k)
                                    <p>{{ $k->user?->name ?? '-' }}</p>
                                @empty
                                    -
                                @endforelse
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-center gap-2">

                                    @can('wilayah.update')
                                        <a
                                            href="{{ route('admin.penugasan.edit', $w) }}"
                                            class="inline-flex h-8 items-center rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white hover:bg-[#B7436D]"
                                        >
                                            Edit
                                        </a>
                                    @endcan

                                    @if ($bidanMap->has($w->id) || $kaders->isNotEmpty())

                                        @can('wilayah.delete')
                                            <form
                                                method="POST"
                                                action="{{ route('admin.penugasan.destroy', $w) }}"
                                                class="m-0"
                                                data-confirm-delete
                                                data-name="{{ $w->nama_wilayah }}"
                                                data-title="Apakah akan menghapus penugasan {{ $w->nama_wilayah }}?"
                                                data-text="Bidan dan kader akan dilepas dari wilayah ini. Akun mereka tidak dihapus. Lanjutkan?"
                                                data-confirm-text="Ya, Hapus"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex h-8 items-center rounded-lg bg-red-600 px-3 text-xs font-bold text-white hover:bg-red-700"
                                                >
                                                    Hapus
                                                </button>
                                            </form>
                                        @endcan

                                    @endif
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                                Belum ada data wilayah penugasan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Total Data --}}
        <div class="border-t border-[#F1D5E0] bg-white px-4 py-3 text-xs font-semibold text-slate-700">
            Menampilkan {{ $wilayahs->count() }} data
        </div>

    </div>
</div>
@endsection