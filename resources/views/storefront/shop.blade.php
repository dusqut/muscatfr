@extends('storefront.layout')

@section('title', __('store.primary_nav.shop') . ' — Muscat Fragrances')

@section('content')
    <section class="sf-page-section section-wrap">
        <header class="sf-page-header">
            <p class="eyebrow">MUSCAT FRAGRANCES</p>
            <h1>
                @if (! empty($activeFilters['q']))
                    {{ __('store.pages.search_results_for', ['query' => $activeFilters['q']]) }}
                @else
                    {{ __('store.primary_nav.shop') }}
                @endif
            </h1>
        </header>

        <form class="sf-filter-bar" method="GET" action="{{ route('store.shop', ['locale' => $locale]) }}">
            @if (request()->filled('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif

            <select name="gender" aria-label="{{ __('store.mega_shop.gender_title') }}">
                <option value="">{{ __('store.mega_shop.gender_title') }}</option>
                @foreach (['men', 'women', 'unisex'] as $g)
                    <option value="{{ $g }}" @selected(request('gender') === $g)>{{ __('store.mega_shop.gender_' . $g) }}</option>
                @endforeach
            </select>

            @if ($categories->isNotEmpty())
                <select name="category" aria-label="{{ __('store.mega_shop.family_title') }}">
                    <option value="">{{ __('store.mega_shop.family_title') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->displayName($locale) }}</option>
                    @endforeach
                </select>
            @endif

            <select name="budget" aria-label="{{ __('store.mega_shop.budget_title') }}">
                <option value="">{{ __('store.mega_shop.budget_title') }}</option>
                @foreach (config('storefront.budget_ranges', []) as $range)
                    <option value="{{ $range['key'] }}" @selected(request('budget') === $range['key'])>{{ __('store.mega_shop.budget_' . $range['key']) }}</option>
                @endforeach
            </select>

            <button class="button button-dark" type="submit">{{ __('store.navigation.search_submit') }}</button>

            @if ($activeFilters !== [])
                <a class="text-link" href="{{ route('store.shop', ['locale' => $locale]) }}">{{ __('store.pages.clear_filters') }}</a>
            @endif
        </form>

        @include('storefront.partials._product-grid', [
            'products' => $products,
            'clearUrl' => $activeFilters !== [] ? route('store.shop', ['locale' => $locale]) : null,
        ])
    </section>
@endsection
