<?php

namespace App\Console\Commands;

use App\Jobs\WeatherJob;
use App\Services\Weather;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WeatherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:forecast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get weather forecast';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Cache::forget('weather-api');
        try {
            $weather = Weather::getWeather();
            if ($weather==403) {
                WeatherJob::dispatch()->delay(now()->addMinutes(30));
            }
        }catch (\Exception $e) {}
    }
}
