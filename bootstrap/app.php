<?php

use App\Console\Commands\CreateAdminUser;
use App\Http\Middleware\EnsureCatalogAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([CreateAdminUser::class])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'catalog.admin' => EnsureCatalogAdmin::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    })->create();
