@extends('layouts.app')

@section('title', 'Data Anak')

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-8">

    @forelse ($detailAnak as $detail)
        <div class="space-y-6">
            {{-- Sapaan sesuai nama anak --}}
            <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">
                Halo, {{ \Illuminate\Support\Str::before($detail['anak']->nama_anak ?: 'Anak', ' ') }}
            </h1>

            @include('data-anak.partials.detail-anak', [
                'anak' => $detail['anak'],
                'base' => $base,
                'riwayatPemeriksaan' => $detail['riwayatPemeriksaan'],
                'pemeriksaanTerakhir' => $detail['pemeriksaanTerakhir'],
                'tampilanGabungan' => true,
                'nomor' => $loop->iteration,
                'jumlahAnak' => 1,
            ])
        </div>
    @empty
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Anak</h1>

        <section class="rounded-2xl border border-[#F1D5E0] bg-white p-6 text-center shadow-sm">
            <h2 class="text-base font-bold text-[#542D3D]">Data Anak Belum Tersedia</h2>
            <p class="mt-2 text-sm text-slate-600">
                Belum ada data anak yang terhubung dengan akun Anda. Silakan hubungi admin.
            </p>
        </section>
    @endforelse

</div>
@endsection