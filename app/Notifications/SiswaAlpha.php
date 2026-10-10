<?php

namespace App\Notifications;

use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Kabar untuk wali: anaknya dicatat alpha (tanpa keterangan). */
class SiswaAlpha extends Notification
{
    public function __construct(private Siswa $siswa, private Carbon $tanggal) {}

    /** Email hanya bila SMTP sungguhan dikonfigurasi; mailer "log" akan menulis isi surat ke berkas log. */
    public function via(object $notifiable): array
    {
        $kirimEmail = $notifiable->email && ! in_array(config('mail.default'), ['log', 'array'], true);

        return $kirimEmail ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'judul' => 'Ananda tidak hadir tanpa keterangan',
            'pesan' => "{$this->siswa->nama_lengkap} tercatat alpha pada {$this->tanggal->translatedFormat('l, d F Y')}.",
            'url' => route('wali.show', ['siswa' => $this->siswa, 'bulan' => $this->tanggal->format('Y-m')]),
            'kunci' => "alpha:{$this->siswa->id}:{$this->tanggal->toDateString()}",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->toDatabase($notifiable);

        return (new MailMessage)
            ->subject($data['judul'])
            ->greeting('Assalamu\'alaikum')
            ->line($data['pesan'])
            ->line('Jika ananda sebenarnya izin atau sakit, mohon hubungi wali kelas agar catatan dapat diperbaiki.')
            ->action('Lihat kehadiran', $data['url']);
    }
}
