<?php

namespace App\Console\Commands;

use App\Jobs\WeatherForecastJob;
use Illuminate\Console\Command;

class TidesForecastCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:tides-forecast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get tides forecast';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            WeatherForecastJob::dispatch('tides');
            WeatherForecastJob::dispatch('forecast')->delay(now()->addSecond());
        }catch (\Exception $e) {}
    }
}
