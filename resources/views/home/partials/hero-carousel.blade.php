@php
    $slides = $slides ?? [];
    $side = $side ?? 'left';
    $variant = $variant ?? 'mobile'; // mobile|desktop
    $autoplayMs = (int) ($autoplayMs ?? 5000);
    $isMerged = $side === 'merged';
    $isLarge = $side === 'left' || $isMerged;
    $carouselId = 'hero-carousel-' . $side . '-' . $variant;
    $singleClass = count($slides) <= 1 ? ' is-single' : '';
    $variantClass = $variant === 'desktop'
        ? 'hero-carousel--desktop'
        : ($isMerged ? 'hero-carousel--mobile-merged' : ('hero-carousel--mobile-' . $side));
@endphp
@if(count($slides) > 0)
<div class="hero-carousel {{ $variantClass }}{{ $singleClass }}"
     id="{{ $carouselId }}"
     data-hero-carousel
     data-hero-side="{{ $side }}"
     data-autoplay-ms="{{ $autoplayMs }}">
    <div class="hero-carousel__viewport" data-hero-viewport>
        @foreach ($slides as $index => $slide)
            @php
                $previewSide = $slide['_side'] ?? $side;
                $previewIndex = $slide['_side_index'] ?? $index;
                $slideIsLarge = ($previewSide === 'left') || ($isMerged && empty($slide['_side']));
            @endphp
            <div class="hero-carousel__slide {{ $index === 0 ? 'is-active' : '' }}" data-hero-index="{{ $index }}">
                <a href="{{ $slide['url'] }}" class="hero-carousel__card group">
                    <img src="{{ $slide['image'] }}"
                         alt="{{ $slide['alt'] ?? '' }}"
                         loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                         data-home-preview="hero.{{ $previewSide }}_slides.{{ $previewIndex }}.image">
                    <div class="hero-carousel__vignette" aria-hidden="true"></div>
                    <div class="hero-carousel__content {{ $slideIsLarge || $isMerged ? 'p-6 sm:p-8 lg:p-10' : 'p-5' }}">
                        @if (!empty($slide['eyebrow']))
                            <p class="text-white/80 text-sm font-semibold tracking-widest uppercase mb-2 inline-flex items-center gap-2"
                               data-home-preview="hero.{{ $previewSide }}_slides.{{ $previewIndex }}.eyebrow">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M9.5 3.2l1.15 3.1 3.15 1.15-3.15 1.15-1.15 3.1-1.15-3.1L5.2 7.45l3.15-1.15L9.5 3.2z"/>
                                </svg>
                                {{ $slide['eyebrow'] }}
                            </p>
                        @endif
                        @if ($variant === 'desktop' && $side === 'left')
                            <h2 class="hero-banner-title text-3xl sm:text-5xl lg:text-6xl text-white leading-tight"
                                data-home-preview="hero.{{ $previewSide }}_slides.{{ $previewIndex }}.title_html">{!! $slide['title_html'] !!}</h2>
                        @elseif ($variant === 'desktop')
                            <h2 class="hero-banner-title text-2xl sm:text-3xl text-white"
                                data-home-preview="hero.{{ $previewSide }}_slides.{{ $previewIndex }}.title_html">{!! $slide['title_html'] !!}</h2>
                        @else
                            <h2 class="hero-banner-title {{ $slide['title_size'] ?? 'text-2xl sm:text-3xl' }} text-white leading-tight"
                                data-home-preview="hero.{{ $previewSide }}_slides.{{ $previewIndex }}.title_html">{!! $slide['title_html'] !!}</h2>
                        @endif
                        @if (!empty($slide['description']) && ($slideIsLarge || $isMerged || $variant === 'mobile'))
                            <p class="mt-3 text-white/90 max-w-md text-sm sm:text-base"
                               data-home-preview="hero.{{ $previewSide }}_slides.{{ $previewIndex }}.description">{{ $slide['description'] }}</p>
                        @endif
                        <span class="btn-cta {{ ($slideIsLarge || $isMerged) ? 'mt-5' : 'mt-3 text-sm px-5 py-2' }} w-fit">{{ $slide['button_label'] ?? 'Shop Now' }}</span>
                    </div>
                </a>
            </div>
        @endforeach
        <div class="hero-carousel__progress" aria-hidden="true">
            <span class="hero-carousel__progress-bar" data-hero-progress></span>
        </div>
    </div>
    <div class="hero-carousel__dots" data-hero-dots role="tablist" aria-label="Hero banners">
        @foreach ($slides as $index => $slide)
            <button type="button"
                    class="hero-carousel__dot {{ $index === 0 ? 'is-active' : '' }}"
                    data-hero-dot="{{ $index }}"
                    aria-label="Go to {{ $slide['alt'] ?? ('slide ' . ($index + 1)) }}"
                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"></button>
        @endforeach
    </div>
</div>
@endif
