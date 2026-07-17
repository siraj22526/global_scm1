<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Refresh weather, currency, news, and risk scores hourly (PRD section 5.6/7.4).
// Locally, run `php artisan schedule:work` to simulate cron execution.
Schedule::command('app:refresh-data')->hourly()->withoutOverlapping();
