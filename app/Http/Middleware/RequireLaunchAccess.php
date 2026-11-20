<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class RequireLaunchAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('launch.enabled') || $request->session()->get('launch.access_granted') === true) {
            return $next($request);
        }

        $locale = $request->segment(1);
        $locale = in_array($locale, ['en', 'ar'], true) ? $locale : 'en';
        App::setLocale($locale);

        $requestedUri = $request->getRequestUri();
        if (str_starts_with($requestedUri, '/') && ! str_starts_with($requestedUri, '//')) {
            $request->session()->put('launch.intended_uri', $requestedUri);
        }

        return response()
            ->view('storefront.launch', ['locale' => $locale])
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }
}
