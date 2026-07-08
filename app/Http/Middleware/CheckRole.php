<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }
            abort(403, 'Vous n\'avez pas les droits nécessaires pour accéder à cette page.');
        }

        return $next($request);
    }
}
