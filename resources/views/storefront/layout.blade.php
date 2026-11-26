<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', __('store.meta.description'))">
    <meta name="theme-color" content="#f5f1e9">
    @php
        $currentPath = '/' . ltrim(request()->path(), '/');
        $enPath = preg_replace('#^/(en|ar)(?=/|$)#', '/en', $currentPath) ?: '/en';
        $arPath = preg_replace('#^/(en|ar)(?=/|$)#', '/ar', $currentPath) ?: '/ar';
        $baseUrl = rtrim(config('app.url'), '/');
    @endphp
    <link rel="canonical" href="{{ $baseUrl . ($locale === 'ar' ? $arPath : $enPath) }}">
    <link rel="alternate" hreflang="en" href="{{ $baseUrl . $enPath }}">
    <link rel="alternate" hreflang="ar" href="{{ $baseUrl . $arPath }}">
    <link rel="alternate" hreflang="x-default" href="{{ $baseUrl . $enPath }}">
    <title>@yield('title', __('store.meta.title'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront">
    <a class="skip-link" href="#main-content">{{ __('store.navigation.skip_to_content') }}</a>

    @include('storefront.partials.navbar')

    <main id="main-content">
        @yield('content')
    </main>

    @include('storefront.partials.footer')
</body>
</html>
