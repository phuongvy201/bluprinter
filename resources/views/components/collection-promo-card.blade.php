@props([
    'collection' => null,
    'endsAt' => null,
    'fallbackUrl' => null,
    'fallbackTitle' => 'Shop all personalized gifts',
    'fallbackSubtitle' => 'Discover unique designs made for you',
    'layout' => 'default',
    'bannerImage' => null,
    'bannerUrl' => null,
    'bannerTitle' => null,
    'bannerSubtitle' => null,
    'bannerTag' => null,
    'previewImage' => null,
    'previewTitle' => null,
    'previewSubtitle' => null,
    'previewTag' => null,
])

@php
    $url = trim((string) ($bannerUrl ?? ''));
    if ($url === '') {
        $url = $collection
            ? route('collections.show', $collection->slug)
            : ($fallbackUrl ?? route('products.index'));
    }

    $baseTitle = $collection?->name ?? $fallbackTitle;
    $baseSubtitle = $collection?->description
        ? Str::limit(strip_tags($collection->description), 80)
        : $fallbackSubtitle;

    $customTitle = trim((string) ($bannerTitle ?? ''));
    $customSubtitle = trim((string) ($bannerSubtitle ?? ''));
    $displayTitle = $customTitle !== '' ? $customTitle : 'Explore ' . $baseTitle;
    $displaySubtitle = $customSubtitle !== '' ? $customSubtitle : $baseSubtitle;

    $image = trim((string) ($bannerImage ?? ''));
    if ($image === '') {
        $image = $collection?->image;
    }

    $customTag = trim((string) ($bannerTag ?? ''));
    $endLabel = $customTag !== ''
        ? $customTag
        : ($endsAt ? 'End in ' . $endsAt->format('M d Y') : null);

    $cardClass = 'collection-promo-card' . ($layout === 'tall' ? ' collection-promo-card--tall' : '');
@endphp

<a href="{{ $url }}" class="{{ $cardClass }}" aria-label="View {{ strip_tags($displayTitle) }}">
    @if ($image)
        <img class="collection-promo-card__bg"
             src="{{ $image }}"
             alt=""
             loading="lazy"
             @if($previewImage) data-home-preview="{{ $previewImage }}" @endif>
    @endif
    <div class="collection-promo-card__overlay" aria-hidden="true"></div>

    @if ($endLabel)
        <span class="collection-promo-card__tag" @if($previewTag) data-home-preview="{{ $previewTag }}" @endif>{{ $endLabel }}</span>
    @endif

    <div class="collection-promo-card__content">
        <h3 class="collection-promo-card__title" @if($previewTitle) data-home-preview="{{ $previewTitle }}" @endif>{{ $displayTitle }}</h3>
        @if ($displaySubtitle)
            <p class="collection-promo-card__subtitle" @if($previewSubtitle) data-home-preview="{{ $previewSubtitle }}" @endif>{{ $displaySubtitle }}</p>
        @endif
        <span class="collection-promo-card__cta">
            View more
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </span>
    </div>
</a>
