@php
    $promo = $promo ?? [];
    $url = $promo['url'] ?? route('studio.index');
@endphp
<a href="{{ $url }}" class="catalog-promo scroll-reveal" style="background: {{ $promo['background'] ?? 'linear-gradient(135deg, #005366, #003d4d)' }};">
    @if (!empty($promo['image']))
        <img class="catalog-promo__image" src="{{ $promo['image'] }}" alt="" loading="lazy">
    @endif
    <div class="catalog-promo__overlay" aria-hidden="true"></div>
    <div class="catalog-promo__content">
        @if (!empty($promo['title']))
            <h2 class="catalog-promo__title">{{ $promo['title'] }}</h2>
        @endif
        @if (!empty($promo['subtitle']))
            <p class="catalog-promo__subtitle">{{ $promo['subtitle'] }}</p>
        @endif
        @if (!empty($promo['cta_label']))
            <span class="catalog-promo__cta">{{ $promo['cta_label'] }} →</span>
        @endif
    </div>
</a>
