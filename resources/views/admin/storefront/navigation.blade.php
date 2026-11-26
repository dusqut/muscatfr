@extends('admin.layout')

@section('title', 'Navigation & Menus')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">STOREFRONT &amp; CONTENT</p>
            <h1>Navigation &amp; Menus</h1>
        </div>
        <a class="admin-secondary-button" href="{{ rtrim((string) config('app.url'), '/') . '/en' }}" target="_blank" rel="noopener">Preview Storefront</a>
    </div>

    <form class="admin-product-form" method="POST" action="{{ route('admin.storefront.navigation.update') }}">
        @csrf

        <section class="admin-form-section">
            <h2>Announcement Bar</h2>
            <p class="admin-help">Update the promotional banner shown above the storefront header without editing source code.</p>
            <div class="admin-form-grid">
                <label class="admin-checkbox">
                    <input type="checkbox" name="announcement_enabled" value="1" @checked((bool) old('announcement_enabled', $settings['announcement']['enabled'] ?? true))>
                    <span>Show announcement bar on storefront</span>
                </label>
                <div class="admin-field">
                    <label for="announcement_url_path">Target Path (relative to locale)</label>
                    <input id="announcement_url_path" name="announcement_url_path" value="{{ old('announcement_url_path', $settings['announcement']['url_path'] ?? '/shop') }}" placeholder="/shop">
                </div>
                <div class="admin-field">
                    <label for="announcement_en">Announcement (English)</label>
                    <input id="announcement_en" name="announcement_en" value="{{ old('announcement_en', $settings['announcement']['en'] ?? '') }}" required>
                </div>
                <div class="admin-field" dir="rtl">
                    <label for="announcement_ar">Announcement (Arabic)</label>
                    <input id="announcement_ar" name="announcement_ar" value="{{ old('announcement_ar', $settings['announcement']['ar'] ?? '') }}" required>
                </div>
            </div>
        </section>

        <section class="admin-form-section">
            <h2>Oman Delivery, Concierge &amp; Seasonal Gift Menus</h2>
            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="free_delivery_threshold_omr">Free Delivery Threshold (OMR)</label>
                    <input id="free_delivery_threshold_omr" name="free_delivery_threshold_omr" type="number" step="0.5" min="0" value="{{ old('free_delivery_threshold_omr', $settings['free_delivery_threshold_omr'] ?? 40) }}" required>
                </div>
                <div class="admin-field">
                    <label for="whatsapp_number">WhatsApp Concierge Number</label>
                    <input id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $settings['support']['whatsapp_number'] ?? '') }}" placeholder="+96871826515">
                </div>
                <label class="admin-checkbox">
                    <input type="checkbox" name="gifts_eid" value="1" @checked((bool) old('gifts_eid', $settings['seasonal_gifts']['eid'] ?? false))>
                    <span>Show "Eid Gifts" in Gifts menu</span>
                </label>
                <label class="admin-checkbox">
                    <input type="checkbox" name="gifts_ramadan" value="1" @checked((bool) old('gifts_ramadan', $settings['seasonal_gifts']['ramadan'] ?? false))>
                    <span>Show "Ramadan Gifts" in Gifts menu</span>
                </label>
                <label class="admin-checkbox">
                    <input type="checkbox" name="gifts_wedding" value="1" @checked((bool) old('gifts_wedding', $settings['seasonal_gifts']['wedding'] ?? true))>
                    <span>Show "Wedding Gifts" in Gifts menu</span>
                </label>
            </div>
        </section>

        <div class="admin-form-actions">
            <button class="admin-primary-button" type="submit">Save Navigation Settings</button>
        </div>
    </form>
@endsection
