<?php

namespace App\Http\Controllers\Storefront;

use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class HomeController
{
    public function __invoke(string $locale): View
    {
        App::setLocale($locale);

        return view('storefront.home', ['locale' => $locale]);
    }
}
