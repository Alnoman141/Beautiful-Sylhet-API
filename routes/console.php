<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
Schedule::command('weather:forecast')->dailyAt('08:00');
Schedule::command('weather:tides-forecast')->dailyAt('08:00');
Schedule::command('app:weather-forecast')->dailyAt('08:03');
