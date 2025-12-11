<?php

namespace App\Providers;

use App\Domains\StateMachine\Factory\StateHandlerFactory;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StateHandlerFactory::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Login rate limiter: 5 attempts per minute per email/IP
        RateLimiter::for('login', function (Request $request) {
            $key = $request->ip().':'.($request->input('email') ?? 'unknown');

            return Limit::perMinute(5)->by($key);
        });

        // Test execution rate limiter: custom bucket for test execution
        RateLimiter::for('test', function (Request $request) {
            $key = $request->user()?->id ?? $request->ip();

            return Limit::perMinute(30)->by($key);
        });

        // Session reveal rate limiter: custom bucket for reveal endpoint
        RateLimiter::for('session.reveal', function (Request $request) {
            $key = $request->user()?->id ?? $request->ip();

            return Limit::perHour(10)->by($key);
        });
    }
}
