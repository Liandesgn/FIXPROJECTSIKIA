{{-- Parameter: $kecamatans, $wilayah (opsional, untuk Edit) --}}
@php
    $wilayah = $wilayah ?? null;

    // Relasi aman (bila belum dimuat, diambil satu kali)
    $relasi = function ($nama) use ($wilayah) {
        if (! $wilayah) return null;
        if ($wilayah->relationLoaded($nama)) return $wilayah->getRelation($nama);
        return method_exists($wilayah, $nama) ? $wilayah->{$nama}()->first() : null;
    };

    // Pilihan awal: dari input sebelumnya (gagal validasi) atau dari data wilayah (edit)
    $pilihan = [
        'kecamatan_id'      => old('kecamatan_id',      $wilayah ? (data_get($wilayah, 'kecamatan_id') ?? $relasi('kecamatan')?->id ?? $relasi('puskesmas')?->kecamatan_id) : null),
        'puskesmas_id'      => old('puskesmas_id',      $wilayah ? data_get($wilayah, 'puskesmas_id') : null),
        'kelurahan_desa_id' => old('kelurahan_desa_id', $wilayah ? data_get($wilayah, 'kelurahan_desa_id') : null),
        'rw_id'             => old('rw_id',             $wilayah ? data_get($wilayah, 'rw_id') : null),
        'rt_id'             => old('rt_id',             $wilayah ? data_get($wilayah, 'rt_id') : null),
    ];
    $pilihan = array_map(fn ($v) => $v === null ? '' : (string) $v, $pilihan);

    $listKecamatan = collect($kecamatans ?? []);

    // Cadangan: bila controller belum mengirim daftar kecamatan saat Edit, pakai kecamatan wilayah itu
    if ($listKecamatan->isEmpty() && $wilayah && ($kecamatanSaatIni = $relasi('kecamatan'))) {
        $listKecamatan = collect([$kecamatanSaatIni]);
    }

    $ddLevels = [
        [
            'title' => 'Pilih Kecamatan',
            'items' => $listKecamatan->map(fn ($k) => [
                'value'  => (string) $k->id,
                'label'  => $k->nama_kecamatan,
                'parent' => '',
            ])->values()->all(),
        ],
        [
            'title' => 'Pilih Puskesmas',
            'url'   => route('superadmin.wilayah-kerja.dropdown.puskesmas'),
            'param' => 'kecamatan_id',
            'map'   => ['value' => 'id', 'label' => 'nama_puskesmas', 'prefix' => 'Puskesmas '],
        ],
        [
            'title' => 'Pilih Kelurahan / Desa',
            'url'   => route('superadmin.wilayah-kerja.dropdown.kelurahan-desa'),
            'param' => 'puskesmas_id',
            'map'   => ['value' => 'id', 'label' => 'nama_kelurahan_desa', 'prefix' => ''],
        ],
        [
            'title' => 'Pilih RW',
            'url'   => route('superadmin.wilayah-kerja.dropdown.rw'),
            'param' => 'kelurahan_desa_id',
            'map'   => ['value' => 'id', 'label' => 'nomor_rw', 'prefix' => 'RW '],
        ],
        [
            'title' => 'Pilih RT',
            'url'   => route('superadmin.wilayah-kerja.dropdown.rt'),
            'param' => 'rw_id',
            'map'   => ['value' => 'id', 'label' => 'nomor_rt', 'prefix' => 'RT '],
        ],
    ];

    $ddHidden = [
        ['name' => 'kecamatan_id',      'level' => 0, 'value' => $pilihan['kecamatan_id']],
        ['name' => 'puskesmas_id',      'level' => 1, 'value' => $pilihan['puskesmas_id']],
        ['name' => 'kelurahan_desa_id', 'level' => 2, 'value' => $pilihan['kelurahan_desa_id']],
        ['name' => 'rw_id',             'level' => 3, 'value' => $pilihan['rw_id']],
        ['name' => 'rt_id',             'level' => 4, 'value' => $pilihan['rt_id']],
    ];

    $ddSelected = array_values($pilihan);
@endphp

@include('partials.dropdown-bertingkat', [
    'ddId'          => 'wilayah_drill',
    'ddLabel'       => 'Wilayah',
    'ddPlaceholder' => 'Pilih Kecamatan',
    'ddLevels'      => $ddLevels,
    'ddHidden'      => $ddHidden,
    'ddSelected'    => $ddSelected,
    'ddRequired'    => true,
    'ddHint'        => 'Pilih berurutan: Kecamatan, Puskesmas, Kelurahan/Desa, RW, lalu RT.',
])

<div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
    <a href="{{ route('superadmin.wilayah-kerja.index') }}"
       class="inline-flex w-full justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] active:scale-[0.98] sm:w-auto">
        Batal
    </a>
    <button type="submit"
            class="inline-flex w-full justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.98] sm:w-auto">
        Simpan
    </button>
</div>