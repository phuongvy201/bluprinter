@props([
    'posts',
    'gridId' => 'catalog-blog-grid',
    'emptyTitle' => 'No posts found',
    'emptySub' => 'Try adjusting your filters or browse all articles again.',
    'emptyCtaUrl' => null,
    'emptyCtaLabel' => 'View all posts',
])

@if ($posts->isEmpty())
    <div class="catalog-empty">
        <svg class="catalog-empty__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h2 class="catalog-empty__title">{{ $emptyTitle }}</h2>
        <p class="catalog-empty__sub">{{ $emptySub }}</p>
        @if ($emptyCtaUrl)
            <a href="{{ $emptyCtaUrl }}" class="btn-cta">{{ $emptyCtaLabel }}</a>
        @endif
    </div>
@else
    <div class="catalog-blog-grid" id="{{ $gridId }}">
        @foreach ($posts as $post)
            <x-catalog-blog-post-item :post="$post" class="scroll-reveal" />
        @endforeach
    </div>

    @if ($posts->hasPages())
        <div class="catalog-pagination">
            {{ $posts->onEachSide(1)->links('vendor.pagination.catalog') }}
        </div>
    @endif
@endif
