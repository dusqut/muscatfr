<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LaunchAccessController
{
    public function show(?string $locale = null): View
    {
        $locale = $this->locale($locale);
        App::setLocale($locale);

        return view('storefront.launch-access', ['locale' => $locale]);
    }

    public function authenticate(Request $request, ?string $locale = null): RedirectResponse
    {
        $locale = $this->locale($locale);
        App::setLocale($locale);

        $validated = $request->validate([
            'password' => ['required', 'string', 'max:255'],
        ]);

        $passwordHash = config('launch.password_hash');
        if (! is_string($passwordHash) || password_get_info($passwordHash)['algoName'] === 'unknown') {
            Log::critical('Launch access is enabled but its password hash is missing or invalid.');
            abort(503, __('launch.access_unavailable'));
        }

        if (! Hash::check($validated['password'], $passwordHash)) {
            return redirect()
                ->route('launch.access', ['locale' => $locale])
                ->withErrors(['password' => __('launch.invalid_password')]);
        }

        $request->session()->regenerate();
        $request->session()->put('launch.access_granted', true);

        $intendedUri = $request->session()->pull('launch.intended_uri');
        if (! is_string($intendedUri) || ! str_starts_with($intendedUri, '/') || str_starts_with($intendedUri, '//')) {
            $intendedUri = route('store.home', ['locale' => $locale]);
        }

        return redirect($intendedUri);
    }

    private function locale(?string $locale): string
    {
        return in_array($locale, ['en', 'ar'], true) ? $locale : 'en';
    }
}
