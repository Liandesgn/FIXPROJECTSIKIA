@extends('layouts.app')

@section('title', 'Kelola Ibu')

@section('content')
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Kelola Ibu</h2>
                <p class="text-sm text-slate-500">Registrasi dan kelola akun ibu beserta status akunnya.</p>
            </div>
            <a href="{{ route('admin.ibu.create') }}"
               class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                + Registrasi Ibu
            </a>
        </div>

        <form method="GET" action="{{ route('admin.ibu.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama ibu..."
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

            <select name="status"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                <option value="">Semua Status Akun</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>

            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">
                Filter
            </button>
            @if (request()->filled('search') || request()->filled('wilayah_kerja_id') || request()->filled('status'))
                <a href="{{ route('admin.ibu.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
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
                            <th class="px-5 py-3">Wilayah</th>
                            <th class="px-5 py-3">Status Akun</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($ibus as $ibu)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <div class="font-medium text-slate-800">{{ $ibu->name }}</div>
                                    <div class="text-xs text-slate-400">NIK {{ $ibu->nik ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $ibu->email }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $ibu->phone ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600">
                                    {{ $ibu->wilayahKerja?->desa ?? '-' }}
                                    <div class="text-xs text-slate-400">{{ $ibu->wilayahKerja?->kecamatan ?? '' }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    @include('admin.partials.status', ['status' => $ibu->status])
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-3 text-xs font-medium">
                                        <a href="{{ route('admin.ibu.edit', $ibu) }}" class="text-sky-600 hover:text-sky-700">Edit</a>
                                        <form method="POST" action="{{ route('admin.ibu.toggle-status', $ibu) }}">
                                            @csrf
                                            @method('PATCH')
                                            @if ($ibu->status === \App\Enums\UserStatusEnum::Active)
                                                <button type="submit" class="text-amber-600 hover:text-amber-700">Nonaktifkan</button>
                                            @else
                                                <button type="submit" class="text-emerald-600 hover:text-emerald-700">Aktifkan</button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada data ibu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $ibus->links() }}
        </div>
    </div>
@endsection
