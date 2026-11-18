<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ __('store.meta.description') }}">
    <meta name="theme-color" content="#f5f1e9">
    <link rel="canonical" href="{{ rtrim(config('app.url'), '/') . route('store.home', ['locale' => $locale], false) }}">
    <link rel="alternate" hreflang="en" href="{{ rtrim(config('app.url'), '/') . route('store.home', ['locale' => 'en'], false) }}">
    <link rel="alternate" hreflang="ar" href="{{ rtrim(config('app.url'), '/') . route('store.home', ['locale' => 'ar'], false) }}">
    <link rel="alternate" hreflang="x-default" href="{{ rtrim(config('app.url'), '/') . route('store.home', ['locale' => 'en'], false) }}">
    <title>{{ __('store.meta.title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront">
    <a class="skip-link" href="#main-content">{{ __('store.navigation.skip_to_content') }}</a>
    <div class="announcement">{{ __('store.announcement') }}</div>

    <header class="site-header" x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false">
        <a class="wordmark" href="{{ route('store.home', ['locale' => $locale]) }}" aria-label="{{ __('store.navigation.home') }}">
            <span class="wordmark-name">MUSCAT</span>
            <span class="wordmark-subtitle">FRAGRANCES</span>
        </a>

        <button class="menu-toggle" type="button" @click="menuOpen = ! menuOpen"
                :aria-label="menuOpen ? @js(__('store.navigation.close_menu')) : @js(__('store.navigation.menu'))"
                :aria-expanded="menuOpen.toString()">
            <span></span><span></span>
        </button>

        <nav class="site-nav" :class="{ 'is-open': menuOpen }" aria-label="{{ __('store.navigation.aria_label') }}">
            <a href="#discover" @click="menuOpen = false">{{ __('store.navigation.discover') }}</a>
            <a href="#story" @click="menuOpen = false">{{ __('store.navigation.our_story') }}</a>
            <a class="language-link" href="{{ route('store.home', ['locale' => $locale === 'ar' ? 'en' : 'ar']) }}"
               lang="{{ $locale === 'ar' ? 'en' : 'ar' }}">
                {{ __('store.navigation.language') }}
            </a>
        </nav>
    </header>

    <main id="main-content">
        <section class="hero" aria-labelledby="hero-title">
            <div class="hero-copy">
                <p class="eyebrow">{{ __('store.hero.eyebrow') }}</p>
                <h1 id="hero-title">{!! __('store.hero.title') !!}</h1>
                <p class="hero-description">{{ __('store.hero.description') }}</p>
                <div class="hero-actions">
                    <a class="button button-dark" href="#discover">
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
                    <article class="note-card note-oud">
                        <span class="note-index">01</span>
                        <div class="note-visual" aria-hidden="true"><span></span></div>
                        <h3>{{ __('store.notes.oud') }}</h3>
                        <p>{{ __('store.notes.oud_caption') }}</p>
                    </article>
                    <article class="note-card note-floral">
                        <span class="note-index">02</span>
                        <div class="note-visual" aria-hidden="true"><span></span></div>
                        <h3>{{ __('store.notes.floral') }}</h3>
                        <p>{{ __('store.notes.floral_caption') }}</p>
                    </article>
                    <article class="note-card note-amber">
                        <span class="note-index">03</span>
                        <div class="note-visual" aria-hidden="true"><span></span></div>
                        <h3>{{ __('store.notes.amber') }}</h3>
                        <p>{{ __('store.notes.amber_caption') }}</p>
                    </article>
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
    </main>

    <footer class="site-footer">
        <a class="wordmark footer-wordmark" href="{{ route('store.home', ['locale' => $locale]) }}">
            <span class="wordmark-name">MUSCAT</span>
            <span class="wordmark-subtitle">FRAGRANCES</span>
        </a>
        <p>{{ __('store.footer.tagline') }}</p>
        <div class="footer-meta">
            <span>{{ __('store.footer.location') }}</span>
            <span>{{ __('store.footer.copyright', ['year' => now()->year]) }}</span>
        </div>
    </footer>
</body>
</html>
