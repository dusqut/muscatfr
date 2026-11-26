<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class AdminNavigation
{
    public const LOW_STOCK_THRESHOLD = 5;

    /**
     * Build the full sidebar tree annotated with active flags and live badge counts.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function sidebar(?array $counts = null): array
    {
        $counts ??= self::counts();
        $currentRoute = Route::currentRouteName();
        $currentParams = Route::current()?->parameters() ?? [];

        return array_map(function (array $section) use ($counts, $currentRoute, $currentParams): array {
            $sectionHasActive = false;

            $items = array_map(function (array $item) use ($counts, $currentRoute, $currentParams, &$sectionHasActive): array {
                $isActive = self::isItemActive($item, $currentRoute, $currentParams);
                if ($isActive) {
                    $sectionHasActive = true;
                }

                $badge = $item['tag'] ?? null;
                if (isset($item['badge_key']) && isset($counts[$item['badge_key']])) {
                    $badge = (string) $counts[$item['badge_key']];
                }

                return [
                    ...$item,
                    'url' => route($item['route'], $item['route_params'] ?? []),
                    'active' => $isActive,
                    'badge' => $badge,
                ];
            }, $section['items']);

            return [
                ...$section,
                'items' => $items,
                'active' => $sectionHasActive,
                'expanded' => $sectionHasActive || (bool) ($section['default_open'] ?? false),
            ];
        }, config('admin_navigation', []));
    }

    /**
     * Find a planned/phase module definition by section key and slug.
     *
     * @return array{section: array<string, mixed>, item: array<string, mixed>}|null
     */
    public static function findModule(string $sectionKey, string $slug): ?array
    {
        foreach (config('admin_navigation', []) as $section) {
            if ($section['key'] !== $sectionKey) {
                continue;
            }

            foreach ($section['items'] as $item) {
                $params = $item['route_params'] ?? [];
                if (($params['section'] ?? null) === $sectionKey && ($params['slug'] ?? null) === $slug) {
                    return [
                        'section' => $section,
                        'item' => $item,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Collect lightweight live counts for sidebar badges and dashboard tasks.
     *
     * @return array<string, int>
     */
    public static function counts(): array
    {
        $productsCount = Product::query()->count();
        $categoriesCount = Category::query()->count();
        $brandsCount = Brand::query()->count();
        $lowStockCount = ProductVariant::query()
            ->where('stock_on_hand', '<=', self::LOW_STOCK_THRESHOLD)
            ->count();
        $usersCount = User::query()->count();

        $sampleCount = Product::query()->where('is_sample', true)->count();
        $productsWithoutImages = Product::query()->doesntHave('images')->count();

        $tasksCount = 0;
        if ($sampleCount > 0) {
            $tasksCount++;
        }
        if ($lowStockCount > 0) {
            $tasksCount++;
        }
        if ($productsWithoutImages > 0) {
            $tasksCount++;
        }

        return [
            'products_count' => $productsCount,
            'categories_count' => $categoriesCount,
            'brands_count' => $brandsCount,
            'low_stock_count' => $lowStockCount,
            'users_count' => $usersCount,
            'tasks_count' => $tasksCount,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $currentParams
     */
    private static function isItemActive(array $item, ?string $currentRoute, array $currentParams): bool
    {
        if ($currentRoute === 'admin.module' && $item['route'] === 'admin.module') {
            $params = $item['route_params'] ?? [];

            return ($currentParams['section'] ?? null) === ($params['section'] ?? null)
                && ($currentParams['slug'] ?? null) === ($params['slug'] ?? null);
        }

        if (isset($item['match']) && is_array($item['match'])) {
            return in_array($currentRoute, $item['match'], true);
        }

        return $currentRoute === $item['route'];
    }
}
