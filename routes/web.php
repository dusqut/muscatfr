<?php

use App\Http\Controllers\LaunchAccessController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Middleware\RequireLaunchAccess;
use Illuminate\Support\Facades\Route;

Route::get('/launch-access/{locale?}', [LaunchAccessController::class, 'show'])
    ->where('locale', 'en|ar')
    ->name('launch.access');

Route::post('/launch-access/{locale?}', [LaunchAccessController::class, 'authenticate'])
    ->where('locale', 'en|ar')
    ->middleware('throttle:5,1')
    ->name('launch.authenticate');

Route::middleware(RequireLaunchAccess::class)->group(function (): void {
    Route::redirect('/', '/en');

    Route::get('/{locale}', HomeController::class)
        ->whereIn('locale', ['en', 'ar'])
        ->name('store.home');
});
