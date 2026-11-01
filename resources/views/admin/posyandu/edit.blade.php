@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">

    <div class="mb-4">
        <h2 class="fw-bold">Edit Posyandu</h2>
        <p class="text-muted">
            Data Master / Kelola Wilayah Kerja / Edit Posyandu
        </p>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <form
                action="{{ route('admin.posyandu.update', $posyandu) }}"
                method="POST"
            >
                @csrf
                @method('PUT')

                {{-- Nama Posyandu --}}
                <div class="mb-3">
                    <label for="nama_posyandu" class="form-label fw-semibold">
                        Nama Posyandu <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="nama_posyandu"
                        id="nama_posyandu"
                        class="form-control @error('nama_posyandu') is-invalid @enderror"
                        value="{{ old('nama_posyandu', $posyandu->nama_posyandu) }}"
                        required
                    >

                    @error('nama_posyandu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Wilayah Kerja --}}
                <div class="mb-3">
                    <label for="wilayah_kerja_id" class="form-label fw-semibold">
                        Wilayah Kerja <span class="text-danger">*</span>
                    </label>

                    <select
                        name="wilayah_kerja_id"
                        id="wilayah_kerja_id"
                        class="form-select @error('wilayah_kerja_id') is-invalid @enderror"
                        required
                    >
                        <option value="">-- Pilih Kecamatan dan Desa --</option>

                        @foreach ($wilayahs as $wilayah)
                            <option
                                value="{{ $wilayah->id }}"
                                @selected(
                                    old('wilayah_kerja_id', $posyandu->wilayah_kerja_id)
                                    == $wilayah->id
                                )
                            >
                                {{ $wilayah->kecamatan }} — {{ $wilayah->desa }}
                            </option>
                        @endforeach
                    </select>

                    @error('wilayah_kerja_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Alamat --}}
                <div class="mb-4">
                    <label for="alamat" class="form-label fw-semibold">
                        Alamat Posyandu <span class="text-danger">*</span>
                    </label>

                    <textarea
                        name="alamat"
                        id="alamat"
                        rows="3"
                        class="form-control @error('alamat') is-invalid @enderror"
                        required
                    >{{ old('alamat', $posyandu->alamat) }}</textarea>

                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tombol --}}
                <div class="d-flex justify-content-end gap-2">
                    <a
                        href="{{ route('admin.posyandu.index') }}"
                        class="btn btn-light border"
                    >
                        Kembali
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection