@extends('admin.layout')

@section('title', 'Brands')

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">PRODUCT CATALOGUE</p>
            <h1>Brands</h1>
        </div>
        <a class="admin-primary-button" href="{{ route('admin.products.create') }}">Add New Product</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Brand</th>
                    <th>Slug</th>
                    <th>Products</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($brands as $brand)
                    <tr>
                        <td><strong>{{ $brand->name }}</strong></td>
                        <td><span class="admin-subtext">{{ $brand->slug }}</span></td>
                        <td>{{ $brand->products_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="admin-empty">No brands found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
