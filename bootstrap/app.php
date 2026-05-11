<?php

use App\Console\Commands\NormalizeClassExpenses;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        NormalizeClassExpenses::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'client' => \App\Http\Middleware\ClientMiddleware::class,
            'employee' => \App\Http\Middleware\EmployeeMiddleware::class,
            'finance' => \App\Http\Middleware\FinanceMiddleware::class,
            'trainer' => \App\Http\Middleware\TrainerMiddleware::class,
            'marketing' => \App\Http\Middleware\MarketingMiddleware::class,
            'akademik' => \App\Http\Middleware\AkademikMiddleware::class,
            'nocache' => \App\Http\Middleware\NoCacheHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
