<?php

use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\WorkspaceController;
use App\Http\Controllers\LaunchAccessController;
use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\StoreInfoController;
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
        Route::get('/search', [AdminAccountController::class, 'search'])->name('search');

        Route::get('/account/profile', [AdminAccountController::class, 'profile'])->name('account.profile');
        Route::put('/account/profile', [AdminAccountController::class, 'updateProfile'])->name('account.profile.update');
        Route::get('/account/password', [AdminAccountController::class, 'password'])->name('account.password');
        Route::put('/account/password', [AdminAccountController::class, 'updatePassword'])->name('account.password.update');
        Route::get('/account/preferences', [AdminAccountController::class, 'preferences'])->name('account.preferences');
        Route::put('/account/preferences', [AdminAccountController::class, 'updatePreferences'])->name('account.preferences.update');

        Route::resource('products', ProductController::class)->except(['show']);

        Route::get('/catalog/categories', [WorkspaceController::class, 'categories'])->name('catalog.categories');
        Route::get('/catalog/brands', [WorkspaceController::class, 'brands'])->name('catalog.brands');

        Route::get('/inventory/stock-overview', [WorkspaceController::class, 'stockOverview'])->name('inventory.stock-overview');
        Route::get('/inventory/low-stock', [WorkspaceController::class, 'lowStock'])->name('inventory.low-stock');

        Route::get('/settings/users-roles', [WorkspaceController::class, 'usersRoles'])->name('settings.users-roles');
        Route::get('/storefront/navigation', [WorkspaceController::class, 'storefrontSettings'])->name('storefront.navigation');
        Route::post('/storefront/navigation', [WorkspaceController::class, 'updateStorefrontSettings'])->name('storefront.navigation.update');

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

    Route::prefix('{locale}')
        ->whereIn('locale', ['en', 'ar'])
        ->group(function (): void {
            Route::get('/', HomeController::class)->name('store.home');
            Route::get('/shop', [CatalogController::class, 'shop'])->name('store.shop');
            Route::get('/new-in', [CatalogController::class, 'newIn'])->name('store.new-in');
            Route::get('/brands', [CatalogController::class, 'brands'])->name('store.brands');
            Route::get('/brands/{slug}', [CatalogController::class, 'brandShow'])->name('store.brands.show');
            Route::get('/oud-arabian', [CatalogController::class, 'oudArabian'])->name('store.oud-arabian');
            Route::get('/best-sellers', [CatalogController::class, 'bestSellers'])->name('store.best-sellers');
            Route::get('/gifts', [CatalogController::class, 'gifts'])->name('store.gifts');
            Route::get('/offers', [CatalogController::class, 'offers'])->name('store.offers');
            Route::get('/products/{slug}', [CatalogController::class, 'productShow'])->name('store.products.show');
            Route::get('/search/suggestions', [CatalogController::class, 'suggestions'])->name('store.search.suggestions');

            Route::get('/stores', [StoreInfoController::class, 'stores'])->name('store.stores');
            Route::get('/track-order', [StoreInfoController::class, 'trackOrder'])->name('store.track-order');
            Route::get('/support', [StoreInfoController::class, 'support'])->name('store.support');
            Route::get('/account', [StoreInfoController::class, 'account'])->name('store.account');
            Route::get('/wishlist', [StoreInfoController::class, 'wishlist'])->name('store.wishlist');
            Route::get('/bag', [StoreInfoController::class, 'bag'])->name('store.bag');
            Route::get('/pages/{slug}', [StoreInfoController::class, 'page'])
                ->whereIn('slug', [
                    'about-us',
                    'authenticity',
                    'shipping-delivery',
                    'returns-refunds',
                    'faq',
                    'privacy-policy',
                    'terms-of-use',
                    'cookie-policy',
                    'disclaimer',
                ])
                ->name('store.page');
        });
});
