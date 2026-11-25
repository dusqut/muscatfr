@extends('storefront.layout')

@section('title', __('store.primary_nav.gifts') . ' — Muscat Fragrances')

@section('content')
    <section class="sf-page-section section-wrap">
        <header class="sf-page-header">
            <p class="eyebrow">{{ __('store.mega_gifts.title') }}</p>
            <h1>{{ __('store.primary_nav.gifts') }}</h1>
        </header>

        <div class="sf-gift-links">
            @foreach ($storeNav['gifts'] as $giftLink)
                <a class="sf-brand-card" href="{{ $giftLink['url'] }}">
                    <span class="sf-brand-name">{{ $giftLink['label'] }}</span>
                </a>
            @endforeach
        </div>

        @include('storefront.partials._product-grid', ['products' => $products])
    </section>
@endsection
