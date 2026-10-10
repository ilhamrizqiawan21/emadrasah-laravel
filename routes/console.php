<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cadangan harian database + berkas unggahan (disimpan 14 hari). Butuh cron: lihat docs/DEPLOYMENT.md.
Schedule::command('madrasah:backup')->dailyAt('01:30')->withoutOverlapping();

// Pengingat harian: tugas jatuh tempo dan sarana yang belum dikembalikan (masuk ke kotak notifikasi).
Schedule::command('madrasah:pengingat')->dailyAt('07:00')->withoutOverlapping();

// Catatan audit lebih dari 2 tahun dibersihkan tiap Minggu dini hari.
Schedule::command('madrasah:bersihkan-audit')->weeklyOn(0, '02:15')->withoutOverlapping();
