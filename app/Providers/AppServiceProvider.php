<?php

namespace App\Providers;

use App\Support\AdminNavigation;
use App\Support\StorefrontNavigation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        View::composer('admin.layout', function ($view): void {
            $counts = AdminNavigation::counts();
            $view->with('adminNavigation', AdminNavigation::sidebar($counts));
            $view->with('adminUnreadCount', $counts['tasks_count'] ?? 0);
        });

        View::composer('storefront.*', function ($view): void {
            if (in_array($view->getName(), ['storefront.launch', 'storefront.launch-access'], true)) {
                return;
            }

            $data = $view->getData();
            if (isset($data['storeNav'])) {
                return;
            }

            $locale = in_array($data['locale'] ?? null, ['en', 'ar'], true)
                ? $data['locale']
                : (in_array(App::getLocale(), ['en', 'ar'], true) ? App::getLocale() : 'en');

            $view->with('storeNav', StorefrontNavigation::forLocale($locale));
        });
    }
}
