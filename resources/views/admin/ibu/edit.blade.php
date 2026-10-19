@extends('layouts.app')

@section('title', 'Edit Ibu')

@section('content')
<div class="mx-auto max-w-4xl space-y-4">

    <div>
        <a href="{{ route('admin.ibu.index') }}"
           class="inline-flex items-center gap-2 rounded-xl border-2 border-[#C9517E] bg-white px-4 py-2 text-sm font-semibold text-[#C9517E] shadow-sm transition hover:bg-[#C9517E] hover:text-white focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m6-6-6 6 6 6"/></svg>
            Kembali ke Kelola Ibu
        </a>
        <p class="mt-3 text-sm font-medium text-slate-700">Manajemen User / Kelola Ibu / Edit</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#E99AB7]/60 bg-[#FCE7F0] px-6 py-5 sm:px-8">
            <h1 class="text-xl font-bold text-[#542D3D] sm:text-2xl">Edit Ibu</h1>
            <p class="mt-1 text-sm text-slate-700">Perbarui data akun dan data ibu.</p>
        </div>

        <div class="p-6 sm:p-8">
            {{-- Pesan gagal ditampilkan sebagai popup SweetAlert dari layout --}}

            <form action="{{ route('admin.ibu.update', $ibu->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @include('admin.ibu.partials.fields', ['ibu' => $ibu])

                <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.ibu.index') }}"
                       class="inline-flex justify-center rounded-xl border border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Ikon mata (tampilkan/sembunyikan) untuk semua kolom password --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const eye = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
    const eyeOff = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6 0 9.5 7 9.5 7a15 15 0 0 1-3.1 3.9M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.5 7 9.5 7c1 0 1.9-.2 2.8-.5"/></svg>';

    document.querySelectorAll('input[type="password"]').forEach(function (input) {
        if (input.dataset.eye) return;
        input.dataset.eye = '1';

        const wrap = document.createElement('div');
        wrap.className = 'relative';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        input.classList.add('pr-12');

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-500 transition hover:text-[#C9517E] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#C9517E]';
        btn.setAttribute('aria-label', 'Tampilkan password');
        btn.setAttribute('aria-pressed', 'false');
        btn.innerHTML = eye;
        btn.addEventListener('click', function () {
            const hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            btn.innerHTML = hidden ? eyeOff : eye;
            btn.setAttribute('aria-pressed', String(hidden));
            btn.setAttribute('aria-label', hidden ? 'Sembunyikan password' : 'Tampilkan password');
        });
        wrap.appendChild(btn);
    });
});
</script>
@endsection