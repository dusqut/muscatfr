<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ProductController
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['brand', 'category', 'translations', 'variants'])
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $search = trim((string) $request->query('q'));

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('slug', 'like', "%{$search}%")
                        ->orWhereHas('translations', fn (Builder $translations) => $translations->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('variants', fn (Builder $variants) => $variants->where('sku', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($request->query('status'), ['draft', 'published'], true), fn (Builder $query) => $query->where('status', $request->query('status')))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return $this->formView(new Product());
    }

    public function store(Request $request): RedirectResponse
    {
        $product = $this->persist($request);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', __('admin.product_created'));
    }

    public function edit(Product $product): View
    {
        $product->load(['translations', 'variants']);

        return $this->formView($product);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product = $this->persist($request, $product);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', __('admin.product_updated'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', __('admin.product_deleted'));
    }

    private function formView(Product $product): View
    {
        if ($product->exists) {
            $product->load(['translations', 'variants', 'images']);
        }

        return view('admin.products.form', [
            'product' => $product,
            'brands' => Brand::query()->orderBy('name')->get(),
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'translations' => $product->exists ? $product->translations->keyBy('locale') : collect(),
            'variants' => $product->exists ? $product->variants : collect([null]),
        ]);
    }

    private function persist(Request $request, ?Product $product = null): Product
    {
        $inputVariants = $request->input('variants', []);
        if (! is_array($inputVariants)) {
            $inputVariants = [];
        }

        foreach ($inputVariants as $index => $variant) {
            if (is_array($variant) && isset($variant['sku'])) {
                $inputVariants[$index]['sku'] = strtoupper(trim((string) $variant['sku']));
            }
        }
        $request->merge(['variants' => $inputVariants]);

        $slugRule = Rule::unique('products', 'slug');
        if ($product) {
            $slugRule->ignore($product->id);
        }

        $rules = [
            'slug' => ['required', 'string', 'max:160', 'alpha_dash', $slugRule],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'concentration' => ['nullable', 'string', 'max:80'],
            'audience' => ['required', Rule::in(['unisex', 'women', 'men'])],
            'country_of_origin' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_sample' => ['nullable', 'boolean'],
            'translations' => ['required', 'array'],
            'translations.en.name' => ['required', 'string', 'max:180'],
            'translations.en.description' => ['required', 'string', 'max:5000'],
            'translations.en.top_notes' => ['nullable', 'string', 'max:1000'],
            'translations.en.middle_notes' => ['nullable', 'string', 'max:1000'],
            'translations.en.base_notes' => ['nullable', 'string', 'max:1000'],
            'translations.ar.name' => ['required', 'string', 'max:180'],
            'translations.ar.description' => ['required', 'string', 'max:5000'],
            'translations.ar.top_notes' => ['nullable', 'string', 'max:1000'],
            'translations.ar.middle_notes' => ['nullable', 'string', 'max:1000'],
            'translations.ar.base_notes' => ['nullable', 'string', 'max:1000'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpeg,png,webp', 'max:5120'],
            'image_alt_en' => ['nullable', 'string', 'max:180'],
            'image_alt_ar' => ['nullable', 'string', 'max:180'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', 'distinct', 'exists:product_images,id'],
            'variants' => ['required', 'array', 'min:1', 'max:10'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['required', 'string', 'max:64', 'distinct:ignore_case'],
            'variants.*.volume_ml' => ['required', 'integer', 'min:1', 'max:1000', 'distinct'],
            'variants.*.price' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:9999999.999'],
            'variants.*.stock_on_hand' => ['required', 'integer', 'min:0', 'max:100000000'],
        ];

        foreach ($inputVariants as $index => $variant) {
            $skuRule = Rule::unique('product_variants', 'sku');
            if (! empty($variant['id'])) {
                $skuRule->ignore((int) $variant['id']);
            }
            $rules["variants.{$index}.sku"][] = $skuRule;
        }

        $validated = $request->validate($rules);
        $isSample = (bool) ($validated['is_sample'] ?? false);

        if ($isSample && $validated['status'] === 'published') {
            throw ValidationException::withMessages([
                'status' => __('admin.sample_publish_blocked'),
            ]);
        }

        $removeImageIds = array_map('intval', $validated['remove_images'] ?? []);
        if ($removeImageIds !== [] && (! $product || $product->images()->whereIn('id', $removeImageIds)->count() !== count($removeImageIds))) {
            throw ValidationException::withMessages([
                'remove_images' => __('admin.invalid_image'),
            ]);
        }

        $existingVariantIds = $product?->variants()->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];
        foreach ($validated['variants'] as $index => $variant) {
            if (isset($variant['id']) && ! in_array((int) $variant['id'], $existingVariantIds, true)) {
                throw ValidationException::withMessages([
                    "variants.{$index}.id" => __('admin.invalid_variant'),
                ]);
            }
        }

        $storedPaths = [];
        $deletedPaths = [];

        try {
            $product = DB::transaction(function () use ($validated, $product, $isSample, $removeImageIds, &$storedPaths, &$deletedPaths): Product {
                $product ??= new Product();
                $product->fill([
                    'brand_id' => $validated['brand_id'],
                    'category_id' => $validated['category_id'],
                    'slug' => $validated['slug'],
                    'concentration' => $validated['concentration'] ?? null,
                    'audience' => $validated['audience'],
                    'country_of_origin' => $validated['country_of_origin'] ?? null,
                    'status' => $validated['status'],
                    'is_sample' => $isSample,
                    'published_at' => $validated['status'] === 'published'
                        ? ($product->published_at ?? now())
                        : null,
                ])->save();

                foreach (['en', 'ar'] as $locale) {
                    $product->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'name' => $validated['translations'][$locale]['name'],
                            'description' => $validated['translations'][$locale]['description'],
                            'top_notes' => $validated['translations'][$locale]['top_notes'] ?? null,
                            'middle_notes' => $validated['translations'][$locale]['middle_notes'] ?? null,
                            'base_notes' => $validated['translations'][$locale]['base_notes'] ?? null,
                        ]
                    );
                }

                $savedVariantIds = [];
                foreach ($validated['variants'] as $variantData) {
                    $variant = isset($variantData['id'])
                        ? $product->variants()->findOrFail($variantData['id'])
                        : $product->variants()->make();

                    $variant->fill([
                        'sku' => $variantData['sku'],
                        'volume_ml' => $variantData['volume_ml'],
                        'price' => $variantData['price'],
                        'stock_on_hand' => $variantData['stock_on_hand'],
                        'is_active' => true,
                    ])->save();

                    $savedVariantIds[] = $variant->id;
                }

                $product->variants()->whereNotIn('id', $savedVariantIds)->delete();

                if ($removeImageIds !== []) {
                    $deletedPaths = $product->images()->whereIn('id', $removeImageIds)->pluck('path')->all();
                    $product->images()->whereIn('id', $removeImageIds)->delete();
                }

                $nextSortOrder = (int) ($product->images()->max('sort_order') ?? -1) + 1;
                foreach ($validated['images'] ?? [] as $image) {
                    $path = $image->storePublicly("products/{$product->id}", 'public');
                    if (! is_string($path) || $path === '') {
                        throw new RuntimeException('Unable to store the uploaded product image.');
                    }

                    $storedPaths[] = $path;
                    $product->images()->create([
                        'path' => $path,
                        'alt_en' => $validated['image_alt_en'] ?? $validated['translations']['en']['name'],
                        'alt_ar' => $validated['image_alt_ar'] ?? $validated['translations']['ar']['name'],
                        'sort_order' => $nextSortOrder++,
                    ]);
                }

                return $product;
            });
        } catch (Throwable $exception) {
            if ($storedPaths !== []) {
                Storage::disk('public')->delete($storedPaths);
            }

            throw $exception;
        }

        if ($deletedPaths !== [] && ! Storage::disk('public')->delete($deletedPaths)) {
            throw new RuntimeException('Unable to remove one or more product image files.');
        }

        return $product;
    }
}
