@extends('admin.layout')

@section('title', __('admin.products'))

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">{{ __('admin.catalog') }}</p>
            <h1>{{ __('admin.products') }}</h1>
        </div>
        <a class="admin-primary-button" href="{{ route('admin.products.create') }}">{{ __('admin.add_product') }}</a>
    </div>

    <form class="admin-filters" method="GET" action="{{ route('admin.products.index') }}">
        <label class="sr-only" for="catalog-search">{{ __('admin.search_products') }}</label>
        <input id="catalog-search" name="q" type="search" value="{{ request('q') }}"
               placeholder="{{ __('admin.search_placeholder') }}">
        <label class="sr-only" for="catalog-status">{{ __('admin.filter_status') }}</label>
        <select id="catalog-status" name="status">
            <option value="">{{ __('admin.all_statuses') }}</option>
            <option value="draft" @selected(request('status') === 'draft')>{{ __('admin.draft') }}</option>
            <option value="published" @selected(request('status') === 'published')>{{ __('admin.published') }}</option>
        </select>
        <button class="admin-secondary-button" type="submit">{{ __('admin.filter') }}</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>{{ __('admin.product') }}</th>
                    <th>{{ __('admin.category') }}</th>
                    <th>{{ __('admin.variants') }}</th>
                    <th>{{ __('admin.status') }}</th>
                    <th><span class="sr-only">{{ __('admin.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <strong>{{ $product->translated('en')?->name ?? $product->slug }}</strong>
                            <span class="admin-subtext">{{ $product->slug }}</span>
                            @if ($product->is_sample)
                                <span class="admin-badge admin-badge-sample">{{ __('admin.sample') }}</span>
                            @endif
                        </td>
                        <td>{{ $product->category->displayName('en') }}</td>
                        <td>
                            @foreach ($product->variants as $variant)
                                <span class="admin-subtext">{{ $variant->volume_ml }} ml · {{ $variant->sku }}</span>
                            @endforeach
                        </td>
                        <td>
                            <span class="admin-badge {{ $product->status === 'published' ? 'admin-badge-published' : 'admin-badge-draft' }}">
                                {{ __('admin.' . $product->status) }}
                            </span>
                        </td>
                        <td class="admin-row-actions">
                            <a class="admin-text-link" href="{{ route('admin.products.edit', $product) }}">{{ __('admin.edit') }}</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                  onsubmit="return confirm(@js(__('admin.confirm_delete')))">
                                @csrf
                                @method('DELETE')
                                <button class="admin-danger-link" type="submit">{{ __('admin.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="admin-empty">{{ __('admin.no_products') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $products->links() }}
@endsection
