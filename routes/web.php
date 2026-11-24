<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\WorkspaceController;
use App\Http\Controllers\LaunchAccessController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Middleware\RequireLaunchAccess;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    Route::middleware(['auth', 'catalog.admin'])->group(function (): void {
        Route::redirect('/', '/admin/dashboard')->name('home');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/notifications', [DashboardController::class, 'notifications'])->name('dashboard.notifications');

        Route::resource('products', ProductController::class)->except(['show']);

        Route::get('/catalog/categories', [WorkspaceController::class, 'categories'])->name('catalog.categories');
        Route::get('/catalog/brands', [WorkspaceController::class, 'brands'])->name('catalog.brands');

        Route::get('/inventory/stock-overview', [WorkspaceController::class, 'stockOverview'])->name('inventory.stock-overview');
        Route::get('/inventory/low-stock', [WorkspaceController::class, 'lowStock'])->name('inventory.low-stock');

        Route::get('/settings/users-roles', [WorkspaceController::class, 'usersRoles'])->name('settings.users-roles');

        Route::get('/{section}/{slug}', [WorkspaceController::class, 'module'])
            ->whereIn('section', [
                'orders',
                'catalog',
                'inventory',
                'customers',
                'marketing',
                'storefront',
                'finance',
                'reports',
                'support',
                'settings',
            ])
            ->name('module');
    });
});

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
