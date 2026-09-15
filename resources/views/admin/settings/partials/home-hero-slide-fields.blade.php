@php
    $side = $side ?? 'left';
    $index = $index ?? 0;
    $slide = $slide ?? [];
    $fieldClassSm = $fieldClassSm ?? 'w-full rounded-lg border-gray-300 text-sm';
    $indexKey = (string) $index;
    $labelNumber = is_numeric($index) ? ((int) $index + 1) : '#';
    $keywords = isset($slide['category_keywords'])
        ? (is_array($slide['category_keywords']) ? implode(', ', $slide['category_keywords']) : $slide['category_keywords'])
        : '';
@endphp
<div class="p-3 rounded-lg border border-gray-100 bg-gray-50 space-y-2" data-hero-slide-row data-hero-side="{{ $side }}">
    <div class="flex items-center justify-between gap-2">
        <h3 class="text-xs font-bold text-gray-800" data-hero-slide-label>{{ ucfirst($side) }} #{{ $labelNumber }}</h3>
        <button type="button" class="text-xs font-semibold text-red-600 hover:text-red-700" data-hero-remove-slide>Remove</button>
    </div>
    <div class="grid grid-cols-1 gap-2">
        @include('admin.settings.partials.home-image-field', [
            'urlName' => "hero[{$side}_slides][{$indexKey}][image]",
            'fileName' => "hero[{$side}_slides][{$indexKey}][image_file]",
            'value' => $slide['image'] ?? '',
            'previewKey' => "hero.{$side}_slides.{$indexKey}.image",
            'placeholder' => 'Hero image URL',
            'inputClass' => $fieldClassSm,
        ])
        <input type="text" name="hero[{{ $side }}_slides][{{ $indexKey }}][url]" value="{{ $slide['url'] ?? '' }}"
               class="{{ $fieldClassSm }}" placeholder="Link URL">
        <input type="text" name="hero[{{ $side }}_slides][{{ $indexKey }}][category_keywords]"
               value="{{ $keywords }}"
               class="{{ $fieldClassSm }}" placeholder="Category keywords">
        <input type="text" name="hero[{{ $side }}_slides][{{ $indexKey }}][eyebrow]" value="{{ $slide['eyebrow'] ?? '' }}"
               class="{{ $fieldClassSm }}" placeholder="Eyebrow" data-home-input="hero.{{ $side }}_slides.{{ $indexKey }}.eyebrow">
        <input type="text" name="hero[{{ $side }}_slides][{{ $indexKey }}][title_html]" value="{{ $slide['title_html'] ?? '' }}"
               class="{{ $fieldClassSm }}" placeholder="Title HTML" data-home-input="hero.{{ $side }}_slides.{{ $indexKey }}.title_html">
        <textarea name="hero[{{ $side }}_slides][{{ $indexKey }}][description]" rows="2" class="{{ $fieldClassSm }}"
                  placeholder="Description" data-home-input="hero.{{ $side }}_slides.{{ $indexKey }}.description">{{ $slide['description'] ?? '' }}</textarea>
        <input type="hidden" name="hero[{{ $side }}_slides][{{ $indexKey }}][alt]" value="{{ $slide['alt'] ?? '' }}">
        <input type="hidden" name="hero[{{ $side }}_slides][{{ $indexKey }}][button_label]" value="{{ $slide['button_label'] ?? 'Shop Now' }}">
        <input type="hidden" name="hero[{{ $side }}_slides][{{ $indexKey }}][title_size]" value="{{ $slide['title_size'] ?? 'text-2xl sm:text-3xl' }}">
    </div>
</div>
