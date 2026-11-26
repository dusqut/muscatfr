<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCatalogAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->canManageCatalog(), 403);

        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

        return $response;
    }
}
