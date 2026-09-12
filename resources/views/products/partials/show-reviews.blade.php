{{-- Product reviews section — expects $product --}}
<section class="product-show-reviews space-y-6" aria-label="Product reviews">
    <div class="product-show-reviews__header flex items-center justify-between">
        <h3 class="product-show-reviews__title text-2xl font-bold text-gray-900">Reviews</h3>
    </div>

    <div class="product-show-reviews__subtabs border-b border-gray-200">
        <nav class="product-show-reviews__subtabs-nav -mb-px flex space-x-8" aria-label="Review filters">
            <button type="button" class="product-show-reviews__subtab product-show-reviews__subtab--active border-b-2 border-[#005366] py-2 px-1 text-sm font-medium text-[#005366]">
                Reviews for this item
            </button>
            <button type="button" class="product-show-reviews__subtab border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Reviews for this shop
            </button>
        </nav>
    </div>

    @php
        $averageRating = $product->getAverageRating();
        $totalReviews = $product->getTotalReviews();
        $ratingBreakdown = $product->getRatingBreakdown();
    @endphp

    <div class="product-show-reviews__summary flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div class="product-show-reviews__average flex items-center space-x-4">
            <div class="flex items-center">
                <svg class="product-show-reviews__star-icon w-8 h-8 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
                <span class="product-show-reviews__score text-2xl font-bold text-gray-900 ml-2">{{ number_format($averageRating, 1) }} /5.0</span>
            </div>
            <div class="product-show-reviews__count text-sm text-gray-600">
                <span class="underline">{{ $totalReviews }} Reviews</span>
            </div>
        </div>

        <div class="product-show-reviews__breakdown grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-2">
            <div class="product-show-reviews__breakdown-col space-y-2">
                @for($star = 5; $star >= 3; $star--)
                    @php
                        $count = $ratingBreakdown[$star] ?? 0;
                        $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                    @endphp
                    <div class="product-show-reviews__breakdown-row flex items-center space-x-2">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700 ml-1">{{ $star }}</span>
                        </div>
                        <div class="product-show-reviews__bar flex-1 bg-gray-200 rounded-full h-2 max-w-[120px]">
                            <div class="product-show-reviews__bar-fill bg-[#005366] h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500 w-8">({{ $percentage }}%)</span>
                    </div>
                @endfor
            </div>

            <div class="product-show-reviews__breakdown-col space-y-2">
                @for($star = 2; $star >= 1; $star--)
                    @php
                        $count = $ratingBreakdown[$star] ?? 0;
                        $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                    @endphp
                    <div class="product-show-reviews__breakdown-row flex items-center space-x-2">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700 ml-1">{{ $star }}</span>
                        </div>
                        <div class="product-show-reviews__bar flex-1 bg-gray-200 rounded-full h-2 max-w-[120px]">
                            <div class="product-show-reviews__bar-fill bg-[#005366] h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500 w-8">({{ $percentage }}%)</span>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    @if($product->approvedReviews->count() > 0)
        <div class="product-show-reviews__list space-y-6">
            @foreach($product->approvedReviews->take(3) as $review)
                <article class="product-show-reviews__item border-t border-dotted border-gray-300 pt-6">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between space-y-4 lg:space-y-0">
                        <div class="product-show-reviews__content flex-1">
                            <div class="product-show-reviews__rating flex items-center mb-3" aria-label="{{ $review->rating }} out of 5 stars">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($review->rating))
                                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @elseif($i - 0.5 <= $review->rating)
                                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <defs>
                                                <linearGradient id="half-star-{{ $review->id }}-{{ $i }}">
                                                    <stop offset="50%" stop-color="currentColor"/>
                                                    <stop offset="50%" stop-color="#E5E7EB"/>
                                                </linearGradient>
                                            </defs>
                                            <path fill="url(#half-star-{{ $review->id }}-{{ $i }})" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @endif
                                @endfor
                            </div>

                            @if($review->review_text)
                                @php
                                    $words = explode(' ', $review->review_text);
                                    $title = count($words) > 8 ? implode(' ', array_slice($words, 0, 8)) . '...' : $review->review_text;
                                @endphp
                                <h4 class="product-show-reviews__item-title font-semibold text-gray-900 mb-2">{{ $title }}</h4>
                                <p class="product-show-reviews__item-text text-gray-700 text-sm leading-relaxed">{{ $review->review_text }}</p>
                            @endif
                        </div>

                        <div class="product-show-reviews__author flex items-center space-x-3 lg:ml-6">
                            <div class="flex-shrink-0">
                                <div class="product-show-reviews__avatar w-8 h-8 bg-[#005366]/10 rounded-full flex items-center justify-center text-[#005366] font-medium text-sm">
                                    {{ Str::upper(substr($review->display_name, 0, 2)) }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="product-show-reviews__author-name text-sm font-medium text-gray-900">{{ $review->display_name }}</span>
                                    @if($review->is_verified_purchase)
                                        <span class="product-show-reviews__verified inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Verified
                                        </span>
                                    @endif
                                </div>
                                <div class="product-show-reviews__date text-xs text-gray-500">{{ $review->created_at->format('D M d Y') }}</div>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="product-show-reviews__empty text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
            </svg>
            <p class="text-gray-500 text-lg mb-2">No reviews yet</p>
            <p class="text-gray-400 text-sm">Be the first to review this product</p>
        </div>
    @endif

    <div class="product-show-reviews__footer flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 pt-6 border-t border-gray-200">
        <button type="button" class="product-show-reviews__write-btn inline-flex items-center px-4 py-2 border border-[#e2150c] text-[#e2150c] rounded-lg hover:bg-[#e2150c]/5 transition-colors">
            <svg class="w-4 h-4 mr-2 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
            </svg>
            Write your review
        </button>

        @if($product->approvedReviews->count() > 3)
            <div class="product-show-reviews__pagination flex items-center space-x-2">
                <button type="button" class="product-show-reviews__pagination-btn p-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors" aria-label="Previous reviews">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <span class="product-show-reviews__pagination-info text-sm text-gray-600 px-3">1/3</span>
                <button type="button" class="product-show-reviews__pagination-btn p-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors" aria-label="Next reviews">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        @endif
    </div>
</section>
