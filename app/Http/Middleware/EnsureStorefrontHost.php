<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStorefrontHost
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->getHost() === config('app.admin_domain'), 404);

        return $next($request);
    }
}
