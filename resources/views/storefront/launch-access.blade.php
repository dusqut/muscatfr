<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f1e9">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>{{ __('launch.access_title') }} — Muscat Fragrances</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="launch-page">
    <main class="launch-content launch-access-content">
        <a class="launch-wordmark" href="/" aria-label="Muscat Fragrances">
            <span class="launch-wordmark-name">MUSCAT</span>
            <span class="launch-wordmark-subtitle">FRAGRANCES</span>
        </a>

        <p class="launch-eyebrow">{{ __('launch.location') }}</p>
        <h1>{{ __('launch.access_title') }}</h1>
        <p class="launch-message">{{ __('launch.access_message') }}</p>

        <form class="launch-form" method="POST" action="{{ route('launch.authenticate', ['locale' => $locale]) }}">
            @csrf
            <label for="password">{{ __('launch.password_label') }}</label>
            <input id="password" name="password" type="password" autocomplete="current-password"
                   required aria-describedby="password-error">
            @error('password')
                <p class="launch-error" id="password-error" role="alert">{{ $message }}</p>
            @enderror
            <button class="launch-submit" type="submit">{{ __('launch.submit') }}</button>
        </form>

        <a class="launch-developer launch-back" href="{{ url('/') }}">{{ __('launch.back') }}</a>
    </main>

    <footer class="launch-footer">
        {{ __('launch.copyright', ['year' => now()->year]) }}
    </footer>
</body>
</html>
