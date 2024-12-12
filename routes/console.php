<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('weather:forecast')->dailyAt('08:00');
Schedule::command('weather:tides-forecast')->dailyAt('08:00');
Schedule::command('app:weather-forecast')->dailyAt('08:03');
