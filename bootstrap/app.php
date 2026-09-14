<?php

use App\Console\Commands\NormalizeClassExpenses;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        NormalizeClassExpenses::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add CSRF and session middleware globally for all web requests
        $middleware->web(append: [
            // Ensure sessions are persisted
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'client' => \App\Http\Middleware\ClientMiddleware::class,
            'employee' => \App\Http\Middleware\EmployeeMiddleware::class,
            'finance' => \App\Http\Middleware\FinanceMiddleware::class,
            'trainer' => \App\Http\Middleware\TrainerMiddleware::class,
            'marketing' => \App\Http\Middleware\MarketingMiddleware::class,
            'akademik' => \App\Http\Middleware\AkademikMiddleware::class,
            'nocache' => \App\Http\Middleware\NoCacheHeaders::class,
            'api.key' => \App\Http\Middleware\VerifyApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi login atau formulir sudah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.',
                ], 419);
            }

            return response()->view('errors.419', [], 419);
        });
    })->create();
