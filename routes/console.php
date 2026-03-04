<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('participants:import-contracts --scheduled')
    ->dailyAt(env('CONTRACT_IMPORT_SCHEDULE_TIME', '01:00'))
    ->timezone(env('CONTRACT_IMPORT_SCHEDULE_TIMEZONE', config('app.timezone', 'UTC')))
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/contracts-import.log'));

