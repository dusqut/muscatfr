@extends('storefront.layout')

@section('title', __('store.primary_nav.oud_arabian') . ' — Muscat Fragrances')

@section('content')
    <section class="sf-page-section section-wrap">
        <header class="sf-page-header">
            <p class="eyebrow">{{ __('store.mega_oud.eyebrow') }}</p>
            <h1>{{ __('store.primary_nav.oud_arabian') }}</h1>
            <p class="sf-page-lead">{{ __('store.mega_oud.description') }}</p>
        </header>

        @include('storefront.partials._product-grid', ['products' => $products])
    </section>
@endsection
