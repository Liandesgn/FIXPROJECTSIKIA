
@extends('layouts.app')

@section('title', 'Tambah Wilayah Kerja')

@section('content')
@php
    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40 disabled:bg-gray-100 disabled:text-gray-400';
@endphp

<div class="mx-auto w-full max-w-3xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Kelola Wilayah</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Kelola Wilayah / Tambah</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-[#FCE7F0] px-6 py-4">
            <h2 class="text-lg font-bold text-[#542D3D]">Tambah Wilayah Kerja</h2>
            <p class="mt-0.5 text-sm text-slate-600">Pilih data wilayah sesuai alamat kerja.</p>
        </div>

        <form action="{{ route('superadmin.wilayah-kerja.store') }}" method="POST" class="space-y-5 p-6">
            @csrf

            <div>
                <label for="kecamatan_id" class="text-sm font-semibold text-[#542D3D]">
                    Kecamatan <span class="text-[#C9517E]">*</span>
                </label>
                <select id="kecamatan_id" name="kecamatan_id" class="{{ $fieldClass }}" required>
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach ($kecamatans as $kecamatan)
                        <option value="{{ $kecamatan->id }}" @selected(old('kecamatan_id') == $kecamatan->id)>
                            {{ $kecamatan->nama_kecamatan }}
                        </option>
                    @endforeach
                </select>
                @error('kecamatan_id')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="puskesmas_id" class="text-sm font-semibold text-[#542D3D]">
                    Puskesmas <span class="text-[#C9517E]">*</span>
                </label>
                <select id="puskesmas_id" name="puskesmas_id" class="{{ $fieldClass }}" disabled required>
                    <option value="">-- Pilih Kecamatan Terlebih Dahulu --</option>
                </select>
                @error('puskesmas_id')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kelurahan_desa_id" class="text-sm font-semibold text-[#542D3D]">
                    Kelurahan / Desa <span class="text-[#C9517E]">*</span>
                </label>
                <select id="kelurahan_desa_id" name="kelurahan_desa_id" class="{{ $fieldClass }}" disabled required>
                    <option value="">-- Pilih Puskesmas Terlebih Dahulu --</option>
                </select>
                @error('kelurahan_desa_id')
                    <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="rw_id" class="text-sm font-semibold text-[#542D3D]">
                        RW <span class="text-[#C9517E]">*</span>
                    </label>
                    <select id="rw_id" name="rw_id" class="{{ $fieldClass }}" disabled required>
                        <option value="">-- Pilih Kelurahan / Desa --</option>
                    </select>
                    @error('rw_id')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rt_id" class="text-sm font-semibold text-[#542D3D]">
                        RT <span class="text-[#C9517E]">*</span>
                    </label>
                    <select id="rt_id" name="rt_id" class="{{ $fieldClass }}" disabled required>
                        <option value="">-- Pilih RW Terlebih Dahulu --</option>
                    </select>
                    @error('rt_id')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>


            <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('superadmin.wilayah-kerja.index') }}"
                   class="inline-flex justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D]">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const kecamatan = document.getElementById('kecamatan_id');
    const puskesmas = document.getElementById('puskesmas_id');
    const kelurahan = document.getElementById('kelurahan_desa_id');
    const rw = document.getElementById('rw_id');
    const rt = document.getElementById('rt_id');

    const urls = {
        puskesmas: @json(route('superadmin.wilayah-kerja.dropdown.puskesmas')),
        kelurahan: @json(route('superadmin.wilayah-kerja.dropdown.kelurahan-desa')),
        rw: @json(route('superadmin.wilayah-kerja.dropdown.rw')),
        rt: @json(route('superadmin.wilayah-kerja.dropdown.rt')),
    };

    function reset(select, text) {
        select.innerHTML = '';
        select.add(new Option(text, ''));
        select.disabled = true;
    }

    async function load(select, url, placeholder, nameField) {
        reset(select, 'Memuat data...');

        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status + ' - ' + await response.text());
            }

            const data = await response.json();
            reset(select, placeholder);

            data.forEach(item => {
                select.add(new Option(item[nameField], item.id));
            });

            select.disabled = false;

            if (data.length === 0) {
                reset(select, 'Data tidak ditemukan');
            }
        } catch (error) {
            console.error('Dropdown error:', error);
            reset(select, 'Gagal memuat data - periksa Controller');
        }
    }

    kecamatan.addEventListener('change', () => {
        reset(puskesmas, '-- Pilih Puskesmas --');
        reset(kelurahan, '-- Pilih Kelurahan / Desa --');
        reset(rw, '-- Pilih RW --');
        reset(rt, '-- Pilih RT --');

        if (kecamatan.value) {
            load(
                puskesmas,
                urls.puskesmas + '?kecamatan_id=' + encodeURIComponent(kecamatan.value),
                '-- Pilih Puskesmas --',
                'nama_puskesmas'
            );
        }
    });

    puskesmas.addEventListener('change', () => {
        reset(kelurahan, '-- Pilih Kelurahan / Desa --');
        reset(rw, '-- Pilih RW --');
        reset(rt, '-- Pilih RT --');

        if (puskesmas.value) {
            load(
                kelurahan,
                urls.kelurahan + '?puskesmas_id=' + encodeURIComponent(puskesmas.value),
                '-- Pilih Kelurahan / Desa --',
                'nama_kelurahan_desa'
            );
        }
    });

    kelurahan.addEventListener('change', () => {
        reset(rw, '-- Pilih RW --');
        reset(rt, '-- Pilih RT --');

        if (kelurahan.value) {
            load(
                rw,
                urls.rw + '?kelurahan_desa_id=' + encodeURIComponent(kelurahan.value),
                '-- Pilih RW --',
                'nomor_rw'
            );
        }
    });

    rw.addEventListener('change', () => {
        reset(rt, '-- Pilih RT --');

        if (rw.value) {
            load(
                rt,
                urls.rt + '?rw_id=' + encodeURIComponent(rw.value),
                '-- Pilih RT --',
                'nomor_rt'
            );
        }
    });
});
</script>
@endsection
