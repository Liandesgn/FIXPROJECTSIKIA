@extends('layouts.app')

@section('title', 'Data Ibu')

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-5">

    <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
        Halo, {{ \Illuminate\Support\Str::before($ibu?->user?->name ?? auth()->user()->name, ' ') }}
    </h1>

    @if ($ibu)
        @include('data-ibu.partials.kartu-ibu', ['ibu' => $ibu])

        @php $daftarAnak = $ibu->anak->sortBy('tanggal_lahir')->values(); @endphp

        @forelse ($daftarAnak as $anak)
            @include('data-anak.partials.kartu-anak', [
                'anak' => $anak,
                'judul' => $daftarAnak->count() > 1 ? 'Data Anak '.$loop->iteration : 'Data Anak',
            ])
        @empty
            <section class="rounded-2xl border border-[#F1D5E0] bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-base font-bold text-[#542D3D]">Data Anak</h2>
                <p class="mt-3 text-sm text-slate-600">Belum ada data anak yang terhubung.</p>
            </section>
        @endforelse
    @else
        <section class="rounded-2xl border border-[#F1D5E0] bg-white p-5 text-sm text-slate-600 shadow-sm">
            Data ibu Anda belum tersedia. Silakan hubungi admin.
        </section>
    @endif
</div>
@endsection