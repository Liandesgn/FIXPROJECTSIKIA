@extends('layouts.app')

@section('title', 'Data Anak')

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-5">

    <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
        Halo, {{ \Illuminate\Support\Str::before($ibu?->user?->name ?? auth()->user()->name, ' ') }}
    </h1>

    @if ($ibu)
        @php $daftarAnak = $ibu->anak->sortBy('tanggal_lahir')->values(); @endphp

        @forelse ($daftarAnak as $anak)
            @include('data-anak.partials.kartu-anak', [
                'anak'  => $anak,
                'judul' => $daftarAnak->count() > 1 ? 'Data Anak '.$loop->iteration : 'Data Anak',
            ])
        @empty
            <section class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
                <h2 class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-5 py-3 text-sm font-bold text-[#542D3D]">Data Anak</h2>
                <p class="px-5 py-6 text-sm font-medium text-slate-700">Belum ada data anak yang terhubung.</p>
            </section>
        @endforelse

        @include('data-ibu.partials.kartu-ibu', ['ibu' => $ibu])
    @else
        <section class="rounded-2xl border border-[#F1D5E0] bg-white p-5 text-sm font-medium text-slate-700 shadow-sm">
            Data ibu Anda belum tersedia. Silakan hubungi admin.
        </section>
    @endif
</div>
@endsection