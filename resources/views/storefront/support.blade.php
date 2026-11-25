@extends('storefront.layout')

@section('title', __('store.navigation.support') . ' — Muscat Fragrances')

@section('content')
    <section class="sf-page-section section-wrap">
        <header class="sf-page-header">
            <p class="eyebrow">MUSCAT · OMAN</p>
            <h1>{{ __('store.navigation.support') }}</h1>
        </header>

        <div class="sf-info-grid">
            <article class="sf-info-card">
                <h2>{{ __('store.pages.whatsapp_concierge') }}</h2>
                <p>{{ $support['whatsapp_number'] ?? '+96800000000' }}</p>
                <p>{{ $support['email'] ?? 'care@muscatfragrances.com' }}</p>
            </article>
            <article class="sf-info-card">
                <h2>{{ __('store.navigation.currency_label', ['currency' => 'OMR']) }}</h2>
                <p>{{ __('store.announcement') }}</p>
                <a class="text-link" href="{{ route('store.stores', ['locale' => $locale]) }}">{{ __('store.navigation.find_store') }}</a>
            </article>
        </div>
    </section>
@endsection
