<?php

use App\Http\Middleware\CoursePaidMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__ . '/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function (Application $app) { // The closure now uses the standard Route:: facade

            // 1. Load the default WEB routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // 2. Load the default API routes (Shared/Public)
            Route::middleware('api') // Uses the 'api' middleware group (rate-limiting, stateless)
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // 3. Load the dedicated USER API routes
            Route::middleware('api')
                ->prefix('api/user')
                ->name('api.user.')
                ->group(base_path('routes/user_api.php'));

            // 4. Load the dedicated ADMIN API routes
            Route::middleware('api')
                ->prefix('api/admin')
                ->name('api.admin.')
                ->group(base_path('routes/admin_api.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(HandleCors::class);

        // Trust all proxies (Cloudflare Tunnel)
        // we did that to make sure all response url's are https not http, such as
        // images, videos, etc.
        $middleware->trustProxies(
            at: '*',
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
        );

        $middleware->alias([
            // Add the Sanctum abilities middleware here
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'course.paid' => CoursePaidMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {})->create();
