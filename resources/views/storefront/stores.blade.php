@extends('storefront.layout')

@section('title', __('store.navigation.find_store') . ' — Muscat Fragrances')

@section('content')
    <section class="sf-page-section section-wrap">
        <header class="sf-page-header">
            <p class="eyebrow">{{ __('store.hero.eyebrow') }}</p>
            <h1>{{ __('store.navigation.find_store') }}</h1>
        </header>

        <div class="sf-info-grid">
            <article class="sf-info-card">
                <h2>{{ $locale === 'ar' ? ($support['store_name_ar'] ?? '') : ($support['store_name_en'] ?? '') }}</h2>
                <p>{{ $locale === 'ar' ? ($support['store_address_ar'] ?? '') : ($support['store_address_en'] ?? '') }}</p>
                <p>{{ $locale === 'ar' ? ($support['store_hours_ar'] ?? '') : ($support['store_hours_en'] ?? '') }}</p>
            </article>
            <article class="sf-info-card">
                <h2>{{ __('store.navigation.support') }}</h2>
                <p>{{ $support['email'] ?? 'care@muscatfragrances.com' }}</p>
                <p>{{ __('store.announcement') }}</p>
            </article>
        </div>
    </section>
@endsection
