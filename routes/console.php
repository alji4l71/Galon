<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Setiap 1 Januari pukul 01:00: arsipkan pesanan tahun lalu ke CSV lalu hapus dari database.
// Butuh cron di server: * * * * * php /path/ke/project/artisan schedule:run
Schedule::command('orders:archive-reset')->yearlyOn(1, 1, '01:00');
