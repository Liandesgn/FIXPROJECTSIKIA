<?php

namespace App\Console\Commands;

use App\Models\Jadwal;
use App\Models\Kader;
use App\Models\User;
use App\Notifications\SikiaNotification;
use Illuminate\Console\Command;

class KirimPengingatJadwalKader extends Command
{
    protected $signature = 'notifikasi:kirim-pengingat-kader';

    protected $description = 'Mengirim pengingat jadwal Posyandu kepada Kader pada H-3 dan hari-H';

    public function handle(): int
    {
        $tanggalTarget = [
            today()->addDays(3)->toDateString() => 'pengingat_h3',
            today()->toDateString() => 'pengingat_hari_h',
        ];

        foreach ($tanggalTarget as $tanggal => $jenis) {
            $this->kirimPengingatPosyandu($tanggal, $jenis);
        }

        $this->info('Pengiriman pengingat jadwal Kader selesai.');

        return self::SUCCESS;
    }

    /**
     * Mengirim pengingat kepada semua Kader dalam wilayah kegiatan.
     */
    private function kirimPengingatPosyandu(
        string $tanggal,
        string $jenis
    ): void {
        $jadwals = Jadwal::query()
            ->whereDate('tanggal', $tanggal)
            ->whereHas('jadwalPosyandu.kegiatan')
            ->with([
                'jadwalPosyandu.kegiatan',
                'jadwalPosyandu.kader.user',
                'jadwalPosyandu.bidan.user',
            ])
            ->get();

        foreach ($jadwals as $jadwal) {
            $detail = $jadwal->jadwalPosyandu;
            $kegiatan = $detail?->kegiatan;

            if (!$kegiatan || !$kegiatan->id_wilayah) {
                continue;
            }

            // Nama kegiatan mengikuti kartu Jadwal Kader.
            $namaKegiatan = $kegiatan->jenis_kegiatan
                ?? $jadwal->jenis_kegiatan
                ?? 'Kegiatan Posyandu';

            // Petugas mengikuti penugasan pada jadwal Posyandu.
            $namaKader = $detail->kader?->user?->name ?? '-';
            $namaBidan = $detail->bidan?->user?->name ?? '-';

            // Format lokasi mengikuti kartu Jadwal Kader.
            $lokasi = $jadwal->lokasi
                ?? $kegiatan->lokasi
                ?? '-';

            // Ambil seluruh Kader di kelurahan/desa wilayah kegiatan.
            $kaders = Kader::with('user')
                ->where('wilayah_kerja_id', $kegiatan->id_wilayah)
                ->get();

            foreach ($kaders as $kader) {
                $user = $kader->user;

                if (!$user) {
                    continue;
                }

                $this->kirimJikaBelumAda(
                    $user,
                    $jadwal,
                    $jenis,
                    $namaKegiatan,
                    $lokasi,
                    $namaKader,
                    $namaBidan
                );
            }
        }
    }

    /**
     * Mencegah notifikasi ganda untuk jadwal dan jenis pengingat yang sama.
     */
    private function kirimJikaBelumAda(
        User $user,
        Jadwal $jadwal,
        string $jenis,
        string $namaKegiatan,
        string $lokasi,
        string $namaKader,
        string $namaBidan
    ): void {
        $sudahAda = $user->notifications()
            ->where('data->id_jadwal', $jadwal->id_jadwal)
            ->where('data->jenis', $jenis)
            ->exists();

        if ($sudahAda) {
            return;
        }

        $tanggalTampil = $jadwal->tanggal
            ? $jadwal->tanggal->translatedFormat('d F Y')
            : '-';

        $waktuTampil = $jadwal->waktu ?? '-';

        $isi = implode(' | ', [
            "Kegiatan: {$namaKegiatan}",
            "Tanggal: {$tanggalTampil}",
            "Waktu: {$waktuTampil}",
            "Lokasi: {$lokasi}",
            "Kader: {$namaKader}",
            "Bidan Pemeriksa: {$namaBidan}",
        ]);

        $user->notify(new SikiaNotification(
            $jenis,
            'Pengingat Jadwal Posyandu',
            $isi,
            [
                'id_jadwal' => $jadwal->id_jadwal,
                'kategori_jadwal' => 'posyandu',
                'tanggal' => $jadwal->tanggal?->toDateString(),
                'waktu' => $jadwal->waktu,
                'lokasi' => $lokasi,
                'jenis_kegiatan' => $namaKegiatan,
                'kader' => $namaKader,
                'bidan' => $namaBidan,
            ]
        ));
    }
}
