<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'staff.psdm' => \App\Http\Middleware\StaffPsdmMiddleware::class,
            'staff.keuangan' => \App\Http\Middleware\StaffKeuanganMiddleware::class,
            'user.only' => \App\Http\Middleware\UserMiddleware::class,
            '2fa' => \App\Http\Middleware\TwoFactorMiddleware::class,
        ]);

        // Append 2FA check to all web requests
        $middleware->web(append: [
            \App\Http\Middleware\TwoFactorMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
