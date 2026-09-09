<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Selesaikan pesanan shipped 3 hari dan buat ulasan otomatis bintang 5
// Dijalankan setiap hari pukul 02:00 WIB (UTC+7 = 19:00 UTC).
Schedule::command('pesanan:selesaikan-otomatis')->dailyAt('02:00');

// Batalkan pesanan yang belum dibayar melewati batas waktu 24 jam secara berkala setiap jam
Schedule::command('pesanan:batalkan-kedaluwarsa')->hourly();