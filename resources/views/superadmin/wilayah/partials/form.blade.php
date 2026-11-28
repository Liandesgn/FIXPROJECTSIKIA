
{{-- Parameter: $kecamatans, $wilayah (opsional, untuk Edit) --}}

@php
    $wilayah = $wilayah ?? null;

    $relasi = function ($nama) use ($wilayah) {
        if (! $wilayah) {
            return null;
        }

        if ($wilayah->relationLoaded($nama)) {
            return $wilayah->getRelation($nama);
        }

        return method_exists($wilayah, $nama)
            ? $wilayah->{$nama}()->first()
            : null;
    };

    $pilihan = [
        'kecamatan_id' => old(
            'kecamatan_id',
            $wilayah?->kecamatan_id ?? $relasi('kecamatan')?->id
                ?? $relasi('puskesmas')?->kecamatan_id
        ),
        'puskesmas_id' => old('puskesmas_id', $wilayah?->puskesmas_id),
        'kelurahan_desa_id' => old('kelurahan_desa_id', $wilayah?->kelurahan_desa_id),
        'rw_id' => old('rw_id', $wilayah?->rw_id),
    ];

    $pilihan = array_map(
        fn ($v) => $v === null ? '' : (string) $v,
        $pilihan
    );

    $listKecamatan = collect($kecamatans ?? []);

    if (
        $listKecamatan->isEmpty()
        && $wilayah
        && ($kecamatanSaatIni = $relasi('kecamatan'))
    ) {
        $listKecamatan = collect([$kecamatanSaatIni]);
    }

    $ddLevels = [
        [
            'title' => 'Pilih Kecamatan',
            'items' => $listKecamatan->map(fn ($k) => [
                'value' => (string) $k->id,
                'label' => $k->nama_kecamatan,
                'parent' => '',
            ])->values()->all(),
        ],
        [
            'title' => 'Pilih Puskesmas',
            'url' => route('superadmin.wilayah-kerja.dropdown.puskesmas'),
            'param' => 'kecamatan_id',
            'map' => ['value' => 'id', 'label' => 'nama_puskesmas'],
        ],
        [
            'title' => 'Pilih Kelurahan / Desa',
            'url' => route('superadmin.wilayah-kerja.dropdown.kelurahan-desa'),
            'param' => 'puskesmas_id',
            'map' => [
                'value' => 'id',
                'label' => 'nama_kelurahan_desa',
            ],
        ],
        [
            'title' => 'Pilih RW',
            'url' => route('superadmin.wilayah-kerja.dropdown.rw'),
            'param' => 'kelurahan_desa_id',
            'map' => [
                'value' => 'id',
                'label' => 'nomor_rw',
                'prefix' => 'RW ',
            ],
        ],
    ];

    $ddHidden = [
        ['name' => 'kecamatan_id', 'level' => 0, 'value' => $pilihan['kecamatan_id']],
        ['name' => 'puskesmas_id', 'level' => 1, 'value' => $pilihan['puskesmas_id']],
        ['name' => 'kelurahan_desa_id', 'level' => 2, 'value' => $pilihan['kelurahan_desa_id']],
        ['name' => 'rw_id', 'level' => 3, 'value' => $pilihan['rw_id']],
    ];

    $ddSelected = array_values($pilihan);
@endphp

@include('partials.dropdown-bertingkat', [
    'ddId' => 'wilayah_drill',
    'ddLabel' => 'Wilayah',
    'ddPlaceholder' => 'Pilih Kecamatan',
    'ddLevels' => $ddLevels,
    'ddHidden' => $ddHidden,
    'ddSelected' => $ddSelected,
    'ddRequired' => true,
    'ddHint' => 'Pilih berurutan: Kecamatan, Puskesmas, Kelurahan/Desa, lalu RW.',
])

{{-- Informasi RT dalam RW terpilih --}}
<div
    id="informasi-rt-wilayah"
    class="rounded-xl border border-[#F1D5E0] bg-[#FFF8FA] p-4"
>
    <h3 class="text-sm font-bold text-[#542D3D]">
        Cakupan RT dalam RW
    </h3>

    <p id="pesan-rt-wilayah" class="mt-1 text-sm text-slate-500">
        Pilih RW untuk melihat seluruh RT yang termasuk dalam wilayah kerja ini.
    </p>

    <div
        id="daftar-rt-wilayah"
        class="mt-3 flex flex-wrap gap-2"
    ></div>
</div>

<div class="flex flex-col-reverse gap-3 border-t border-[#F1D5E0] pt-5 sm:flex-row sm:justify-end">
    <a
        href="{{ route('superadmin.wilayah-kerja.index') }}"
        class="inline-flex w-full justify-center rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-3 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] active:scale-[0.98] sm:w-auto"
    >
        Batal
    </a>

    <button
        type="submit"
        class="inline-flex w-full justify-center rounded-xl bg-[#C9517E] px-8 py-3 text-sm font-semibold text-white shadow-md shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40 active:scale-[0.98] sm:w-auto"
    >
        Simpan
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rwInput = document.querySelector(
        '[name="rw_id"]'
    );

    const pesan = document.getElementById('pesan-rt-wilayah');
    const daftar = document.getElementById('daftar-rt-wilayah');

    if (!rwInput || !pesan || !daftar) {
        return;
    }

    const url = @json(route('superadmin.wilayah-kerja.dropdown.rt'));

    async function tampilkanRt() {
        const rwId = rwInput.value;

        daftar.innerHTML = '';

        if (!rwId) {
            pesan.textContent =
                'Pilih RW untuk melihat seluruh RT yang termasuk dalam wilayah kerja ini.';
            return;
        }

        pesan.textContent = 'Memuat daftar RT...';

        try {
            const response = await fetch(
                `${url}?rw_id=${encodeURIComponent(rwId)}`,
                {
                    headers: {
                        'Accept': 'application/json',
                    },
                }
            );

            if (!response.ok) {
                throw new Error('Gagal mengambil daftar RT.');
            }

            const data = await response.json();

            if (!data.length) {
                pesan.textContent = 'Belum ada RT yang terdaftar pada RW ini.';
                return;
            }

            pesan.textContent = `${data.length} RT termasuk dalam wilayah kerja ini.`;

            data.forEach(function (rt) {
                const badge = document.createElement('span');

                badge.className =
                    'rounded-lg border border-[#E99AB7] bg-white px-3 py-2 text-sm font-semibold text-[#542D3D]';

                badge.textContent = `RT ${rt.nomor_rt}`;

                daftar.appendChild(badge);
            });
        } catch (error) {
            pesan.textContent = 'Daftar RT tidak dapat dimuat. Silakan coba kembali.';
        }
    }

    rwInput.addEventListener('change', tampilkanRt);

    if (rwInput.value) {
        tampilkanRt();
    }
});
</script>
