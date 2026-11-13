<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
            color: #C9517E;
            letter-spacing: 1px;
        }

        h3 {
            margin: 18px 0 6px;
            color: #542D3D;
            font-size: 11px;
        }

        p {
            text-align: center;
            margin: 4px 0 18px;
            color: #542D3D;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #E99AB7;
            padding: 7px;
        }

        th {
            background: #FCE7F0;
            color: #542D3D;
            text-align: center;
        }

        tbody tr:nth-child(even) td {
            background: #FFF8FA;
        }

        td.center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>LAPORAN {{ strtoupper($judul) }}</h2>
    <p>Periode: {{ $periode }} | Wilayah: {{ $namaWilayah }}</p>

    {{-- 1. REKAP KESEHATAN: ringkasan + tabel per wilayah --}}
    @if ($tab === 'rekap-kesehatan')
        @php
            $baris = collect($data);

            // Bila controller tidak mengirim $ringkasan, hitung dari baris data per wilayah
            $ringkasan = $ringkasan ?? [
                'ibu_hamil'       => $baris->sum('ibu_hamil'),
                'anak'            => $baris->sum('anak'),
                'perlu_perhatian' => $baris->sum(fn ($r) => $r->risiko ?? $r->perlu_perhatian ?? 0),
            ];
        @endphp

        <table>
            <thead>
                <tr>
                    <th>Ibu Hamil</th>
                    <th>Anak Terdaftar</th>
                    <th>Perlu Perhatian</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center">{{ $ringkasan['ibu_hamil'] }}</td>
                    <td class="center">{{ $ringkasan['anak'] }}</td>
                    <td class="center">{{ $ringkasan['perlu_perhatian'] }}</td>
                </tr>
            </tbody>
        </table>

        <h3>Rekap Per Wilayah</h3>
        <table>
            <thead>
                <tr>
                    <th>Wilayah</th><th>Ibu Hamil</th><th>Anak</th>
                    <th>Diperiksa</th><th>Perlu Perhatian</th><th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td>{{ $item->wilayah ?? $item->nama_wilayah ?? '-' }}</td>
                        <td class="center">{{ $item->ibu_hamil ?? 0 }}</td>
                        <td class="center">{{ $item->anak ?? 0 }}</td>
                        <td class="center">{{ $item->diperiksa ?? 0 }}</td>
                        <td class="center">{{ $item->risiko ?? $item->perlu_perhatian ?? 0 }}</td>
                        <td class="center">{{ $item->persentase ?? 0 }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="center">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>

    {{-- 2. KEGIATAN POSYANDU --}}
    @elseif ($tab === 'kegiatan-posyandu')
        <table>
            <thead>
                <tr>
                    <th>No</th><th>Tanggal</th><th>Wilayah</th><th>Jenis Kegiatan</th>
                    <th>Lokasi</th><th>Anak Diperiksa</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $item->tanggal?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $item->wilayah?->nama_wilayah ?? '-' }}</td>
                        <td>{{ $item->jenis_kegiatan ?? '-' }}</td>
                        <td>{{ $item->lokasi ?? '-' }}</td>
                        <td class="center">{{ $item->pemeriksaan_count ?? $item->anak_diperiksa ?? 0 }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="center">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>

    {{-- 3. DATA RISIKO --}}
    @elseif ($tab === 'data-risiko')
        <table>
            <thead>
                <tr>
                    <th>No</th><th>Nama</th><th>Jenis</th><th>Wilayah</th>
                    <th>Pemeriksaan Terakhir</th><th>Indikator</th><th>Penanda</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    @php
                        $penanda = $item->penanda ?? null;
                        $penanda = $penanda instanceof \Illuminate\Support\Collection
                            ? $penanda->all()
                            : \Illuminate\Support\Arr::wrap($penanda);
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->jenis }}</td>
                        <td>{{ $item->wilayah }}</td>
                        <td>{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $item->indikator }}</td>
                        <td>{{ implode(', ', array_filter($penanda)) ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="center">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>

    {{-- 4. REKAP PEMERIKSAAN --}}
    @elseif ($tab === 'rekap-pemeriksaan')
        <table>
            <thead>
                <tr>
                    <th>Bulan</th><th>Pemeriksaan Ibu Hamil</th>
                    <th>Pemeriksaan Anak</th><th>Kegiatan Posyandu</th>
                    <th>Total Pemeriksaan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td>{{ $item->bulan }}</td>
                        <td class="center">{{ $item->ibu ?? $item->pemeriksaan_ibu ?? 0 }}</td>
                        <td class="center">{{ $item->anak ?? $item->pemeriksaan_anak ?? 0 }}</td>
                        <td class="center">{{ $item->kegiatan ?? $item->kegiatan_posyandu ?? 0 }}</td>
                        <td class="center">{{ $item->total ?? 0 }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="center">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>