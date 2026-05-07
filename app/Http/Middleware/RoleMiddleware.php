<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next, ...$roles): Response
    {
        $accessDeniedMessage = 'Acces refuse : vous n\'avez pas les droits necessaires pour acceder a cette ressource.';

        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect('/login');
        }

        if (!in_array(auth()->user()->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $accessDeniedMessage,
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', $accessDeniedMessage);
        }

        return $next($request);
    }
}