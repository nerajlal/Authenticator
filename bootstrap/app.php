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
        // Allow Shopify to embed the app in iframe
        $middleware->appendToGroup('web', \App\Http\Middleware\AllowShopifyEmbedding::class);
        // Register biometric enrollment popup injection middleware for web routes
        $middleware->appendToGroup('web', \App\Http\Middleware\InjectBiometricEnrollmentPopup::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

