<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\AdminNavigation;
use Illuminate\View\View;

class DashboardController
{
    public function index(): View
    {
        $totalProducts = Product::query()->count();
        $publishedProducts = Product::query()->where('status', 'published')->count();
        $draftProducts = Product::query()->where('status', 'draft')->count();
        $sampleProducts = Product::query()->where('is_sample', true)->count();
        $totalCategories = Category::query()->count();
        $activeCategories = Category::query()->where('is_active', true)->count();
        $totalBrands = Brand::query()->count();
        $totalVariants = ProductVariant::query()->count();
        $totalStockUnits = (int) ProductVariant::query()->sum('stock_on_hand');
        $lowStockVariants = ProductVariant::query()
            ->with(['product.translations', 'product.brand'])
            ->where('stock_on_hand', '<=', AdminNavigation::LOW_STOCK_THRESHOLD)
            ->orderBy('stock_on_hand')
            ->limit(6)
            ->get();
        $lowStockCount = ProductVariant::query()
            ->where('stock_on_hand', '<=', AdminNavigation::LOW_STOCK_THRESHOLD)
            ->count();

        $recentProducts = Product::query()
            ->with(['brand', 'category', 'translations', 'variants'])
            ->latest('updated_at')
            ->limit(6)
            ->get();

        $categories = Category::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        $tasks = $this->buildTasks($sampleProducts, $lowStockCount);

        return view('admin.dashboard.index', [
            'metrics' => [
                'total_products' => $totalProducts,
                'published_products' => $publishedProducts,
                'draft_products' => $draftProducts,
                'sample_products' => $sampleProducts,
                'total_categories' => $totalCategories,
                'active_categories' => $activeCategories,
                'total_brands' => $totalBrands,
                'total_variants' => $totalVariants,
                'total_stock_units' => $totalStockUnits,
                'low_stock_count' => $lowStockCount,
                'admin_users_count' => User::query()->whereIn('role', ['admin', 'product_manager'])->count(),
            ],
            'lowStockVariants' => $lowStockVariants,
            'recentProducts' => $recentProducts,
            'categories' => $categories,
            'tasks' => $tasks,
        ]);
    }

    public function notifications(): View
    {
        $sampleProducts = Product::query()->where('is_sample', true)->count();
        $lowStockCount = ProductVariant::query()
            ->where('stock_on_hand', '<=', AdminNavigation::LOW_STOCK_THRESHOLD)
            ->count();

        $tasks = $this->buildTasks($sampleProducts, $lowStockCount);

        return view('admin.dashboard.notifications', [
            'tasks' => $tasks,
            'launchGateEnabled' => (bool) config('launch.gate_enabled', true),
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function buildTasks(int $sampleProducts, int $lowStockCount): array
    {
        $tasks = [];
        $productsWithoutImages = Product::query()->doesntHave('images')->count();

        if ($sampleProducts > 0) {
            $tasks[] = [
                'severity' => 'attention',
                'title' => "{$sampleProducts} demo sample product(s) still in catalog",
                'detail' => 'Replace placeholder copy and uncheck the sample flag before publishing to the storefront.',
                'action_label' => 'Review Products',
                'action_url' => route('admin.products.index', ['status' => 'draft']),
            ];
        }

        if ($lowStockCount > 0) {
            $tasks[] = [
                'severity' => 'warning',
                'title' => "{$lowStockCount} SKU variant(s) at or below " . AdminNavigation::LOW_STOCK_THRESHOLD . ' units',
                'detail' => 'Update stock quantities or deactivate out-of-stock sizes before launch.',
                'action_label' => 'View Low Stock',
                'action_url' => route('admin.inventory.low-stock'),
            ];
        }

        if ($productsWithoutImages > 0) {
            $tasks[] = [
                'severity' => 'info',
                'title' => "{$productsWithoutImages} product(s) without product photography",
                'detail' => 'Upload JPEG, PNG, or WebP imagery with English and Arabic alt text.',
                'action_label' => 'Open Catalog',
                'action_url' => route('admin.products.index'),
            ];
        }

        return $tasks;
    }
}
