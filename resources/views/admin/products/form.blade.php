@extends('admin.layout')

@section('title', $product->exists ? __('admin.edit_product') : __('admin.add_product'))

@section('content')
    <div class="admin-page-heading">
        <div>
            <p class="admin-eyebrow">{{ __('admin.catalog') }}</p>
            <h1>{{ $product->exists ? __('admin.edit_product') : __('admin.add_product') }}</h1>
        </div>
        <a class="admin-text-link" href="{{ route('admin.products.index') }}">{{ __('admin.back_to_products') }}</a>
    </div>

    @if ($errors->any())
        <div class="admin-errors" role="alert">
            <p>{{ __('admin.form_errors') }}</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="admin-product-form" method="POST" enctype="multipart/form-data"
          action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <section class="admin-form-section">
            <h2>{{ __('admin.product_details') }}</h2>
            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="slug">{{ __('admin.slug') }}</label>
                    <input id="slug" name="slug" value="{{ old('slug', $product->slug) }}" required>
                </div>
                <div class="admin-field">
                    <label for="brand_id">{{ __('admin.brand') }}</label>
                    <select id="brand_id" name="brand_id" required>
                        <option value="">{{ __('admin.choose_brand') }}</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected((string) old('brand_id', $product->brand_id) === (string) $brand->id)>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-field">
                    <label for="category_id">{{ __('admin.category') }}</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">{{ __('admin.choose_category') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>
                                {{ $category->name_en }} / {{ $category->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-field">
                    <label for="concentration">{{ __('admin.concentration') }}</label>
                    <input id="concentration" name="concentration"
                           value="{{ old('concentration', $product->concentration) }}" placeholder="EDP">
                </div>
                <div class="admin-field">
                    <label for="audience">{{ __('admin.audience') }}</label>
                    <select id="audience" name="audience" required>
                        @foreach (['unisex', 'women', 'men'] as $audience)
                            <option value="{{ $audience }}" @selected(old('audience', $product->audience ?? 'unisex') === $audience)>
                                {{ __('admin.audience_' . $audience) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-field">
                    <label for="country_of_origin">{{ __('admin.country_of_origin') }}</label>
                    <input id="country_of_origin" name="country_of_origin"
                           value="{{ old('country_of_origin', $product->country_of_origin) }}">
                </div>
                <div class="admin-field">
                    <label for="status">{{ __('admin.status') }}</label>
                    <select id="status" name="status" required>
                        <option value="draft" @selected(old('status', $product->status ?? 'draft') === 'draft')>{{ __('admin.draft') }}</option>
                        <option value="published" @selected(old('status', $product->status ?? 'draft') === 'published')>{{ __('admin.published') }}</option>
                    </select>
                </div>
                <label class="admin-checkbox">
                    <input type="checkbox" name="is_sample" value="1" @checked((bool) old('is_sample', $product->is_sample ?? false))>
                    <span>{{ __('admin.sample_flag') }}</span>
                </label>
            </div>
        </section>

        <section class="admin-form-section">
            <h2>{{ __('admin.translations') }}</h2>
            <div class="admin-language-grid">
                @foreach (['en' => 'English', 'ar' => 'العربية'] as $locale => $label)
                    <fieldset class="admin-language-fields" lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
                        <legend>{{ $label }}</legend>
                        <div class="admin-field">
                            <label for="name-{{ $locale }}">{{ __('admin.product_name') }}</label>
                            <input id="name-{{ $locale }}" name="translations[{{ $locale }}][name]"
                                   value="{{ old("translations.{$locale}.name", data_get($translations->get($locale), 'name')) }}" required>
                        </div>
                        <div class="admin-field">
                            <label for="description-{{ $locale }}">{{ __('admin.description') }}</label>
                            <textarea id="description-{{ $locale }}" name="translations[{{ $locale }}][description]" rows="4" required>{{ old("translations.{$locale}.description", data_get($translations->get($locale), 'description')) }}</textarea>
                        </div>
                        <div class="admin-field">
                            <label for="top-{{ $locale }}">{{ __('admin.top_notes') }}</label>
                            <input id="top-{{ $locale }}" name="translations[{{ $locale }}][top_notes]"
                                   value="{{ old("translations.{$locale}.top_notes", data_get($translations->get($locale), 'top_notes')) }}">
                        </div>
                        <div class="admin-field">
                            <label for="middle-{{ $locale }}">{{ __('admin.middle_notes') }}</label>
                            <input id="middle-{{ $locale }}" name="translations[{{ $locale }}][middle_notes]"
                                   value="{{ old("translations.{$locale}.middle_notes", data_get($translations->get($locale), 'middle_notes')) }}">
                        </div>
                        <div class="admin-field">
                            <label for="base-{{ $locale }}">{{ __('admin.base_notes') }}</label>
                            <input id="base-{{ $locale }}" name="translations[{{ $locale }}][base_notes]"
                                   value="{{ old("translations.{$locale}.base_notes", data_get($translations->get($locale), 'base_notes')) }}">
                        </div>
                    </fieldset>
                @endforeach
            </div>
        </section>

        <section class="admin-form-section">
            <h2>{{ __('admin.product_images') }}</h2>
            <p class="admin-help">{{ __('admin.image_help') }}</p>
            @if ($product->exists && $product->images->isNotEmpty())
                <div class="admin-image-grid">
                    @foreach ($product->images as $image)
                        <label class="admin-image-card" for="remove-image-{{ $image->id }}">
                            <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->alt_en ?? '' }}">
                            <span>
                                <input id="remove-image-{{ $image->id }}" type="checkbox" name="remove_images[]" value="{{ $image->id }}">
                                {{ __('admin.remove_image') }}
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
            <div class="admin-form-grid admin-image-inputs">
                <div class="admin-field">
                    <label for="images">{{ __('admin.upload_images') }}</label>
                    <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                </div>
                <div class="admin-field">
                    <label for="image_alt_en">{{ __('admin.image_alt_en') }}</label>
                    <input id="image_alt_en" name="image_alt_en" value="{{ old('image_alt_en') }}">
                </div>
                <div class="admin-field">
                    <label for="image_alt_ar">{{ __('admin.image_alt_ar') }}</label>
                    <input id="image_alt_ar" name="image_alt_ar" value="{{ old('image_alt_ar') }}">
                </div>
            </div>
        </section>

        <section class="admin-form-section">
            <div class="admin-section-heading">
                <div>
                    <h2>{{ __('admin.variants') }}</h2>
                    <p>{{ __('admin.variant_help') }}</p>
                </div>
                <button class="admin-secondary-button" type="button" data-add-variant>{{ __('admin.add_variant') }}</button>
            </div>
            <div class="admin-variant-list" data-variant-list data-next-index="{{ $variants->count() }}">
                @foreach ($variants as $index => $variant)
                    @include('admin.products._variant-row', compact('index', 'variant'))
                @endforeach
            </div>
            <template data-variant-template>
                @include('admin.products._variant-row', ['index' => '__INDEX__', 'variant' => null])
            </template>
        </section>

        <div class="admin-form-actions">
            <button class="admin-primary-button" type="submit">{{ __('admin.save_product') }}</button>
            <a class="admin-text-link" href="{{ route('admin.products.index') }}">{{ __('admin.cancel') }}</a>
        </div>
    </form>
@endsection
