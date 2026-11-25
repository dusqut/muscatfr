<?php

namespace App\Http\Controllers\Storefront;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\StorefrontNavigation;
use App\Support\StorefrontSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class CatalogController
{
    public function shop(Request $request, string $locale): View
    {
        App::setLocale($locale);

        if (! $this->catalogTablesReady()) {
            return view('storefront.shop', [
                'locale' => $locale,
                'products' => new LengthAwarePaginator([], 0, 12),
                'categories' => collect(),
                'brands' => collect(),
                'activeFilters' => [],
            ]);
        }

        $query = Product::query()
            ->with(['brand', 'category', 'translations', 'variants', 'images'])
            ->where('status', 'published')
            ->where('is_sample', false);

        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(function (Builder $q) use ($search): void {
                $q->where('slug', 'like', "%{$search}%")
                    ->orWhere('concentration', 'like', "%{$search}%")
                    ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('category', fn (Builder $c) => $c->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%"))
                    ->orWhereHas('translations', function (Builder $t) use ($search): void {
                        $t->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('top_notes', 'like', "%{$search}%")
                            ->orWhere('middle_notes', 'like', "%{$search}%")
                            ->orWhere('base_notes', 'like', "%{$search}%");
                    })
                    ->orWhereHas('variants', fn (Builder $v) => $v->where('sku', 'like', "%{$search}%"));
            });
        }

        if (in_array($request->query('gender'), ['men', 'women', 'unisex'], true)) {
            $query->where('audience', $request->query('gender'));
        }

        if ($request->filled('category')) {
            $categorySlug = (string) $request->query('category');
            $query->whereHas('category', fn (Builder $c) => $c->where('slug', $categorySlug));
        }

        if ($request->filled('brand')) {
            $brandSlug = (string) $request->query('brand');
            $query->whereHas('brand', fn (Builder $b) => $b->where('slug', $brandSlug));
        }

        if ($request->filled('type')) {
            $query->where('concentration', (string) $request->query('type'));
        }

        $budgetKey = (string) $request->query('budget', '');
        if ($budgetKey !== '') {
            $range = collect(config('storefront.budget_ranges', []))->firstWhere('key', $budgetKey);
            if ($range) {
                $query->whereHas('variants', function (Builder $v) use ($range): void {
                    $v->where('is_active', true)->where('price', '>=', $range['min']);
                    if ($range['max'] !== null) {
                        $v->where('price', '<=', $range['max']);
                    }
                });
            }
        }

        match ($request->query('sort')) {
            'price_asc' => $query->withMin('variants', 'price')->orderBy('variants_min_price'),
            'price_desc' => $query->withMax('variants', 'price')->orderByDesc('variants_max_price'),
            default => $query->latest('published_at')->latest('id'),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::query()->where('is_active', true)->orderBy('sort_order')->get();
        $brands = Brand::query()->orderBy('name')->get();

        return view('storefront.shop', [
            'locale' => $locale,
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'activeFilters' => array_filter($request->only(['q', 'gender', 'category', 'brand', 'type', 'budget'])),
        ]);
    }

    public function newIn(string $locale): View
    {
        App::setLocale($locale);

        $products = $this->catalogTablesReady()
            ? Product::query()
                ->with(['brand', 'category', 'translations', 'variants', 'images'])
                ->where('status', 'published')
                ->where('is_sample', false)
                ->latest('published_at')
                ->latest('created_at')
                ->paginate(12)
            : new LengthAwarePaginator([], 0, 12);

        $recentBrands = $this->catalogTablesReady()
            ? Brand::query()->latest('created_at')->limit(6)->get()
            : collect();

        return view('storefront.new-in', [
            'locale' => $locale,
            'products' => $products,
            'recentBrands' => $recentBrands,
        ]);
    }

    public function brands(Request $request, string $locale): View
    {
        App::setLocale($locale);

        $brands = collect();
        if ($this->catalogTablesReady()) {
            $brands = Brand::query()
                ->withCount([
                    'products as published_products_count' => fn (Builder $q) => $q->where('status', 'published')->where('is_sample', false),
                ])
                ->when($request->filled('q'), fn (Builder $q) => $q->where('name', 'like', '%' . trim((string) $request->query('q')) . '%'))
                ->when($request->filled('letter'), fn (Builder $q) => $q->where('name', 'like', substr((string) $request->query('letter'), 0, 1) . '%'))
                ->orderBy('name')
                ->get();
        }

        $grouped = $brands->groupBy(fn (Brand $brand) => strtoupper(mb_substr($brand->name, 0, 1)));

        return view('storefront.brands', [
            'locale' => $locale,
            'brands' => $brands,
            'grouped' => $grouped,
            'search' => (string) $request->query('q', ''),
            'activeLetter' => strtoupper((string) $request->query('letter', '')),
        ]);
    }

    public function brandShow(string $locale, string $slug): View
    {
        App::setLocale($locale);
        abort_unless($this->catalogTablesReady(), 404);

        $brand = Brand::query()->where('slug', $slug)->firstOrFail();

        $products = $brand->products()
            ->with(['brand', 'category', 'translations', 'variants', 'images'])
            ->where('status', 'published')
            ->where('is_sample', false)
            ->latest('published_at')
            ->paginate(12);

        return view('storefront.brand-show', [
            'locale' => $locale,
            'brand' => $brand,
            'products' => $products,
        ]);
    }

    public function oudArabian(string $locale): View
    {
        App::setLocale($locale);

        $products = $this->catalogTablesReady()
            ? Product::query()
                ->with(['brand', 'category', 'translations', 'variants', 'images'])
                ->where('status', 'published')
                ->where('is_sample', false)
                ->where(function (Builder $q): void {
                    $q->whereHas('category', fn (Builder $c) => $c->whereIn('slug', ['oud', 'amber']))
                        ->orWhereIn('concentration', ['Attar', 'Oud Oil', 'Bakhoor', 'Luban']);
                })
                ->latest('published_at')
                ->paginate(12)
            : new LengthAwarePaginator([], 0, 12);

        return view('storefront.oud-arabian', [
            'locale' => $locale,
            'products' => $products,
        ]);
    }

    public function bestSellers(string $locale): View
    {
        App::setLocale($locale);

        return view('storefront.integration-page', [
            'locale' => $locale,
            'eyebrow' => __('store.primary_nav.best_sellers'),
            'title' => __('store.primary_nav.best_sellers'),
            'notice' => __('store.pages.best_sellers_notice'),
            'dependency' => 'Orders & Sales Analytics tables',
        ]);
    }

    public function gifts(Request $request, string $locale): View
    {
        App::setLocale($locale);
        $settings = StorefrontSettings::all();

        $products = $this->catalogTablesReady()
            ? Product::query()
                ->with(['brand', 'category', 'translations', 'variants', 'images'])
                ->where('status', 'published')
                ->where('is_sample', false)
                ->latest('published_at')
                ->limit(8)
                ->get()
            : collect();

        return view('storefront.gifts', [
            'locale' => $locale,
            'products' => $products,
            'seasonal' => $settings['seasonal_gifts'] ?? [],
            'occasion' => (string) $request->query('occasion', ''),
        ]);
    }

    public function offers(string $locale): View
    {
        App::setLocale($locale);

        return view('storefront.integration-page', [
            'locale' => $locale,
            'eyebrow' => __('store.primary_nav.offers'),
            'title' => __('store.primary_nav.offers'),
            'notice' => __('store.pages.offers_notice'),
            'dependency' => 'Promotions & Coupon Management backend',
        ]);
    }

    public function productShow(string $locale, string $slug): View
    {
        App::setLocale($locale);
        abort_unless($this->catalogTablesReady(), 404);

        $product = Product::query()
            ->with(['brand', 'category', 'translations', 'variants', 'images'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('is_sample', false)
            ->firstOrFail();

        return view('storefront.product', [
            'locale' => $locale,
            'product' => $product,
            'translation' => $product->translated($locale) ?? $product->translated('en'),
        ]);
    }

    public function suggestions(Request $request, string $locale): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2 || ! $this->catalogTablesReady()) {
            return response()->json(['products' => [], 'brands' => []]);
        }

        $products = Product::query()
            ->with(['brand', 'translations', 'variants'])
            ->where('status', 'published')
            ->where('is_sample', false)
            ->where(function (Builder $q) use ($query): void {
                $q->where('slug', 'like', "%{$query}%")
                    ->orWhereHas('brand', fn (Builder $b) => $b->where('name', 'like', "%{$query}%"))
                    ->orWhereHas('translations', function (Builder $t) use ($query): void {
                        $t->where('name', 'like', "%{$query}%")
                            ->orWhere('top_notes', 'like', "%{$query}%")
                            ->orWhere('middle_notes', 'like', "%{$query}%")
                            ->orWhere('base_notes', 'like', "%{$query}%");
                    })
                    ->orWhereHas('variants', fn (Builder $v) => $v->where('sku', 'like', "%{$query}%"));
            })
            ->limit(6)
            ->get()
            ->map(function (Product $product) use ($locale): array {
                $translation = $product->translated($locale) ?? $product->translated('en');
                $minPrice = $product->variants->where('is_active', true)->min('price');

                return [
                    'name' => $translation?->name ?? $product->slug,
                    'brand' => $product->brand?->name ?? '',
                    'price' => $minPrice !== null ? number_format((float) $minPrice, 3) : null,
                    'url' => route('store.products.show', ['locale' => $locale, 'slug' => $product->slug]),
                ];
            })
            ->values();

        $brands = Brand::query()
            ->where('name', 'like', "%{$query}%")
            ->whereHas('products', fn (Builder $q) => $q->where('status', 'published')->where('is_sample', false))
            ->limit(4)
            ->get()
            ->map(fn (Brand $brand): array => [
                'name' => $brand->name,
                'url' => route('store.brands.show', ['locale' => $locale, 'slug' => $brand->slug]),
            ])
            ->values();

        return response()->json([
            'products' => $products,
            'brands' => $brands,
        ]);
    }

    private function catalogTablesReady(): bool
    {
        try {
            return Schema::hasTable('products')
                && Schema::hasTable('categories')
                && Schema::hasTable('brands')
                && Schema::hasTable('product_variants');
        } catch (Throwable) {
            return false;
        }
    }
}
