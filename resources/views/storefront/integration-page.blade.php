@extends('storefront.layout')

@section('title', $title . ' — Muscat Fragrances')

@section('content')
    <section class="sf-page-section section-wrap">
        <header class="sf-page-header">
            <p class="eyebrow">{{ $eyebrow }}</p>
            <h1>{{ $title }}</h1>
        </header>

        <div class="sf-empty-state">
            <p class="sf-integration-dep">{{ $dependency }}</p>
            <p>{{ $notice }}</p>
            <div class="sf-empty-actions">
                <a class="button button-dark" href="{{ route('store.shop', ['locale' => $locale]) }}">{{ __('store.pages.back_to_shop') }}</a>
                <a class="text-link" href="{{ route('store.support', ['locale' => $locale]) }}">{{ __('store.navigation.support') }}</a>
            </div>
        </div>
    </section>
@endsection
