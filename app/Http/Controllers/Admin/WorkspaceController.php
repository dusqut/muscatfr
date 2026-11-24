<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\AdminNavigation;
use Illuminate\View\View;

class WorkspaceController
{
    public function categories(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        return view('admin.catalog.categories', compact('categories'));
    }

    public function brands(): View
    {
        $brands = Brand::query()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('admin.catalog.brands', compact('brands'));
    }

    public function stockOverview(): View
    {
        $variants = ProductVariant::query()
            ->with(['product.translations', 'product.brand'])
            ->orderBy('stock_on_hand')
            ->orderBy('sku')
            ->paginate(25);

        return view('admin.inventory.stock', [
            'variants' => $variants,
            'lowStockOnly' => false,
            'threshold' => AdminNavigation::LOW_STOCK_THRESHOLD,
        ]);
    }

    public function lowStock(): View
    {
        $variants = ProductVariant::query()
            ->with(['product.translations', 'product.brand'])
            ->where('stock_on_hand', '<=', AdminNavigation::LOW_STOCK_THRESHOLD)
            ->orderBy('stock_on_hand')
            ->orderBy('sku')
            ->paginate(25);

        return view('admin.inventory.stock', [
            'variants' => $variants,
            'lowStockOnly' => true,
            'threshold' => AdminNavigation::LOW_STOCK_THRESHOLD,
        ]);
    }

    public function usersRoles(): View
    {
        $users = User::query()
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('admin.settings.users', compact('users'));
    }

    public function module(string $section, string $slug): View
    {
        $module = AdminNavigation::findModule($section, $slug);
        abort_if($module === null, 404);

        return view('admin.module', [
            'section' => $module['section'],
            'item' => $module['item'],
        ]);
    }
}
