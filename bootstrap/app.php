<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();


    // 🕐 Laravel 12 style scheduler registration
$app->booted(function () use ($app) {
    $schedule = $app->make(Schedule::class);

    // প্রতিদিন রাত ১১:৫৯ এ AutoAttendance কমান্ড চালাবে
    $schedule->command('app:auto-attendance')->dailyAt('12:53');
});


return $app;


    