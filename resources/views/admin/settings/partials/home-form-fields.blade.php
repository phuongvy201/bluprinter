@php
    $settings = $settings ?? [];
    $compact = $compact ?? false;
    $fieldClass = $compact ? 'w-full rounded-lg border-gray-300 text-sm' : 'w-full rounded-xl border-gray-300';
    $fieldClassSm = $compact ? 'w-full rounded-md border-gray-300 text-xs' : 'w-full rounded-lg border-gray-300 text-sm';
    $boxClass = $compact ? 'p-3 rounded-lg border border-gray-200 bg-white space-y-3' : 'bg-white shadow-md rounded-2xl p-6 space-y-5';
    $heroSlides = old('hero.slides', $settings['hero']['slides'] ?? []);
    $sectionKeys = [
        'pick_a_gift' => 'Pick a Gift',
        'flash_sale' => 'Flash Sale',
        'top_picks' => 'Top Picks',
        'latest_collections' => 'Latest Collections',
        'new_products' => 'New Products',
        'customize_hero' => 'Customize Hero',
        'blog' => 'Blog Posts',
        'recently_viewed' => 'Recently Viewed',
        'why_choose' => 'Why Choose Bluprinter',
    ];
@endphp

<div class="{{ $boxClass }}" data-home-form-section="hero">
    <div>
        <h2 class="{{ $compact ? 'text-sm font-bold text-gray-900' : 'text-lg font-bold text-gray-900' }}">Hero banners</h2>
        @unless($compact)
            <p class="text-sm text-gray-500">Mobile: slideshow tự chuyển. URL trống → tự tìm danh mục.</p>
        @endunless
    </div>
    <div class="max-w-xs">
        <label class="block text-xs font-semibold text-gray-700 mb-1">Autoplay (ms)</label>
        <input type="number" min="2000" max="15000" step="500" name="hero[autoplay_ms]"
               value="{{ old('hero.autoplay_ms', $settings['hero']['autoplay_ms'] ?? 5000) }}"
               class="{{ $fieldClassSm }}" data-home-input="hero.autoplay_ms">
    </div>
    @for ($i = 0; $i < 6; $i++)
        @php $slide = $heroSlides[$i] ?? []; @endphp
        <div class="p-3 rounded-lg border border-gray-100 bg-gray-50 space-y-2" data-home-form-section="hero-slide-{{ $i }}">
            <h3 class="text-xs font-bold text-gray-800">Slide #{{ $i + 1 }}</h3>
            <div class="grid grid-cols-1 gap-2">
                @include('admin.settings.partials.home-image-field', [
                    'urlName' => "hero[slides][{$i}][image]",
                    'fileName' => "hero[slides][{$i}][image_file]",
                    'value' => $slide['image'] ?? '',
                    'previewKey' => "hero.slides.{$i}.image",
                    'placeholder' => 'Hero image URL',
                    'inputClass' => $fieldClassSm,
                ])
                <input type="text" name="hero[slides][{{ $i }}][url]" value="{{ $slide['url'] ?? '' }}"
                       class="{{ $fieldClassSm }}" placeholder="Link URL">
                <input type="text" name="hero[slides][{{ $i }}][category_keywords]"
                       value="{{ isset($slide['category_keywords']) ? (is_array($slide['category_keywords']) ? implode(', ', $slide['category_keywords']) : $slide['category_keywords']) : '' }}"
                       class="{{ $fieldClassSm }}" placeholder="Category keywords">
                <input type="text" name="hero[slides][{{ $i }}][eyebrow]" value="{{ $slide['eyebrow'] ?? '' }}"
                       class="{{ $fieldClassSm }}" placeholder="Eyebrow" data-home-input="hero.slides.{{ $i }}.eyebrow">
                <input type="text" name="hero[slides][{{ $i }}][title_html]" value="{{ $slide['title_html'] ?? '' }}"
                       class="{{ $fieldClassSm }}" placeholder="Title HTML" data-home-input="hero.slides.{{ $i }}.title_html">
                <textarea name="hero[slides][{{ $i }}][description]" rows="2" class="{{ $fieldClassSm }}"
                          placeholder="Description" data-home-input="hero.slides.{{ $i }}.description">{{ $slide['description'] ?? '' }}</textarea>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="hero[slides][{{ $i }}][overlay_from]" value="{{ $slide['overlay_from'] ?? '' }}"
                           class="{{ $fieldClassSm }}" placeholder="Overlay from" data-home-input="hero.slides.{{ $i }}.overlay_from">
                    <input type="text" name="hero[slides][{{ $i }}][overlay_to]" value="{{ $slide['overlay_to'] ?? '' }}"
                           class="{{ $fieldClassSm }}" placeholder="Overlay to" data-home-input="hero.slides.{{ $i }}.overlay_to">
                </div>
                <input type="hidden" name="hero[slides][{{ $i }}][alt]" value="{{ $slide['alt'] ?? '' }}">
                <input type="hidden" name="hero[slides][{{ $i }}][button_label]" value="{{ $slide['button_label'] ?? 'Shop Now' }}">
                <input type="hidden" name="hero[slides][{{ $i }}][title_size]" value="{{ $slide['title_size'] ?? 'text-2xl sm:text-3xl' }}">
            </div>
        </div>
    @endfor
