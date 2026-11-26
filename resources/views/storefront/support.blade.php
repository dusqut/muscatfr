@extends('storefront.layout')

@section('title', __('store.navigation.support') . ' — Muscat Fragrances')

@section('content')
    <section class="sf-page-section section-wrap">
        <header class="sf-page-header">
            <p class="eyebrow">MUSCAT · OMAN</p>
            <h1>{{ __('store.navigation.support') }}</h1>
        </header>

        <div class="sf-info-grid">
            @php
                $waNumber = $support['whatsapp_number'] ?? '+96871826515';
                $waDigits = preg_replace('/\D+/', '', (string) $waNumber);
            @endphp
            <article class="sf-info-card">
                <h2>{{ __('store.pages.whatsapp_concierge') }}</h2>
                <p>
                    <a class="text-link" href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener noreferrer">
                        {{ $waNumber }}
                    </a>
                </p>
                <p>
                    <a href="mailto:{{ $support['email'] ?? 'care@muscatfragrances.com' }}">{{ $support['email'] ?? 'care@muscatfragrances.com' }}</a>
                </p>
            </article>
            <article class="sf-info-card">
                <h2>{{ __('store.navigation.currency_label', ['currency' => 'OMR']) }}</h2>
                <p>{{ __('store.announcement') }}</p>
                <a class="text-link" href="{{ route('store.stores', ['locale' => $locale]) }}">{{ __('store.navigation.find_store') }}</a>
            </article>
        </div>
    </section>
@endsection
