@extends('storefront.layout')

@section('title', __('store.primary_nav.brands') . ' — Muscat Fragrances')

@section('content')
    <section class="sf-page-section section-wrap">
        <header class="sf-page-header">
            <p class="eyebrow">{{ __('store.mega_brands.all_az') }}</p>
            <h1>{{ __('store.primary_nav.brands') }}</h1>
        </header>

        <form class="sf-filter-bar" method="GET" action="{{ route('store.brands', ['locale' => $locale]) }}">
            <label class="sr-only" for="brands-page-search">{{ __('store.mega_brands.search_placeholder') }}</label>
            <input id="brands-page-search" name="q" type="search" value="{{ $search }}" placeholder="{{ __('store.mega_brands.search_placeholder') }}">
            <button class="button button-dark" type="submit">{{ __('store.navigation.search_submit') }}</button>
            @if ($search !== '' || $activeLetter !== '')
                <a class="text-link" href="{{ route('store.brands', ['locale' => $locale]) }}">{{ __('store.pages.clear_filters') }}</a>
            @endif
        </form>

        @if ($grouped->isEmpty())
            <div class="sf-empty-state">
                <p>{{ __('store.mega_brands.empty') }}</p>
            </div>
        @else
            <div class="sf-brand-directory">
                @foreach ($grouped as $letter => $letterBrands)
                    <section class="sf-brand-letter-group">
                        <h2>{{ $letter }}</h2>
                        <div class="sf-brands-grid">
                            @foreach ($letterBrands as $brand)
                                <a class="sf-brand-card" href="{{ route('store.brands.show', ['locale' => $locale, 'slug' => $brand->slug]) }}">
                                    <span class="sf-brand-name">{{ $brand->name }}</span>
                                    <span class="sf-brand-count">{{ __('store.mega_brands.product_count', ['count' => $brand->published_products_count]) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </section>
@endsection
