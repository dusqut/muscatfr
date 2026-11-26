<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="googlebot" content="noindex, nofollow, noarchive">
    <meta name="theme-color" content="#f5f1e9">
    <title>{{ __('admin.login_title') }} — Muscat Fragrances</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-page admin-login-page">
    <main class="admin-login-card">
        <a class="admin-brand" href="/">
            <span>MUSCAT FRAGRANCES</span>
        </a>
        <h1>{{ __('admin.login_title') }}</h1>
        <p>{{ __('admin.login_message') }}</p>

        @if ($errors->any())
            <div class="admin-errors" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form class="admin-form" method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <label for="email">{{ __('admin.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   autocomplete="username" required autofocus>

            <label for="password">{{ __('admin.password') }}</label>
            <input id="password" name="password" type="password"
                   autocomplete="current-password" required>

            <button class="admin-primary-button" type="submit">{{ __('admin.login') }}</button>
        </form>
        <a class="admin-back-link" href="{{ config('app.url') }}">{{ __('admin.back_to_site') }}</a>
    </main>
</body>
</html>
