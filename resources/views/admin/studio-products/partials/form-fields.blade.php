@php
    $area = $studioProduct->normalizedPrintArea();
    $colorsText = old('colors_text', collect($studioProduct->colors ?? [])->map(function ($color) {
        if (is_array($color)) {
            return trim(($color['name'] ?? '').'|'.($color['hex'] ?? ''), '|');
        }
        return (string) $color;
    })->filter()->implode("\n"));
    $sizesText = old('sizes_text', implode(', ', $studioProduct->sizes ?? []));
    $mockups = $studioProduct->relationLoaded('mockups') ? $studioProduct->mockups : collect();
    $mockupRows = old('mockups');
    if (!is_array($mockupRows)) {
        $mockupRows = $mockups->map(function ($mockup) {
            $print = $mockup->normalizedPrintArea();
            return [
                'id' => $mockup->id,
                'name' => $mockup->name,
                'color' => $mockup->color,
                'image_url' => $mockup->image_url,
                'print_area_x' => $print['x'],
                'print_area_y' => $print['y'],
                'print_area_width' => $print['width'],
                'print_area_height' => $print['height'],
                'sort_order' => $mockup->sort_order,
            ];
        })->all();
    }
    if (count($mockupRows) === 0) {
        $mockupRows[] = [];
    }
@endphp

<style>
    .studio-print-picker__stage {
        position: relative;
        width: 100%;
        max-width: 420px;
        aspect-ratio: 1;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        user-select: none;
        touch-action: none;
        cursor: crosshair;
    }
    .studio-print-picker__img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        pointer-events: none;
        user-select: none;
    }
    .studio-print-picker__img.is-empty {
        display: none;
    }
    .studio-print-picker__empty {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        text-align: center;
        color: #6b7280;
        font-size: 13px;
        pointer-events: none;
    }
    .studio-print-picker__box {
        position: absolute;
        border: 2px dashed #005366;
        background: rgba(0, 83, 102, 0.14);
        box-sizing: border-box;
        cursor: move;
    }
    .studio-print-picker__handle {
        position: absolute;
        width: 12px;
        height: 12px;
        background: #ffffff;
        border: 2px solid #005366;
        border-radius: 2px;
        box-sizing: border-box;
    }
    .studio-print-picker__handle[data-print-handle="nw"] { left: -6px; top: -6px; cursor: nwse-resize; }
    .studio-print-picker__handle[data-print-handle="ne"] { right: -6px; top: -6px; cursor: nesw-resize; }
    .studio-print-picker__handle[data-print-handle="sw"] { left: -6px; bottom: -6px; cursor: nesw-resize; }
    .studio-print-picker__handle[data-print-handle="se"] { right: -6px; bottom: -6px; cursor: nwse-resize; }
