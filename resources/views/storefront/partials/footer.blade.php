@php
    $social = $storeNav['social'] ?? config('storefront.social', []);
    $socialLinks = $social['links'] ?? [];
    $socialHandle = $social['handle'] ?? 'MuscatFragrances';
    $support = $storeNav['support'] ?? [];
    $otherLocale = $storeNav['other_locale'] ?? ($locale === 'ar' ? 'en' : 'ar');
    $currentPath = '/' . ltrim(request()->path(), '/');
    $switchUrl = Route::currentRouteName() === 'store.home'
        ? route('store.home', ['locale' => $otherLocale])
        : url(preg_replace('#^/(en|ar)(?=/|$)#', '/' . $otherLocale, $currentPath) ?: '/' . $otherLocale);
@endphp

<footer class="site-footer">
    <div class="sf-container sf-footer-grid">
        <div class="sf-footer-brand">
            <a class="wordmark sf-logo footer-wordmark" href="{{ route('store.home', ['locale' => $locale]) }}" aria-label="{{ __('store.navigation.home') }}">
                <img class="sf-brand-mark" src="{{ asset('images/logo.webp') }}" alt="" width="40" height="40" loading="lazy">
                <span class="wordmark-text">
                    <span class="wordmark-name">MUSCAT</span>
                    <span class="wordmark-subtitle">FRAGRANCES</span>
                </span>
            </a>
            <p class="sf-footer-tagline">{{ __('store.footer.tagline') }}</p>
            @if (! empty($support['email']))
                <p class="sf-footer-contact">
                    <a href="mailto:{{ $support['email'] }}">{{ $support['email'] }}</a>
                </p>
            @endif

            <div class="sf-footer-social" aria-label="{{ __('store.footer.social_aria_label') }}">
                <p class="sf-footer-social-label">{{ __('store.footer.follow_us', ['handle' => $socialHandle]) }}</p>
                <ul class="sf-social-list">
                    @foreach ($socialLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               aria-label="{{ $link['label'] }} — {{ $socialHandle }}"
                               title="{{ $link['label'] }} ({{ '@' . $socialHandle }})">
                                @switch($link['key'])
                                    @case('facebook')
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                            <path d="M15 3h-3a4 4 0 0 0-4 4v3H5v4h3v7h4v-7h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                                        </svg>
                                        @break
                                    @case('instagram')
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                            <rect x="3" y="3" width="18" height="18" rx="5"/>
                                            <circle cx="12" cy="12" r="4"/>
                                            <circle cx="17.5" cy="6.5" r=".8" fill="currentColor"/>
                                        </svg>
                                        @break
                                    @case('tiktok')
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                            <path d="M14 3v11a4 4 0 1 1-3-3.87"/>
                                            <path d="M14 3c1 2.7 3.2 4.3 6 4.5"/>
                                        </svg>
                                        @break
                                    @case('x')
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                            <path d="M4 4l16 16M20 4L4 20"/>
                                        </svg>
                                        @break
                                    @case('youtube')
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                            <rect x="2.5" y="5.5" width="19" height="13" rx="3.5"/>
                                            <path d="M10 9.5l5 2.5-5 2.5z" fill="currentColor"/>
                                        </svg>
                                        @break
                                    @default
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                            <path d="M12 3c3.3 0 5 2.3 5 5.4 0 1.4-.3 2.7.5 3.6.7.8 1.9 1.1 2.5 1.7-.7 1.1-2 1.5-3.2 1.8-.4 1.3-1.4 2.8-4.8 2.8s-4.4-1.5-4.8-2.8C6 15.2 4.7 14.8 4 13.7c.6-.6 1.8-.9 2.5-1.7.8-.9.5-2.2.5-3.6C7 5.3 8.7 3 12 3z"/>
                                        </svg>
                                @endswitch
                                <span>{{ $link['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <nav class="sf-footer-col" aria-label="{{ __('store.footer.shop_title') }}">
            <h3>{{ __('store.footer.shop_title') }}</h3>
            <ul>
                <li><a href="{{ route('store.new-in', ['locale' => $locale]) }}">{{ __('store.primary_nav.new_in') }}</a></li>
                <li><a href="{{ route('store.shop', ['locale' => $locale]) }}">{{ __('store.mobile_drawer.shop_all') }}</a></li>
                <li><a href="{{ route('store.brands', ['locale' => $locale]) }}">{{ __('store.primary_nav.brands') }}</a></li>
                <li><a href="{{ route('store.oud-arabian', ['locale' => $locale]) }}">{{ __('store.primary_nav.oud_arabian') }}</a></li>
                <li><a href="{{ route('store.best-sellers', ['locale' => $locale]) }}">{{ __('store.primary_nav.best_sellers') }}</a></li>
                <li><a href="{{ route('store.gifts', ['locale' => $locale]) }}">{{ __('store.primary_nav.gifts') }}</a></li>
                <li><a href="{{ route('store.offers', ['locale' => $locale]) }}">{{ __('store.primary_nav.offers') }}</a></li>
            </ul>
        </nav>

        <nav class="sf-footer-col" aria-label="{{ __('store.footer.care_title') }}">
            <h3>{{ __('store.footer.care_title') }}</h3>
            <ul>
                <li><a href="{{ route('store.stores', ['locale' => $locale]) }}">{{ __('store.navigation.find_store') }}</a></li>
                <li><a href="{{ route('store.track-order', ['locale' => $locale]) }}">{{ __('store.navigation.track_order') }}</a></li>
                <li><a href="{{ route('store.support', ['locale' => $locale]) }}">{{ __('store.navigation.support') }}</a></li>
                <li><a href="{{ route('store.page', ['locale' => $locale, 'slug' => 'shipping-delivery']) }}">{{ __('store.footer.shipping_delivery') }}</a></li>
                <li><a href="{{ route('store.page', ['locale' => $locale, 'slug' => 'returns-refunds']) }}">{{ __('store.footer.returns_refunds') }}</a></li>
                <li><a href="{{ route('store.page', ['locale' => $locale, 'slug' => 'faq']) }}">{{ __('store.footer.faq') }}</a></li>
            </ul>
        </nav>

        <nav class="sf-footer-col" aria-label="{{ __('store.footer.company_title') }}">
            <h3>{{ __('store.footer.company_title') }}</h3>
            <ul>
                <li><a href="{{ route('store.page', ['locale' => $locale, 'slug' => 'about-us']) }}">{{ __('store.footer.about_us') }}</a></li>
                <li><a href="{{ route('store.page', ['locale' => $locale, 'slug' => 'authenticity']) }}">{{ __('store.footer.authenticity') }}</a></li>
                <li><a href="{{ route('store.account', ['locale' => $locale]) }}">{{ __('store.navigation.account') }}</a></li>
                <li><a href="{{ route('store.wishlist', ['locale' => $locale]) }}">{{ __('store.navigation.wishlist') }}</a></li>
                <li><a href="{{ route('store.bag', ['locale' => $locale]) }}">{{ __('store.navigation.bag') }}</a></li>
            </ul>
        </nav>

        <nav class="sf-footer-col" aria-label="{{ __('store.footer.legal_title') }}">
            <h3>{{ __('store.footer.legal_title') }}</h3>
            <ul>
                <li><a href="{{ route('store.page', ['locale' => $locale, 'slug' => 'privacy-policy']) }}">{{ __('store.footer.privacy_policy') }}</a></li>
                <li><a href="{{ route('store.page', ['locale' => $locale, 'slug' => 'terms-of-use']) }}">{{ __('store.footer.terms_of_use') }}</a></li>
                <li><a href="{{ route('store.page', ['locale' => $locale, 'slug' => 'cookie-policy']) }}">{{ __('store.footer.cookie_policy') }}</a></li>
                <li><a href="{{ route('store.page', ['locale' => $locale, 'slug' => 'disclaimer']) }}">{{ __('store.footer.disclaimer') }}</a></li>
            </ul>
        </nav>
    </div>

    <div class="sf-container footer-meta">
        <span>{{ __('store.footer.location') }} · {{ __('store.navigation.currency_label', ['currency' => $storeNav['currency'] ?? 'OMR']) }}</span>
        <div class="sf-footer-meta-right">
            <a href="{{ $switchUrl }}" lang="{{ $otherLocale }}">{{ __('store.navigation.language') }}</a>
            <span>{{ __('store.footer.copyright', ['year' => now()->year]) }}</span>
        </div>
    </div>
</footer>
