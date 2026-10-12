<?php

namespace App\Jobs;

use App\Services\Weather;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

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
        Log::info('WeatherForecastJob started', [
            'type' => $this->type,
        ]);

        if ($this->type === 'tides') {
            Weather::getTides();
        } else {
            Weather::getForecast();
        }

        Log::info('WeatherForecastJob completed', [
            'type' => $this->type,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('WeatherForecastJob failed', [
            'type' => $this->type,
            'error' => $exception?->getMessage(),
        ]);
    }
}
