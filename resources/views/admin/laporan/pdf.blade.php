
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 5px; }
        p { text-align: center; margin: 4px 0 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #555; padding: 7px; }
        th { background: #e5e7eb; text-align: center; }
        td.center { text-align: center; }
    </style>
</head>
<body>
    <h2>LAPORAN {{ strtoupper($judul) }}</h2>
    <p>Periode: {{ $periode }} | Wilayah: {{ $namaWilayah }}</p>

    <table>
        @if ($tab === 'rekap-kesehatan')
            <thead>
                <tr>
                    <th>Wilayah</th><th>Ibu Hamil</th><th>Anak</th>
                    <th>Diperiksa</th><th>Perlu Perhatian</th><th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td>{{ $item->wilayah }}</td>
                        <td class="center">{{ $item->ibu_hamil }}</td>
                        <td class="center">{{ $item->anak }}</td>
                        <td class="center">{{ $item->diperiksa }}</td>
                        <td class="center">{{ $item->risiko }}</td>
                        <td class="center">{{ $item->persentase }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada data.</td></tr>
                @endforelse
            </tbody>

        @elseif ($tab === 'kegiatan-posyandu')
            <thead>
                <tr>
                    <th>No</th><th>Tanggal</th><th>Posyandu</th>
                    <th>Lokasi</th><th>Anak Diperiksa</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $item->tanggal->format('d-m-Y') }}</td>
                        <td>{{ $item->posyandu?->nama_posyandu ?? '-' }}</td>
                        <td>{{ $item->lokasi ?? '-' }}</td>
                        <td class="center">{{ $item->pemeriksaan_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada data.</td></tr>
                @endforelse
            </tbody>

        @elseif ($tab === 'data-risiko')
            <thead>
                <tr>
                    <th>No</th><th>Nama</th><th>Jenis</th>
                    <th>Wilayah</th><th>Tanggal Pemeriksaan</th><th>Indikator</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->jenis }}</td>
                        <td>{{ $item->wilayah }}</td>
                        <td>{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $item->indikator }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada data.</td></tr>
                @endforelse
            </tbody>

        @elseif ($tab === 'rekap-pemeriksaan')
            <thead>
                <tr>
                    <th>Periode</th><th>Pemeriksaan Ibu</th>
                    <th>Pemeriksaan Anak</th><th>Kegiatan Posyandu</th>
                    <th>Total Pemeriksaan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td>{{ $item->bulan }}</td>
                        <td class="center">{{ $item->ibu }}</td>
                        <td class="center">{{ $item->anak }}</td>
                        <td class="center">{{ $item->kegiatan }}</td>
                        <td class="center">{{ $item->total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        @endif
    </table>
</body>
</html>
