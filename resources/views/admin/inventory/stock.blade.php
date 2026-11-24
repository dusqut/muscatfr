@extends('admin.layout')

@section('title', $lowStockOnly ? 'Low Stock Alerts' : 'Stock Overview')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">INVENTORY</p>
            <h1>{{ $lowStockOnly ? 'Low Stock Alerts' : 'Stock Overview' }}</h1>
        </div>
        <div class="admin-header-actions">
            <a class="{{ $lowStockOnly ? 'admin-secondary-button' : 'admin-primary-button' }}"
               href="{{ route('admin.inventory.stock-overview') }}">All Stock</a>
            <a class="{{ $lowStockOnly ? 'admin-primary-button' : 'admin-secondary-button' }}"
               href="{{ route('admin.inventory.low-stock') }}">Low Stock (&le; {{ $threshold }})</a>
        </div>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Volume</th>
                    <th>Price (OMR)</th>
                    <th>On Hand</th>
                    <th>Status</th>
                    <th><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($variants as $variant)
                    <tr>
                        <td>
                            <strong>{{ $variant->product?->translated('en')?->name ?? '—' }}</strong>
                            <span class="admin-subtext">{{ $variant->product?->brand?->name ?? '' }}</span>
                        </td>
                        <td>{{ $variant->sku }}</td>
                        <td>{{ $variant->volume_ml }} ml</td>
                        <td>{{ $variant->price }}</td>
                        <td><strong>{{ $variant->stock_on_hand }}</strong></td>
                        <td>
                            @if ($variant->stock_on_hand === 0)
                                <span class="admin-badge admin-badge-draft">Out of stock</span>
                            @elseif ($variant->stock_on_hand <= $threshold)
                                <span class="admin-badge admin-badge-sample">Low stock</span>
                            @else
                                <span class="admin-badge admin-badge-published">In stock</span>
                            @endif
                        </td>
                        <td class="admin-row-actions">
                            @if ($variant->product)
                                <a class="admin-text-link" href="{{ route('admin.products.edit', $variant->product) }}">Update stock</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="admin-empty">
                            {{ $lowStockOnly ? 'No low-stock SKU variants at this time.' : 'No product variants found.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $variants->links() }}
@endsection
