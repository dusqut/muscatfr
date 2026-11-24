@extends('admin.layout')

@section('title', 'Users & Roles')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">SETTINGS &amp; ADMINISTRATION</p>
            <h1>Users &amp; Roles</h1>
        </div>
        <a class="admin-secondary-button" href="{{ route('admin.dashboard') }}">Back to Overview</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Catalog Access</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="admin-badge {{ $user->canManageCatalog() ? 'admin-badge-published' : 'admin-badge-draft' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td>{{ $user->canManageCatalog() ? 'Allowed' : 'Customer only' }}</td>
                        <td>{{ $user->created_at?->format('Y-m-d') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="admin-empty">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
