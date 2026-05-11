<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Check project deadlines daily at 8 AM
Schedule::command('notifications:check-deadlines')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta');

