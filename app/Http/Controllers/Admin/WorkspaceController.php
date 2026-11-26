<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\AdminNavigation;
use App\Support\StorefrontSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function storefrontSettings(): View
    {
        return view('admin.storefront.navigation', [
            'settings' => StorefrontSettings::all(),
        ]);
    }

    public function updateStorefrontSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'announcement_enabled' => ['nullable', 'boolean'],
            'announcement_en' => ['required', 'string', 'max:180'],
            'announcement_ar' => ['required', 'string', 'max:180'],
            'announcement_url_path' => ['nullable', 'string', 'max:120'],
            'free_delivery_threshold_omr' => ['required', 'numeric', 'min:0', 'max:1000'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'gifts_eid' => ['nullable', 'boolean'],
            'gifts_ramadan' => ['nullable', 'boolean'],
            'gifts_wedding' => ['nullable', 'boolean'],
        ]);

        StorefrontSettings::save([
            'announcement' => [
                'enabled' => (bool) ($validated['announcement_enabled'] ?? false),
                'en' => $validated['announcement_en'],
                'ar' => $validated['announcement_ar'],
                'url_path' => $validated['announcement_url_path'] ?? '/shop',
            ],
            'free_delivery_threshold_omr' => (float) $validated['free_delivery_threshold_omr'],
            'support' => [
                'whatsapp_number' => $validated['whatsapp_number'] ?? '+96871826515',
            ],
            'seasonal_gifts' => [
                'eid' => (bool) ($validated['gifts_eid'] ?? false),
                'ramadan' => (bool) ($validated['gifts_ramadan'] ?? false),
                'wedding' => (bool) ($validated['gifts_wedding'] ?? false),
            ],
        ]);

        return redirect()
            ->route('admin.storefront.navigation')
            ->with('status', 'Storefront navigation & announcement settings updated.');
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
