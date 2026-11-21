@extends('layouts.app')

@section('title', 'Edit Data Ibu')

@section('content')
@php
    $fieldClass = 'mt-1.5 w-full rounded-xl border border-[#E99AB7] bg-white px-3.5 py-2.5 text-sm font-medium text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-[#C9517E] focus:ring-4 focus:ring-[#E99AB7]/40';
    $labelClass = 'text-sm font-semibold text-[#542D3D]';
    $errorClass = 'mt-1 text-xs font-medium text-red-600';
    $statusDipilih = old('status_kehamilan', $ibu->status_kehamilan?->value);

    // ===== Susun data Alamat untuk dropdown bertingkat =====
    $listWilayah = collect($wilayahs ?? []);

    $relasi = function ($w, string $nama) {
        if ($w->relationLoaded($nama)) return $w->getRelation($nama);
        return method_exists($w, $nama) ? $w->{$nama}()->first() : null;
    };

    $baris = $listWilayah->map(function ($w) use ($relasi) {
        $kec  = $relasi($w, 'kecamatan');
        $desa = $relasi($w, 'kelurahanDesa');
        $rw   = $relasi($w, 'rw');
        $rt   = $relasi($w, 'rt');

        $kolomKec  = $w->getAttribute('kecamatan');
        $kolomDesa = $w->getAttribute('kelurahan');

        return [
            'id'      => (string) $w->id,
            'kec_id'  => (string) ($kec?->id ?? ''),
            'kec'     => $kec?->nama_kecamatan ?? (is_string($kolomKec) ? $kolomKec : ''),
            'desa_id' => (string) ($desa?->id ?? ''),
            'desa'    => $desa?->nama_kelurahan_desa
                            ? (strtolower((string) $desa->jenis) === 'kelurahan' ? 'Kelurahan ' : 'Desa ') . $desa->nama_kelurahan_desa
                            : (is_string($kolomDesa) && $kolomDesa !== '' ? 'Desa ' . $kolomDesa : ''),
            'rw_id'   => (string) ($rw?->id ?? ''),
            'rw'      => $rw?->nomor_rw ? 'RW ' . $rw->nomor_rw : '',
            'rt'      => $rt?->nomor_rt ? 'RT ' . $rt->nomor_rt : '',
        ];
    })->values();

    $adaRw = $baris->contains(fn ($b) => $b['rw_id'] !== '');
    $adaRt = $adaRw && $baris->contains(fn ($b) => $b['rt'] !== '');

    // Tingkat pilihan: kunci = penanda unik di tingkat itu, label = teks yang tampil
    $tingkat = [
        ['title' => 'Pilih Kecamatan',      'kunci' => fn ($b) => $b['kec_id']  ?: 'k' . $b['id'], 'label' => fn ($b) => $b['kec']  ?: '-'],
        ['title' => 'Pilih Kelurahan/Desa', 'kunci' => fn ($b) => $b['desa_id'] ?: 'd' . $b['id'], 'label' => fn ($b) => $b['desa'] ?: '-'],
    ];

    if ($adaRw) {
        $tingkat[] = ['title' => 'Pilih RW', 'kunci' => fn ($b) => $b['rw_id'] ?: 'r' . $b['id'], 'label' => fn ($b) => $b['rw'] ?: '-'];
    }

    if ($adaRt) {
        $tingkat[] = ['title' => 'Pilih RT', 'kunci' => fn ($b) => $b['id'], 'label' => fn ($b) => $b['rt'] ?: '-'];
    }

    $jumlahTingkat = count($tingkat);

    // Tingkat terakhir selalu bernilai id wilayah kerja (yang dikirim ke server)
    $ddLevels = [];
    foreach ($tingkat as $i => $t) {
        $kunciFn = $i === $jumlahTingkat - 1 ? fn ($b) => $b['id'] : $t['kunci'];
        $indukFn = $i === 0 ? null : $tingkat[$i - 1]['kunci'];

        $items = $baris->map(fn ($b) => [
            'value'  => (string) $kunciFn($b),
            'label'  => $t['label']($b),
            'parent' => $indukFn ? (string) $indukFn($b) : '',
        ])->unique('value')->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();

        $ddLevels[] = ['title' => $t['title'], 'items' => $items];
    }

    // Pilihan awal: dari input sebelumnya (gagal validasi) atau dari data ibu
    $idTerpilih = (string) old('wilayah_kerja_id', $ibu->wilayah_kerja_id);
    $barisAktif = $baris->firstWhere('id', $idTerpilih);
    $ddSelected = [];

    if ($barisAktif) {
        foreach ($tingkat as $i => $t) {
            $ddSelected[] = (string) ($i === $jumlahTingkat - 1 ? $barisAktif['id'] : $t['kunci']($barisAktif));
        }
    }

    $ddHidden = [['name' => 'wilayah_kerja_id', 'level' => $jumlahTingkat - 1, 'value' => $idTerpilih]];
