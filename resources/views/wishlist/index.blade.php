@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
@php
    $itemCount = $wishlistItems->total();
@endphp
<section class="catalog-page" aria-labelledby="wishlist-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="catalog-breadcrumb__sep" aria-hidden="true">/</span>
            <span class="catalog-breadcrumb__current">Wishlist</span>
        </nav>

        <header class="section-heading section-heading--catalog">
            <p class="section-heading__eyebrow">Saved</p>
            <h1 id="wishlist-heading" class="section-heading__title">
                My <span class="gradient-text">Wishlist</span>
            </h1>
            <p class="section-heading__sub">Items you love, saved for later</p>
            <span class="section-heading__accent" aria-hidden="true"></span>
        </header>

        @if($wishlistItems->count() > 0)
            <div class="catalog-toolbar">
                <div class="catalog-toolbar__desktop">
                    <p class="catalog-toolbar__summary">
                        <span class="catalog-toolbar__summary-count" data-wishlist-count>{{ number_format($itemCount) }}</span>
                        saved {{ $itemCount === 1 ? 'item' : 'items' }}
                        @if ($wishlistItems->hasPages())
                            <span class="catalog-toolbar__summary-dot" aria-hidden="true">·</span>
                            <span class="catalog-toolbar__summary-range">{{ $wishlistItems->firstItem() }}–{{ $wishlistItems->lastItem() }}</span>
                        @endif
                    </p>
                    <div class="catalog-toolbar__actions ml-auto">
                        <button type="button" id="clear-wishlist-btn" class="btn-outline-petrol btn-outline-petrol--compact">
                            Clear all
                        </button>
                    </div>
                </div>
                <div class="catalog-toolbar__mobile">
                    <div class="catalog-toolbar__mobile-head">
                        <p class="catalog-toolbar__summary">
                            <span class="catalog-toolbar__summary-count" data-wishlist-count>{{ number_format($itemCount) }}</span>
                            saved {{ $itemCount === 1 ? 'item' : 'items' }}
                        </p>
                        <button type="button" class="catalog-toolbar__clear" data-clear-wishlist aria-label="Clear all saved items">
                            Clear all
                        </button>
                    </div>
                </div>
            </div>

            <div class="catalog-grid" id="wishlist-product-grid">
                @foreach($wishlistItems as $wishlistItem)
                    <div data-wishlist-item="{{ $wishlistItem->product_id }}">
                        @if($wishlistItem->product)
                            <x-product-card
                                :product="$wishlistItem->product"
                                :show-shop="true"
                                :show-description="true"
                                class="catalog-product-card"
                            />
                        @else
                            <div class="product-card catalog-product-card">
                                <div class="product-card__media-shell">
                                    <div class="product-card__placeholder" aria-hidden="true">
                                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="product-card__body">
                                    <h3 class="product-card__title">Product no longer available</h3>
                                    <p class="product-card__desc">This item was removed from the catalog.</p>
                                </div>
                                <div class="product-card__footer">
                                    <button type="button"
                                            class="btn-outline-petrol btn-outline-petrol--compact"
                                            data-remove-unavailable
                                            data-product-id="{{ $wishlistItem->product_id }}">
                                        Remove from wishlist
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($wishlistItems->hasPages())
                <div class="catalog-pagination">
                    {{ $wishlistItems->onEachSide(1)->links('vendor.pagination.catalog') }}
                </div>
            @endif
        @else
            <div class="catalog-empty">
                <svg class="catalog-empty__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <h2 class="catalog-empty__title">Your wishlist is empty</h2>
                <p class="catalog-empty__sub">Tap the heart on any product to save it here for later.</p>
                <div class="catalog-empty__actions">
                    <a href="{{ route('products.index') }}" class="btn-cta">Browse products</a>
                    <a href="{{ route('collections.index') }}" class="btn-outline-petrol">View collections</a>
                </div>
            </div>
        @endif

        @include('partials.recently-viewed-section', ['recentlyViewedId' => 'wishlist-recently-viewed'])
    </div>
