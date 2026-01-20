<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictDocsAccess
{
    /**
     * Handle an incoming request.
     *
     * Restrict access to API documentation in production environments.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Block access to docs in production
        if (app()->environment('production')) {
            abort(404);
        }

        return $next($request);
    }
}
