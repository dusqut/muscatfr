<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="theme-color" content="#f5f1e9">
    <title>@yield('title') — Muscat Fragrances</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $authUser = auth()->user();
    $roleLabel = strtoupper(str_replace('_', ' ', $authUser->role));
    $nameParts = preg_split('/\s+/', trim((string) $authUser->name)) ?: ['A'];
    $initials = strtoupper(mb_substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? mb_substr($nameParts[1], 0, 1) : ''));
    $prefs = session('admin.preferences', ['sidebar_default' => 'expanded', 'open_new_tab_storefront' => true]);
    $defaultCollapsed = ($prefs['sidebar_default'] ?? 'expanded') === 'collapsed';
@endphp
<body class="admin-page"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('mf_admin_sidebar_collapsed') !== null
              ? localStorage.getItem('mf_admin_sidebar_collapsed') === '1'
              : {{ $defaultCollapsed ? 'true' : 'false' }},
          mobileSearchOpen: false,
          accountOpen: false,
          toggleSidebar() {
              if (window.innerWidth <= 1024) {
                  this.sidebarOpen = !this.sidebarOpen;
              } else {
                  this.sidebarCollapsed = !this.sidebarCollapsed;
                  localStorage.setItem('mf_admin_sidebar_collapsed', this.sidebarCollapsed ? '1' : '0');
              }
          },
          openSearch() {
              if (window.innerWidth <= 1024) {
                  this.mobileSearchOpen = true;
              }
              this.$nextTick(() => {
                  this.$refs.adminSearchInput?.focus();
                  this.$refs.adminSearchInput?.select();
              });
          }
      }"
      @keydown.window="
          if (($event.ctrlKey || $event.metaKey) && $event.key.toLowerCase() === 'k') {
              $event.preventDefault();
              openSearch();
          }
      "
      @keydown.escape.window="sidebarOpen = false; accountOpen = false; mobileSearchOpen = false">

    <header class="admin-header"
            x-data="{
                q: '',
                searchOpen: false,
                loading: false,
                orders: [],
                products: [],
                customers: [],
                timer: null,
                runSearch() {
                    const term = this.q.trim();
                    if (term.length < 2) {
                        this.orders = [];
                        this.products = [];
                        this.customers = [];
                        this.searchOpen = false;
                        return;
                    }
                    clearTimeout(this.timer);
                    this.timer = setTimeout(async () => {
                        this.loading = true;
                        this.searchOpen = true;
                        try {
                            const res = await fetch(@js(route('admin.search')) + '?q=' + encodeURIComponent(term), {
                                headers: { 'Accept': 'application/json' }
                            });
                            if (res.ok) {
                                const data = await res.json();
                                this.orders = data.orders || [];
                                this.products = data.products || [];
                                this.customers = data.customers || [];
                            }
                        } catch (_) {
                            this.orders = [];
                            this.products = [];
                            this.customers = [];
                        } finally {
                            this.loading = false;
                        }
                    }, 200);
                },
                get hasResults() {
                    return this.orders.length > 0 || this.products.length > 0 || this.customers.length > 0;
                },
                goFirst() {
                    const first = this.products[0] || this.orders[0] || this.customers[0];
                    if (first) {
                        window.location.href = first.url;
                    } else if (this.q.trim() !== '') {
                        window.location.href = @js(route('admin.products.index')) + '?q=' + encodeURIComponent(this.q.trim());
                    }
                }
            }"
            @click.outside="searchOpen = false">

        <div class="admin-header-left">
            <button type="button"
                    class="admin-header-icon-btn admin-sidebar-toggle"
                    @click="toggleSidebar()"
                    :aria-expanded="(window.innerWidth <= 1024 ? sidebarOpen : !sidebarCollapsed).toString()"
                    aria-controls="admin-sidebar"
                    aria-label="Toggle sidebar navigation"
                    title="Toggle sidebar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M9 3v18"/>
                </svg>
            </button>

            <a class="admin-brand-inline" href="{{ route('admin.dashboard') }}">
                <img class="admin-brand-logo" src="{{ asset('images/logo.webp') }}" alt="" width="28" height="28">
                <span class="admin-brand-title">MUSCAT FRAGRANCES</span>
            </a>

            <span class="admin-role-pill">{{ $roleLabel }}</span>
        </div>

        <div class="admin-header-center" :class="{ 'is-mobile-open': mobileSearchOpen }">
            <div class="admin-global-search">
                <svg class="admin-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <label class="sr-only" for="admin-global-search-input">Search orders, products, customers</label>
                <input id="admin-global-search-input"
                       x-ref="adminSearchInput"
                       type="search"
                       x-model="q"
                       @input="runSearch()"
                       @focus="if (q.trim().length >= 2) searchOpen = true"
                       @keydown.enter.prevent="goFirst()"
                       autocomplete="off"
                       placeholder="Search orders, products, customers...">
                <kbd class="admin-kbd" aria-hidden="true">Ctrl+K</kbd>
                <button type="button"
                        class="admin-mobile-search-close"
                        x-show="mobileSearchOpen"
                        x-cloak
                        @click="mobileSearchOpen = false; searchOpen = false"
                        aria-label="Close search">×</button>

                <div class="admin-search-results" x-show="searchOpen" x-cloak role="region" aria-live="polite">
                    <template x-if="loading">
                        <p class="admin-search-empty">Searching...</p>
                    </template>
                    <template x-if="!loading && !hasResults">
                        <p class="admin-search-empty">No matching orders, products, or customers.</p>
                    </template>
                    <template x-if="!loading && orders.length > 0">
                        <div class="admin-search-group">
                            <p class="admin-search-group-title">Orders</p>
                            <template x-for="item in orders" :key="item.url + item.title">
                                <a class="admin-search-item" :href="item.url">
                                    <span><strong x-text="item.title"></strong><small x-text="item.subtitle"></small></span>
                                </a>
                            </template>
                        </div>
                    </template>
                    <template x-if="!loading && products.length > 0">
                        <div class="admin-search-group">
                            <p class="admin-search-group-title">Products</p>
                            <template x-for="item in products" :key="item.url">
                                <a class="admin-search-item" :href="item.url">
                                    <span><strong x-text="item.title"></strong><small x-text="item.subtitle"></small></span>
                                    <span class="admin-search-tag" x-text="item.badge"></span>
                                </a>
                            </template>
                        </div>
                    </template>
                    <template x-if="!loading && customers.length > 0">
                        <div class="admin-search-group">
                            <p class="admin-search-group-title">Customers &amp; Users</p>
                            <template x-for="item in customers" :key="item.subtitle">
                                <a class="admin-search-item" :href="item.url">
                                    <span><strong x-text="item.title"></strong><small x-text="item.subtitle"></small></span>
                                    <span class="admin-search-tag" x-text="item.badge"></span>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="admin-header-right">
            <button type="button"
                    class="admin-header-icon-btn admin-mobile-search-btn"
                    @click="mobileSearchOpen = !mobileSearchOpen; if (mobileSearchOpen) $nextTick(() => $refs.adminSearchInput?.focus())"
                    aria-label="Search (Ctrl+K)"
                    title="Search (Ctrl+K)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
            </button>

            <a class="admin-header-icon-btn admin-bell-btn"
               href="{{ route('admin.dashboard.notifications') }}"
               aria-label="Notifications and tasks ({{ $adminUnreadCount }} unread)"
               title="Notifications / Tasks">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                </svg>
                @if ($adminUnreadCount > 0)
                    <span class="admin-bell-count">{{ $adminUnreadCount }}</span>
                @endif
            </a>

            <a class="admin-storefront-btn"
               href="{{ url('/') }}"
               @if ($prefs['open_new_tab_storefront'] ?? true) target="_blank" rel="noopener" @endif
               aria-label="View Storefront"
               title="View Storefront">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 3h6v6"/>
                    <path d="M10 14 21 3"/>
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                </svg>
                <span class="admin-storefront-label">View Storefront</span>
            </a>

            <div class="admin-account-wrap" @click.outside="accountOpen = false">
                <button type="button"
                        class="admin-avatar-btn"
                        @click="accountOpen = !accountOpen"
                        aria-haspopup="menu"
                        :aria-expanded="accountOpen.toString()"
                        aria-label="Account menu"
                        title="{{ $authUser->name }}">
                    <span class="admin-avatar-circle">{{ $initials }}</span>
                    <svg class="admin-avatar-caret" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <div class="admin-account-menu" x-show="accountOpen" x-cloak role="menu">
                    <div class="admin-account-summary">
                        <strong>{{ $authUser->name }}</strong>
                        <span>{{ $authUser->email }}</span>
                        <small>{{ $roleLabel }}</small>
                    </div>
                    <a href="{{ route('admin.account.profile') }}" role="menuitem" @click="accountOpen = false">My Profile</a>
                    <a href="{{ route('admin.account.password') }}" role="menuitem" @click="accountOpen = false">Change Password</a>
                    <a href="{{ route('admin.account.preferences') }}" role="menuitem" @click="accountOpen = false">Account Preferences</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" role="menuitem">Sign Out</button>
                    </form>
                </div>
            </div>
        </div>

    </header>

    <div class="admin-workspace" :class="{ 'is-sidebar-collapsed': sidebarCollapsed }">
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
