@extends('storefront.layout')

@section('title', __('store.primary_nav.new_in') . ' — Muscat Fragrances')

@section('content')
    <section class="sf-page-section section-wrap">
        <header class="sf-page-header">
            <p class="eyebrow">{{ __('store.hero.eyebrow') }}</p>
            <h1>{{ __('store.primary_nav.new_in') }}</h1>
        </header>

        @include('storefront.partials._product-grid', ['products' => $products])
    </section>
@endsection
