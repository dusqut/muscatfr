<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="theme-color" content="#f5f1e9">
    <title>@yield('title') — Muscat Fragrances Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-page">
    <header class="admin-header">
        <a class="admin-brand" href="{{ route('admin.products.index') }}">
            <span>MUSCAT FRAGRANCES</span>
            <small>{{ __('admin.admin_label') }}</small>
        </a>
        <div class="admin-user">
            <span>{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="admin-link-button" type="submit">{{ __('admin.logout') }}</button>
            </form>
        </div>
    </header>

    <main class="admin-shell">
        @if (session('status'))
            <p class="admin-notice" role="status">{{ session('status') }}</p>
        @endif
        @yield('content')
    </main>
</body>
</html>
