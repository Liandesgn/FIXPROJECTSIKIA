@extends('layouts.app')

@section('title', 'Data Ibu')

@section('content')
@php
    $inputClass = 'rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 outline-none focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
@endphp
<div class="mx-auto w-full max-w-[96rem] space-y-5">

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Ibu</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Ibu</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        <div class="border-b border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4">
            <form method="GET" action="{{ route($base.'.data-ibu.index') }}" class="flex flex-wrap gap-2">
                <input type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                       placeholder="Cari nama / NIK" class="{{ $inputClass }} w-full max-w-xs">

                @if ($base === 'admin')
                    <select name="wilayah_kerja_id" onchange="this.form.submit()" class="{{ $inputClass }}">
                        <option value="">Semua wilayah</option>
                        @foreach ($wilayahs as $w)
                            <option value="{{ $w->id }}" @selected((string) request('wilayah_kerja_id') === (string) $w->id)>{{ $w->nama_wilayah }}</option>
                        @endforeach
                    </select>
                @endif

                <select name="status_kehamilan" onchange="this.form.submit()" class="{{ $inputClass }}">
                    <option value="">Semua status kehamilan</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected(request('status_kehamilan') === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-xl bg-[#C9517E] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#B7436D]">Cari</button>
                @if (request()->hasAny(['search', 'wilayah_kerja_id', 'status_kehamilan']))
                    <a href="{{ route($base.'.data-ibu.index') }}" class="px-3 py-2.5 text-sm text-slate-600 hover:underline">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">NIK</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Wilayah</th>
                        <th class="px-4 py-3">Status Kehamilan</th>
                        <th class="px-4 py-3">Usia Kehamilan</th>
                        <th class="px-4 py-3">HPL</th>
                        {{-- UBAH 1: kolom Aksi disembunyikan untuk Kader --}}
                        @if ($base !== 'kader')
                            <th class="px-4 py-3 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($ibus as $ibu)
                        <tr class="hover:bg-[#FCE7F0]/50">
                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">{{ $ibus->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $ibu->nik ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-sm font-bold text-[#542D3D]">{{ $ibu->user?->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $ibu->wilayahKerja?->desa ?? '-' }}</td>
                            <td class="px-4 py-3.5">@include('data-ibu.partials.badge', ['status' => $ibu->status_kehamilan])</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $ibu->usia_kehamilan ? $ibu->usia_kehamilan.' mgg' : '-' }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $ibu->hpl?->format('d/m/Y') ?? '-' }}</td>

                            {{-- UBAH 2: tombol Detail/Edit disembunyikan untuk Kader --}}
                            @if ($base !== 'kader')
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route($base.'.data-ibu.show', $ibu) }}"
                                           class="inline-flex h-8 items-center rounded-lg border border-[#C9517E] px-3 text-xs font-bold text-[#C9517E] hover:bg-[#FCE7F0]">Detail</a>
                                        @if ($base === 'admin')
                                            @can('ibu.update')
                                                <a href="{{ route('admin.data-ibu.edit', $ibu) }}"
                                                   class="inline-flex h-8 items-center rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white hover:bg-[#B7436D]">Edit</a>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            {{-- UBAH 3: jumlah kolom 7 untuk Kader, 8 untuk role lain --}}
                            <td colspan="{{ $base === 'kader' ? 7 : 8 }}" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">Data tidak ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[#F1D5E0] bg-white px-4 py-3">
            <span class="text-xs font-semibold text-slate-700">
                Menampilkan {{ $ibus->firstItem() ?? 0 }}-{{ $ibus->lastItem() ?? 0 }} dari {{ $ibus->total() }} data
            </span>
            <div>{{ $ibus->links() }}</div>
        </div>
    </div>
</div>
@endsection