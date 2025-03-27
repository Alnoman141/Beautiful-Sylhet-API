<?php

namespace App\Providers;

use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('Alal', fn() => new Fa());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('tour-plan', function (Request $request) {
            return Limit::perDay(config('openai.daily_limit'))->by($request->ip())->response(function () {
                return response()->json([
                    'status_code' => 429,
                    'message' => 'Too many requests. Please try again later.',
                ], 429);
            });
        });
    }
}
