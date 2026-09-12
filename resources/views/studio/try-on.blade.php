@extends('layouts.app')

@section('title', 'Virtual Try-On')

@section('content')
@php
    $aiEnabled = !empty($aiEnabled);
    $timeout = (int) ($timeout ?? 180);
    $catalog = $products->map(function ($product) {
        $media = $product->getEffectiveMedia();
        $urls = [];
        $back = null;
        if (is_array($media)) {
            foreach ($media as $item) {
                $raw = is_array($item) ? ($item['url'] ?? $item['path'] ?? null) : $item;
                if (! is_string($raw) || $raw === '') {
                    continue;
                }
                if (! preg_match('#^https?://#i', $raw) && ! str_starts_with($raw, '//')) {
                    $raw = url($raw);
                }
                $label = strtolower(is_array($item) ? implode(' ', array_filter([
                    $item['alt'] ?? null,
                    $item['name'] ?? null,
                    $item['view'] ?? null,
                    $item['side'] ?? null,
                    $item['label'] ?? null,
                    $raw,
                ])) : $raw);
                if ($back === null && (str_contains($label, 'back') || str_contains($label, 'rear'))) {
                    $back = $raw;
                    continue;
                }
                $urls[] = $raw;
            }
        }
        $image = $urls[0] ?? $back;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'type' => $product->getDisplayCategoryName() ?: '',
            'slug' => $product->slug,
            'url' => route('products.show', $product->slug),
            'image' => $image,
            'back' => $back && $back !== $image ? $back : null,
            'price' => format_price_usd((float) ($product->price ?? $product->base_price ?? 0)),
        ];
    })->values();
