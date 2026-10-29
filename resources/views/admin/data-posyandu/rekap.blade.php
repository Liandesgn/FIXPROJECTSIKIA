@extends('layouts.app')

@section('title', 'Rekap Kegiatan Posyandu')

@section('content')
@php
    $fieldBase = 'w-full rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $cards = [
        ['label' => 'Jumlah Kegiatan', 'value' => $ringkasan['kegiatan'], 'icon' => 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z'],
        ['label' => 'Anak Diperiksa',  'value' => $ringkasan['anak'],     'icon' => 'M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6'],
        ['label' => 'Posyandu Aktif',  'value' => $ringkasan['posyandu'], 'icon' => 'M3 21h18M5 21V8l7-5 7 5v13M10 21v-6h4v6'],
    ];
@endphp
<div class="mx-auto w-full max-w-[96rem] space-y-5">

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Posyandu</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Posyandu</p>
    </div>

    @include('admin.data-posyandu.partials.tabs', ['active' => 'rekap'])

    {{-- Filter periode & Posyandu --}}
    <form method="GET" action="{{ route('admin.data-posyandu.rekap') }}"
          class="flex flex-col gap-3 rounded-2xl border border-[#E99AB7] bg-[#FCE7F0] p-3 sm:p-4 md:flex-row md:items-center">
        <div class="md:w-56">
            <label for="periode" class="sr-only">Periode</label>
            <input id="periode" type="month" name="periode" value="{{ $periodeValue }}" class="{{ $fieldBase }}">
        </div>

        <div class="md:w-64">
            <label for="posyandu_id" class="sr-only">Posyandu</label>
            <select id="posyandu_id" name="posyandu_id" class="{{ $fieldBase }}">
                <option value="">Semua Posyandu</option>
                @foreach ($posyandus as $p)
                    <option value="{{ $p->id }}" @selected((string) request('posyandu_id') === (string) $p->id)>{{ $p->nama_posyandu }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit"
                class="rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
            Tampilkan
        </button>
    </form>

    {{-- Ringkasan --}}
    <div class="grid max-w-4xl grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach ($cards as $card)
            <div class="flex items-center gap-3 rounded-xl border border-[#F1D5E0] bg-[#FCE7F0] p-3.5 sm:p-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-[#C9517E] shadow-sm">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold leading-tight text-[#542D3D] sm:text-sm">{{ $card['label'] }}</p>
                    <p class="mt-0.5 text-2xl font-bold leading-none text-[#C9517E]">{{ $card['value'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Tabel rekap --}}
    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="max-h-[28rem] overflow-x-auto overflow-y-auto">
            <table class="w-full text-left">
                <thead class="sticky top-0 z-10 text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-4 py-3">Posyandu</th>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-4 py-3">Wilayah</th>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-4 py-3">Jumlah Kegiatan</th>
                        <th class="whitespace-nowrap bg-[#FCE7F0] px-4 py-3">Anak Diperiksa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($rekap as $r)
                        <tr class="transition hover:bg-[#FCE7F0]/50">
                            <td class="px-4 py-3.5 text-sm font-bold text-[#542D3D]">{{ $r['posyandu']?->nama_posyandu ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-sm font-medium text-slate-800">{{ $r['posyandu']?->wilayahKerja?->nama_wilayah ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-800">{{ $r['kegiatan'] }}</td>
                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-800">{{ $r['anak'] }}</td>
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