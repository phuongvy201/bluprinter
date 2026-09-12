@props([
    'averageRating' => 4.8,
    'happyCustomersLabel' => '50,000+',
    'pages' => [],
    'viewAllUrl' => null,
])

@php
    $viewAllUrl = $viewAllUrl ?? route('products.index');
    $reviewPages = collect($pages)->filter(fn ($page) => count($page) > 0)->values();
    if ($reviewPages->isEmpty()) {
        return;
    }
    $totalPages = $reviewPages->count();
    $allItems = $reviewPages->flatten(1)->values();
@endphp

<section class="customer-reviews py-10 sm:py-12 bg-white border-t border-gray-100" aria-labelledby="customer-reviews-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <header class="customer-reviews__header">
            <div class="customer-reviews__score" aria-label="{{ number_format($averageRating, 1) }} out of 5 stars">
                <span class="customer-reviews__stars" aria-hidden="true">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </span>
                <span class="customer-reviews__score-value">{{ number_format($averageRating, 1) }}/5</span>
            </div>

            <h2 id="customer-reviews-heading" class="customer-reviews__title">
                Over {{ $happyCustomersLabel }} Happy Customers
            </h2>

            <a href="{{ $viewAllUrl }}" class="customer-reviews__view-all btn-outline-petrol">
                View all reviews
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </header>

        {{-- Mobile: single-card carousel --}}
        <div class="customer-reviews__mobile" id="customerReviewsMobile">
            <div class="customer-reviews__carousel" id="customerReviewsCarousel">
                <div class="customer-reviews__track" id="customerReviewsTrack">
                    @foreach ($allItems as $item)
                        @php
                            $firstName = strtok($item['name'], ' ') ?: $item['name'];
                            $cardRating = (int) ($item['rating'] ?? 5);
                        @endphp
                        <article class="customer-reviews__slide">
                            <div class="customer-reviews__mobile-card">
                                <div class="customer-reviews__mobile-top">
                                    <a href="{{ $item['url'] }}" class="customer-reviews__mobile-media" tabindex="-1" aria-hidden="true">
                                        @if (!empty($item['image']))
                                            <img src="{{ $item['image'] }}" alt="" loading="lazy">
                                        @else
                                            <div class="customer-reviews__media-placeholder">
                                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </a>

                                    <div class="customer-reviews__mobile-text">
                                        <p class="customer-reviews__mobile-headline">{{ $item['headline'] }}</p>
                                        <span class="customer-reviews__card-stars" aria-label="{{ $cardRating }} out of 5 stars">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <svg viewBox="0 0 20 20" fill="{{ $i <= $cardRating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </span>
                                    </div>
                                </div>

                                <div class="customer-reviews__divider" aria-hidden="true"></div>

                                <div class="customer-reviews__author customer-reviews__author--mobile">
                                    <span class="customer-reviews__avatar" aria-hidden="true">{{ $item['initials'] }}</span>
                                    <div class="customer-reviews__author-meta">
                                        <span class="customer-reviews__name">{{ $firstName }}</span>
                                        @if (!empty($item['verified']))
                                            <span class="customer-reviews__verified customer-reviews__verified--mobile">
                                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Verified Buyer
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            @if ($allItems->count() > 1)
                <div class="customer-reviews__dots" id="customerReviewsDots" role="tablist" aria-label="Review slides">
                    @foreach ($allItems as $dotIndex => $item)
                        <button type="button"
                                class="customer-reviews__dot {{ $dotIndex === 0 ? 'is-active' : '' }}"
                                data-index="{{ $dotIndex }}"
                                role="tab"
                                aria-label="Review {{ $dotIndex + 1 }}"
                                aria-selected="{{ $dotIndex === 0 ? 'true' : 'false' }}"></button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Desktop: 3-column paginated grid --}}
        <div class="customer-reviews__desktop" id="customerReviewsDesktop">
            <div class="customer-reviews__panels" id="customerReviewsPanels">
                @foreach ($reviewPages as $pageIndex => $pageItems)
                    <div class="customer-reviews__page {{ $pageIndex === 0 ? 'is-active' : '' }}"
                         data-page="{{ $pageIndex + 1 }}"
                         @if($pageIndex !== 0) hidden @endif>
                        <div class="customer-reviews__grid">
                            @foreach ($pageItems as $item)
                                <article class="customer-reviews__card">
                                    <a href="{{ $item['url'] }}" class="customer-reviews__media" tabindex="-1" aria-hidden="true">
                                        @if (!empty($item['image']))
                                            <img src="{{ $item['image'] }}" alt="" loading="lazy">
                                        @else
                                            <div class="customer-reviews__media-placeholder">
                                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </a>

                                    <div class="customer-reviews__content">
                                        <p class="customer-reviews__headline">{{ $item['headline'] }}</p>
                                        @if (!empty($item['body']))
                                            <p class="customer-reviews__body">"{{ $item['body'] }}"</p>
                                        @endif
                                    </div>

                                    <div class="customer-reviews__author">
                                        <span class="customer-reviews__avatar" aria-hidden="true">{{ $item['initials'] }}</span>
                                        <div class="customer-reviews__author-meta">
                                            <span class="customer-reviews__name">{{ $item['name'] }}</span>
                                            @if (!empty($item['verified']))
                                                <span class="customer-reviews__verified">
                                                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Verified Buyer
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($totalPages > 1)
                <nav class="customer-reviews__pager" aria-label="Review pages">
                    <button type="button"
                            id="customerReviewsPrev"
                            class="customer-reviews__pager-btn"
                            aria-label="Previous reviews"
                            disabled>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <span class="customer-reviews__pager-status" id="customerReviewsStatus">1/{{ $totalPages }}</span>
                    <button type="button"
                            id="customerReviewsNext"
                            class="customer-reviews__pager-btn"
                            aria-label="Next reviews"
                            {{ $totalPages <= 1 ? 'disabled' : '' }}>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </nav>
            @endif
        </div>
    </div>
</section>