</div>

@foreach ($sectionKeys as $key => $label)
    @php $section = old("sections.$key", $settings['sections'][$key] ?? []); @endphp
    <div class="{{ $compact ? 'p-3 rounded-lg border border-gray-200 bg-white space-y-2' : 'bg-white shadow-md rounded-2xl p-6 space-y-4' }}"
         data-home-form-section="{{ $key }}">
        <div class="flex items-center justify-between gap-2">
            <h2 class="{{ $compact ? 'text-sm font-bold text-gray-900' : 'text-lg font-bold text-gray-900' }}">{{ $label }}</h2>
            <label class="inline-flex items-center gap-1 text-xs text-gray-700">
                <input type="hidden" name="sections[{{ $key }}][enabled]" value="0">
                <input type="checkbox" name="sections[{{ $key }}][enabled]" value="1"
                       @checked($section['enabled'] ?? true) class="rounded border-gray-300 text-blue-600">
                Hiện
            </label>
        </div>
        <input type="text" name="sections[{{ $key }}][background]" value="{{ $section['background'] ?? '#ffffff' }}"
               class="{{ $fieldClassSm }}" placeholder="Background #hex"
               data-home-input="sections.{{ $key }}.background">
        @if ($key === 'why_choose')
            <input type="number" min="2000" max="15000" name="sections[why_choose][autoplay_ms]"
                   value="{{ $section['autoplay_ms'] ?? 4500 }}" class="{{ $fieldClassSm }}" placeholder="Autoplay ms">
        @endif
        @if ($key === 'pick_a_gift')
            @php $giftItems = old('sections.pick_a_gift.items', $section['items'] ?? []); @endphp
            <p class="text-xs text-gray-500">Tối đa 12 mục — hình tròn + nhãn + link (không dùng collection)</p>
            @for ($gi = 0; $gi < 12; $gi++)
                @php $giftItem = $giftItems[$gi] ?? []; @endphp
                <div class="p-2 rounded-lg bg-gray-50 space-y-2 border border-gray-100" data-home-form-section="pick-a-gift-{{ $gi }}">
                    <h3 class="text-xs font-bold text-gray-800">Gift #{{ $gi + 1 }}</h3>
                    @include('admin.settings.partials.home-image-field', [
                        'urlName' => "sections[pick_a_gift][items][{$gi}][image]",
                        'fileName' => "sections[pick_a_gift][items][{$gi}][image_file]",
                        'value' => $giftItem['image'] ?? '',
                        'previewKey' => "sections.pick_a_gift.items.{$gi}.image",
                        'placeholder' => 'Hình tròn (URL)',
                        'inputClass' => $fieldClassSm,
                    ])
                    <input type="text" name="sections[pick_a_gift][items][{{ $gi }}][url]" value="{{ $giftItem['url'] ?? '' }}"
                           class="{{ $fieldClassSm }}" placeholder="Link URL">
                    <input type="text" name="sections[pick_a_gift][items][{{ $gi }}][label]" value="{{ $giftItem['label'] ?? '' }}"
                           class="{{ $fieldClassSm }}" placeholder="Nhãn hiển thị (vd: FOR HER)"
                           data-home-input="sections.pick_a_gift.items.{{ $gi }}.label">
                </div>
            @endfor
        @endif
        @if ($key === 'top_picks')
            @php
                $featuredBanner = $section['banners']['featured'] ?? [];
                $promoBanner = $section['banners']['promo'] ?? [];
            @endphp
            <div class="p-2 rounded-lg bg-gray-50 space-y-2 border border-gray-100">
                <p class="text-xs font-bold text-gray-800">Banner dọc (cột trái)</p>
                @include('admin.settings.partials.home-image-field', [
                    'urlName' => 'sections[top_picks][banners][featured][image]',
                    'fileName' => 'sections[top_picks][banners][featured][image_file]',
                    'value' => $featuredBanner['image'] ?? '',
                    'previewKey' => 'sections.top_picks.banners.featured.image',
                    'placeholder' => 'Featured banner image URL',
                    'inputClass' => $fieldClassSm,
                ])
                <input type="text" name="sections[top_picks][banners][featured][url]" value="{{ $featuredBanner['url'] ?? '' }}"
                       class="{{ $fieldClassSm }}" placeholder="Link URL (vd: /products hoặc https://...)">
                <input type="text" name="sections[top_picks][banners][featured][title]" value="{{ $featuredBanner['title'] ?? '' }}"
                       class="{{ $fieldClassSm }}" placeholder="Tiêu đề banner" data-home-input="sections.top_picks.banners.featured.title">
                <input type="text" name="sections[top_picks][banners][featured][subtitle]" value="{{ $featuredBanner['subtitle'] ?? '' }}"
                       class="{{ $fieldClassSm }}" placeholder="Mô tả ngắn" data-home-input="sections.top_picks.banners.featured.subtitle">
            </div>
            <div class="p-2 rounded-lg bg-gray-50 space-y-2 border border-gray-100">
                <p class="text-xs font-bold text-gray-800">Banner ngang (promo)</p>
                @include('admin.settings.partials.home-image-field', [
                    'urlName' => 'sections[top_picks][banners][promo][image]',
                    'fileName' => 'sections[top_picks][banners][promo][image_file]',
                    'value' => $promoBanner['image'] ?? '',
                    'previewKey' => 'sections.top_picks.banners.promo.image',
                    'placeholder' => 'Promo banner image URL',
                    'inputClass' => $fieldClassSm,
                ])
                <input type="text" name="sections[top_picks][banners][promo][url]" value="{{ $promoBanner['url'] ?? '' }}"
                       class="{{ $fieldClassSm }}" placeholder="Link URL">
                <input type="text" name="sections[top_picks][banners][promo][title]" value="{{ $promoBanner['title'] ?? '' }}"
                       class="{{ $fieldClassSm }}" placeholder="Tiêu đề banner" data-home-input="sections.top_picks.banners.promo.title">
                <input type="text" name="sections[top_picks][banners][promo][subtitle]" value="{{ $promoBanner['subtitle'] ?? '' }}"
                       class="{{ $fieldClassSm }}" placeholder="Mô tả ngắn" data-home-input="sections.top_picks.banners.promo.subtitle">
                <input type="text" name="sections[top_picks][banners][promo][tag]" value="{{ $promoBanner['tag'] ?? '' }}"
                       class="{{ $fieldClassSm }}" placeholder="Tag (vd: End in Dec 31 2026)" data-home-input="sections.top_picks.banners.promo.tag">
            </div>
        @endif
        @if ($key === 'customize_hero')
            <textarea name="sections[customize_hero][title]" rows="2" class="{{ $fieldClassSm }}"
                      data-home-input="sections.customize_hero.title">{{ $section['title'] ?? '' }}</textarea>
            @include('admin.settings.partials.home-image-field', [
                'urlName' => 'sections[customize_hero][shirt_image]',
                'fileName' => 'sections[customize_hero][shirt_image_file]',
                'value' => $section['shirt_image'] ?? '',
                'previewKey' => 'sections.customize_hero.shirt_image',
                'placeholder' => 'Shirt image URL',
                'inputClass' => $fieldClassSm,
            ])
            @include('admin.settings.partials.home-image-field', [
                'urlName' => 'sections[customize_hero][design_image]',
                'fileName' => 'sections[customize_hero][design_image_file]',
                'value' => $section['design_image'] ?? '',
                'previewKey' => 'sections.customize_hero.design_image',
                'placeholder' => 'Design image URL',
                'inputClass' => $fieldClassSm,
            ])
            <input type="text" name="sections[customize_hero][upload_label]" value="{{ $section['upload_label'] ?? '' }}"
                   class="{{ $fieldClassSm }}" placeholder="Upload label" data-home-input="sections.customize_hero.upload_label">
            <input type="text" name="sections[customize_hero][upload_url]" value="{{ $section['upload_url'] ?? '' }}"
                   class="{{ $fieldClassSm }}" placeholder="Upload URL">
            <textarea name="sections[customize_hero][float_images_text]" rows="3" class="{{ $fieldClassSm }}"
                      placeholder="Float image URLs (mỗi dòng một URL)">{{ old('sections.customize_hero.float_images_text', implode("\n", $section['float_images'] ?? [])) }}</textarea>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Upload float images (S3)</label>
                <input type="file" name="sections[customize_hero][float_image_files][]" accept="image/jpeg,image/png,image/webp,image/gif" multiple
                       class="{{ $fieldClassSm }} file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700">
                <p class="text-[10px] text-gray-400 mt-1">Chọn nhiều ảnh — sẽ thêm vào danh sách float hiện có</p>
            </div>
        @else
            <input type="text" name="sections[{{ $key }}][eyebrow]" value="{{ $section['eyebrow'] ?? '' }}"
                   class="{{ $fieldClassSm }}" placeholder="Eyebrow" data-home-input="sections.{{ $key }}.eyebrow">
            <input type="text" name="sections[{{ $key }}][title_html]" value="{{ $section['title_html'] ?? '' }}"
                   class="{{ $fieldClassSm }}" placeholder="Title HTML" data-home-input="sections.{{ $key }}.title_html">
            <textarea name="sections[{{ $key }}][subtitle]" rows="2" class="{{ $fieldClassSm }}"
                      placeholder="Subtitle" data-home-input="sections.{{ $key }}.subtitle">{{ $section['subtitle'] ?? '' }}</textarea>
        @endif
        @if ($key === 'why_choose')
            @php $features = old('sections.why_choose.features', $section['features'] ?? []); @endphp
            @for ($fi = 0; $fi < 4; $fi++)
                @php $feature = $features[$fi] ?? []; @endphp
                <div class="space-y-1 p-2 rounded bg-gray-50">
                    <input type="text" name="sections[why_choose][features][{{ $fi }}][title]" value="{{ $feature['title'] ?? '' }}"
                           class="{{ $fieldClassSm }}" placeholder="Feature title" data-home-input="sections.why_choose.features.{{ $fi }}.title">
                    <input type="text" name="sections[why_choose][features][{{ $fi }}][description]" value="{{ $feature['description'] ?? '' }}"
                           class="{{ $fieldClassSm }}" placeholder="Feature description" data-home-input="sections.why_choose.features.{{ $fi }}.description">
                    <select name="sections[why_choose][features][{{ $fi }}][accent]" class="{{ $fieldClassSm }}">
                        @foreach (['petrol', 'cta', 'orange'] as $accent)
                            <option value="{{ $accent }}" @selected(($feature['accent'] ?? 'petrol') === $accent)>{{ ucfirst($accent) }}</option>
                        @endforeach
                    </select>
                </div>
            @endfor
        @endif
    </div>
@endforeach
