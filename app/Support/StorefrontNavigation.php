<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StorefrontNavigation
{
    /**
     * Build inventory-backed navigation data for the desktop header, mega-menus, and mobile drawer.
     *
     * @return array<string, mixed>
     */
    public static function forLocale(string $locale): array
    {
        $settings = StorefrontSettings::all();
        $catalog = self::loadCatalogSnapshot($locale);

        $announcementText = $locale === 'ar'
            ? ($settings['announcement']['ar'] ?? __('store.announcement'))
            : ($settings['announcement']['en'] ?? __('store.announcement'));

        $announcementUrl = route('store.shop', ['locale' => $locale]);
        if (! empty($settings['announcement']['url_path'])) {
            $path = ltrim((string) $settings['announcement']['url_path'], '/');
            $announcementUrl = url("/{$locale}/{$path}");
        }

        return [
            'locale' => $locale,
            'other_locale' => $locale === 'ar' ? 'en' : 'ar',
            'currency' => $settings['currency'] ?? 'OMR',
            'announcement' => [
                'enabled' => (bool) ($settings['announcement']['enabled'] ?? true),
                'text' => $announcementText,
                'url' => $announcementUrl,
            ],
            'support' => $settings['support'] ?? [],
            'free_delivery_omr' => $settings['free_delivery_threshold_omr'] ?? 35,
            'shop' => [
                'genders' => $catalog['genders'],
                'types' => $catalog['types'],
                'categories' => $catalog['categories'],
                'budgets' => $catalog['budgets'],
            ],
            'brands' => $catalog['brands'],
            'brand_letters' => $catalog['brand_letters'],
            'oud_arabian' => $catalog['oud_arabian'],
            'gifts' => self::buildGiftLinks($locale, $catalog['genders'], $settings['seasonal_gifts'] ?? []),
            'capabilities' => [
                'has_published_products' => $catalog['has_published_products'],
                'has_orders_backend' => false,
                'has_wishlist_backend' => false,
                'has_cart_backend' => false,
                'has_customer_auth' => false,
                'has_promotions_backend' => false,
            ],
        ];
    }

    /**
     * Safely inspect the database catalog so tests or fresh DBs without migrations never crash.
     *
     * @return array<string, mixed>
     */
    private static function loadCatalogSnapshot(string $locale): array
    {
        $empty = [
            'has_published_products' => false,
            'genders' => [],
            'types' => [],
            'categories' => collect(),
            'budgets' => [],
            'brands' => collect(),
            'brand_letters' => [],
            'oud_arabian' => [],
        ];

        try {
            if (! Schema::hasTable('products') || ! Schema::hasTable('categories') || ! Schema::hasTable('brands')) {
                return $empty;
            }
        } catch (Throwable) {
            return $empty;
        }

        $publishedExists = Product::query()
            ->where('status', 'published')
            ->where('is_sample', false)
            ->exists();

        // Scope products to published catalog when live; otherwise avoid fabricating unsupported links.
        $productScope = Product::query()->when(
            $publishedExists,
            fn ($q) => $q->where('status', 'published')->where('is_sample', false),
            fn ($q) => $q->where('is_sample', false)
        );

        $availableGenders = (clone $productScope)
            ->select('audience')
            ->distinct()
            ->pluck('audience')
            ->filter()
            ->values()
            ->all();

        $orderedGenders = array_values(array_intersect(['men', 'women', 'unisex'], $availableGenders));

        $availableTypes = (clone $productScope)
            ->whereNotNull('concentration')
            ->where('concentration', '!=', '')
            ->select('concentration')
            ->distinct()
            ->pluck('concentration')
            ->values()
            ->all();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount([
                'products as published_products_count' => fn ($q) => $q->where('status', 'published')->where('is_sample', false),
            ])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (Category $cat) => ! $publishedExists || $cat->published_products_count > 0)
            ->values();

        $brands = Brand::query()
            ->withCount([
                'products as published_products_count' => fn ($q) => $q->where('status', 'published')->where('is_sample', false),
            ])
            ->orderBy('name')
            ->get()
            ->filter(fn (Brand $brand) => ! $publishedExists || $brand->published_products_count > 0)
            ->values();

        $brandLetters = $brands
            ->map(fn (Brand $brand) => strtoupper(mb_substr($brand->name, 0, 1)))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $budgets = self::computeSupportedBudgets($publishedExists);
        $oudArabian = self::buildOudArabianLinks($locale, $categories, $availableTypes);

        return [
            'has_published_products' => $publishedExists,
            'genders' => $orderedGenders,
            'types' => $availableTypes,
            'categories' => $categories,
            'budgets' => $budgets,
            'brands' => $brands,
            'brand_letters' => $brandLetters,
            'oud_arabian' => $oudArabian,
        ];
    }

    /**
     * Only include OMR budget buckets backed by real variant pricing in the catalog.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function computeSupportedBudgets(bool $publishedExists): array
    {
        if (! Schema::hasTable('product_variants')) {
            return [];
        }

        $budgets = [];
        foreach (config('storefront.budget_ranges', []) as $range) {
            $query = ProductVariant::query()
                ->where('is_active', true)
                ->whereHas('product', function ($q) use ($publishedExists): void {
                    if ($publishedExists) {
                        $q->where('status', 'published')->where('is_sample', false);
                    } else {
                        $q->where('is_sample', false);
                    }
                })
                ->where('price', '>=', $range['min']);

            if ($range['max'] !== null) {
                $query->where('price', '<=', $range['max']);
            }

            if ($query->exists()) {
                $budgets[] = $range;
            }
        }

        return $budgets;
    }

    /**
     * Only expose Oud & Arabian links that map to existing categories or product types.
     *
     * @param  Collection<int, Category>  $categories
     * @param  array<int, string>  $types
     * @return array<int, array<string, string>>
     */
    private static function buildOudArabianLinks(string $locale, Collection $categories, array $types): array
    {
        $links = [];
        $oudCategory = $categories->firstWhere('slug', 'oud');
        if ($oudCategory) {
            $links[] = [
                'label' => $locale === 'ar' ? 'عطور العود' : 'Oud Perfumes',
                'url' => route('store.shop', ['locale' => $locale, 'category' => 'oud']),
                'subtitle' => $oudCategory->displayName($locale),
            ];
        }

        $amberCategory = $categories->firstWhere('slug', 'amber');
        if ($amberCategory) {
            $links[] = [
                'label' => $locale === 'ar' ? 'العنبر والعطور الشرقية' : 'Amber & Oriental Fragrances',
                'url' => route('store.shop', ['locale' => $locale, 'category' => 'amber']),
                'subtitle' => $amberCategory->displayName($locale),
            ];
        }

        foreach (['Attar', 'Oud Oil', 'Bakhoor', 'Luban'] as $arabianType) {
            if (in_array($arabianType, $types, true)) {
                $links[] = [
                    'label' => $arabianType,
                    'url' => route('store.shop', ['locale' => $locale, 'type' => $arabianType]),
                    'subtitle' => $locale === 'ar' ? 'تشكيلة عُمانية مختارة' : 'Omani & Arabian Craft',
                ];
            }
        }

        return $links;
    }

    /**
     * Build gift links respecting active genders and seasonal gift configuration.
     *
     * @param  array<int, string>  $genders
     * @param  array<string, mixed>  $seasonal
     * @return array<int, array<string, string>>
     */
    private static function buildGiftLinks(string $locale, array $genders, array $seasonal): array
    {
        $links = [];

        if (in_array('men', $genders, true)) {
            $links[] = [
                'label' => __('store.mega_gifts.for_him'),
                'url' => route('store.shop', ['locale' => $locale, 'gender' => 'men']),
            ];
        }
        if (in_array('women', $genders, true)) {
            $links[] = [
                'label' => __('store.mega_gifts.for_her'),
                'url' => route('store.shop', ['locale' => $locale, 'gender' => 'women']),
            ];
        }
        if (! empty($seasonal['eid'])) {
            $links[] = [
                'label' => __('store.mega_gifts.eid'),
                'url' => route('store.gifts', ['locale' => $locale, 'occasion' => 'eid']),
            ];
        }
        if (! empty($seasonal['ramadan'])) {
            $links[] = [
                'label' => __('store.mega_gifts.ramadan'),
                'url' => route('store.gifts', ['locale' => $locale, 'occasion' => 'ramadan']),
            ];
        }
        if (! empty($seasonal['wedding'])) {
            $links[] = [
                'label' => __('store.mega_gifts.wedding'),
                'url' => route('store.gifts', ['locale' => $locale, 'occasion' => 'wedding']),
            ];
        }

        $links[] = [
            'label' => __('store.mega_gifts.custom_box'),
            'url' => route('store.gifts', ['locale' => $locale]),
        ];

        return $links;
    }
}
