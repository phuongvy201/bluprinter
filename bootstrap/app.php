<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Đăng ký middleware cho Spatie Permission
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'has.shop' => \App\Http\Middleware\HasShop::class,
        ]);

        // Exclude API routes from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->command('flash-deals:expire')->hourly();
        $schedule->command('flash-deals:rotate')->dailyAt('00:05');
        $schedule->command('promo:win-back --days=60')->weekly();
        $schedule->command('fbt:refresh')->dailyAt('02:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
