@extends('admin.layout')

@section('title', 'Notifications / Tasks')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">DASHBOARD</p>
            <h1>Notifications / Tasks</h1>
        </div>
        <a class="admin-secondary-button" href="{{ route('admin.dashboard') }}">Back to Overview</a>
    </div>

    <div class="admin-panel">
        <div class="admin-panel-header">
            <div>
                <p class="admin-eyebrow">ACTION QUEUE</p>
                <h2>Operational Checklist</h2>
            </div>
            <span class="admin-badge {{ $launchGateEnabled ? 'admin-badge-sample' : 'admin-badge-published' }}">
                Launch Gate: {{ $launchGateEnabled ? 'Enabled' : 'Disabled' }}
            </span>
        </div>

        @if ($tasks === [])
            <p class="admin-panel-empty">No pending catalog or inventory alerts.</p>
        @else
            <ul class="admin-task-list">
                @foreach ($tasks as $task)
                    <li class="admin-task-item is-{{ $task['severity'] }}">
                        <div>
                            <strong>{{ $task['title'] }}</strong>
                            <p>{{ $task['detail'] }}</p>
                        </div>
                        <a class="admin-primary-button" href="{{ $task['action_url'] }}">{{ $task['action_label'] }}</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
