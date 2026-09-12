{{-- Reviews below gallery — expects $product, optional $shopReviews --}}
@php
    $averageRating = $product->getAverageRating();
    $totalReviews = $product->getTotalReviews();
    $itemReviews = $product->approvedReviews;
    $shop = $product->shop;
    $shopName = $shop?->name ?? 'this shop';
    $shopSlug = $shop?->shop_slug ?? '';
    $shopUrl = $shopSlug ? route('shops.show', $shopSlug) : '#';
    $shopReviews = $shopReviews ?? collect();
    $shopReviewsAverage = $shopReviewsAverage ?? 0;
    $shopReviewsTotal = $shopReviewsTotal ?? 0;
    $shopFeaturedReviews = $shopReviews->where('product_id', '!=', $product->id)->take(12);
    $featuredPages = $shopFeaturedReviews->chunk(3);
    $featuredTotalPages = max($featuredPages->count(), 1);

    $normalizeMediaUrl = static function ($item) {
        if (is_string($item) && $item !== '') {
            return $item;
        }
        if (is_array($item)) {
            $url = $item['url'] ?? $item['path'] ?? (reset($item) ?: null);
            return is_string($url) && $url !== '' ? $url : null;
        }
        return null;
    };

    $productImageUrl = static function ($productModel) use ($normalizeMediaUrl) {
        if (!$productModel) {
            return null;
        }
        foreach ($productModel->getEffectiveMedia() as $mediaItem) {
            $url = $normalizeMediaUrl($mediaItem);
            if ($url && !preg_match('/\.(mp4|mov|avi|webm)(\?|$)/i', $url)) {
                return $url;
            }
        }
        return null;
    };

    $extractReviewImageUrls = static function ($reviews): array {
        $urls = [];
        $seen = [];

        foreach ($reviews as $review) {
            $images = $review->images ?? [];
            if (!is_array($images)) {
                continue;
            }

            foreach ($images as $image) {
                if (is_string($image) && $image !== '' && !isset($seen[$image])) {
                    $urls[] = $image;
                    $seen[$image] = true;
                }
            }
        }

        return $urls;
    };

    $itemReviewPhotoUrls = $extractReviewImageUrls(
        \App\Models\Review::query()
            ->approved()
            ->where('product_id', $product->id)
            ->orderByDesc('created_at')
            ->get(['id', 'images'])
    );

    $shopReviewPhotoUrls = [];
    if ($product->shop_id) {
        $shopReviewPhotoUrls = $extractReviewImageUrls(
            \App\Models\Review::query()
                ->approved()
                ->whereHas('product', function ($query) use ($product) {
                    $query->where('shop_id', $product->shop_id);
                })
                ->orderByDesc('created_at')
                ->get(['id', 'images'])
        );
    }

    $reviewHeadline = static function (?string $text, int $wordLimit = 8): ?string {
        if (!$text) {
            return null;
        }
        $words = preg_split('/\s+/', trim($text));
        if (!$words) {
            return null;
        }
        return count($words) > $wordLimit
            ? implode(' ', array_slice($words, 0, $wordLimit)) . '...'
            : $text;
    };
@endphp

