@extends('layouts.app')

@section('title', 'Detail Orang Tua')

@section('content')
    <div class="mx-auto max-w-3xl space-y-4">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">{{ $orangTua->nama_ibu ?? $orangTua->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $orangTua->email }}</p>
                </div>
                @if ($orangTua->status_kehamilan)
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $orangTua->sedangHamil() ? 'bg-pink-50 text-pink-700' : 'bg-emerald-50 text-emerald-700' }}">
                        {{ $orangTua->status_kehamilan->label() }}
                    </span>
                @endif
            </div>

            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Data Akun</p>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">Nama Akun</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $orangTua->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">No. HP</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $orangTua->phone ?? '-' }}</dd>
                </div>
            </dl>

            <p class="mb-2 mt-6 text-xs font-semibold uppercase tracking-wide text-slate-400">Data Ibu</p>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">NIK</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $orangTua->nik ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">Nama Ibu</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $orangTua->nama_ibu ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">TTL</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $orangTua->ttl() ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">Status Kehamilan</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $orangTua->status_kehamilan?->label() ?? '-' }}</dd>
                </div>
                @if ($orangTua->sedangHamil())
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-400">HPL</dt>
                        <dd class="text-sm font-medium text-slate-700">{{ $orangTua->hpl?->format('d-m-Y') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Usia Kehamilan</dt>
                        <dd class="text-sm font-medium text-slate-700">{{ $orangTua->usia_kehamilan ? $orangTua->usia_kehamilan.' minggu' : '-' }}</dd>
                    </div>
                @endif
            </dl>

            <p class="mb-2 mt-6 text-xs font-semibold uppercase tracking-wide text-slate-400">Wilayah</p>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">Kabupaten</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $orangTua->wilayahKerja?->kabupaten ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">Kecamatan</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $orangTua->wilayahKerja?->kecamatan ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">Desa</dt>
                    <dd class="text-sm font-medium text-slate-700">{{ $orangTua->wilayahKerja?->desa ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="font-semibold text-slate-800">Petugas Wilayah</h3>
            <p class="mb-4 text-sm text-slate-500">Bidan dan kader yang bertugas di wilayah orang tua ini.</p>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Bidan</p>
                    <p class="mt-1 text-sm font-medium text-slate-700">{{ $orangTua->bidanWilayah?->name ?? 'Belum ada' }}</p>
                    <p class="text-xs text-slate-500">{{ $orangTua->bidanWilayah?->phone ?? '-' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Kader</p>
                    <p class="mt-1 text-sm font-medium text-slate-700">{{ $orangTua->kaderWilayah?->name ?? 'Belum ada' }}</p>
                    <p class="text-xs text-slate-500">{{ $orangTua->kaderWilayah?->phone ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-4">
                <a href="{{ route('panel.kelola-orang-tua.edit', $orangTua) }}"
                   class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                    Edit
                </a>
                <a href="{{ route('panel.kelola-orang-tua.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Kembali</a>
            </div>
        </div>
    </div>
@endsection
