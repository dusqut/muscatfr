@extends('storefront.layout')

@section('title', ($translation?->name ?? $product->slug) . ' — Muscat Fragrances')

@section('content')
    <section class="sf-page-section section-wrap">
        <div class="sf-product-detail">
            <div class="sf-product-gallery">
                @if ($product->images->isNotEmpty())
                    @php $heroImg = $product->images->first(); @endphp
                    <img src="{{ asset('storage/' . $heroImg->path) }}"
                         alt="{{ $locale === 'ar' ? ($heroImg->alt_ar ?? $translation?->name) : ($heroImg->alt_en ?? $translation?->name) }}">
                @else
                    <div class="sf-product-placeholder sf-product-placeholder-lg" aria-hidden="true"><span>M</span></div>
                @endif
            </div>

            <div class="sf-product-info">
                <p class="eyebrow">{{ $product->brand?->name }} · {{ $product->category?->displayName($locale) }}</p>
                <h1>{{ $translation?->name ?? $product->slug }}</h1>
                @if ($translation?->description)
                    <p class="sf-page-lead">{{ $translation->description }}</p>
                @endif

                <dl class="sf-notes-dl">
                    @if ($translation?->top_notes)
                        <div><dt>{{ __('admin.top_notes') }}</dt><dd>{{ $translation->top_notes }}</dd></div>
                    @endif
                    @if ($translation?->middle_notes)
                        <div><dt>{{ __('admin.middle_notes') }}</dt><dd>{{ $translation->middle_notes }}</dd></div>
                    @endif
                    @if ($translation?->base_notes)
                        <div><dt>{{ __('admin.base_notes') }}</dt><dd>{{ $translation->base_notes }}</dd></div>
                    @endif
                </dl>

                <div class="sf-variant-list">
                    @foreach ($product->variants->where('is_active', true) as $variant)
                        <div class="sf-variant-item">
                            <span>{{ $variant->volume_ml }} ml · {{ $variant->sku }}</span>
                            <strong>{{ $variant->price }} {{ __('store.pages.currency_omr') }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