<div class="product-show-gallery-reviews" id="productShowGalleryReviews" aria-label="Product reviews">
    <section class="product-show-gallery-reviews__card">
        <div class="product-show-gallery-reviews__top">
            <h2 class="product-show-gallery-reviews__heading">Reviews</h2>
            <button
                type="button"
                class="product-show-gallery-reviews__write-btn"
                onclick="document.getElementById('write-review-panel')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })"
            >
                Write a Review
            </button>
        </div>

        <div class="product-show-gallery-reviews__tabs" role="tablist" aria-label="Review views">
            <button
                type="button"
                class="product-show-gallery-reviews__tab product-show-gallery-reviews__tab--active"
                role="tab"
                id="gallery-reviews-tab-item"
                aria-selected="true"
                aria-controls="gallery-reviews-panel-item"
                data-review-tab="item"
            >
                Reviews for this item
            </button>
            <button
                type="button"
                class="product-show-gallery-reviews__tab"
                role="tab"
                id="gallery-reviews-tab-shop"
                aria-selected="false"
                aria-controls="gallery-reviews-panel-shop"
                data-review-tab="shop"
            >
                Reviews for this shop
            </button>
        </div>

        {{-- Tab: product reviews --}}
        <div
            class="product-show-gallery-reviews__panel"
            id="gallery-reviews-panel-item"
            role="tabpanel"
            aria-labelledby="gallery-reviews-tab-item"
            data-review-panel="item"
        >
            <header class="product-show-gallery-reviews__panel-header">
                <h3 class="product-show-gallery-reviews__title">
                    Reviews for this item ({{ $totalReviews }})
                </h3>
                @if($totalReviews > 0)
                    <div class="product-show-gallery-reviews__summary" aria-label="{{ number_format($averageRating, 1) }} out of 5 stars">
                        <span class="product-show-gallery-reviews__stars" aria-hidden="true">
                            @for($i = 1; $i <= 5; $i++)
                                <svg viewBox="0 0 20 20" fill="{{ $i <= round($averageRating) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </span>
                        <span class="product-show-gallery-reviews__score">{{ number_format($averageRating, 1) }}/5</span>
                        <span class="product-show-gallery-reviews__count">({{ $totalReviews }})</span>
                    </div>
                @endif
            </header>

            @if($itemReviews->isNotEmpty())
                <div class="product-show-gallery-reviews__list">
                    @foreach($itemReviews as $review)
                        @php
                            $headline = $reviewHeadline($review->review_text);
                            $reviewImages = $review->images ?? [];
                            $thumbUrl = $reviewImages[0] ?? null;
                            $thumbPhotoIndex = $thumbUrl ? array_search($thumbUrl, $itemReviewPhotoUrls, true) : false;
                            $authorInitial = Str::upper(Str::substr($review->display_name, 0, 1));
                        @endphp
                        <article class="product-show-gallery-reviews__item">
                            <div class="product-show-gallery-reviews__item-top">
                                <div class="product-show-gallery-reviews__item-meta">
                                    <span class="product-show-gallery-reviews__stars product-show-gallery-reviews__stars--sm" aria-label="{{ $review->rating }} out of 5 stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg viewBox="0 0 20 20" fill="{{ $i <= $review->rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </span>
                                    @if($review->is_verified_purchase)
                                        <span class="product-show-gallery-reviews__verified">Verified</span>
                                    @endif
                                </div>
                                <div class="product-show-gallery-reviews__author-block">
                                    <span class="product-show-gallery-reviews__author-name">{{ $review->display_name }}</span>
                                    <time class="product-show-gallery-reviews__date" datetime="{{ $review->created_at->toDateString() }}">
                                        {{ $review->created_at->format('M j, Y') }}
                                    </time>
                                </div>
                            </div>

                            <div class="product-show-gallery-reviews__item-body">
                                <div class="product-show-gallery-reviews__item-text">
                                    @if($headline)
                                        <h4 class="product-show-gallery-reviews__item-title">{{ $headline }}</h4>
                                    @endif
                                    @if($review->review_text)
                                        <p class="product-show-gallery-reviews__item-copy">{{ $review->review_text }}</p>
                                    @endif
                                </div>
                                @if($thumbUrl)
                                    <button
                                        type="button"
                                        class="product-show-gallery-reviews__item-thumb"
                                        aria-label="View review photo"
                                        onclick="openReviewPhotoLightbox('item', {{ $thumbPhotoIndex !== false ? $thumbPhotoIndex : 0 }})"
                                    >
                                        <img src="{{ $thumbUrl }}" alt="" loading="lazy">
                                    </button>
                                @else
                                    <div class="product-show-gallery-reviews__item-avatar" aria-hidden="true">{{ $authorInitial }}</div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if(count($itemReviewPhotoUrls) > 0)
                    <div class="product-show-gallery-reviews__photos">
                        <h4 class="product-show-gallery-reviews__photos-title">Photos from reviews</h4>
                        <div class="product-show-gallery-reviews__photos-grid">
                            @foreach($itemReviewPhotoUrls as $photoIndex => $photoUrl)
                                <button
                                    type="button"
                                    class="product-show-gallery-reviews__photo"
                                    onclick="openReviewPhotoLightbox('item', {{ $photoIndex }})"
                                    aria-label="View review photo {{ $photoIndex + 1 }}"
                                >
                                    <img src="{{ $photoUrl }}" alt="" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <p class="product-show-gallery-reviews__empty">No reviews yet for this item.</p>
            @endif

            <div class="product-show-gallery-reviews__write" id="write-review-panel" aria-labelledby="write-review-heading">
                <h4 id="write-review-heading" class="product-show-gallery-reviews__write-title">Write a Review</h4>
                <p class="product-show-gallery-reviews__write-text">
                    You can submit a review after completing an order that includes this product.
                </p>
            </div>
        </div>

        {{-- Tab: shop reviews --}}
        <div
            class="product-show-gallery-reviews__panel"
            id="gallery-reviews-panel-shop"
            role="tabpanel"
            aria-labelledby="gallery-reviews-tab-shop"
            data-review-panel="shop"
            hidden
        >
            <header class="product-show-gallery-reviews__panel-header">
                <div class="product-show-gallery-reviews__panel-header-row">
                    <h3 class="product-show-gallery-reviews__title">
                        Reviews for {{ $shopName }} ({{ $shopReviewsTotal }})
                    </h3>
                    <div class="product-show-gallery-reviews__featured-links">
                        <a href="{{ $shopUrl }}#reviews" class="product-show-gallery-reviews__link">All shop reviews</a>
                        <a href="{{ $shopUrl }}" class="product-show-gallery-reviews__link">Visit shop</a>
                    </div>
                </div>
                @if($shopReviewsTotal > 0)
                    <div class="product-show-gallery-reviews__summary" aria-label="{{ number_format($shopReviewsAverage, 1) }} out of 5 stars">
                        <span class="product-show-gallery-reviews__stars" aria-hidden="true">
                            @for($i = 1; $i <= 5; $i++)
                                <svg viewBox="0 0 20 20" fill="{{ $i <= round($shopReviewsAverage) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </span>
                        <span class="product-show-gallery-reviews__score">{{ number_format($shopReviewsAverage, 1) }}/5</span>
                        <span class="product-show-gallery-reviews__count">({{ $shopReviewsTotal }})</span>
                    </div>
                @endif
            </header>

            @if($shopReviews->isNotEmpty())
                <div class="product-show-gallery-reviews__list">
                    @foreach($shopReviews as $shopReview)
                        @php
                            $shopProduct = $shopReview->product;
                            $shopProductUrl = $shopProduct ? route('products.show', $shopProduct->slug) : '#';
                            $headline = $reviewHeadline($shopReview->review_text);
                            $shopReviewImages = $shopReview->images ?? [];
                            $thumbUrl = $shopReviewImages[0] ?? null;
                            $shopThumbPhotoIndex = $thumbUrl ? array_search($thumbUrl, $shopReviewPhotoUrls, true) : false;
                            $authorInitial = Str::upper(Str::substr($shopReview->display_name, 0, 1));
                        @endphp
                        <article class="product-show-gallery-reviews__item">
                            <div class="product-show-gallery-reviews__item-top">
                                <div class="product-show-gallery-reviews__item-meta">
                                    <span class="product-show-gallery-reviews__stars product-show-gallery-reviews__stars--sm" aria-label="{{ $shopReview->rating }} out of 5 stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg viewBox="0 0 20 20" fill="{{ $i <= $shopReview->rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </span>
                                    @if($shopReview->is_verified_purchase)
                                        <span class="product-show-gallery-reviews__verified">Verified</span>
                                    @endif
                                </div>
                                <div class="product-show-gallery-reviews__author-block">
                                    <span class="product-show-gallery-reviews__author-name">{{ $shopReview->display_name }}</span>
                                    <time class="product-show-gallery-reviews__date" datetime="{{ $shopReview->created_at->toDateString() }}">
                                        {{ $shopReview->created_at->format('M j, Y') }}
                                    </time>
                                </div>
                            </div>

                            @if($shopProduct)
                                <a href="{{ $shopProductUrl }}" class="product-show-gallery-reviews__item-product">{{ $shopProduct->name }}</a>
                            @endif

                            <div class="product-show-gallery-reviews__item-body">
                                <div class="product-show-gallery-reviews__item-text">
                                    @if($headline)
                                        <h4 class="product-show-gallery-reviews__item-title">{{ $headline }}</h4>
                                    @endif
                                    @if($shopReview->review_text)
                                        <p class="product-show-gallery-reviews__item-copy">{{ $shopReview->review_text }}</p>
                                    @endif
                                </div>
                                @if($thumbUrl)
                                    <button
                                        type="button"
                                        class="product-show-gallery-reviews__item-thumb"
                                        aria-label="View review photo"
                                        onclick="openReviewPhotoLightbox('shop', {{ $shopThumbPhotoIndex !== false ? $shopThumbPhotoIndex : 0 }})"
                                    >
                                        <img src="{{ $thumbUrl }}" alt="" loading="lazy">
                                    </button>
                                @else
                                    <div class="product-show-gallery-reviews__item-avatar" aria-hidden="true">{{ $authorInitial }}</div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                @if(count($shopReviewPhotoUrls) > 0)
                    <div class="product-show-gallery-reviews__photos">
                        <h4 class="product-show-gallery-reviews__photos-title">Photos from reviews</h4>
                        <div class="product-show-gallery-reviews__photos-grid">
                            @foreach($shopReviewPhotoUrls as $photoIndex => $photoUrl)
                                <button
                                    type="button"
                                    class="product-show-gallery-reviews__photo"
                                    onclick="openReviewPhotoLightbox('shop', {{ $photoIndex }})"
                                    aria-label="View review photo {{ $photoIndex + 1 }}"
                                >
                                    <img src="{{ $photoUrl }}" alt="" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($shopFeaturedReviews->isNotEmpty())
                    <div class="product-show-gallery-reviews__featured-inline" id="productShowGalleryFeaturedReviews">
                        <h4 class="product-show-gallery-reviews__featured-inline-title">
                            Featured reviews from {{ $shopName }}
                        </h4>
                        <p class="product-show-gallery-reviews__featured-caption">
                            What customers say about other products from this shop
                        </p>

                        <div class="product-show-gallery-reviews__carousel" data-featured-carousel>
                            @foreach($featuredPages as $pageIndex => $pageReviews)
                                <div
                                    class="product-show-gallery-reviews__carousel-page"
                                    data-featured-page
                                    @if($pageIndex > 0) hidden @endif
                                >
                                    @foreach($pageReviews as $featuredReview)
                                        @php
                                            $featuredProduct = $featuredReview->product;
                                            $featuredProductUrl = $featuredProduct ? route('products.show', $featuredProduct->slug) : '#';
                                            $featuredImage = ($featuredReview->images[0] ?? null) ?: $productImageUrl($featuredProduct);
                                            $featuredHeadline = $reviewHeadline($featuredReview->review_text);
                                            $featuredBody = $featuredReview->review_text;
                                            if ($featuredBody && strlen($featuredBody) > 160) {
                                                $featuredBody = Str::limit($featuredBody, 160);
                                            }
                                        @endphp
                                        <article class="product-show-gallery-reviews__featured-card">
                                            <div class="product-show-gallery-reviews__featured-card-top">
                                                <span class="product-show-gallery-reviews__stars product-show-gallery-reviews__stars--sm" aria-label="{{ $featuredReview->rating }} out of 5 stars">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg viewBox="0 0 20 20" fill="{{ $i <= $featuredReview->rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    @endfor
                                                </span>
                                                @if($featuredProduct)
                                                    <a href="{{ $featuredProductUrl }}" class="product-show-gallery-reviews__featured-product">{{ $featuredProduct->name }}</a>
                                                @endif
                                            </div>
                                            @if($featuredHeadline)
                                                <h5 class="product-show-gallery-reviews__featured-headline">{{ $featuredHeadline }}</h5>
                                            @endif
                                            @if($featuredBody)
                                                <p class="product-show-gallery-reviews__featured-copy">{{ $featuredBody }}</p>
                                            @endif
                                            <footer class="product-show-gallery-reviews__featured-footer">
                                                <span>{{ $featuredReview->display_name }}</span>
                                                <time datetime="{{ $featuredReview->created_at->toDateString() }}">{{ $featuredReview->created_at->format('M j, Y') }}</time>
                                            </footer>
                                            @if($featuredImage)
                                                <a href="{{ $featuredProductUrl }}" class="product-show-gallery-reviews__featured-media" tabindex="-1" aria-hidden="true">
                                                    <img src="{{ $featuredImage }}" alt="" loading="lazy">
                                                </a>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        @if($featuredTotalPages > 1)
                            <div class="product-show-gallery-reviews__pager">
                                <button
                                    type="button"
                                    class="product-show-gallery-reviews__pager-btn"
                                    data-featured-prev
                                    aria-label="Previous featured reviews"
                                    disabled
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                                <span class="product-show-gallery-reviews__pager-status" data-featured-status>1/{{ $featuredTotalPages }}</span>
                                <button
                                    type="button"
                                    class="product-show-gallery-reviews__pager-btn"
                                    data-featured-next
                                    aria-label="Next featured reviews"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            @else
                <p class="product-show-gallery-reviews__empty">No reviews yet for this shop.</p>
            @endif
        </div>
    </section>

    <div
        id="review-photo-lightbox"
        class="product-show-review-lightbox hidden"
        role="dialog"
        aria-modal="true"
        aria-label="Review photo viewer"
        hidden
    >
        <button type="button" class="product-show-review-lightbox__close" data-review-lightbox-close aria-label="Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <button type="button" class="product-show-review-lightbox__nav product-show-review-lightbox__nav--prev" data-review-lightbox-prev aria-label="Previous photo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <button type="button" class="product-show-review-lightbox__nav product-show-review-lightbox__nav--next" data-review-lightbox-next aria-label="Next photo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        <div class="product-show-review-lightbox__stage">
            <img id="review-photo-lightbox-image" src="" alt="Review photo">
        </div>
        <div class="product-show-review-lightbox__counter" id="review-photo-lightbox-counter"></div>
    </div>
</div>

<script>
window.productReviewPhotoSets = {
    item: @json($itemReviewPhotoUrls),
    shop: @json($shopReviewPhotoUrls),
};

(function () {
    const lightbox = document.getElementById('review-photo-lightbox');
    const lightboxImage = document.getElementById('review-photo-lightbox-image');
    const lightboxCounter = document.getElementById('review-photo-lightbox-counter');
    const closeBtn = lightbox ? lightbox.querySelector('[data-review-lightbox-close]') : null;
    const prevBtn = lightbox ? lightbox.querySelector('[data-review-lightbox-prev]') : null;
    const nextBtn = lightbox ? lightbox.querySelector('[data-review-lightbox-next]') : null;

    let activeSet = 'item';
    let activeIndex = 0;

    function getPhotos(set) {
        const photos = window.productReviewPhotoSets[set];
        return Array.isArray(photos) ? photos : [];
    }

    function updateLightbox() {
        const photos = getPhotos(activeSet);
        if (!lightbox || !lightboxImage || photos.length === 0) {
            return;
        }

        activeIndex = Math.max(0, Math.min(activeIndex, photos.length - 1));
        lightboxImage.src = photos[activeIndex];

        if (lightboxCounter) {
            lightboxCounter.textContent = String(activeIndex + 1) + ' / ' + String(photos.length);
        }

        if (prevBtn) prevBtn.hidden = photos.length <= 1;
        if (nextBtn) nextBtn.hidden = photos.length <= 1;
    }

    window.openReviewPhotoLightbox = function (set, index) {
        const photos = getPhotos(set);
        if (!lightbox || photos.length === 0) {
            return;
        }

        activeSet = set;
        activeIndex = Number(index) || 0;
        updateLightbox();

        lightbox.hidden = false;
        lightbox.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.hidden = true;
        lightbox.classList.add('hidden');
        if (lightboxImage) lightboxImage.src = '';
        document.body.style.overflow = '';
    }

    function stepLightbox(direction) {
        const photos = getPhotos(activeSet);
        if (photos.length <= 1) return;
        if (direction === 'prev') {
            activeIndex = activeIndex > 0 ? activeIndex - 1 : photos.length - 1;
        } else {
            activeIndex = activeIndex < photos.length - 1 ? activeIndex + 1 : 0;
        }
        updateLightbox();
    }

    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
    if (prevBtn) prevBtn.addEventListener('click', function () { stepLightbox('prev'); });
    if (nextBtn) nextBtn.addEventListener('click', function () { stepLightbox('next'); });

    if (lightbox) {
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (!lightbox || lightbox.hidden) return;
        if (event.key === 'Escape') closeLightbox();
        if (event.key === 'ArrowLeft') stepLightbox('prev');
        if (event.key === 'ArrowRight') stepLightbox('next');
    });
})();
</script>

<script>
(function () {
    const root = document.getElementById('productShowGalleryReviews');
    if (!root) return;

    const tabs = root.querySelectorAll('[data-review-tab]');
    const panels = root.querySelectorAll('[data-review-panel]');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const target = tab.getAttribute('data-review-tab');

            tabs.forEach(function (item) {
                const isActive = item === tab;
                item.classList.toggle('product-show-gallery-reviews__tab--active', isActive);
                item.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach(function (panel) {
                const isMatch = panel.getAttribute('data-review-panel') === target;
                panel.hidden = !isMatch;
            });
        });
    });
})();

(function () {
    const root = document.getElementById('productShowGalleryFeaturedReviews');
    if (!root) return;

    const pages = root.querySelectorAll('[data-featured-page]');
    const prevBtn = root.querySelector('[data-featured-prev]');
    const nextBtn = root.querySelector('[data-featured-next]');
    const statusEl = root.querySelector('[data-featured-status]');
    if (pages.length <= 1) return;

    let current = 0;

    function goTo(index) {
        current = Math.max(0, Math.min(index, pages.length - 1));
        pages.forEach(function (page, idx) {
            page.hidden = idx !== current;
        });
        if (statusEl) {
            statusEl.textContent = String(current + 1) + '/' + String(pages.length);
        }
        if (prevBtn) prevBtn.disabled = current === 0;
        if (nextBtn) nextBtn.disabled = current === pages.length - 1;
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            goTo(current - 1);
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            goTo(current + 1);
        });
    }

    goTo(0);
})();
</script>