</section>

<div id="clear-wishlist-modal" class="catalog-modal hidden" role="dialog" aria-modal="true" aria-labelledby="clear-wishlist-title">
    <div class="catalog-modal__panel">
        <div class="catalog-modal__head">
            <h2 id="clear-wishlist-title" class="catalog-modal__title">Clear all saved items</h2>
            <button type="button" class="catalog-modal__close" id="cancel-clear-wishlist" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p class="text-gray-600 mb-6">This removes every product from your wishlist. You cannot undo this action.</p>
        <div class="catalog-modal__actions">
            <button type="button" id="confirm-clear-wishlist" class="btn-cta">Clear all</button>
            <button type="button" class="btn-outline-petrol" data-clear-wishlist-cancel>Cancel</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var clearModal = document.getElementById('clear-wishlist-modal');
    var confirmClearBtn = document.getElementById('confirm-clear-wishlist');
    var openClearButtons = document.querySelectorAll('#clear-wishlist-btn, [data-clear-wishlist]');
    var closeClearButtons = document.querySelectorAll('#cancel-clear-wishlist, [data-clear-wishlist-cancel]');

    function openClearModal() {
        if (!clearModal) return;
        clearModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeClearModal() {
        if (!clearModal) return;
        clearModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    openClearButtons.forEach(function (btn) {
        btn.addEventListener('click', openClearModal);
    });

    closeClearButtons.forEach(function (btn) {
        btn.addEventListener('click', closeClearModal);
    });

    if (clearModal) {
        clearModal.addEventListener('click', function (e) {
            if (e.target === clearModal) closeClearModal();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && clearModal && !clearModal.classList.contains('hidden')) {
            closeClearModal();
        }
    });

    function notify(message, type) {
        if (window.wishlistManager) {
            window.wishlistManager.showMessage(message, type);
            return;
        }
        alert(message);
    }

    if (confirmClearBtn) {
        confirmClearBtn.addEventListener('click', function () {
            confirmClearBtn.disabled = true;
            var original = confirmClearBtn.textContent;
            confirmClearBtn.textContent = 'Clearing…';

            fetch(@json(route('wishlist.clear')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    closeClearModal();
                    location.reload();
                    return;
                }
                confirmClearBtn.disabled = false;
                confirmClearBtn.textContent = original;
                notify(data.message || 'Failed to clear wishlist.', 'error');
            })
            .catch(function () {
                confirmClearBtn.disabled = false;
                confirmClearBtn.textContent = original;
                notify('An error occurred while clearing the wishlist.', 'error');
            });
        });
    }

    document.querySelectorAll('[data-remove-unavailable]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var productId = btn.getAttribute('data-product-id');
            btn.disabled = true;

            fetch(@json(route('wishlist.remove')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ product_id: productId }),
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    var item = document.querySelector('[data-wishlist-item="' + productId + '"]');
                    if (item) item.remove();
                    if (!document.querySelector('[data-wishlist-item]')) {
                        location.reload();
                    }
                    return;
                }
                btn.disabled = false;
                notify(data.message || 'Failed to remove product from wishlist.', 'error');
            })
            .catch(function () {
                btn.disabled = false;
                notify('An error occurred while removing the product.', 'error');
            });
        });
    });

    window.addEventListener('wishlistUpdated', function (e) {
        var detail = e.detail || {};
        if (detail.action !== 'removed') return;

        var item = document.querySelector('[data-wishlist-item="' + detail.productId + '"]');
        if (item) item.remove();

        document.querySelectorAll('[data-wishlist-count]').forEach(function (el) {
            if (typeof detail.count === 'number') {
                el.textContent = Number(detail.count).toLocaleString();
            }
        });

        if (!document.querySelector('[data-wishlist-item]')) {
            location.reload();
        }
    });
});
</script>
@endsection
