<div class="admin-variant-row" data-variant-row>
    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant?->id }}">
    <div class="admin-field">
        <label for="variant-sku-{{ $index }}">{{ __('admin.sku') }}</label>
        <input id="variant-sku-{{ $index }}" name="variants[{{ $index }}][sku]"
               value="{{ old("variants.{$index}.sku", $variant?->sku) }}" required>
    </div>
    <div class="admin-field">
        <label for="variant-volume-{{ $index }}">{{ __('admin.volume_ml') }}</label>
        <input id="variant-volume-{{ $index }}" name="variants[{{ $index }}][volume_ml]" type="number"
               min="1" max="1000" value="{{ old("variants.{$index}.volume_ml", $variant?->volume_ml) }}" required>
    </div>
    <div class="admin-field">
        <label for="variant-price-{{ $index }}">{{ __('admin.price_omr') }}</label>
        <input id="variant-price-{{ $index }}" name="variants[{{ $index }}][price]" type="number"
               min="0" step="0.001" value="{{ old("variants.{$index}.price", $variant?->price) }}" required>
    </div>
    <div class="admin-field">
        <label for="variant-stock-{{ $index }}">{{ __('admin.stock') }}</label>
        <input id="variant-stock-{{ $index }}" name="variants[{{ $index }}][stock_on_hand]" type="number"
               min="0" step="1" value="{{ old("variants.{$index}.stock_on_hand", $variant?->stock_on_hand ?? 0) }}" required>
    </div>
    <button class="admin-danger-link" type="button" data-remove-variant>{{ __('admin.remove') }}</button>
</div>
