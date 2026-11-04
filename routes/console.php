<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sip:check-expiry')
    ->dailyAt('07:00')
    ->timezone('Asia/Makassar')
    ->description('Early Warning Engine Harian Pukul 07.00 WITA untuk Monitoring SIP Dokter');