</style>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Garment type</label>
    <input type="text" name="name" value="{{ old('name', $studioProduct->name) }}" required class="w-full rounded-lg border-gray-300" placeholder="T-Shirts">
    @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <input type="text" name="category" value="{{ old('category', $studioProduct->category ?? 'Clothing') }}" class="w-full rounded-lg border-gray-300" placeholder="Clothing">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Fallback price (USD)</label>
        <input type="number" step="0.01" min="0" name="price" id="studio-fallback-price" value="{{ old('price', $studioProduct->price ?? 12) }}" class="w-full rounded-lg border-gray-300">
        <p class="text-xs text-gray-500 mt-1">Used when a size below is left blank. Customers pay the garment size price (AI generate and upload are free).</p>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Fulfillment catalog product (optional)</label>
    <select name="product_id" class="w-full rounded-lg border-gray-300">
        <option value="">None — customers can still design, checkout needs a linked product</option>
        @if($studioProduct->product)
            <option value="{{ $studioProduct->product_id }}" selected>{{ $studioProduct->product->name }}</option>
        @endif
        @foreach($products as $product)
            <option value="{{ $product->id }}" @selected((int) old('product_id', $studioProduct->product_id) === (int) $product->id)>{{ $product->name }}</option>
        @endforeach
    </select>
    <p class="text-xs text-gray-500 mt-1">Used only to add the designed garment to cart (size/color SKU). This is not the mockup the customer sees.</p>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Colors (one per line: Name|#hex)</label>
    <textarea name="colors_text" rows="4" class="w-full rounded-lg border-gray-300" placeholder="White|#ffffff&#10;Black|#000000&#10;Navy|#1e3a8a">{{ $colorsText }}</textarea>
    <p class="text-xs text-gray-500 mt-1">These become the color dots. Clicking a color tints the current mockup, unless you uploaded a photo tagged with that color.</p>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Sizes (comma separated)</label>
    <input type="text" name="sizes_text" id="studio-sizes-text" value="{{ $sizesText }}" class="w-full rounded-lg border-gray-300" placeholder="S, M, L, XL, 2XL, 3XL, 4XL, 5XL">
    <p class="text-xs text-gray-500 mt-1">Each size needs its own price. Example: S $12, 5XL $17.</p>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Price per size (USD)</label>
    <div id="studio-size-prices" class="grid grid-cols-1 sm:grid-cols-2 gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4">
        @php
            $sizePriceMap = old('size_prices', $studioProduct->normalizedSizePrices());
            $sizeList = collect(preg_split('/[,|\n]/', $sizesText) ?: [])->map(fn ($s) => trim((string) $s))->filter()->values();
        @endphp
        @forelse($sizeList as $size)
            <label class="flex items-center gap-3">
                <span class="w-14 text-sm font-semibold text-gray-800">{{ $size }}</span>
                <input type="number" step="0.01" min="0" name="size_prices[{{ $size }}]" data-size="{{ $size }}"
                       value="{{ $sizePriceMap[$size] ?? $studioProduct->price }}"
                       class="flex-1 rounded-lg border-gray-300">
            </label>
        @empty
            <p class="text-sm text-gray-500 sm:col-span-2">Add sizes above to set prices.</p>
        @endforelse
    </div>
</div>

<details class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
    <summary class="cursor-pointer text-sm font-medium text-gray-700">Fallback print area (%)</summary>
    <p class="text-xs text-gray-500 mt-2 mb-3">Used only when a mockup does not have its own print box. Prefer drawing the box on each mockup below.</p>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs text-gray-500 mb-1">X</label>
            <input type="number" step="0.1" min="0" max="96" name="print_area_x" value="{{ old('print_area_x', $area['x']) }}" required class="w-full rounded-lg border-gray-300">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Y</label>
            <input type="number" step="0.1" min="0" max="96" name="print_area_y" value="{{ old('print_area_y', $area['y']) }}" required class="w-full rounded-lg border-gray-300">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Width</label>
            <input type="number" step="0.1" min="4" max="100" name="print_area_width" value="{{ old('print_area_width', $area['width']) }}" required class="w-full rounded-lg border-gray-300">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Height</label>
            <input type="number" step="0.1" min="4" max="100" name="print_area_height" value="{{ old('print_area_height', $area['height']) }}" required class="w-full rounded-lg border-gray-300">
        </div>
    </div>
</details>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Sort order</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $studioProduct->sort_order ?? 0) }}" class="w-full rounded-lg border-gray-300">
    </div>
    <div class="flex items-end pb-2">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $studioProduct->is_active))>
            Active in Create Your Own
        </label>
    </div>
</div>

<div>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Mockups</h2>
            <p class="text-sm text-gray-500 mt-1">Upload each garment photo, then draw the dashed print box where customers place their design.</p>
        </div>
        <button type="button" id="studio-add-mockup" class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Add mockup
        </button>
    </div>
    <div id="studio-mockup-list" class="space-y-4">
        @foreach($mockupRows as $index => $row)
            @include('admin.studio-products.partials.mockup-row', [
                'index' => $index,
                'row' => $row,
                'fallback' => $area,
            ])
        @endforeach
    </div>
</div>

<template id="studio-mockup-template">
    @include('admin.studio-products.partials.mockup-row', [
        'index' => '__INDEX__',
        'row' => [],
        'fallback' => $area,
    ])
</template>

<script>
(function () {
    const sizesInput = document.getElementById('studio-sizes-text');
    const wrap = document.getElementById('studio-size-prices');
    const fallback = document.getElementById('studio-fallback-price');
    if (!sizesInput || !wrap) return;

    function parseSizes(text) {
        return String(text || '').split(/[,|\n]/).map(function (s) { return s.trim(); }).filter(Boolean);
    }

    function currentValues() {
        const map = {};
        wrap.querySelectorAll('input[data-size]').forEach(function (input) {
            map[input.getAttribute('data-size')] = input.value;
        });
        return map;
    }

    function render() {
        const sizes = parseSizes(sizesInput.value);
        const existing = currentValues();
        const fallbackPrice = fallback && fallback.value !== '' ? fallback.value : '';
        if (!sizes.length) {
            wrap.innerHTML = '<p class="text-sm text-gray-500 sm:col-span-2">Add sizes above to set prices.</p>';
            return;
        }
        wrap.innerHTML = sizes.map(function (size) {
            const value = existing[size] != null && existing[size] !== '' ? existing[size] : fallbackPrice;
            const safe = size.replace(/"/g, '&quot;');
            return '<label class="flex items-center gap-3"><span class="w-14 text-sm font-semibold text-gray-800">' +
                size.replace(/</g, '') + '</span><input type="number" step="0.01" min="0" name="size_prices[' + safe +
                ']" data-size="' + safe + '" value="' + value + '" class="flex-1 rounded-lg border-gray-300"></label>';
        }).join('');
    }

    sizesInput.addEventListener('blur', render);
    sizesInput.addEventListener('change', render);
})();
</script>
