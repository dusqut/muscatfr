@extends('admin.layout')

@section('title', 'Account Preferences')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">WORKSPACE</p>
            <h1>Account Preferences</h1>
        </div>
        <a class="admin-secondary-button" href="{{ route('admin.dashboard') }}">Back to Overview</a>
    </div>

    <form class="admin-product-form" method="POST" action="{{ route('admin.account.preferences.update') }}">
        @csrf
        @method('PUT')
        <section class="admin-form-section">
            <h2>Navigation &amp; Display</h2>
            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="sidebar_default">Default Desktop Sidebar State</label>
                    <select id="sidebar_default" name="sidebar_default">
                        <option value="expanded" @selected(($preferences['sidebar_default'] ?? 'expanded') === 'expanded')>Expanded</option>
                        <option value="collapsed" @selected(($preferences['sidebar_default'] ?? 'expanded') === 'collapsed')>Collapsed</option>
                    </select>
                </div>
                <label class="admin-checkbox">
                    <input type="checkbox" name="open_new_tab_storefront" value="1" @checked((bool) ($preferences['open_new_tab_storefront'] ?? true))>
                    <span>Open storefront preview in a new browser tab</span>
                </label>
            </div>
        </section>

        <div class="admin-form-actions">
            <button class="admin-primary-button" type="submit">Save Preferences</button>
        </div>
    </form>
@endsection
