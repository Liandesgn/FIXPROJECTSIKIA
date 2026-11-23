<?php

namespace App\Console\Commands;

use App\Models\Ibu;
use App\Models\Jadwal;
use App\Models\User;
use App\Notifications\SikiaNotification;
use Illuminate\Console\Command;

class KirimPengingatJadwalIbu extends Command
{
    protected $signature = 'notifikasi:kirim-pengingat-ibu';

    protected $description = 'Mengirim pengingat jadwal Posyandu dan pemeriksaan kepada Ibu pada H-3 dan hari-H';

    public function handle(): int
    {
        $tanggalTarget = [
            today()->addDays(3)->toDateString() => 'pengingat_h3',
            today()->toDateString() => 'pengingat_hari_h',
        ];

        foreach ($tanggalTarget as $tanggal => $jenis) {
            $this->kirimPengingatPosyandu($tanggal, $jenis);
            $this->kirimPengingatPemeriksaan($tanggal, $jenis);
        }

        $this->info('Pengiriman pengingat jadwal Ibu selesai.');

        return self::SUCCESS;
    }

    /**
     * Pengingat Posyandu untuk Ibu sesuai RT, RW, atau wilayah kegiatan.
     */
    private function kirimPengingatPosyandu(
        string $tanggal,
        string $jenis
    ): void {
        $jadwals = Jadwal::query()
            ->whereDate('tanggal', $tanggal)
            ->whereHas('jadwalPosyandu.kegiatan')
            ->with([
                'jadwalPosyandu.kegiatan.rw',
                'jadwalPosyandu.bidan.user',
                'jadwalPosyandu.kader.user',
            ])
            ->get();

        foreach ($jadwals as $jadwal) {
            $penugasan = $jadwal->jadwalPosyandu;
            $kegiatan = $penugasan?->kegiatan;

            if (!$kegiatan) {
                continue;
            }

            $queryIbu = Ibu::with('user');

            // Prioritas 1: kegiatan khusus RT.
            if ($kegiatan->rt_id) {
                $queryIbu->where('rt_id', $kegiatan->rt_id);

            // Prioritas 2: kegiatan untuk seluruh RW.
            } elseif ($kegiatan->rw_id) {
                $queryIbu->whereHas('rt', function ($query) use ($kegiatan) {
                    $query->where('rw_id', $kegiatan->rw_id);
                });

            // Prioritas 3: kegiatan untuk seluruh wilayah kerja.
            } elseif ($kegiatan->id_wilayah) {
                $queryIbu->where(
                    'wilayah_kerja_id',
                    $kegiatan->id_wilayah
                );

            } else {
                continue;
            }

            $namaBidan = $penugasan?->bidan?->user?->name
                ?? 'Belum ditentukan';

            $namaKader = $penugasan?->kader?->user?->name
                ?? 'Belum ditentukan';

            $nomorRw = $kegiatan->rw?->nomor_rw
                ?? 'Tidak ditentukan';

            $namaKegiatan = $kegiatan->jenis_kegiatan
                ?? 'Kegiatan Posyandu';

            $isi = implode(' | ', [
                "Kegiatan: {$namaKegiatan}",
                "Tanggal: {$jadwal->tanggal->format('d-m-Y')}",
                "Waktu: {$jadwal->waktu} WIB",
                "Lokasi: {$jadwal->lokasi}",
                "RW: {$nomorRw}",
                "Bidan: {$namaBidan}",
                "Kader: {$namaKader}",
            ]);

            foreach ($queryIbu->get() as $ibu) {
                if (!$ibu->user) {
                    continue;
                }

                $this->kirimJikaBelumAda(
                    $ibu->user,
                    $jadwal,
                    $jenis,
                    'posyandu',
                    'Pengingat Jadwal Posyandu',
                    $isi,
                    [
                        'jenis_kegiatan' => $namaKegiatan,
                        'rw' => $nomorRw,
                        'bidan' => $namaBidan,
                        'kader' => $namaKader,
                    ]
                );
            }
        }
    }

    /**
     * Pengingat pemeriksaan untuk Ibu yang sedang hamil.
     */
    private function kirimPengingatPemeriksaan(
        string $tanggal,
        string $jenis
    ): void {
        $jadwals = Jadwal::query()
            ->whereDate('tanggal', $tanggal)
            ->whereHas('jadwalPemeriksaan')
            ->with([
                'jadwalPemeriksaan.bidan.user',
            ])
            ->get();

        foreach ($jadwals as $jadwal) {
            $pemeriksaan = $jadwal->jadwalPemeriksaan;

            if (!$pemeriksaan || !$pemeriksaan->id_wilayah_kerja) {
                continue;
            }

            $namaBidan = $pemeriksaan->bidan?->user?->name
                ?? 'Belum ditentukan';

            $namaPemeriksaan = $jadwal->jenis_kegiatan
                ?? 'Pemeriksaan Ibu Hamil';

            $catatan = $pemeriksaan->catatan
                ?? 'Tidak ada catatan';

            $isi = implode(' | ', [
                "Pemeriksaan: {$namaPemeriksaan}",
                "Tanggal: {$jadwal->tanggal->format('d-m-Y')}",
                "Waktu: {$jadwal->waktu} WIB",
                "Lokasi: {$jadwal->lokasi}",
                "Bidan Pemeriksa: {$namaBidan}",
                "Catatan: {$catatan}",
            ]);

            $ibus = Ibu::with('user')
                ->where('wilayah_kerja_id', $pemeriksaan->id_wilayah_kerja)
                ->where('status_kehamilan', 'hamil')
                ->get();

            foreach ($ibus as $ibu) {
                if (!$ibu->user) {
                    continue;
                }

                $this->kirimJikaBelumAda(
                    $ibu->user,
                    $jadwal,
                    $jenis,
                    'pemeriksaan',
                    'Pengingat Jadwal Pemeriksaan Ibu Hamil',
                    $isi,
                    [
                        'jenis_pemeriksaan' => $namaPemeriksaan,
                        'bidan' => $namaBidan,
                        'catatan' => $catatan,
                    ]
                );
            }
        }
    }

    /**
     * Mencegah notifikasi ganda untuk jadwal dan kategori yang sama.
     */
    private function kirimJikaBelumAda(
        User $user,
        Jadwal $jadwal,
        string $jenis,
        string $kategori,
        string $judul,
        string $isi,
        array $dataTambahan = []
    ): void {
        $sudahAda = $user->notifications()
            ->where('data->id_jadwal', $jadwal->id_jadwal)
            ->where('data->jenis', $jenis)
            ->where('data->kategori_jadwal', $kategori)
            ->exists();

        if ($sudahAda) {
            return;
        }

        $user->notify(new SikiaNotification(
            $jenis,
            $judul,
            $isi,
            array_merge([
                'id_jadwal' => $jadwal->id_jadwal,
                'kategori_jadwal' => $kategori,
                'tanggal' => $jadwal->tanggal->toDateString(),
                'waktu' => $jadwal->waktu,
                'lokasi' => $jadwal->lokasi,
            ], $dataTambahan)
        ));
    }
}
