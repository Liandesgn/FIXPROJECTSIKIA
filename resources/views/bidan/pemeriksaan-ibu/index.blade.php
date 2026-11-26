
@extends('layouts.app')

@section('title', 'Pemeriksaan Ibu Hamil')

@section('content')
<div class="min-h-screen bg-[#FFF8FA] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        {{-- Judul halaman --}}
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-[#542D3D]">
                Pemeriksaan Ibu Hamil
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Pemeriksaan
            </p>
        </div>

        {{-- Notifikasi --}}
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 rounded-lg bg-yellow-100 px-4 py-3 text-sm text-yellow-800">
                {{ session('warning') }}
            </div>
        @endif

        {{-- Pencarian dan filter --}}
        <form method="GET"
              action="{{ route('bidan.pemeriksaan-ibu.index') }}"
              class="mb-7 flex flex-col gap-2 sm:flex-row">

            <div class="relative min-w-0 flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="m21 21-4.35-4.35M19 10.5a8.5 8.5 0 1 1-17 0 8.5 8.5 0 0 1 17 0Z"/>
                    </svg>
                </span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama / NIK"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3 text-sm text-gray-700 placeholder:text-gray-400 focus:border-[#C9517E] focus:outline-none focus:ring-1 focus:ring-[#C9517E]"
                >
            </div>

            <select
                name="risiko"
                class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-600 focus:border-[#C9517E] focus:outline-none"
            >
                <option value="">Semua Risiko</option>
                <option value="tinggi" @selected(request('risiko') === 'tinggi')>
                    Risiko Tinggi
                </option>
                <option value="sedang" @selected(request('risiko') === 'sedang')>
                    Risiko Sedang
                </option>
                <option value="rendah" @selected(request('risiko') === 'rendah')>
                    Risiko Rendah
                </option>
            </select>

            <select
                name="periode"
                class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-600 focus:border-[#C9517E] focus:outline-none"
            >
                <option value="">Semua Periode</option>
            </select>

            <button type="submit"
                    class="h-10 rounded-lg bg-[#C9517E] px-4 text-sm font-semibold text-white hover:bg-[#ad416b]">
                Cari
            </button>

            @if(request()->hasAny(['search', 'risiko', 'periode']))
                <a href="{{ route('bidan.pemeriksaan-ibu.index') }}"
                   class="flex h-10 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm text-gray-600 hover:bg-gray-50">
                    Reset
                </a>
            @endif
        </form>

        {{-- Tabel pemeriksaan --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">

                    <thead class="bg-white text-[12px] font-semibold uppercase text-gray-700">
                        <tr>
                            <th class="w-12 px-4 py-4">No</th>
                            <th class="px-4 py-4">Nama Ibu</th>
                            <th class="px-4 py-4">Usia Kehamilan</th>
                            <th class="px-4 py-4">HPL</th>
                            <th class="px-4 py-4">Status Risiko</th>
                            <th class="px-4 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 text-gray-700">

                        @forelse($ibu as $item)
                            @php
                                $terakhir = $item->pemeriksaanIbu()
                                    ->latest('tgl_pemeriksaan')
                                    ->first();

                                $usia = $item->usia_kehamilan_sekarang;

                                $warna = match($terakhir?->tingkat_risiko) {
                                    'tinggi' => 'bg-red-100 text-red-600',
                                    'sedang' => 'bg-yellow-100 text-yellow-700',
                                    'rendah' => 'bg-green-100 text-green-700',
                                    default => 'bg-gray-100 text-gray-500',
                                };
                            @endphp

                            <tr class="transition hover:bg-[#FFF8FA]">

                                <td class="px-4 py-3.5">
                                    {{ $ibu->firstItem() + $loop->index }}
                                </td>

                                <td class="px-4 py-3.5 font-medium text-gray-800">
                                    {{ $item->user?->name ?? '-' }}
                                </td>

                                <td class="px-4 py-3.5">
                                    {{ $usia !== null ? $usia . ' minggu' : '-' }}
                                </td>

                                <td class="px-4 py-3.5">
                                    {{ $item->hpl?->format('d/m/Y') ?? '-' }}
                                </td>

                                <td class="px-4 py-3.5">
                                    @if($terakhir)
                                        <span class="inline-flex min-w-[65px] justify-center rounded-full px-3 py-1 text-xs font-medium {{ $warna }}">
                                            {{ ucfirst($terakhir->tingkat_risiko ?? '-') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-center gap-1.5">

                                        {{-- Detail menuju halaman Bidan yang baru --}}
                                        <a href="{{ route('bidan.pemeriksaan-ibu.show', $item->id) }}"
                                           class="rounded-md bg-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-300">
                                            Detail
                                        </a>

                                        {{-- Input atau Edit --}}
                                        @if($terakhir)
                                            <a href="{{ route('bidan.pemeriksaan-ibu.edit', [$item->id, $terakhir->id]) }}"
                                               class="rounded-md bg-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-300">
                                                Edit
                                            </a>
                                        @else
                                            <a href="{{ route('bidan.pemeriksaan-ibu.create', $item->id) }}"
                                               class="rounded-md bg-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-300">
                                                Input
                                            </a>
                                        @endif

                                    </div>
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">
                                    Data tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- Footer tabel --}}
            <div class="flex flex-col gap-3 border-t border-gray-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

                <p class="text-xs text-gray-400">
                    Menampilkan
                    {{ $ibu->firstItem() ?? 0 }}–{{ $ibu->lastItem() ?? 0 }}
                    dari {{ $ibu->total() }} data
                </p>

                <div class="text-sm">
                    {{ $ibu->links() }}
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
