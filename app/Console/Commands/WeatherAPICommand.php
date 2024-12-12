<?php

namespace App\Console\Commands;

use App\Services\Weather;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WeatherAPICommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:weather-forecast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Cache::forget('tide-forecast');
            Weather::tideAndForecast();
        }catch (\Exception $e) {}
    }
}
