@php
    $otherLocale = $storeNav['other_locale'];
    $currentRouteName = Route::currentRouteName();
    $currentPath = '/' . ltrim(request()->path(), '/');
    $switchUrl = $currentRouteName === 'store.home'
        ? route('store.home', ['locale' => $otherLocale])
        : url(preg_replace('#^/(en|ar)(?=/|$)#', '/' . $otherLocale, $currentPath) ?: '/' . $otherLocale);
@endphp

<div class="sf-header-wrap"
     x-data="{
         activeMenu: null,
         closeTimer: null,
         mobileOpen: false,
         touchStartX: 0,
         openMenu(name) {
             if (this.closeTimer) { clearTimeout(this.closeTimer); this.closeTimer = null; }
             this.activeMenu = name;
         },
         scheduleClose() {
             if (this.closeTimer) { clearTimeout(this.closeTimer); }
             this.closeTimer = setTimeout(() => { this.activeMenu = null; }, 140);
         },
         toggleMenu(name) {
             this.activeMenu = this.activeMenu === name ? null : name;
         },
         setMobile(state) {
             this.mobileOpen = state;
             document.body.style.overflow = state ? 'hidden' : '';
         },
         focusMobileSearch() {
             this.setMobile(false);
             window.scrollTo({ top: 0, behavior: 'smooth' });
             this.$nextTick(() => {
                 this.$refs.mobileSearchInput?.focus();
             });
         }
     }"
     @keydown.escape.window="activeMenu = null; setMobile(false)">

    @if ($storeNav['announcement']['enabled'])
        <div class="announcement">
            <a href="{{ $storeNav['announcement']['url'] }}">{{ $storeNav['announcement']['text'] }}</a>
        </div>
    @endif

    <div class="sf-utility-bar">
        <div class="sf-container sf-utility-inner">
            <nav class="sf-utility-links" aria-label="{{ __('store.navigation.utility_aria_label') }}">
                <a href="{{ route('store.stores', ['locale' => $locale]) }}">{{ __('store.navigation.find_store') }}</a>
                <a href="{{ route('store.track-order', ['locale' => $locale]) }}">{{ __('store.navigation.track_order') }}</a>
                <a href="{{ route('store.support', ['locale' => $locale]) }}">{{ __('store.navigation.support') }}</a>
            </nav>
            <div class="sf-utility-meta">
                <span class="sf-currency-badge">{{ __('store.navigation.currency_label', ['currency' => $storeNav['currency']]) }}</span>
                <a class="sf-language-link" href="{{ $switchUrl }}" lang="{{ $otherLocale }}">{{ __('store.navigation.language') }}</a>
            </div>
        </div>
    </div>

    <header class="site-header sf-header">
        <div class="sf-container sf-main-header">
            {{-- Mobile left hamburger --}}
            <button class="menu-toggle sf-hamburger"
                    type="button"
                    @click="setMobile(!mobileOpen)"
                    :aria-label="mobileOpen ? @js(__('store.navigation.close_menu')) : @js(__('store.navigation.menu'))"
                    :aria-expanded="mobileOpen.toString()"
                    aria-controls="mobile-nav-drawer">
                <span></span><span></span><span></span>
            </button>

            {{-- Brand logo --}}
            <a class="wordmark sf-logo" href="{{ route('store.home', ['locale' => $locale]) }}" aria-label="{{ __('store.navigation.home') }}">
                <img class="sf-brand-mark" src="{{ asset('images/logo.webp') }}" alt="" width="40" height="40">
                <span class="wordmark-text">
                    <span class="wordmark-name">MUSCAT</span>
                    <span class="wordmark-subtitle">FRAGRANCES</span>
                </span>
            </a>

            {{-- Desktop centered search with live autocomplete --}}
            <div class="sf-search-desktop"
                 x-data="{
                     q: @js((string) request('q', '')),
                     open: false,
                     loading: false,
                     products: [],
                     brands: [],
                     timer: null,
                     fetchSuggestions() {
                         const term = this.q.trim();
                         if (term.length < 2) {
                             this.products = [];
                             this.brands = [];
                             this.open = false;
                             return;
                         }
                         clearTimeout(this.timer);
                         this.timer = setTimeout(async () => {
                             this.loading = true;
                             this.open = true;
                             try {
                                 const res = await fetch(@js(route('store.search.suggestions', ['locale' => $locale])) + '?q=' + encodeURIComponent(term), {
                                     headers: { 'Accept': 'application/json' }
                                 });
                                 if (res.ok) {
                                     const data = await res.json();
                                     this.products = data.products || [];
                                     this.brands = data.brands || [];
                                 }
                             } catch (_) {
                                 this.products = [];
                                 this.brands = [];
                             } finally {
                                 this.loading = false;
                             }
                         }, 220);
                     }
                 }"
                 @click.outside="open = false">
                <form class="sf-search-form" method="GET" action="{{ route('store.shop', ['locale' => $locale]) }}" role="search">
                    <label class="sr-only" for="desktop-store-search">{{ __('store.navigation.search_label') }}</label>
                    <input id="desktop-store-search"
                           name="q"
                           type="search"
                           x-model="q"
                           @input="fetchSuggestions()"
                           @focus="if (q.trim().length >= 2) open = true"
                           autocomplete="off"
                           placeholder="{{ __('store.navigation.search_placeholder') }}">
                    <button type="submit" aria-label="{{ __('store.navigation.search_submit') }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="M20 20l-3.5-3.5"/>
                        </svg>
                    </button>
                </form>

                <div class="sf-search-dropdown" x-show="open" x-cloak role="region" aria-live="polite">
                    <template x-if="loading">
                        <p class="sf-search-state">{{ __('store.navigation.search_loading') }}</p>
                    </template>
                    <template x-if="!loading && products.length === 0 && brands.length === 0">
                        <p class="sf-search-state">{{ __('store.navigation.search_empty') }}</p>
                    </template>
                    <template x-if="!loading && brands.length > 0">
                        <div class="sf-search-group">
                            <p class="sf-search-group-title">{{ __('store.primary_nav.brands') }}</p>
                            <template x-for="brand in brands" :key="brand.url">
                                <a class="sf-search-hit" :href="brand.url" x-text="brand.name"></a>
                            </template>
                        </div>
                    </template>
                    <template x-if="!loading && products.length > 0">
                        <div class="sf-search-group">
                            <p class="sf-search-group-title">{{ __('store.primary_nav.shop') }}</p>
                            <template x-for="item in products" :key="item.url">
                                <a class="sf-search-hit" :href="item.url">
                                    <span>
                                        <strong x-text="item.name"></strong>
                                        <small x-text="item.brand"></small>
                                    </span>
                                    <span class="sf-search-price" x-show="item.price" x-text="item.price + ' ' + @js($storeNav['currency'])"></span>
                                </a>
                            </template>
                        </div>
                    </template>
                    <a class="sf-search-view-all"
                       :href="@js(route('store.shop', ['locale' => $locale])) + '?q=' + encodeURIComponent(q.trim())"
                       x-show="q.trim().length >= 2">
                        {{ __('store.navigation.search_view_all') }}
                    </a>
                </div>
            </div>

            {{-- Right actions: Account, Wishlist, Bag --}}
            <div class="sf-header-actions">
                <a class="sf-icon-link" href="{{ route('store.account', ['locale' => $locale]) }}" aria-label="{{ __('store.navigation.account') }}">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"/>
                    </svg>
                    <span class="sf-icon-label">{{ __('store.navigation.account') }}</span>
                </a>
                <a class="sf-icon-link sf-hide-mobile" href="{{ route('store.wishlist', ['locale' => $locale]) }}" aria-label="{{ __('store.navigation.wishlist') }}">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M12 20.5s-7-4.5-9-9.2C1.7 8.1 3.7 5 7 5c2 0 3.7 1.1 5 2.8C13.3 6.1 15 5 17 5c3.3 0 5.3 3.1 4 6.3-2 4.7-9 9.2-9 9.2z"/>
                    </svg>
                    <span class="sf-icon-label">{{ __('store.navigation.wishlist') }}</span>
                </a>
                <a class="sf-icon-link" href="{{ route('store.bag', ['locale' => $locale]) }}" aria-label="{{ __('store.navigation.bag') }}">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M6 8h12l1 12H5L6 8z"/>
                        <path d="M9 8V6a3 3 0 0 1 6 0v2"/>
                    </svg>
                    <span class="sf-icon-label">{{ __('store.navigation.bag') }}</span>
                </a>
            </div>
        </div>

        {{-- Mobile full-width search bar below header --}}
        <div class="sf-search-mobile">
            <form class="sf-search-form" method="GET" action="{{ route('store.shop', ['locale' => $locale]) }}" role="search">
                <label class="sr-only" for="mobile-store-search">{{ __('store.navigation.search_label') }}</label>
                <input id="mobile-store-search"
                       x-ref="mobileSearchInput"
                       name="q"
                       type="search"
                       value="{{ request('q') }}"
                       placeholder="{{ __('store.navigation.search_placeholder') }}">
                <button type="submit" aria-label="{{ __('store.navigation.search_submit') }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M20 20l-3.5-3.5"/>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Desktop 7-item Primary Navigation + Mega-Menus --}}
        <nav class="sf-primary-nav" aria-label="{{ __('store.navigation.aria_label') }}" @mouseleave="scheduleClose()">
            <div class="sf-container sf-primary-nav-inner">
                <ul class="sf-nav-list">
                    <li class="sf-nav-item">
                        <a class="sf-nav-trigger {{ request()->routeIs('store.new-in') ? 'is-active' : '' }}"
                           href="{{ route('store.new-in', ['locale' => $locale]) }}"
                           @mouseenter="activeMenu = null">
                            {{ __('store.primary_nav.new_in') }}
                        </a>
                    </li>

                    <li class="sf-nav-item" @mouseenter="openMenu('shop')" @focusin="openMenu('shop')">
                        <a class="sf-nav-trigger {{ request()->routeIs('store.shop') ? 'is-active' : '' }}"
                           href="{{ route('store.shop', ['locale' => $locale]) }}"
                           aria-haspopup="true"
                           :aria-expanded="(activeMenu === 'shop').toString()"
                           aria-controls="mega-shop"
                           @click="if (window.matchMedia('(hover: none)').matches) { $event.preventDefault(); toggleMenu('shop'); }">
                            {{ __('store.primary_nav.shop') }}
                        </a>
                    </li>

                    <li class="sf-nav-item" @mouseenter="openMenu('brands')" @focusin="openMenu('brands')">
                        <a class="sf-nav-trigger {{ request()->routeIs('store.brands*') ? 'is-active' : '' }}"
                           href="{{ route('store.brands', ['locale' => $locale]) }}"
                           aria-haspopup="true"
                           :aria-expanded="(activeMenu === 'brands').toString()"
                           aria-controls="mega-brands"
                           @click="if (window.matchMedia('(hover: none)').matches) { $event.preventDefault(); toggleMenu('brands'); }">
                            {{ __('store.primary_nav.brands') }}
                        </a>
                    </li>

                    <li class="sf-nav-item" @mouseenter="openMenu('oud')" @focusin="openMenu('oud')">
                        <a class="sf-nav-trigger {{ request()->routeIs('store.oud-arabian') ? 'is-active' : '' }}"
                           href="{{ route('store.oud-arabian', ['locale' => $locale]) }}"
                           aria-haspopup="true"
                           :aria-expanded="(activeMenu === 'oud').toString()"
                           aria-controls="mega-oud"
                           @click="if (window.matchMedia('(hover: none)').matches) { $event.preventDefault(); toggleMenu('oud'); }">
                            {{ __('store.primary_nav.oud_arabian') }}
                        </a>
                    </li>

                    <li class="sf-nav-item">
                        <a class="sf-nav-trigger {{ request()->routeIs('store.best-sellers') ? 'is-active' : '' }}"
                           href="{{ route('store.best-sellers', ['locale' => $locale]) }}"
                           @mouseenter="activeMenu = null">
                            {{ __('store.primary_nav.best_sellers') }}
                        </a>
                    </li>

                    <li class="sf-nav-item" @mouseenter="openMenu('gifts')" @focusin="openMenu('gifts')">
                        <a class="sf-nav-trigger {{ request()->routeIs('store.gifts') ? 'is-active' : '' }}"
                           href="{{ route('store.gifts', ['locale' => $locale]) }}"
                           aria-haspopup="true"
                           :aria-expanded="(activeMenu === 'gifts').toString()"
                           aria-controls="mega-gifts"
                           @click="if (window.matchMedia('(hover: none)').matches) { $event.preventDefault(); toggleMenu('gifts'); }">
                            {{ __('store.primary_nav.gifts') }}
                        </a>
                    </li>

                    <li class="sf-nav-item">
                        <a class="sf-nav-trigger is-offers {{ request()->routeIs('store.offers') ? 'is-active' : '' }}"
                           href="{{ route('store.offers', ['locale' => $locale]) }}"
                           @mouseenter="activeMenu = null">
                            {{ __('store.primary_nav.offers') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Mega-Menu Panels --}}
            <div class="sf-mega-backdrop"
                 x-show="activeMenu !== null"
                 x-cloak
                 @mouseenter="openMenu(activeMenu)">

                {{-- SHOP Mega-Menu --}}
                <div id="mega-shop" class="sf-container sf-mega-panel" x-show="activeMenu === 'shop'">
                    <div class="sf-mega-columns">
                        @if ($storeNav['shop']['genders'] !== [])
                            <div class="sf-mega-col">
                                <h3>{{ __('store.mega_shop.gender_title') }}</h3>
                                <ul>
                                    @foreach ($storeNav['shop']['genders'] as $gender)
                                        <li>
                                            <a href="{{ route('store.shop', ['locale' => $locale, 'gender' => $gender]) }}">
                                                {{ __('store.mega_shop.gender_' . $gender) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($storeNav['shop']['types'] !== [])
                            <div class="sf-mega-col">
                                <h3>{{ __('store.mega_shop.type_title') }}</h3>
                                <ul>
                                    @foreach ($storeNav['shop']['types'] as $type)
                                        <li>
                                            <a href="{{ route('store.shop', ['locale' => $locale, 'type' => $type]) }}">{{ $type }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($storeNav['shop']['categories']->isNotEmpty())
                            <div class="sf-mega-col">
                                <h3>{{ __('store.mega_shop.family_title') }}</h3>
                                <ul>
                                    @foreach ($storeNav['shop']['categories'] as $category)
                                        <li>
                                            <a href="{{ route('store.shop', ['locale' => $locale, 'category' => $category->slug]) }}">
                                                {{ $category->displayName($locale) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($storeNav['shop']['budgets'] !== [])
                            <div class="sf-mega-col">
                                <h3>{{ __('store.mega_shop.budget_title') }}</h3>
                                <ul>
                                    @foreach ($storeNav['shop']['budgets'] as $budget)
                                        <li>
                                            <a href="{{ route('store.shop', ['locale' => $locale, 'budget' => $budget['key']]) }}">
                                                {{ __('store.mega_shop.budget_' . $budget['key']) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="sf-mega-feature">
                            <p class="eyebrow">{{ __('store.primary_nav.shop') }}</p>
                            @if (! $storeNav['capabilities']['has_published_products'])
                                <p class="sf-mega-note">{{ __('store.mega_shop.empty_catalog') }}</p>
                            @endif
                            <a class="button button-dark" href="{{ route('store.shop', ['locale' => $locale]) }}">
                                {{ __('store.mega_shop.view_all') }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- BRANDS Mega-Menu --}}
                <div id="mega-brands"
                     class="sf-container sf-mega-panel"
                     x-show="activeMenu === 'brands'"
                     x-data="{
                         brandQuery: '',
                         selectedLetter: '',
                         brands: @js($storeNav['brands']->map(fn ($b) => [
                             'name' => $b->name,
                             'slug' => $b->slug,
                             'letter' => strtoupper(mb_substr($b->name, 0, 1)),
                             'count' => $b->published_products_count ?? 0,
                             'url' => route('store.brands.show', ['locale' => $locale, 'slug' => $b->slug]),
                         ])->values()),
                         get filtered() {
                             const q = this.brandQuery.trim().toLowerCase();
                             return this.brands.filter(b => {
                                 const matchQ = q === '' || b.name.toLowerCase().includes(q);
                                 const matchL = this.selectedLetter === '' || b.letter === this.selectedLetter;
                                 return matchQ && matchL;
                             }).slice(0, 12);
                         }
                     }">
                    <div class="sf-brands-mega-top">
                        <div>
                            <h3>{{ __('store.mega_brands.title') }}</h3>
                            <div class="sf-az-bar" role="group" aria-label="{{ __('store.mega_brands.all_az') }}">
                                <button type="button"
                                        :class="{ 'is-active': selectedLetter === '' }"
                                        @click="selectedLetter = ''">
                                    {{ __('store.mega_brands.all_az') }}
                                </button>
                                @foreach ($storeNav['brand_letters'] as $letter)
                                    <button type="button"
                                            :class="{ 'is-active': selectedLetter === @js($letter) }"
                                            @click="selectedLetter = selectedLetter === @js($letter) ? '' : @js($letter)">
                                        {{ $letter }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="sf-brands-mega-controls">
                            <label class="sr-only" for="mega-brand-filter">{{ __('store.mega_brands.search_placeholder') }}</label>
                            <input id="mega-brand-filter"
                                   type="search"
                                   x-model="brandQuery"
                                   placeholder="{{ __('store.mega_brands.search_placeholder') }}">
                            <a class="text-link" href="{{ route('store.brands', ['locale' => $locale]) }}">
                                {{ __('store.mega_brands.view_all') }}
                            </a>
                        </div>
                    </div>

                    <div class="sf-brands-grid">
                        <template x-for="brand in filtered" :key="brand.slug">
                            <a class="sf-brand-card" :href="brand.url">
                                <span class="sf-brand-name" x-text="brand.name"></span>
                            </a>
                        </template>
                        <p class="sf-mega-note" x-show="filtered.length === 0">{{ __('store.mega_brands.empty') }}</p>
                    </div>
                </div>

                {{-- OUD & ARABIAN Mega-Menu --}}
                <div id="mega-oud" class="sf-container sf-mega-panel" x-show="activeMenu === 'oud'">
                    <div class="sf-oud-mega">
                        <div class="sf-oud-links">
                            <h3>{{ __('store.primary_nav.oud_arabian') }}</h3>
                            @if ($storeNav['oud_arabian'] !== [])
                                <ul>
                                    @foreach ($storeNav['oud_arabian'] as $item)
                                        <li>
                                            <a href="{{ $item['url'] }}">
                                                <strong>{{ $item['label'] }}</strong>
                                                <span>{{ $item['subtitle'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="sf-mega-note">{{ __('store.mega_oud.empty') }}</p>
                            @endif
                        </div>

                        <div class="sf-oud-feature">
                            <div class="sf-oud-motif" aria-hidden="true">
                                <span class="sf-oud-arch"></span>
                            </div>
                            <div class="sf-oud-copy">
                                <p class="eyebrow">{{ __('store.mega_oud.eyebrow') }}</p>
                                <h3>{{ __('store.mega_oud.title') }}</h3>
                                <p>{{ __('store.mega_oud.description') }}</p>
                                <a class="button button-dark" href="{{ route('store.oud-arabian', ['locale' => $locale]) }}">
                                    {{ __('store.mega_oud.cta') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GIFTS Mega-Menu --}}
                <div id="mega-gifts" class="sf-container sf-mega-panel" x-show="activeMenu === 'gifts'">
                    <div class="sf-gifts-mega">
                        <div>
                            <h3>{{ __('store.mega_gifts.title') }}</h3>
                            <ul class="sf-gifts-list">
                                @foreach ($storeNav['gifts'] as $giftLink)
                                    <li><a href="{{ $giftLink['url'] }}">{{ $giftLink['label'] }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="sf-mega-feature">
                            <p class="eyebrow">{{ __('store.primary_nav.gifts') }}</p>
                            <p class="sf-mega-note">{{ __('store.story.body') }}</p>
                            <a class="button button-dark" href="{{ route('store.gifts', ['locale' => $locale]) }}">
                                {{ __('store.mega_gifts.cta') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    {{-- Mobile Slide-Out Drawer --}}
    <div class="sf-drawer-backdrop" x-show="mobileOpen" x-cloak @click="setMobile(false)"></div>

    <aside id="mobile-nav-drawer"
           class="sf-mobile-drawer"
           :class="{ 'is-open': mobileOpen }"
           aria-label="{{ __('store.navigation.mobile_aria_label') }}"
           @touchstart.passive="touchStartX = $event.changedTouches[0].clientX"
           @touchend.passive="
               const deltaX = $event.changedTouches[0].clientX - touchStartX;
               const isRtl = document.documentElement.dir === 'rtl';
               if ((!isRtl && deltaX < -60) || (isRtl && deltaX > 60)) { setMobile(false); }
           "
           x-data="{ openSection: null }">
        <div class="sf-drawer-header">
            <a class="wordmark sf-logo" href="{{ route('store.home', ['locale' => $locale]) }}" @click="setMobile(false)">
                <img class="sf-brand-mark" src="{{ asset('images/logo.webp') }}" alt="" width="34" height="34">
                <span class="wordmark-text">
                    <span class="wordmark-name">MUSCAT</span>
                    <span class="wordmark-subtitle">FRAGRANCES</span>
                </span>
            </a>
            <button type="button" class="sf-drawer-close" @click="setMobile(false)" aria-label="{{ __('store.navigation.close_menu') }}">×</button>
        </div>

        <nav class="sf-drawer-nav" aria-label="{{ __('store.navigation.mobile_aria_label') }}">
            <a class="sf-drawer-link" href="{{ route('store.new-in', ['locale' => $locale]) }}" @click="setMobile(false)">{{ __('store.primary_nav.new_in') }}</a>

            {{-- Shop All accordion --}}
            <div class="sf-drawer-group">
                <div class="sf-drawer-row">
                    <a class="sf-drawer-link" href="{{ route('store.shop', ['locale' => $locale]) }}" @click="setMobile(false)">{{ __('store.mobile_drawer.shop_all') }}</a>
                    @if ($storeNav['shop']['categories']->isNotEmpty() || $storeNav['shop']['budgets'] !== [])
                        <button type="button" class="sf-drawer-expand" @click="openSection = openSection === 'shop' ? null : 'shop'" :aria-expanded="(openSection === 'shop').toString()" aria-label="{{ __('store.primary_nav.shop') }}">
                            <span x-text="openSection === 'shop' ? '−' : '+'"></span>
                        </button>
                    @endif
                </div>
                <div class="sf-drawer-sub" x-show="openSection === 'shop'" x-cloak>
                    @foreach ($storeNav['shop']['categories'] as $category)
                        <a href="{{ route('store.shop', ['locale' => $locale, 'category' => $category->slug]) }}" @click="setMobile(false)">{{ $category->displayName($locale) }}</a>
                    @endforeach
                    @foreach ($storeNav['shop']['budgets'] as $budget)
                        <a href="{{ route('store.shop', ['locale' => $locale, 'budget' => $budget['key']]) }}" @click="setMobile(false)">{{ __('store.mega_shop.budget_' . $budget['key']) }}</a>
                    @endforeach
                </div>
            </div>

            <a class="sf-drawer-link" href="{{ route('store.shop', ['locale' => $locale, 'gender' => 'men']) }}" @click="setMobile(false)">{{ __('store.mobile_drawer.men') }}</a>
            <a class="sf-drawer-link" href="{{ route('store.shop', ['locale' => $locale, 'gender' => 'women']) }}" @click="setMobile(false)">{{ __('store.mobile_drawer.women') }}</a>
            <a class="sf-drawer-link" href="{{ route('store.shop', ['locale' => $locale, 'gender' => 'unisex']) }}" @click="setMobile(false)">{{ __('store.mobile_drawer.unisex') }}</a>

            {{-- Brands accordion --}}
            <div class="sf-drawer-group">
                <div class="sf-drawer-row">
                    <a class="sf-drawer-link" href="{{ route('store.brands', ['locale' => $locale]) }}" @click="setMobile(false)">{{ __('store.primary_nav.brands') }}</a>
                    @if ($storeNav['brands']->isNotEmpty())
                        <button type="button" class="sf-drawer-expand" @click="openSection = openSection === 'brands' ? null : 'brands'" :aria-expanded="(openSection === 'brands').toString()" aria-label="{{ __('store.primary_nav.brands') }}">
                            <span x-text="openSection === 'brands' ? '−' : '+'"></span>
                        </button>
                    @endif
                </div>
                <div class="sf-drawer-sub" x-show="openSection === 'brands'" x-cloak>
                    @foreach ($storeNav['brands']->take(8) as $brand)
                        <a href="{{ route('store.brands.show', ['locale' => $locale, 'slug' => $brand->slug]) }}" @click="setMobile(false)">{{ $brand->name }}</a>
                    @endforeach
                    <a href="{{ route('store.brands', ['locale' => $locale]) }}" @click="setMobile(false)">{{ __('store.mega_brands.all_az') }}</a>
                </div>
            </div>

            {{-- Oud & Arabian accordion --}}
            <div class="sf-drawer-group">
                <div class="sf-drawer-row">
                    <a class="sf-drawer-link" href="{{ route('store.oud-arabian', ['locale' => $locale]) }}" @click="setMobile(false)">{{ __('store.primary_nav.oud_arabian') }}</a>
                    @if ($storeNav['oud_arabian'] !== [])
                        <button type="button" class="sf-drawer-expand" @click="openSection = openSection === 'oud' ? null : 'oud'" :aria-expanded="(openSection === 'oud').toString()" aria-label="{{ __('store.primary_nav.oud_arabian') }}">
                            <span x-text="openSection === 'oud' ? '−' : '+'"></span>
                        </button>
                    @endif
                </div>
                <div class="sf-drawer-sub" x-show="openSection === 'oud'" x-cloak>
                    @foreach ($storeNav['oud_arabian'] as $item)
                        <a href="{{ $item['url'] }}" @click="setMobile(false)">{{ $item['label'] }}</a>
                    @endforeach
                </div>
            </div>

            <a class="sf-drawer-link" href="{{ route('store.best-sellers', ['locale' => $locale]) }}" @click="setMobile(false)">{{ __('store.primary_nav.best_sellers') }}</a>

            {{-- Gifts accordion --}}
            <div class="sf-drawer-group">
                <div class="sf-drawer-row">
                    <a class="sf-drawer-link" href="{{ route('store.gifts', ['locale' => $locale]) }}" @click="setMobile(false)">{{ __('store.primary_nav.gifts') }}</a>
                    <button type="button" class="sf-drawer-expand" @click="openSection = openSection === 'gifts' ? null : 'gifts'" :aria-expanded="(openSection === 'gifts').toString()" aria-label="{{ __('store.primary_nav.gifts') }}">
                        <span x-text="openSection === 'gifts' ? '−' : '+'"></span>
                    </button>
                </div>
                <div class="sf-drawer-sub" x-show="openSection === 'gifts'" x-cloak>
                    @foreach ($storeNav['gifts'] as $giftLink)
                        <a href="{{ $giftLink['url'] }}" @click="setMobile(false)">{{ $giftLink['label'] }}</a>
                    @endforeach
                </div>
            </div>

            <a class="sf-drawer-link" href="{{ route('store.offers', ['locale' => $locale]) }}" @click="setMobile(false)">{{ __('store.primary_nav.offers') }}</a>
            <a class="sf-drawer-link" href="{{ route('store.stores', ['locale' => $locale]) }}" @click="setMobile(false)">{{ __('store.navigation.find_store') }}</a>
            <a class="sf-drawer-link" href="{{ route('store.track-order', ['locale' => $locale]) }}" @click="setMobile(false)">{{ __('store.navigation.track_order') }}</a>
        </nav>

        <div class="sf-drawer-footer">
            <a href="{{ route('store.support', ['locale' => $locale]) }}" @click="setMobile(false)">{{ __('store.navigation.support') }}</a>
            <div class="sf-drawer-meta">
                <span>{{ __('store.navigation.currency_label', ['currency' => $storeNav['currency']]) }}</span>
                <a href="{{ $switchUrl }}" lang="{{ $otherLocale }}">{{ __('store.navigation.language') }}</a>
            </div>
        </div>
    </aside>

    {{-- Mobile Bottom Navigation (5 items) --}}
    <nav class="sf-bottom-nav" aria-label="{{ __('store.navigation.bottom_aria_label') }}">
        <a href="{{ route('store.home', ['locale' => $locale]) }}" class="{{ request()->routeIs('store.home') ? 'is-active' : '' }}">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path d="M3 10.5L12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z"/>
            </svg>
            <span>{{ __('store.navigation.bottom_home') }}</span>
        </a>
        <a href="{{ route('store.shop', ['locale' => $locale]) }}" class="{{ request()->routeIs('store.shop') ? 'is-active' : '' }}">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
            </svg>
            <span>{{ __('store.navigation.bottom_shop') }}</span>
        </a>
        <button type="button" @click="focusMobileSearch()">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>
            </svg>
            <span>{{ __('store.navigation.bottom_search') }}</span>
        </button>
        <a href="{{ route('store.wishlist', ['locale' => $locale]) }}" class="{{ request()->routeIs('store.wishlist') ? 'is-active' : '' }}">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path d="M12 20.5s-7-4.5-9-9.2C1.7 8.1 3.7 5 7 5c2 0 3.7 1.1 5 2.8C13.3 6.1 15 5 17 5c3.3 0 5.3 3.1 4 6.3-2 4.7-9 9.2-9 9.2z"/>
            </svg>
            <span>{{ __('store.navigation.bottom_wishlist') }}</span>
        </a>
        <a href="{{ route('store.bag', ['locale' => $locale]) }}" class="{{ request()->routeIs('store.bag') ? 'is-active' : '' }}">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path d="M6 8h12l1 12H5L6 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>
            </svg>
            <span>{{ __('store.navigation.bottom_bag') }}</span>
        </a>
    </nav>
</div>
