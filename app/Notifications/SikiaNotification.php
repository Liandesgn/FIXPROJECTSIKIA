<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SikiaNotification extends Notification
{
    use Queueable;

    protected string $jenis;
    protected string $judul;
    protected string $isi;
    protected array $dataTambahan;

    public function __construct(
        string $jenis,
        string $judul,
        string $isi,
        array $dataTambahan = []
    ) {
        $this->jenis = $jenis;
        $this->judul = $judul;
        $this->isi = $isi;
        $this->dataTambahan = $dataTambahan;
    }

    /**
     * Notifikasi disimpan ke database.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data yang disimpan ke tabel notifikasi.
     */
    public function toDatabase(object $notifiable): array
    {
        return array_merge([
            'jenis' => $this->jenis,
            'judul' => $this->judul,
            'isi' => $this->isi,
        ], $this->dataTambahan);
    }

    /**
     * Format data notifikasi.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
