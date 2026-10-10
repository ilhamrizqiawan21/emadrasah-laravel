<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/** Pengingat umum untuk staf (tugas jatuh tempo, sarana belum kembali). `kunci` mencegah pengingat ganda dalam sehari. */
class Pengingat extends Notification
{
    public function __construct(
        private string $judul,
        private string $pesan,
        private string $url,
        private string $kunci,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return ['judul' => $this->judul, 'pesan' => $this->pesan, 'url' => $this->url, 'kunci' => $this->kunci];
    }
}
