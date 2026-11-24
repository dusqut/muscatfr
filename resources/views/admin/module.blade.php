@extends('admin.layout')

@section('title', $item['label'])

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">{{ $section['title'] }}</p>
            <h1>{{ $item['label'] }}</h1>
        </div>
        <div class="admin-header-actions">
            @if (! empty($item['tag']))
                <span class="admin-badge admin-badge-sample">{{ $item['tag'] }}</span>
            @else
                <span class="admin-badge admin-badge-draft">Roadmap Module</span>
            @endif
            <a class="admin-secondary-button" href="{{ route('admin.dashboard') }}">Dashboard Overview</a>
        </div>
    </div>

    <section class="admin-panel admin-module-panel">
        <h2>Module Scope</h2>
        <p class="admin-module-description">{{ $item['description'] ?? 'This section is reserved in the Muscat Fragrances administration architecture.' }}</p>

        @if (! empty($item['highlights']))
            <div class="admin-module-highlights">
                <h3>Planned Capabilities</h3>
                <ul>
                    @foreach ($item['highlights'] as $highlight)
                        <li>{{ $highlight }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="admin-module-actions">
            <a class="admin-primary-button" href="{{ route('admin.products.index') }}">Manage Products</a>
            <a class="admin-secondary-button" href="{{ route('admin.inventory.stock-overview') }}">View Inventory</a>
        </div>
    </section>
@endsection
