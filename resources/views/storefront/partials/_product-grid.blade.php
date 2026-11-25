@if ($products->isEmpty())
    <div class="sf-empty-state">
        <h2>{{ __('store.pages.no_products_title') }}</h2>
        <p>{{ __('store.pages.no_products_body') }}</p>
        <div class="sf-empty-actions">
            @if (! empty($clearUrl))
                <a class="button button-dark" href="{{ $clearUrl }}">{{ __('store.pages.clear_filters') }}</a>
            @endif
            <a class="text-link" href="{{ route('store.support', ['locale' => $locale]) }}">{{ __('store.navigation.support') }}</a>
        </div>
    </div>
@else
    <div class="sf-product-grid">
        @foreach ($products as $product)
            @php
                $translation = $product->translated($locale) ?? $product->translated('en');
                $firstImage = $product->images->first();
                $activeVariants = $product->variants->where('is_active', true);
                $minPrice = $activeVariants->min('price');
                $inStock = $activeVariants->sum('stock_on_hand') > 0;
            @endphp
            <article class="sf-product-card">
                <a class="sf-product-media" href="{{ route('store.products.show', ['locale' => $locale, 'slug' => $product->slug]) }}">
                    @if ($firstImage)
                        <img src="{{ asset('storage/' . $firstImage->path) }}"
                             alt="{{ $locale === 'ar' ? ($firstImage->alt_ar ?? $translation?->name) : ($firstImage->alt_en ?? $translation?->name) }}"
                             loading="lazy"
                             width="420"
                             height="480">
                    @else
                        <div class="sf-product-placeholder" aria-hidden="true"><span>M</span></div>
                    @endif
                </a>
                <div class="sf-product-body">
                    <p class="sf-product-brand">{{ $product->brand?->name }}</p>
                    <h3>
                        <a href="{{ route('store.products.show', ['locale' => $locale, 'slug' => $product->slug]) }}">
                            {{ $translation?->name ?? $product->slug }}
                        </a>
                    </h3>
                    <div class="sf-product-meta">
                        @if ($minPrice !== null)
                            <span>{{ __('store.pages.from_price', ['price' => number_format((float) $minPrice, 3)]) }}</span>
                        @endif
                        <span class="sf-stock-pill {{ $inStock ? 'is-in' : 'is-out' }}">
                            {{ $inStock ? __('store.pages.in_stock') : __('store.pages.out_of_stock') }}
                        </span>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    @if (method_exists($products, 'links'))
        <div class="sf-pagination">
            {{ $products->links() }}
        </div>
    @endif
@endif
