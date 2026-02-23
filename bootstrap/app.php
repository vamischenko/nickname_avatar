<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->throttleWithRedis();

        $middleware->configureRateLimiting(function (\Illuminate\Cache\RateLimiting\RateLimiter $limiter): void {
            $limiter->for('register', function (\Illuminate\Http\Request $request) {
                $max = (int) config('app.rate_limit_per_minute', 10);

                return \Illuminate\Cache\RateLimiting\Limit::perMinute($max)->by($request->ip());
            });
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
