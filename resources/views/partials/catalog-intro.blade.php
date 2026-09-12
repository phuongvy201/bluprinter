@php
    $introId = $introId ?? 'catalog-intro';
    $introText = trim(strip_tags($text ?? ''));
@endphp

@if ($introText !== '')
    <div class="catalog-intro" data-catalog-intro-wrap>
        <p class="catalog-intro__text" id="{{ $introId }}" data-catalog-intro>{{ $introText }}</p>
        <button type="button"
                class="catalog-intro__toggle"
                data-catalog-intro-toggle
                aria-expanded="false"
                aria-controls="{{ $introId }}"
                hidden>
            Read more
        </button>
    </div>
@endif
