@extends('layouts.app')

@section('title', 'Kelola Kader')

@section('content')
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Kelola Kader</h2>
                <p class="text-sm text-slate-500">Kelola akun kader beserta wilayah kerjanya.</p>
            </div>
            <a href="{{ route('admin.kader.create') }}"
               class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                + Tambah Kader
            </a>
        </div>

        <form method="GET" action="{{ route('admin.kader.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kader..."
                   class="w-52 rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200">

            <select name="wilayah_kerja_id"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                <option value="">Semua Wilayah</option>
                @foreach ($wilayahs->groupBy('kecamatan') as $kecamatan => $desas)
                    <optgroup label="Kec. {{ $kecamatan ?: '-' }}">
                        @foreach ($desas as $wilayah)
                            <option value="{{ $wilayah->id }}" @selected((string) request('wilayah_kerja_id') === (string) $wilayah->id)>
                                Desa {{ $wilayah->desa }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>

            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">
                Filter
            </button>
            @if (request()->filled('search') || request()->filled('wilayah_kerja_id'))
                <a href="{{ route('admin.kader.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
            @endif
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3">No. HP</th>
                            <th class="px-5 py-3">Wilayah Kerja</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($kaders as $kader)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-800">{{ $kader->name }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $kader->email }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $kader->phone ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600">
                                    {{ $kader->wilayahKerja?->desa ?? '-' }}
                                    <div class="text-xs text-slate-400">{{ $kader->wilayahKerja?->kecamatan ?? '' }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-3 text-xs font-medium">
                                        <a href="{{ route('admin.kader.edit', $kader) }}" class="text-sky-600 hover:text-sky-700">Edit</a>
                                        <form method="POST" action="{{ route('admin.kader.destroy', $kader) }}"
                                              onsubmit="return confirm('Yakin ingin menghapus data kader ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada data kader.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $kaders->links() }}
        </div>
    </div>
@endsection
