@extends('admin.layout')

@section('title', 'Dashboard Overview')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">DASHBOARD</p>
            <h1>Overview</h1>
        </div>
        <div class="admin-header-actions">
            <a class="admin-secondary-button" href="{{ route('admin.products.index') }}">All Products</a>
            <a class="admin-primary-button" href="{{ route('admin.products.create') }}">Add New Product</a>
        </div>
    </div>

    <section class="admin-kpi-grid" aria-label="Store metrics">
        <article class="admin-kpi-card">
            <p class="admin-kpi-label">Products in Catalog</p>
            <p class="admin-kpi-value">{{ $metrics['total_products'] }}</p>
            <p class="admin-kpi-meta">
                {{ $metrics['published_products'] }} published · {{ $metrics['draft_products'] }} draft
                @if ($metrics['sample_products'] > 0)
                    · {{ $metrics['sample_products'] }} sample
                @endif
            </p>
        </article>

        <article class="admin-kpi-card">
            <p class="admin-kpi-label">Categories &amp; Brands</p>
            <p class="admin-kpi-value">{{ $metrics['active_categories'] }} / {{ $metrics['total_brands'] }}</p>
            <p class="admin-kpi-meta">{{ $metrics['total_categories'] }} total categories · {{ $metrics['total_brands'] }} brands</p>
        </article>

        <article class="admin-kpi-card">
            <p class="admin-kpi-label">Online Stock Units</p>
            <p class="admin-kpi-value">{{ number_format($metrics['total_stock_units']) }}</p>
            <p class="admin-kpi-meta">Across {{ $metrics['total_variants'] }} bottle size SKU(s)</p>
        </article>

        <article class="admin-kpi-card {{ $metrics['low_stock_count'] > 0 ? 'is-alert' : '' }}">
            <p class="admin-kpi-label">Low Stock Alerts</p>
            <p class="admin-kpi-value">{{ $metrics['low_stock_count'] }}</p>
            <p class="admin-kpi-meta">
                <a class="admin-text-link" href="{{ route('admin.inventory.low-stock') }}">Review low stock SKUs</a>
            </p>
        </article>
    </section>

    <div class="admin-dashboard-grid">
        <section class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <p class="admin-eyebrow">PRODUCT CATALOGUE</p>
                    <h2>Recent Products</h2>
                </div>
                <a class="admin-text-link" href="{{ route('admin.products.index') }}">View all</a>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Variants</th>
                            <th>Status</th>
                            <th><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentProducts as $product)
                            <tr>
                                <td>
                                    <strong>{{ $product->translated('en')?->name ?? $product->slug }}</strong>
                                    <span class="admin-subtext">{{ $product->brand?->name ?? '—' }}</span>
                                    @if ($product->is_sample)
                                        <span class="admin-badge admin-badge-sample">Sample</span>
                                    @endif
                                </td>
                                <td>{{ $product->category?->displayName('en') ?? '—' }}</td>
                                <td>{{ $product->variants->count() }} size(s)</td>
                                <td>
                                    <span class="admin-badge {{ $product->status === 'published' ? 'admin-badge-published' : 'admin-badge-draft' }}">
                                        {{ __('admin.' . $product->status) }}
                                    </span>
                                </td>
                                <td class="admin-row-actions">
                                    <a class="admin-text-link" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="admin-empty">No products yet. Add your first fragrance listing.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="admin-dashboard-side">
            <section class="admin-panel">
                <div class="admin-panel-header">
                    <div>
                        <p class="admin-eyebrow">ATTENTION REQUIRED</p>
                        <h2>Notifications / Tasks</h2>
                    </div>
                    <a class="admin-text-link" href="{{ route('admin.dashboard.notifications') }}">View all</a>
                </div>

                @if ($tasks === [])
                    <p class="admin-panel-empty">All catalog and stock checks look good.</p>
                @else
                    <ul class="admin-task-list">
                        @foreach ($tasks as $task)
                            <li class="admin-task-item is-{{ $task['severity'] }}">
                                <div>
                                    <strong>{{ $task['title'] }}</strong>
                                    <p>{{ $task['detail'] }}</p>
                                </div>
                                <a class="admin-text-link" href="{{ $task['action_url'] }}">{{ $task['action_label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="admin-panel">
                <div class="admin-panel-header">
                    <div>
                        <p class="admin-eyebrow">INVENTORY</p>
                        <h2>Low Stock Watchlist</h2>
                    </div>
                    <a class="admin-text-link" href="{{ route('admin.inventory.stock-overview') }}">Stock overview</a>
                </div>

                @if ($lowStockVariants->isEmpty())
                    <p class="admin-panel-empty">All variants are stocked above threshold.</p>
                @else
                    <ul class="admin-compact-list">
                        @foreach ($lowStockVariants as $variant)
                            <li>
                                <div>
                                    <strong>{{ $variant->product?->translated('en')?->name ?? $variant->sku }}</strong>
                                    <span class="admin-subtext">{{ $variant->sku }} · {{ $variant->volume_ml }} ml</span>
                                </div>
                                <span class="admin-badge {{ $variant->stock_on_hand === 0 ? 'admin-badge-draft' : 'admin-badge-sample' }}">
                                    {{ $variant->stock_on_hand }} left
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="admin-panel">
                <div class="admin-panel-header">
                    <div>
                        <p class="admin-eyebrow">TAXONOMY</p>
                        <h2>Categories</h2>
                    </div>
                    <a class="admin-text-link" href="{{ route('admin.catalog.categories') }}">Manage</a>
                </div>
                @if ($categories->isEmpty())
                    <p class="admin-panel-empty">No categories defined.</p>
                @else
                    <ul class="admin-compact-list">
                        @foreach ($categories as $category)
                            <li>
                                <div>
                                    <strong>{{ $category->name_en }}</strong>
                                    <span class="admin-subtext">{{ $category->name_ar }}</span>
                                </div>
                                <span class="admin-subtext">{{ $category->products_count }} product(s)</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
@endsection
