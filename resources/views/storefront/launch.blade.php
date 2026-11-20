<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f1e9">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>{{ __('launch.title') }} — Muscat Fragrances</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="launch-page">
    <main class="launch-content">
        <a class="launch-wordmark" href="/" aria-label="Muscat Fragrances">
            <span class="launch-wordmark-name">MUSCAT</span>
            <span class="launch-wordmark-subtitle">FRAGRANCES</span>
        </a>

        <div class="launch-art" aria-hidden="true">
            <span class="launch-orbit launch-orbit-one"></span>
            <span class="launch-orbit launch-orbit-two"></span>
            <span class="launch-sun"></span>
            <span class="launch-bottle"><span>M</span></span>
        </div>

        <p class="launch-eyebrow">{{ __('launch.location') }}</p>
        <h1>{{ __('launch.title') }}</h1>
        <p class="launch-message">{{ __('launch.message') }}</p>
        <p class="launch-thanks">{{ __('launch.thanks') }}</p>

        <a class="launch-developer" href="{{ route('launch.access', ['locale' => $locale]) }}">
            {{ __('launch.developer_access') }}
        </a>
    </main>

    <footer class="launch-footer">
        {{ __('launch.copyright', ['year' => now()->year]) }}
    </footer>
</body>
</html>
