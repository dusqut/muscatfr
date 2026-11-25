@extends('storefront.layout')

@section('content')
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="eyebrow">{{ __('store.hero.eyebrow') }}</p>
            <h1 id="hero-title">{!! __('store.hero.title') !!}</h1>
            <p class="hero-description">{{ __('store.hero.description') }}</p>
            <div class="hero-actions">
                <a class="button button-dark" href="{{ route('store.shop', ['locale' => $locale]) }}">
                    {{ __('store.hero.primary_cta') }} <span aria-hidden="true">↗</span>
                </a>
                <a class="text-link" href="#story">{{ __('store.hero.secondary_cta') }}</a>
            </div>
            <div class="hero-index"><span>01</span><span class="index-line"></span><span>03</span></div>
        </div>

        <div class="hero-art" role="img" aria-label="{{ __('store.hero.art_label') }}">
            <div class="art-orbit orbit-one"></div>
            <div class="art-orbit orbit-two"></div>
            <div class="art-sun"></div>
            <div class="art-bottle">
                <div class="bottle-cap"></div>
                <div class="bottle-neck"></div>
                <div class="bottle-body"><span>M</span></div>
            </div>
            <span class="art-vertical">MUSCAT · OMAN</span>
            <p class="art-caption">{{ __('store.hero.art_caption') }}</p>
            <span class="art-number">01</span>
        </div>
    </section>

    <section class="intro section-wrap" id="discover">
        <div class="intro-mark" aria-hidden="true">M<span>.</span></div>
        <div class="intro-copy">
            <p class="eyebrow">{{ __('store.intro.eyebrow') }}</p>
            <h2>{!! __('store.intro.title') !!}</h2>
            <p>{{ __('store.intro.body') }}</p>
        </div>
    </section>

    <section class="notes-section">
        <div class="section-wrap">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">{{ __('store.notes.eyebrow') }}</p>
                    <h2>{{ __('store.notes.title') }}</h2>
                </div>
                <span class="section-count">01 — 03</span>
            </div>
            <div class="notes-grid">
                <a class="note-card note-oud" href="{{ route('store.shop', ['locale' => $locale, 'category' => 'oud']) }}">
                    <span class="note-index">01</span>
                    <div class="note-visual" aria-hidden="true"><span></span></div>
                    <h3>{{ __('store.notes.oud') }}</h3>
                    <p>{{ __('store.notes.oud_caption') }}</p>
                </a>
                <a class="note-card note-floral" href="{{ route('store.shop', ['locale' => $locale, 'category' => 'floral']) }}">
                    <span class="note-index">02</span>
                    <div class="note-visual" aria-hidden="true"><span></span></div>
                    <h3>{{ __('store.notes.floral') }}</h3>
                    <p>{{ __('store.notes.floral_caption') }}</p>
                </a>
                <a class="note-card note-amber" href="{{ route('store.shop', ['locale' => $locale, 'category' => 'amber']) }}">
                    <span class="note-index">03</span>
                    <div class="note-visual" aria-hidden="true"><span></span></div>
                    <h3>{{ __('store.notes.amber') }}</h3>
                    <p>{{ __('store.notes.amber_caption') }}</p>
                </a>
            </div>
        </div>
    </section>

    <section class="story-section section-wrap" id="story">
        <div class="story-art" aria-hidden="true">
            <div class="story-sun"></div>
            <div class="story-arch"><span></span></div>
            <span class="story-location">MUSCAT · OMAN</span>
        </div>
        <div class="story-copy">
            <p class="eyebrow">{{ __('store.story.eyebrow') }}</p>
            <h2>{{ __('store.story.title') }}</h2>
            <p>{{ __('store.story.body') }}</p>
            <span class="story-signature">{{ __('store.story.signature') }}</span>
        </div>
    </section>
@endsection
