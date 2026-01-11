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

        // Piston code execution rate limiter: per user
        RateLimiter::for('piston', function (Request $request) {
            $userId = $request->user()?->id ?? $request->ip();
            $sessionId = $request->route('session');

            return [
                // 30 executions per minute per user
                Limit::perMinute(30)->by('piston:user:'.$userId),
                // 10 executions per minute per session (prevents hammering one problem)
                Limit::perMinute(10)->by('piston:session:'.$sessionId),
            ];
        });

        // Session reveal rate limiter: custom bucket for reveal endpoint
        RateLimiter::for('session.reveal', function (Request $request) {
            $key = $request->user()?->id ?? $request->ip();

            return Limit::perHour(10)->by($key);
        });
    }
}
