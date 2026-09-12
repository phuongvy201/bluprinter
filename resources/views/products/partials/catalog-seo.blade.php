@php
    $seo = $seo ?? [];
    $highlights = $seo['highlights'] ?? [];
    $icons = [
        'spark' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
        'filter' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 4h18M6 12h12M10 20h4"/>',
        'globe' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'gift' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
    ];
@endphp
<aside class="catalog-seo scroll-reveal" aria-label="About this collection">
    <div class="catalog-seo__head">
        @if (!empty($seo['eyebrow']))
            <p class="catalog-seo__eyebrow">{{ $seo['eyebrow'] }}</p>
        @endif
        @if (!empty($seo['title']))
            <h2 class="catalog-seo__title">{{ $seo['title'] }}</h2>
        @endif
        @if (!empty($seo['intro']))
            <p class="catalog-seo__intro">{{ $seo['intro'] }}</p>
        @endif
    </div>

    @if (count($highlights) > 0)
        <ul class="catalog-seo__grid">
            @foreach ($highlights as $item)
                @php
                    $tone = $item['tone'] ?? 'petrol';
                    $iconKey = $item['icon'] ?? 'spark';
                    $iconPath = $icons[$iconKey] ?? $icons['spark'];
                @endphp
                <li class="catalog-seo__card">
                    <div class="catalog-seo__icon catalog-seo__icon--{{ $tone }}" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $iconPath !!}</svg>
                    </div>
                    <h3 class="catalog-seo__card-title">{{ $item['title'] ?? '' }}</h3>
                    <p class="catalog-seo__card-text">{{ $item['text'] ?? '' }}</p>
                </li>
            @endforeach
        </ul>
    @elseif (!empty($seo['content']))
        <div class="catalog-seo__content">
            {!! $seo['content'] !!}
        </div>
    @endif
</aside>
