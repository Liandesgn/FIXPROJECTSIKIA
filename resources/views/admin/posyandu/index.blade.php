@extends('layouts.app')

@section('title', 'Kelola Posyandu')

@section('content')
<div class="mx-auto w-full max-w-[96rem] space-y-5">

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Wilayah Posyandu</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Kelola Wilayah Kerja</p>
    </div>

    @include('admin.wilayah.partials.tabs', ['active' => 'posyandu'])

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-[#F1D5E0] bg-[#FCE7F0] p-3 sm:flex-row sm:items-center sm:justify-between sm:p-4">
            <form method="GET" action="{{ route('admin.posyandu.index') }}" class="flex flex-1 flex-wrap gap-2">
                <input type="search" name="search" value="{{ request('search') }}" autocomplete="off"
                       placeholder="Cari posyandu"
                       class="w-full max-w-xs rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-medium outline-none focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40">

                <select name="wilayah_kerja_id" onchange="this.form.submit()"
                        class="rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold outline-none focus:border-[#C9517E]">
                    <option value="">Semua wilayah</option>
                    @foreach ($wilayahs as $w)
                        <option value="{{ $w->id }}" @selected((string) request('wilayah_kerja_id') === (string) $w->id)>
                            {{ $w->nama_wilayah }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-xl bg-[#C9517E] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#B7436D]">Cari</button>
                @if (request()->hasAny(['search', 'wilayah_kerja_id']))
                    <a href="{{ route('admin.posyandu.index') }}" class="px-3 py-2.5 text-sm text-slate-600 hover:underline">Reset</a>
                @endif
            </form>

            @can('wilayah.create')
                <a href="{{ route('admin.posyandu.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 hover:bg-[#B7436D]">
                    <span class="text-lg leading-none">+</span> Tambah Posyandu
                </a>
            @endcan
        </div>

        <div class="max-h-[28rem] overflow-x-auto overflow-y-auto">
            <table class="w-full text-left">
                <thead class="sticky top-0 z-10 bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="bg-[#FCE7F0] px-4 py-3">No</th>
                        <th class="bg-[#FCE7F0] px-4 py-3">Nama Posyandu</th>
                        <th class="bg-[#FCE7F0] px-4 py-3">Wilayah</th>
                        <th class="bg-[#FCE7F0] px-4 py-3">Alamat</th>
                        <th class="bg-[#FCE7F0] px-4 py-3">Kader</th>
                        <th class="bg-[#FCE7F0] px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($posyandus as $p)
                        <tr class="hover:bg-[#FCE7F0]/50">
                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3.5 text-sm font-bold text-[#542D3D]">{{ $p->nama_posyandu }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $p->wilayahKerja?->nama_wilayah ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $p->alamat }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $kaderCount[$p->wilayah_kerja_id] ?? 0 }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-center gap-2">
                                    @can('wilayah.update')
                                        <a href="{{ route('admin.posyandu.edit', $p) }}"
                                           class="inline-flex h-8 items-center rounded-lg bg-[#C9517E] px-3 text-xs font-bold text-white hover:bg-[#B7436D]">Edit</a>
                                    @endcan
                                    @can('wilayah.delete')
                                        <form method="POST" action="{{ route('admin.posyandu.destroy', $p) }}" class="m-0"
                                              data-confirm-delete
                                              data-name="{{ $p->nama_posyandu }}"
                                              data-title="Apakah akan menghapus posyandu {{ $p->nama_posyandu }}?"
                                              data-text="Data posyandu akan dihapus secara permanen dan tidak dapat dipulihkan. Lanjutkan?"
                                              data-confirm-text="Ya, Hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 items-center rounded-lg bg-red-600 px-3 text-xs font-bold text-white hover:bg-red-700">Hapus</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">Data posyandu tidak ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-[#F1D5E0] bg-white px-4 py-3 text-xs font-semibold text-slate-700">
            Menampilkan {{ $posyandus->count() }} data
        </div>
    </div>
</div>
@endsection