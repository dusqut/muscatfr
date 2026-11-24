@extends('admin.layout')

@section('title', 'Categories')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">PRODUCT CATALOGUE</p>
            <h1>Categories</h1>
        </div>
        <a class="admin-primary-button" href="{{ route('admin.products.create') }}">Add New Product</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>English Name</th>
                    <th>Arabic Name</th>
                    <th>Slug</th>
                    <th>Sort Order</th>
                    <th>Products</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td><strong>{{ $category->name_en }}</strong></td>
                        <td dir="rtl">{{ $category->name_ar }}</td>
                        <td><span class="admin-subtext">{{ $category->slug }}</span></td>
                        <td>{{ $category->sort_order }}</td>
                        <td>{{ $category->products_count }}</td>
                        <td>
                            <span class="admin-badge {{ $category->is_active ? 'admin-badge-published' : 'admin-badge-draft' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="admin-empty">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
