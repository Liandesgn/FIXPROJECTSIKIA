@extends('layouts.app')

@section('title', 'Rekap Kegiatan Posyandu')

@section('content')
@php
    $inputClass = 'rounded-xl border-2 border-[#C9517E]/60 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 outline-none focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
@endphp
<div class="mx-auto w-full max-w-[96rem] space-y-5">

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Posyandu</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Posyandu</p>
    </div>

    @include('admin.data-posyandu.partials.tabs', ['active' => 'rekap'])

    <form method="GET" action="{{ route('admin.data-posyandu.rekap') }}" class="flex flex-wrap items-center gap-2">
        <input type="month" name="periode" value="{{ $periodeValue }}" class="{{ $inputClass }}">

        <select name="posyandu_id" class="{{ $inputClass }}">
            <option value="">Semua Posyandu</option>
            @foreach ($posyandus as $p)
                <option value="{{ $p->id }}" @selected((string) request('posyandu_id') === (string) $p->id)>{{ $p->nama_posyandu }}</option>
            @endforeach
        </select>

        <button type="submit" class="rounded-xl bg-[#C9517E] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#B7436D]">Tampilkan</button>
    </form>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold text-slate-600">Jumlah Kegiatan</p>
            <p class="mt-1 text-3xl font-bold text-[#C9517E]">{{ $ringkasan['kegiatan'] }}</p>
        </div>
        <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold text-slate-600">Anak Diperiksa</p>
            <p class="mt-1 text-3xl font-bold text-[#C9517E]">{{ $ringkasan['anak'] }}</p>
        </div>
        <div class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold text-slate-600">Posyandu Aktif</p>
            <p class="mt-1 text-3xl font-bold text-[#C9517E]">{{ $ringkasan['posyandu'] }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#FCE7F0] text-xs font-bold uppercase tracking-wide text-[#542D3D]">
                    <tr>
                        <th class="px-4 py-3">Posyandu</th>
                        <th class="px-4 py-3">Wilayah</th>
                        <th class="px-4 py-3">Jumlah Kegiatan</th>
                        <th class="px-4 py-3">Anak Diperiksa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F7E6ED]">
                    @forelse ($rekap as $r)
                        <tr class="hover:bg-[#FCE7F0]/50">
                            <td class="px-4 py-3.5 text-sm font-bold text-[#542D3D]">{{ $r['posyandu']?->nama_posyandu ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $r['posyandu']?->wilayahKerja?->nama_wilayah ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $r['kegiatan'] }}</td>
                            <td class="px-4 py-3.5 text-sm text-slate-800">{{ $r['anak'] }}</td>
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