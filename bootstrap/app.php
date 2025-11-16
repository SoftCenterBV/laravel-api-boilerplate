<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            foreach (\App\Enums\ApiVersion::cases() as $key => $value) {
                $version = $value->value;
                Route::prefix("api/{$version}")
                    ->name("api.{$version}.")
                    ->middleware('api')
                    ->group(base_path("routes/api_{$version}.php"));
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
