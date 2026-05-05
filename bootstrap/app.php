<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(function (Request $request, Closure $next) {
            if ($request->getMethod() === 'OPTIONS') {
                return response('', 204)
                    ->header('Access-Control-Allow-Origin', 'https://docu-flow-frontend.vercel.app')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, X-Requested-With');
            }

            $response = $next($request);

            $response->headers->set('Access-Control-Allow-Origin', 'https://docu-flow-frontend.vercel.app');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, X-Requested-With');

            return $response;
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();