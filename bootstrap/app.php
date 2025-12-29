<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
// use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
            web: __DIR__.'/../routes/web.php',
            api: __DIR__.'/../routes/api.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
    )
    // // ✅ Register alias middleware
    // $middleware->alias([
    //     'admin' => AdminMiddleware::class,
    //     // You can give HandleInertiaRequests an alias if needed
    //     'inertia' => HandleInertiaRequests::class,
    // ]);

 ->withMiddleware(function ($middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //


    })->create();