@endphp

<div class="mx-auto w-full max-w-3xl space-y-5">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-[#542D3D] sm:text-3xl">Data Ibu</h1>
        <p class="mt-1 text-sm font-medium text-slate-700">Data Master / Data Ibu / Edit Data Ibu</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#F1D5E0] bg-white shadow-sm">
        <div class="border-b border-[#F1D5E0] bg-gradient-to-r from-[#FCE7F0] to-[#FFF7FA] px-6 py-4">
            <h2 class="text-lg font-bold text-[#542D3D]">Edit Data Ibu</h2>
            <p class="mt-0.5 text-sm text-slate-600">Perbarui data akun dan data kehamilan ibu.</p>
        </div>

        <form method="POST" action="{{ route('admin.data-ibu.update', $ibu) }}" class="space-y-5 p-4 sm:p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="{{ $labelClass }}">Nama <span class="text-[#C9517E]">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name', $ibu->user?->name) }}" placeholder="Nama lengkap" class="{{ $fieldClass }}">
                    @error('name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="{{ $labelClass }}">Email <span class="text-[#C9517E]">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email', $ibu->user?->email) }}" placeholder="nama@gmail.com" class="{{ $fieldClass }}">
                    @error('email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="{{ $labelClass }}">Nomor HP <span class="text-[#C9517E]">*</span></label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $ibu->user?->phone) }}"
                           inputmode="numeric" maxlength="13" pattern="[0-9]{10,13}" title="Hanya angka, 10-13 digit"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="08xxxxxxxxxx" class="{{ $fieldClass }}">
                    @error('phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="nik" class="{{ $labelClass }}">NIK <span class="text-[#C9517E]">*</span></label>
                    <input id="nik" type="text" name="nik" value="{{ old('nik', $ibu->nik) }}"
                           maxlength="16" inputmode="numeric" pattern="[0-9]{16}" title="NIK harus 16 digit angka"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="16 digit NIK" class="{{ $fieldClass }}">
                    @error('nik') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tempat_lahir" class="{{ $labelClass }}">Tempat Lahir <span class="text-[#C9517E]">*</span></label>
                    <input id="tempat_lahir" type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $ibu->tempat_lahir) }}" placeholder="Kota / Kabupaten" class="{{ $fieldClass }}">
                    @error('tempat_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tgl_lahir" class="{{ $labelClass }}">Tanggal Lahir <span class="text-[#C9517E]">*</span></label>
                    <input id="tgl_lahir" type="date" name="tgl_lahir"
                           value="{{ old('tgl_lahir', $ibu->tgl_lahir?->format('Y-m-d')) }}" class="{{ $fieldClass }}">
                    @error('tgl_lahir') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                {{-- Alamat: dropdown bertingkat --}}
                <div class="sm:col-span-2">
                    @include('partials.dropdown-bertingkat', [
                        'ddId'          => 'alamat_drill',
                        'ddLabel'       => 'Alamat',
                        'ddPlaceholder' => 'Pilih Alamat',
                        'ddLevels'      => $ddLevels,
                        'ddHidden'      => $ddHidden,
                        'ddSelected'    => $ddSelected,
                        'ddRequired'    => true,
                    ])
                </div>

                <div>
                    <label for="status_kehamilan" class="{{ $labelClass }}">Status Kehamilan <span class="text-[#C9517E]">*</span></label>
                    <select id="status_kehamilan" name="status_kehamilan" class="{{ $fieldClass }}">
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}" @selected($statusDipilih === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                    @error('status_kehamilan') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                </div>

                <div data-hamil>
                    <label for="usia_kehamilan" class="{{ $labelClass }}">
                        Usia Kehamilan (minggu) <span class="text-[#C9517E]">*</span>
                    </label>

                    <input
                        id="usia_kehamilan"
                        type="number"
                        name="usia_kehamilan"
                        min="1"
                        max="42"
                        value="{{ old('usia_kehamilan', $ibu->usia_kehamilan) }}"
                        placeholder="Minggu"
                        class="{{ $fieldClass }}"
                    >

                    @error('usia_kehamilan')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>

                <div data-hamil>
                    <label for="hpl_display" class="{{ $labelClass }}">Hari Perkiraan Lahir (HPL)</label>

                    <input
                        id="hpl_display"
                        type="text"
                        value="{{ old('hpl', $ibu->hpl?->format('Y-m-d')) }}"
                        placeholder="Dihitung otomatis"
                        readonly
                        class="{{ str_replace('bg-white', 'cursor-not-allowed bg-slate-100 text-slate-700', $fieldClass) }}"
                    >

                    <input
                        type="hidden"
                        id="hpl"
                        name="hpl"
                        value="{{ old('hpl', $ibu->hpl?->format('Y-m-d')) }}"
                    >

                    <p class="mt-1 text-xs text-slate-500">
                        HPL dihitung otomatis berdasarkan usia kehamilan dengan target 40 minggu.
                    </p>

                    @error('hpl')
                        <p class="{{ $errorClass }}">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.data-ibu.show', $ibu) }}"
                   class="inline-flex w-full justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] active:scale-[0.98] sm:w-auto">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex w-full justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.98] sm:w-auto">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const status = document.getElementById('status_kehamilan');
    const usia = document.getElementById('usia_kehamilan');
    const hpl = document.getElementById('hpl');
    const hplDisplay = document.getElementById('hpl_display');
    const blok = document.querySelectorAll('[data-hamil]');

    const TARGET_KEHAMILAN = 40;

    function hitungHPL() {
        const minggu = Number(usia.value);

        if (!Number.isInteger(minggu) || minggu < 1 || minggu > 42) {
            hpl.value = '';
            hplDisplay.value = '';
            return;
        }

        const sisaHari = (TARGET_KEHAMILAN - minggu) * 7;

        const tanggal = new Date();
        tanggal.setHours(12, 0, 0, 0);
        tanggal.setDate(tanggal.getDate() + sisaHari);

        const tahun = tanggal.getFullYear();
        const bulan = String(tanggal.getMonth() + 1).padStart(2, '0');
        const hari = String(tanggal.getDate()).padStart(2, '0');

        // Format untuk database
        hpl.value = `${tahun}-${bulan}-${hari}`;

        // Format untuk tampilan
        hplDisplay.value = `${hari}-${bulan}-${tahun}`;
    }

    function atur() {
        const sedangHamil = status.value === 'hamil';

        blok.forEach(function (el) {
            el.classList.toggle('hidden', !sedangHamil);
        });

        usia.required = sedangHamil;

        if (sedangHamil) {
            hitungHPL();
        } else {
            usia.value = '';
            hpl.value = '';
            hplDisplay.value = '';
        }
    }

    status.addEventListener('change', atur);
    usia.addEventListener('input', hitungHPL);
    usia.addEventListener('change', hitungHPL);

    atur();
});
</script>
@endsection