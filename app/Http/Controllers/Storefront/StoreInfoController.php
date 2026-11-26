<?php

namespace App\Http\Controllers\Storefront;

use App\Support\StorefrontSettings;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class StoreInfoController
{
    public function stores(string $locale): View
    {
        App::setLocale($locale);
        $settings = StorefrontSettings::all();

        return view('storefront.stores', [
            'locale' => $locale,
            'support' => $settings['support'] ?? [],
            'freeDeliveryOmr' => $settings['free_delivery_threshold_omr'] ?? 40,
        ]);
    }

    public function support(string $locale): View
    {
        App::setLocale($locale);
        $settings = StorefrontSettings::all();

        return view('storefront.support', [
            'locale' => $locale,
            'support' => $settings['support'] ?? [],
            'freeDeliveryOmr' => $settings['free_delivery_threshold_omr'] ?? 40,
        ]);
    }

    public function page(string $locale, string $slug): View
    {
        App::setLocale($locale);
        $pages = __('store.legal_pages');
        abort_unless(is_array($pages) && isset($pages[$slug]), 404);

        $settings = StorefrontSettings::all();

        return view('storefront.policy', [
            'locale' => $locale,
            'slug' => $slug,
            'page' => $pages[$slug],
            'support' => $settings['support'] ?? [],
            'freeDeliveryOmr' => $settings['free_delivery_threshold_omr'] ?? 40,
        ]);
    }

    public function trackOrder(string $locale): View
    {
        return $this->integrationView(
            $locale,
            __('store.navigation.track_order'),
            __('store.pages.track_order_notice'),
            'Orders & Delivery Tracking backend'
        );
    }

    public function account(string $locale): View
    {
        return $this->integrationView(
            $locale,
            __('store.navigation.account'),
            __('store.pages.account_notice'),
            'Customer authentication & profile portal'
        );
    }

    public function wishlist(string $locale): View
    {
        return $this->integrationView(
            $locale,
            __('store.navigation.wishlist'),
            __('store.pages.wishlist_notice'),
            'Customer wishlist persistence'
        );
    }

    public function bag(string $locale): View
    {
        return $this->integrationView(
            $locale,
            __('store.navigation.bag'),
            __('store.pages.bag_notice'),
            'Shopping cart & OMR checkout flow'
        );
    }

    private function integrationView(string $locale, string $title, string $notice, string $dependency): View
    {
        App::setLocale($locale);

        return view('storefront.integration-page', [
            'locale' => $locale,
            'eyebrow' => __('store.pages.integration_notice_label'),
            'title' => $title,
            'notice' => $notice,
            'dependency' => $dependency,
        ]);
    }
}
