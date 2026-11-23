<?php

namespace App\Console\Commands;

use App\Models\Jadwal;
use App\Models\User;
use App\Notifications\SikiaNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class KirimPengingatJadwal extends Command
{
    protected $signature = 'notifikasi:kirim-pengingat-jadwal';

    protected $description = 'Mengirim pengingat jadwal Posyandu dan pemeriksaan kepada Bidan pada H-3 dan hari-H';

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

        $this->info('Pengiriman pengingat jadwal Bidan selesai.');

        return self::SUCCESS;
    }

    /**
     * Pengingat Posyandu kepada Bidan yang ditugaskan.
     */
    private function kirimPengingatPosyandu(
        string $tanggal,
        string $jenis
    ): void {
        $jadwals = Jadwal::query()
            ->whereDate('tanggal', $tanggal)
            ->whereHas('jadwalPosyandu.bidan.user')
            ->with([
                'jadwalPosyandu.bidan.user',
                'jadwalPosyandu.kader.user',
            ])
            ->get();

        foreach ($jadwals as $jadwal) {
            $detail = $jadwal->jadwalPosyandu;

            $user = $detail?->bidan?->user;

            if (!$user) {
                continue;
            }

            $namaKader = $detail->kader?->user?->name ?? '-';
            $namaBidan = $detail->bidan?->user?->name ?? '-';

            $namaKegiatan = $jadwal->jenis_kegiatan
                ?? 'Kegiatan Posyandu';

            $tanggalTampil = $jadwal->tanggal
                ? $jadwal->tanggal->translatedFormat('d F Y')
                : '-';

            $waktuTampil = $jadwal->waktu
                ? Carbon::parse($jadwal->waktu)->format('H:i')
                : '-';

            $isi = implode(' | ', [
                "Kegiatan: {$namaKegiatan}",
                "Tanggal: {$tanggalTampil}",
                "Waktu: {$waktuTampil}",
                "Lokasi: " . ($jadwal->lokasi ?? '-'),
                "Kader: {$namaKader}",
                "Bidan Pemeriksa: {$namaBidan}",
            ]);

            $this->kirimJikaBelumAda(
                $user,
                $jadwal,
                $jenis,
                'posyandu',
                'Pengingat Jadwal Posyandu',
                $isi,
                [
                    'jenis_kegiatan' => $namaKegiatan,
                    'kader' => $namaKader,
                    'bidan' => $namaBidan,
                ]
            );
        }
    }

    /**
     * Pengingat pemeriksaan kepada Bidan pemeriksa.
     */
    private function kirimPengingatPemeriksaan(
        string $tanggal,
        string $jenis
    ): void {
        $jadwals = Jadwal::query()
            ->whereDate('tanggal', $tanggal)
            ->whereHas('jadwalPemeriksaan.bidan.user')
            ->with([
                'jadwalPemeriksaan.bidan.user',
            ])
            ->get();

        foreach ($jadwals as $jadwal) {
            $detail = $jadwal->jadwalPemeriksaan;

            $user = $detail?->bidan?->user;

            if (!$user) {
                continue;
            }

            $namaBidan = $detail->bidan?->user?->name ?? '-';

            $namaPemeriksaan = $jadwal->jenis_kegiatan
                ?? 'Pemeriksaan Ibu Hamil';

            $catatan = $detail->catatan ?: '-';

            $tanggalTampil = $jadwal->tanggal
                ? $jadwal->tanggal->translatedFormat('d F Y')
                : '-';

            $waktuTampil = $jadwal->waktu
                ? Carbon::parse($jadwal->waktu)->format('H:i')
                : '-';

            $isi = implode(' | ', [
                "Pemeriksaan: {$namaPemeriksaan}",
                "Tanggal: {$tanggalTampil}",
                "Waktu: {$waktuTampil}",
                "Lokasi: " . ($jadwal->lokasi ?? '-'),
                "Bidan Pemeriksa: {$namaBidan}",
                "Catatan: {$catatan}",
            ]);

            $this->kirimJikaBelumAda(
                $user,
                $jadwal,
                $jenis,
                'pemeriksaan',
                'Pengingat Jadwal Pemeriksaan',
                $isi,
                [
                    'jenis_pemeriksaan' => $namaPemeriksaan,
                    'bidan' => $namaBidan,
                    'catatan' => $catatan,
                ]
            );
        }
    }

    /**
     * Mencegah notifikasi ganda untuk jadwal dan jenis pengingat yang sama.
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
                'tanggal' => $jadwal->tanggal?->toDateString(),
                'waktu' => $jadwal->waktu,
                'lokasi' => $jadwal->lokasi,
            ], $dataTambahan)
        ));
    }
}
