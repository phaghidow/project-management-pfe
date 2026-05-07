<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'active_user' => \App\Http\Middleware\ActiveUserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $accessDeniedMessage = 'Acces refuse : vous n\'avez pas les droits necessaires pour acceder a cette ressource.';

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($accessDeniedMessage) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $accessDeniedMessage,
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', $accessDeniedMessage);
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) use ($accessDeniedMessage) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $accessDeniedMessage,
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', $accessDeniedMessage);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->guest(route('login'));
        });
    })->create();
