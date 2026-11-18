<?php

use App\Http\Controllers\Storefront\HomeController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/en');

Route::get('/{locale}', HomeController::class)
    ->whereIn('locale', ['en', 'ar'])
    ->name('store.home');
