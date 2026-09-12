@extends('layouts.app')

@section('title', 'Explore Design')
@section('meta_description', 'Browse ready-to-print designs and customize them on Create Your Own.')

@section('content')
@php
    $breadcrumbs = [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Explore Design', 'url' => null],
    ];
    $tagQuery = collect(['q' => $search])->filter(fn ($v) => filled($v))->all();
@endphp
<style>
    .explore-design-head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px 24px;
        margin-bottom: 8px;
    }
    .explore-design-head .section-heading {
        margin-bottom: 0;
        flex: 1 1 280px;
    }
    .explore-design-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        margin: 20px 0 16px;
    }
    .explore-design-search {
        display: flex;
        flex: 1 1 240px;
        min-width: 0;
        gap: 8px;
    }
    .explore-design-search input[type="search"] {
        flex: 1 1 auto;
        min-width: 0;
        height: 44px;
        padding: 0 14px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        background: #fff;
        color: #111827;
        font-size: 0.9375rem;
    }
    .explore-design-search input[type="search"]:focus {
        outline: none;
        border-color: #005366;
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.12);
    }
    .explore-design-card {
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .explore-design-card:hover {
        transform: translateY(-2px);
        border-color: rgba(0, 83, 102, 0.25);
        box-shadow: 0 10px 15px -3px rgba(0, 83, 102, 0.12);
    }
    .explore-design-card__media {
        display: block;
        aspect-ratio: 1;
        background: #f7f7f7;
    }
    .explore-design-card__media img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .explore-design-card__body {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 14px;
        flex: 1 1 auto;
    }
    .explore-design-card__name {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.3;
        color: #111827;
    }
    .explore-design-card__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        font-size: 0.8125rem;
        color: #4b5563;
    }
    .explore-design-card__tag {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 9999px;
        background: rgba(0, 83, 102, 0.08);
        color: #005366;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        font-size: 0.6875rem;
    }
    .explore-design-card__action {
        margin-top: auto;
        width: 100%;
        justify-content: center;
        text-align: center;
    }
</style>

<section class="catalog-page" aria-labelledby="explore-design-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
            @foreach ($breadcrumbs as $index => $breadcrumb)
                @if ($index > 0)
                    <span class="catalog-breadcrumb__sep" aria-hidden="true">/</span>
                @endif
                @if ($breadcrumb['url'])
                    <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                @else
                    <span class="catalog-breadcrumb__current">{{ $breadcrumb['name'] }}</span>
                @endif
            @endforeach
        </nav>

        <div class="explore-design-head">
            <header class="section-heading section-heading--catalog scroll-reveal">
                <p class="section-heading__eyebrow">Creator Studio</p>
                <h1 id="explore-design-heading" class="section-heading__title">
                    Explore <span class="gradient-text">Design</span>
                </h1>
                <p class="section-heading__sub">
                    Browse the design library, then place one on a product in Create Your Own.
                </p>
                <span class="section-heading__accent" aria-hidden="true"></span>
            </header>
            <a href="{{ route('studio.index') }}" class="btn-cta">Create Your Own</a>
        </div>

        <form method="GET" action="{{ route('studio.explore') }}" class="explore-design-toolbar">
            @if ($activeTag !== '')
                <input type="hidden" name="tag" value="{{ $activeTag }}">
            @endif
            <label class="explore-design-search">
                <span class="sr-only">Search designs</span>
                <input type="search"
                       name="q"
                       value="{{ $search }}"
                       placeholder="Search designs…"
                       aria-label="Search designs">
                <button type="submit" class="btn-outline-petrol">Search</button>
            </label>
            @if ($search !== '' || $activeTag !== '')
                <a href="{{ route('studio.explore') }}" class="text-sm font-semibold text-[#005366] hover:underline">Clear</a>
            @endif
        </form>

        @if ($tags->isNotEmpty())
            <div class="catalog-collections-tabs" style="background: transparent; border: 0; padding: 0 0 20px;">
                <div class="catalog-collections-tabs__track mobile-scroll-hide">
                    <a href="{{ route('studio.explore', $tagQuery) }}"
                       class="catalog-collections-tab {{ $activeTag === '' ? 'is-active' : '' }}">All</a>
                    @foreach ($tags as $tag)
                        <a href="{{ route('studio.explore', array_filter(['tag' => $tag, 'q' => $search ?: null])) }}"
                           class="catalog-collections-tab {{ $activeTag === $tag ? 'is-active' : '' }}">{{ $tag }}</a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($designs->isEmpty())
            <div class="bg-white border border-gray-200 rounded-xl p-10 text-center">
                <p class="text-gray-900 font-semibold">No designs yet</p>
                <p class="mt-2 text-sm text-gray-600">Start from a blank canvas and print it on a product.</p>
                <a href="{{ route('studio.index') }}" class="btn-outline-petrol inline-flex mt-6">Go to Create Your Own</a>
            </div>
        @else
            <p class="catalog-toolbar__summary mb-6">
                <span class="catalog-toolbar__summary-count">{{ number_format($designs->total()) }}</span>
                {{ $designs->total() === 1 ? 'design' : 'designs' }}
            </p>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                @foreach ($designs as $design)
                    @php
                        $studioUrl = route('studio.index', ['design_id' => $design['id']]);
                        $priceUsd = (float) ($design['price_usd'] ?? 0);
                    @endphp
                    <article class="explore-design-card">
                        <a href="{{ $studioUrl }}" class="explore-design-card__media" aria-label="Use {{ $design['name'] }} in Create Your Own">
                            <img src="{{ $design['image'] }}" alt="{{ $design['name'] }}" loading="lazy">
                        </a>
                        <div class="explore-design-card__body">
                            <h2 class="explore-design-card__name">{{ $design['name'] }}</h2>
                            <div class="explore-design-card__meta">
                                @if (! empty($design['tag']))
                                    <span class="explore-design-card__tag">{{ $design['tag'] }}</span>
                                @endif
                                @if ($priceUsd > 0)
                                    <span>{{ format_price((float) $design['price']) }}</span>
                                @endif
                            </div>
                            <a href="{{ $studioUrl }}" class="btn-outline-petrol explore-design-card__action">Use this design</a>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($designs->hasPages())
                <div class="catalog-pagination">
                    {{ $designs->onEachSide(1)->links('vendor.pagination.catalog') }}
                </div>
            @endif
        @endif
    </div>
</section>
@endsection
