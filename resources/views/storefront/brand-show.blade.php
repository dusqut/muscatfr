@extends('storefront.layout')

@section('title', $brand->name . ' — Muscat Fragrances')

@section('content')
    <section class="sf-page-section section-wrap">
        <header class="sf-page-header">
            <p class="eyebrow">{{ __('store.primary_nav.brands') }}</p>
            <h1>{{ $brand->name }}</h1>
        </header>

        @include('storefront.partials._product-grid', [
            'products' => $products,
            'clearUrl' => route('store.brands', ['locale' => $locale]),
        ])
    </section>
@endsection
