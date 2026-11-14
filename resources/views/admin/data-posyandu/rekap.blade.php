@extends('layouts.app')

@section('title', 'Rekap Kegiatan Posyandu')

@section('content')
@php
    $base = $base ?? 'admin';

    $fieldClass  = 'w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $lockedClass = 'flex w-full items-center gap-2 rounded-xl border border-[#E99AB7] bg-slate-100 px-3.5 py-2.5 text-sm font-semibold text-slate-700';

    $daftarWilayah = collect($wilayahs ?? []);
    $bisaPilihWilayah = $daftarWilayah->count() > 1;

    $kartu = [
        ['Jumlah Kegiatan', $ringkasan['kegiatan'], 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z'],
        ['Anak Diperiksa', $ringkasan['anak'], 'M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6'],
        ['Wilayah Aktif', $ringkasan['wilayah'], 'M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z'],
    ];
@endphp

<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- Judul --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Posyandu</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Posyandu</p>
    </div>

    @include('admin.data-posyandu.partials.tabs', ['active' => 'rekap', 'base' => $base])

    {{-- Filter --}}
    <form method="GET" action="{{ route($base . '.data-posyandu.rekap') }}"
          class="grid gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-4 shadow-sm sm:grid-cols-2 lg:flex lg:items-center">
        <div>
            <label for="periode" class="sr-only">Periode</label>
            <input id="periode" type="month" name="periode" value="{{ $periodeValue }}" class="{{ $fieldClass }} lg:w-52">
        </div>

        @if ($bisaPilihWilayah)
            <div>
                <label for="id_wilayah" class="sr-only">Wilayah</label>
                <select id="id_wilayah" name="id_wilayah" class="{{ $fieldClass }} lg:w-56">
                    <option value="">Semua Wilayah</option>
                    @foreach ($daftarWilayah as $w)
                        <option value="{{ $w->id }}" @selected((string) request('id_wilayah') === (string) $w->id)>{{ $w->nama_wilayah }}</option>
                    @endforeach
                </select>
            </div>
        @elseif ($daftarWilayah->count() === 1)
            <div class="{{ $lockedClass }} lg:w-56" title="Wilayah sesuai akun Anda">
                <svg class="h-4 w-4 shrink-0 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>
                <span class="truncate">{{ $daftarWilayah->first()->nama_wilayah }}</span>
            </div>
        @endif

        <button type="submit"
                class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
            Tampilkan
        </button>
    </form>

    {{-- Kartu ringkasan --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach ($kartu as [$label, $nilai, $ikon])
            <div class="flex items-center gap-3 rounded-2xl border border-[#F1D5E0] bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#FCE7F0] text-[#C9517E]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-[#542D3D]">{{ $label }}</p>
                    <p class="mt-0.5 text-2xl font-bold leading-none text-[#C9517E]">{{ $nilai }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Tabel rekap --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="whitespace-nowrap px-3 py-3 sm:px-4">Wilayah</th>
                        <th class="hidden whitespace-nowrap px-4 py-3 md:table-cell">Kecamatan</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Jumlah Kegiatan</th>
                        <th class="whitespace-nowrap px-3 py-3 text-center sm:px-4">Anak Diperiksa</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($rekap as $r)
                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-3 py-3.5 sm:px-4">
                                <p class="text-sm font-bold text-[#542D3D]">{{ $r['wilayah']?->nama_wilayah ?? '-' }}</p>
                                <p class="text-xs text-slate-700 md:hidden">{{ $r['wilayah']?->kecamatan ?? '-' }}</p>
                            </td>
                            <td class="hidden px-4 py-3.5 text-sm text-slate-800 md:table-cell">{{ $r['wilayah']?->kecamatan ?? '-' }}</td>
                            <td class="px-3 py-3.5 text-center text-sm font-bold text-[#C9517E] sm:px-4">{{ $r['kegiatan'] }}</td>
                            <td class="px-3 py-3.5 text-center text-sm font-bold text-[#C9517E] sm:px-4">{{ $r['anak'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#542D3D]">Tidak ada kegiatan pada periode ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection