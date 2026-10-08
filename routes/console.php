<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cadangan harian database + berkas unggahan (disimpan 14 hari). Butuh cron: lihat docs/DEPLOYMENT.md.
Schedule::command('madrasah:backup')->dailyAt('01:30')->withoutOverlapping();
