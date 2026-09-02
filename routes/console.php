<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


use Illuminate\Support\Facades\Schedule;

// Selesaikan pesanan shipped 3 hari dan buat ulasan otomatis bintang 5
// Dijalankan setiap hari pukul 02:00 WIB (UTC+7 = 19:00 UTC).
Schedule::command('pesanan:selesaikan-otomatis')->dailyAt('02:00');
