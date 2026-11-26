@extends('admin.layout')

@section('title', 'Change Password')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">SECURITY</p>
            <h1>Change Password</h1>
        </div>
        <a class="admin-secondary-button" href="{{ route('admin.account.profile') }}">My Profile</a>
    </div>

    @if ($errors->any())
        <div class="admin-errors" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="admin-product-form" method="POST" action="{{ route('admin.account.password.update') }}">
        @csrf
        @method('PUT')
        <section class="admin-form-section">
            <h2>Update Credentials</h2>
            <p class="admin-help">Choose a strong password of at least 14 characters.</p>
            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="current_password">Current Password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                </div>
                <div class="admin-field"></div>
                <div class="admin-field">
                    <label for="new_password">New Password</label>
                    <input id="new_password" name="password" type="password" autocomplete="new-password" minlength="14" required>
                </div>
                <div class="admin-field">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="14" required>
                </div>
            </div>
        </section>

        <div class="admin-form-actions">
            <button class="admin-primary-button" type="submit">Update Password</button>
        </div>
    </form>
@endsection
