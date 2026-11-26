@extends('admin.layout')

@section('title', 'My Profile')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">ACCOUNT</p>
            <h1>My Profile</h1>
        </div>
        <a class="admin-secondary-button" href="{{ route('admin.dashboard') }}">Back to Overview</a>
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

    <form class="admin-product-form" method="POST" action="{{ route('admin.account.profile.update') }}">
        @csrf
        @method('PUT')
        <section class="admin-form-section">
            <h2>Administrator Details</h2>
            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="profile-name">Full Name</label>
                    <input id="profile-name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="admin-field">
                    <label for="profile-email">Email Address</label>
                    <input id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="admin-field">
                    <label>Assigned Role</label>
                    <input value="{{ strtoupper(str_replace('_', ' ', $user->role)) }}" disabled>
                </div>
            </div>
        </section>

        <div class="admin-form-actions">
            <button class="admin-primary-button" type="submit">Save Profile</button>
            <a class="admin-text-link" href="{{ route('admin.account.password') }}">Change Password</a>
        </div>
    </form>
@endsection
