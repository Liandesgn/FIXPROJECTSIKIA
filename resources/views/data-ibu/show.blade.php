@extends('layouts.app')

@section('title', $base === 'ibu' ? 'Data Ibu' : 'Detail Ibu')

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6">

    @if ($base === 'ibu')
        {{-- Role Ibu: sapaan saja --}}
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
            Halo, {{ \Illuminate\Support\Str::before($ibu->user?->name ?? auth()->user()->name, ' ') }}
        </h1>
    @else
        {{-- Admin dan Bidan: judul dan tombol --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Detail Ibu</h1>
                <p class="mt-1 text-sm font-medium text-slate-600">{{ $base === 'admin' ? 'Data Master / ' : '' }}Data Ibu / Detail</p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route($base.'.data-ibu.index') }}"
                   class="inline-flex justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-5 py-2.5 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">
                    Kembali
                </a>

                @if ($base === 'admin')
                    @can('ibu.update')
                        <a href="{{ route('admin.data-ibu.edit', $ibu) }}"
                           class="inline-flex justify-center rounded-xl bg-[#E99AB7] px-5 py-2.5 text-sm font-semibold text-[#542D3D] shadow-sm transition hover:bg-[#DD87A8]">
                            Edit
                        </a>
                    @endcan
                @endif
            </div>
        </div>
    @endif

    @include('data-ibu.partials.detail-ibu', [
        'ibu' => $ibu,
        'riwayatPemeriksaanIbu' => $riwayatPemeriksaanIbu ?? collect(),
        'filterPeriode' => $base === 'ibu',
    ])

</div>
@endsection