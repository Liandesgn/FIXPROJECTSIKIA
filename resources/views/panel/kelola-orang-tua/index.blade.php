@extends('layouts.app')

@section('title', 'Kelola Orang Tua')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Kelola Orang Tua</h2>
                <p class="text-sm text-slate-500">Registrasi dan pemantauan data orang tua beserta wilayah kerjanya.</p>
            </div>
            <a href="{{ route('panel.kelola-orang-tua.create') }}"
               class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                + Registrasi Orang Tua
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total Orang Tua</p>
                <p class="mt-2 text-3xl font-bold text-sky-600">{{ $stats['orang_tua'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Sedang Hamil</p>
                <p class="mt-2 text-3xl font-bold text-pink-600">{{ $stats['hamil'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Kecamatan</p>
                <p class="mt-2 text-3xl font-bold text-violet-600">{{ $stats['kecamatan'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Desa</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['desa'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="font-semibold text-slate-800">Wilayah Kerja</h3>
                    <p class="text-sm text-slate-500">Setiap desa memiliki 1 bidan dan 1 kader yang bertugas.</p>
                </div>
                <div class="max-h-[28rem] space-y-3 overflow-y-auto p-5">
                    @forelse ($wilayahs as $kecamatan => $desas)
                        <details class="rounded-lg border border-slate-200">
                            <summary class="cursor-pointer px-4 py-2 text-sm font-semibold text-slate-700">
                                Kec. {{ $kecamatan }}
                                <span class="ml-1 text-xs font-normal text-slate-400">({{ $desas->count() }} desa)</span>
                            </summary>
                            <div class="divide-y divide-slate-100">
                                @foreach ($desas as $wilayah)
                                    <div class="px-4 py-3 text-sm">
                                        <p class="font-medium text-slate-700">Desa {{ $wilayah->desa }}</p>
                                        <div class="mt-1 grid grid-cols-1 gap-1 text-xs text-slate-500 sm:grid-cols-3">
                                            <span>Bidan: {{ $wilayah->bidan?->name ?? '-' }}</span>
                                            <span>Kader: {{ $wilayah->kader?->name ?? '-' }}</span>
                                            <span>Orang Tua: {{ $wilayah->orang_tua_count }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada data wilayah.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-5 py-4">
                    <h3 class="font-semibold text-slate-800">Daftar Orang Tua</h3>
                    <form method="GET" action="{{ route('panel.kelola-orang-tua.index') }}" class="flex items-center gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIK..."
                               class="w-40 rounded-lg border border-slate-300 px-3 py-1.5 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                        <select name="kecamatan" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm outline-none focus:border-sky-500">
                            <option value="">Semua Kec.</option>
                            @foreach ($kecamatans as $kecamatan)
                                <option value="{{ $kecamatan }}" @selected(request('kecamatan') === $kecamatan)>{{ $kecamatan }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-lg bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-900">Cari</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Ibu</th>
                                <th class="px-5 py-3">Wilayah</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($orangTua as $item)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3">
                                        <div class="font-medium text-slate-800">{{ $item->nama_ibu ?? $item->name }}</div>
                                        <div class="text-xs text-slate-400">NIK {{ $item->nik ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-3 text-slate-600">
                                        {{ $item->wilayahKerja?->desa ?? '-' }}
                                        <div class="text-xs text-slate-400">{{ $item->wilayahKerja?->kecamatan ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($item->status_kehamilan)
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $item->sedangHamil() ? 'bg-pink-50 text-pink-700' : 'bg-emerald-50 text-emerald-700' }}">
                                                {{ $item->status_kehamilan->label() }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-end gap-3 text-xs font-medium">
                                            <a href="{{ route('panel.kelola-orang-tua.show', $item) }}" class="text-slate-600 hover:text-slate-900">Lihat</a>
                                            <a href="{{ route('panel.kelola-orang-tua.edit', $item) }}" class="text-sky-600 hover:text-sky-700">Edit</a>
                                            <form method="POST" action="{{ route('panel.kelola-orang-tua.destroy', $item) }}"
                                                  onsubmit="return confirm('Yakin ingin menghapus data orang tua ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-700">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada data orang tua.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4">
                    {{ $orangTua->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
