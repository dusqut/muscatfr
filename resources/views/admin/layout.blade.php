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
<body class="admin-page" x-data="{ sidebarOpen: false }">
    <header class="admin-header">
        <div class="admin-header-left">
            <button type="button"
                    class="admin-sidebar-toggle"
                    @click="sidebarOpen = !sidebarOpen"
                    :aria-expanded="sidebarOpen.toString()"
                    aria-controls="admin-sidebar"
                    aria-label="Toggle navigation menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                <span>MUSCAT FRAGRANCES</span>
                <small>{{ __('admin.admin_label') }}</small>
            </a>
        </div>
        <div class="admin-user">
            <a class="admin-header-link" href="{{ url('/') }}" target="_blank" rel="noopener">View Storefront</a>
            <span class="admin-user-identity">
                <strong>{{ auth()->user()->name }}</strong>
                <span class="admin-role-pill">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
            </span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="admin-link-button" type="submit">{{ __('admin.logout') }}</button>
            </form>
        </div>
    </header>

    <div class="admin-workspace">
        <div class="admin-sidebar-backdrop" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

        <aside id="admin-sidebar"
               class="admin-sidebar"
               :class="{ 'is-open': sidebarOpen }"
               aria-label="Admin navigation"
               x-data="{
                   query: '',
                   openGroups: {
                       @foreach ($adminNavigation as $section)
                           '{{ $section['key'] }}': {{ $section['expanded'] ? 'true' : 'false' }},
                       @endforeach
                   },
                   matches(text) {
                       const q = this.query.trim().toLowerCase();
                       return q === '' || text.toLowerCase().includes(q);
                   }
               }">
            <div class="admin-sidebar-search">
                <label class="sr-only" for="admin-nav-filter">Filter navigation</label>
                <input id="admin-nav-filter"
                       type="search"
                       x-model="query"
                       placeholder="Filter menu...">
            </div>

            <nav class="admin-nav">
                @foreach ($adminNavigation as $section)
                    @php
                        $sectionText = strtolower($section['title'] . ' ' . collect($section['items'])->pluck('label')->implode(' '));
                    @endphp
                    <div class="admin-nav-group"
                         x-show="matches(@js($sectionText))"
                         data-nav-section="{{ $section['key'] }}">
                        <button type="button"
                                class="admin-nav-group-toggle"
                                @click="openGroups['{{ $section['key'] }}'] = !openGroups['{{ $section['key'] }}']"
                                :aria-expanded="(query.trim() !== '' || openGroups['{{ $section['key'] }}']).toString()">
                            <span>{{ $section['title'] }}</span>
                            <span class="admin-nav-chevron"
                                  :class="{ 'is-open': query.trim() !== '' || openGroups['{{ $section['key'] }}'] }"
                                  aria-hidden="true">›</span>
                        </button>

                        <ul class="admin-nav-list"
                            x-show="query.trim() !== '' || openGroups['{{ $section['key'] }}']">
                            @foreach ($section['items'] as $item)
                                <li x-show="matches(@js($item['label'] . ' ' . $section['title']))">
                                    <a href="{{ $item['url'] }}"
                                       class="admin-nav-link {{ $item['active'] ? 'is-active' : '' }}"
                                       @if ($item['active']) aria-current="page" @endif>
                                        <span class="admin-nav-label">{{ $item['label'] }}</span>
                                        @if (! empty($item['badge']))
                                            <span class="admin-nav-badge {{ ($item['status'] ?? '') === 'phase_2' ? 'is-phase' : (($item['status'] ?? '') === 'later' ? 'is-later' : 'is-count') }}">
                                                {{ $item['badge'] }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-shell">
                @if (session('status'))
                    <p class="admin-notice" role="status">{{ session('status') }}</p>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