@endphp
@include('studio.partials.ai-progress')
<style>
    body:has(.tryon-page) #gen-ai-fab { display: none !important; }
    .tryon-page { padding: 32px 0 48px; background: #f9fafb; }
    .tryon-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (min-width: 1024px) {
        .tryon-layout {
            grid-template-columns: minmax(280px, 420px) minmax(360px, 1fr);
            gap: 32px;
        }
        .tryon-panel { position: sticky; top: calc(var(--product-show-sticky-top, 88px) + 8px); }
    }
    .tryon-stage {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
        max-width: 420px;
        width: 100%;
        justify-self: center;
    }
    .tryon-stage__frame {
        position: relative;
        aspect-ratio: 3 / 4;
        max-height: min(56vh, 480px);
        border-radius: 16px;
        overflow: hidden;
        background: #f7f7f7;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .tryon-stage__photo {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #fff;
    }
    .tryon-stage__empty {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 24px;
        text-align: center;
        color: #4b5563;
    }
    .tryon-stage__empty strong { color: #111827; font-size: 1.125rem; }
    .tryon-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .tryon-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .tryon-panel h2 {
        margin: 0 0 8px;
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
    }
    .tryon-panel p {
        margin: 0 0 20px;
        font-size: 0.9375rem;
        line-height: 1.5;
        color: #4b5563;
    }
    .tryon-upload {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 120px;
        margin-bottom: 24px;
        border: 1px dashed #d1d5db;
        border-radius: 16px;
        background: #f7f7f7;
        cursor: pointer;
        color: #4b5563;
        font-size: 0.875rem;
        font-weight: 600;
        text-align: center;
        padding: 16px;
    }
    .tryon-upload:hover { border-color: #005366; color: #005366; }
    .tryon-upload.is-ready { border-style: solid; border-color: #005366; color: #005366; }
    .tryon-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        max-height: 320px;
        overflow-y: auto;
        margin-bottom: 20px;
    }
    .tryon-product {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        padding: 8px;
        text-align: left;
        cursor: pointer;
    }
    .tryon-product.is-active {
        border-color: #005366;
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.12);
    }
    .tryon-product img {
        width: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        border-radius: 8px;
        background: #f7f7f7;
    }
    .tryon-product span {
        margin-top: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #111827;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .tryon-view {
        display: flex;
        gap: 8px;
        margin: 0 0 16px;
    }
    .tryon-view label {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 9999px;
        background: #fff;
        font-size: 0.875rem;
        font-weight: 600;
        color: #4b5563;
        cursor: pointer;
    }
    .tryon-view label:has(input:checked) {
        border-color: #005366;
        background: #eef6f8;
        color: #005366;
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.12);
    }
    .tryon-view input { position: absolute; opacity: 0; pointer-events: none; }
    .tryon-cta { width: 100%; gap: 8px; }
    .tryon-cta:disabled { opacity: 0.55; cursor: not-allowed; transform: none; box-shadow: none; }
    .tryon-toast {
        display: none;
        margin-top: 16px;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .tryon-toast.is-success { display: block; background: #f0fdf4; color: #16a34a; }
    .tryon-toast.is-error { display: block; background: #fef2f2; color: #e2150c; }
</style>

<section class="tryon-page">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="catalog-breadcrumb__sep" aria-hidden="true">/</span>
            <span class="catalog-breadcrumb__current">Virtual Try-On</span>
        </nav>

        <header class="section-heading section-heading--catalog mb-8">
            <p class="section-heading__eyebrow">Creator Studio</p>
            <h1 class="section-heading__title">Virtual <span class="gradient-text">Try-On</span></h1>
            <p class="section-heading__sub">Apparel, hats, phone cases, mugs, and more — AI places the product on your photo.</p>
            <span class="section-heading__accent" aria-hidden="true"></span>
        </header>

        <div class="tryon-layout">
            <div class="tryon-stage">
                <div class="tryon-stage__frame" id="tryon-frame">
                    <div class="tryon-stage__empty" id="tryon-empty">
                        <strong>AI virtual try-on</strong>
                        <span>Apparel, hats, phone cases, mugs — pick a product and generate.</span>
                    </div>
                    <img id="tryon-photo" class="tryon-stage__photo" alt="Try-on preview" hidden>
                </div>
                <div class="tryon-actions">
                    <a href="{{ route('products.index') }}" class="btn-outline-petrol" id="tryon-shop">Browse products</a>
                    <a href="#" class="btn-cta" id="tryon-buy" hidden>View this product</a>
                    <a href="#" class="btn-outline-petrol" id="tryon-download" download="bluprinter-try-on.png" hidden>Download</a>
                </div>
            </div>

            <aside class="tryon-panel">
                @if (!$aiEnabled)
                    <p class="text-[#e2150c] font-semibold">AI try-on is currently turned off.</p>
                @else
                    <h2>1. Upload a photo</h2>
                    <p>Use a clear, front-facing photo. Your photo is sent to AI only to create this try-on.</p>
                    <label class="tryon-upload" id="tryon-upload">
                        <input type="file" id="tryon-file" accept="image/png,image/jpeg,image/webp" hidden>
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span id="tryon-upload-label">Choose a photo</span>
                    </label>

                    <h2>2. Choose a product</h2>
                    <p>Select the garment. For 2-sided prints, pick Front or Back so the AI does not mix the designs.</p>
                    @if ($catalog->isEmpty())
                        <p>No products available yet.</p>
                    @else
                        <div class="tryon-grid" id="tryon-grid">
                            @foreach ($catalog as $item)
                                <button type="button"
                                        class="tryon-product{{ $selectedSlug === $item['slug'] ? ' is-active' : '' }}"
                                        data-slug="{{ $item['slug'] }}"
                                        data-url="{{ $item['url'] }}"
                                        data-image="{{ $item['image'] }}"
                                        data-back="{{ $item['back'] }}"
                                        data-name="{{ $item['name'] }}"
                                        data-type="{{ $item['type'] }}">
                                    @if ($item['image'])
                                        <img src="{{ $item['image'] }}" alt="">
                                    @endif
                                    <span>{{ $item['name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <h2>3. Which side?</h2>
                    <p>Front or back of the item (shirt print, case back, hat panel, etc.).</p>
                    <div class="tryon-view" role="radiogroup" aria-label="Garment side">
                        <label>
                            <input type="radio" name="tryon-view" value="front" checked>
                            Front
                        </label>
                        <label>
                            <input type="radio" name="tryon-view" value="back">
                            Back
                        </label>
                    </div>

                    <button type="button" class="btn-cta tryon-cta" id="tryon-generate">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.5 3.2l1.15 3.1 3.15 1.15-3.15 1.15-1.15 3.1-1.15-3.1L5.2 7.45l3.15-1.15L9.5 3.2z"/></svg>
                        Try on with AI
                    </button>
                    <p class="mt-3 text-sm text-gray-600">Free. You’ll see progress while AI generates your photo.</p>
                    <div class="tryon-toast" id="tryon-toast" role="status"></div>
                @endif
            </aside>
        </div>
    </div>
</section>

<script>
    window.TRYON_ROUTES = {
        upload: @json(route('studio.upload')),
        generate: @json(route('studio.try-on.generate')),
        csrf: @json(csrf_token()),
        enabled: @json($aiEnabled),
        timeout: {{ $timeout }},
    };
</script>
<script src="{{ asset('js/studio-ai-progress.js') }}?v={{ @filemtime(public_path('js/studio-ai-progress.js')) }}"></script>
<script src="{{ asset('js/studio-try-on.js') }}?v={{ @filemtime(public_path('js/studio-try-on.js')) }}"></script>
@endsection
