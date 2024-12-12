<?php

namespace App\Jobs;

use App\Services\Weather;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class WeatherForecastJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $type;
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->type == 'tides') {
                Weather::getTides();
            } else {
                Weather::getForecast();
            }
        }catch (\Exception $e) {}
    }
}
