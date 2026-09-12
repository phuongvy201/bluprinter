@extends('layouts.app')

@section('title', 'AI Design Gen')

@section('content')
@php
    $promptExample = $inspirationPrompts[0] ?? 'Vintage camping t-shirt design, mountain, forest, river, campfire, retro color palette';
    $imageCount = max(1, min(4, (int) ($imageCount ?? 2)));
    $promptMax = max(200, min(2000, (int) ($promptMax ?? 1000)));
    $maxReferences = max(1, min(8, (int) ($maxReferences ?? 4)));
    $history = $history ?? [];
    $hasHistory = count($history) > 0;
@endphp
@include('studio.partials.ai-progress')
<style>
    body:has(.ai-gen-page) #gen-ai-fab { display: none !important; }
    .ai-gen-page {
        position: relative;
        padding: 24px 0 48px;
        background:
            radial-gradient(ellipse 80% 50% at 8% 0%, rgba(0, 83, 102, 0.08), transparent 55%),
            radial-gradient(ellipse 60% 40% at 100% 12%, rgba(226, 21, 12, 0.06), transparent 50%),
            #f9fafb;
        overflow: hidden;
    }
    .ai-gen-page::before {
        content: '';
        position: absolute;
        inset: 80px -40px auto auto;
        width: 220px;
        height: 220px;
        border: 16px solid rgba(0, 83, 102, 0.06);
        border-radius: 40px;
        transform: rotate(18deg);
        pointer-events: none;
    }
    .ai-gen-shell { position: relative; z-index: 1; }
    .ai-gen-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (min-width: 1024px) {
        .ai-gen-layout {
            grid-template-columns: minmax(280px, 420px) minmax(360px, 1fr);
            gap: 32px;
            justify-content: center;
        }
        .ai-gen-form { position: sticky; top: calc(var(--product-show-sticky-top, 88px) + 8px); }
    }

    .ai-gen-stage {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
        max-width: 420px;
        width: 100%;
        justify-self: center;
    }
    .ai-gen-board {
        position: relative;
        width: 100%;
        aspect-ratio: 1;
        max-height: min(48vh, 420px);
        border-radius: 24px;
        overflow: hidden;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.55), rgba(255,255,255,0.2)),
            repeating-conic-gradient(#f7f7f7 0% 25%, #fff 0% 50%) 0 0 / 28px 28px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .ai-gen-board.is-empty .ai-gen-board__tools,
    .ai-gen-board.is-empty + .ai-gen-print-bar { display: none; }
    .ai-gen-board__empty,
    .ai-gen-board__art {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ai-gen-board__art img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: rgba(255,255,255,0.72);
    }
    .ai-gen-board__empty {
        flex-direction: column;
        gap: 12px;
        padding: 20px;
        text-align: center;
    }
    .ai-gen-board__mark {
        width: 72px;
        height: 72px;
        border-radius: 24px;
        background: #005366;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 12px 32px rgba(0, 83, 102, 0.28);
        transform: rotate(-8deg);
    }
    .ai-gen-board__empty h2 {
        margin: 8px 0 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
    }
    .ai-gen-board__empty p {
        margin: 0;
        max-width: 28rem;
        font-size: 0.9375rem;
        line-height: 1.5;
        color: #4b5563;
    }
    .ai-gen-board__tools {
        position: absolute;
        inset: 16px 16px auto 16px;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        pointer-events: none;
    }
    .ai-gen-board__tools > * { pointer-events: auto; }
    .ai-gen-iconbtn {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5e7eb;
        border-radius: 9999px;
        background: #fff;
        color: #111827;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        cursor: pointer;
    }
    .ai-gen-iconbtn:hover { color: #005366; border-color: #005366; }
    .ai-gen-iconbtn-row { display: flex; gap: 8px; }
    .ai-gen-print-bar { display: flex; }
    .ai-gen-print-bar .btn-cta {
        width: 100%;
        gap: 8px;
    }

    .ai-gen-history {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px;
    }
    .ai-gen-history__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }
    .ai-gen-history__head h2 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 700;
        color: #111827;
    }
    .ai-gen-history__head p {
        margin: 4px 0 0;
        font-size: 0.8125rem;
        color: #4b5563;
    }
    .ai-gen-history__nav { display: flex; gap: 8px; }
    .ai-gen-history__strip {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding-bottom: 4px;
    }
    .ai-gen-history__item {
        position: relative;
        flex: 0 0 96px;
        width: 96px;
        height: 96px;
        padding: 0;
        border: 2px solid transparent;
        border-radius: 12px;
        background: #f7f7f7;
        overflow: hidden;
        cursor: pointer;
        scroll-snap-align: start;
    }
    .ai-gen-history__item.is-active { border-color: #005366; }
    .ai-gen-history__item img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .ai-gen-history__menu {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 24px;
        height: 24px;
        border: 0;
        border-radius: 9999px;
        background: rgba(255,255,255,0.92);
        color: #111827;
        cursor: pointer;
        font-size: 14px;
        line-height: 1;
    }

    .ai-gen-form {
        position: relative;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .ai-gen-form__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }
    .ai-gen-form__head h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
    }
    .ai-gen-tips {
        position: relative;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #005366;
        background: none;
        border: 0;
        cursor: pointer;
        padding: 0;
    }
    .ai-gen-tips[aria-expanded="true"] + .ai-gen-tips__pop,
    .ai-gen-tips__pop.is-open { display: block; }
    .ai-gen-tips__pop {
        display: none;
        position: absolute;
        right: 24px;
        z-index: 3;
        width: min(280px, 70vw);
        margin-top: 4px;
        padding: 12px 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        font-size: 0.8125rem;
        line-height: 1.45;
        color: #4b5563;
    }
    .ai-gen-form textarea {
        width: 100%;
        min-height: 120px;
        padding: 16px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-size: 1rem;
        line-height: 1.5;
        color: #111827;
        resize: vertical;
    }
    .ai-gen-form textarea:focus {
        outline: none;
        border-color: #005366;
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.12);
    }
    .ai-gen-form__meta {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
        margin: 8px 0 16px;
        font-size: 0.75rem;
        color: #9ca3af;
    }
    .ai-gen-form__meta #ai-gen-count { align-self: flex-end; }
    .ai-gen-form #ai-gen-improve {
        width: 100%;
        background: #005366;
        color: #fff;
        border-color: #005366;
        padding: 0.75rem 1.75rem;
        font-size: 1rem;
        order: 2;
    }
    .ai-gen-form #ai-gen-improve:hover,
    .ai-gen-form #ai-gen-improve:focus {
        background: #003d4d;
        color: #fff;
        border-color: #003d4d;
    }
    .ai-gen-form #ai-gen-improve:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .ai-gen-inspire {
        background: #eef6f8;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
    }
    .ai-gen-inspire__label {
        margin: 0 0 8px;
        font-size: 0.8125rem;
        font-weight: 700;
        color: #111827;
    }
    .ai-gen-inspire button {
        display: block;
        width: 100%;
        text-align: left;
        padding: 0;
        border: 0;
        background: none;
        font-size: 0.9375rem;
        line-height: 1.45;
        color: #4b5563;
        cursor: pointer;
    }
    .ai-gen-inspire button:hover { color: #005366; }
    .ai-gen-refs h3 {
        margin: 0 0 12px;
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
    }
    .ai-gen-refs__row { display: flex; flex-wrap: wrap; gap: 12px; }
    .ai-gen-refs__add,
    .ai-gen-refs__thumb {
        position: relative;
        width: 80px;
        height: 80px;
        border-radius: 12px;
        border: 1px dashed #d1d5db;
        background: #f7f7f7;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #4b5563;
        cursor: pointer;
        overflow: hidden;
    }
    .ai-gen-refs__add:hover { border-color: #005366; color: #005366; }
    .ai-gen-refs__thumb img { width: 100%; height: 100%; object-fit: cover; }
    .ai-gen-refs__thumb button {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 24px;
        height: 24px;
        border: 0;
        border-radius: 9999px;
        background: rgba(17, 24, 39, 0.7);
        color: #fff;
        cursor: pointer;
    }
    .ai-gen-form__actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        margin-top: 24px;
    }
    .ai-gen-form__note { margin: 0; font-size: 0.8125rem; color: #4b5563; flex: 1; min-width: 140px; }
    .ai-gen-toast {
        display: none;
        margin-top: 16px;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .ai-gen-toast.is-success { display: block; background: #f0fdf4; color: #16a34a; }
    .ai-gen-toast.is-error { display: block; background: #fef2f2; color: #e2150c; }

    .ai-gen-lightbox {
        position: fixed;
        inset: 0;
        z-index: 80;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(17, 24, 39, 0.72);
    }
    .ai-gen-lightbox.is-open { display: flex; }
    .ai-gen-lightbox img {
        max-width: min(960px, 100%);
        max-height: 90vh;
        object-fit: contain;
        border-radius: 16px;
        background: #fff;
    }
    .ai-gen-lightbox button {
        position: absolute;
        top: 16px;
        right: 16px;
    }
</style>

<section class="ai-gen-page">
    <div class="ai-gen-shell max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="catalog-breadcrumb__sep" aria-hidden="true">/</span>
            <span class="catalog-breadcrumb__current">AI Design Gen</span>
        </nav>

        <header class="section-heading section-heading--catalog mb-8">
            <p class="section-heading__eyebrow">Creator Studio</p>
            <h1 class="section-heading__title">AI <span class="gradient-text">Design Gen</span></h1>
            <p class="section-heading__sub">Sketch with words. Print what you love.</p>
            <span class="section-heading__accent" aria-hidden="true"></span>
        </header>

        <div class="ai-gen-layout">
            <div class="ai-gen-stage">
                <div class="ai-gen-board{{ $hasHistory ? '' : ' is-empty' }}" id="ai-gen-board">
                    <div class="ai-gen-board__empty" id="ai-gen-empty" {{ $hasHistory ? 'hidden' : '' }}>
                        <div class="ai-gen-board__mark" aria-hidden="true">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M9.5 3.2l1.15 3.1 3.15 1.15-3.15 1.15-1.15 3.1-1.15-3.1L5.2 7.45l3.15-1.15L9.5 3.2z"/></svg>
                        </div>
                        <h2>Your print starts here</h2>
                        <p>Write a prompt on the right. We’ll generate {{ $imageCount }} designs you can drop onto a garment in Studio.</p>
                    </div>
                    <div class="ai-gen-board__art" id="ai-gen-art" {{ $hasHistory ? '' : 'hidden' }}>
                        <img id="ai-gen-preview" alt="Selected design" src="">
                    </div>
                    <div class="ai-gen-board__tools" id="ai-gen-tools" {{ $hasHistory ? '' : 'hidden' }}>
                        <button type="button" class="ai-gen-iconbtn" id="ai-gen-back" aria-label="Clear preview">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <div class="ai-gen-iconbtn-row">
                            <button type="button" class="ai-gen-iconbtn" id="ai-gen-edit" aria-label="Edit prompt">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <a class="ai-gen-iconbtn" id="ai-gen-download" href="#" download="bluprinter-design.png" aria-label="Download design">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>
                            <button type="button" class="ai-gen-iconbtn" id="ai-gen-expand" aria-label="View full size">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4h4M20 8V4h-4M4 16v4h4M20 16v4h-4"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="ai-gen-print-bar" id="ai-gen-print-bar" {{ $hasHistory ? '' : 'hidden' }}>
                    <a class="btn-cta" id="ai-gen-print" href="{{ route('studio.index') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        Print this design
                    </a>
                </div>

                <div class="ai-gen-history" id="ai-gen-history" {{ $hasHistory ? '' : 'hidden' }}>
                    <div class="ai-gen-history__head">
                        <div>
                            <h2>Your design history</h2>
                            <p>Pick up where you left off.</p>
                        </div>
                        <div class="ai-gen-history__nav">
                            <button type="button" class="ai-gen-iconbtn" id="ai-gen-hist-prev" aria-label="Previous designs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" class="ai-gen-iconbtn" id="ai-gen-hist-next" aria-label="Next designs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="ai-gen-history__strip" id="ai-gen-strip"></div>
                    <p class="mt-3 text-sm"><a href="{{ route('studio.history') }}" class="font-semibold text-[#005366] hover:underline">View all designs</a></p>
                </div>
            </div>

            <div class="ai-gen-form">
                @if (empty($aiEnabled))
                    <p class="text-[#e2150c] font-semibold">AI generation is currently turned off.</p>
                @else
                    <div class="ai-gen-form__head">
                        <h2>Prompt</h2>
                        <button type="button" class="ai-gen-tips" id="ai-gen-tips" aria-expanded="false">Tips</button>
                    </div>
                    <div class="ai-gen-tips__pop" id="ai-gen-tips-pop">Name the subject, style, and colors. Example: “koi fish, Japanese tattoo, navy and cream, transparent background”.</div>
                    <textarea id="ai-gen-prompt" maxlength="{{ $promptMax }}" placeholder="Example: {{ $promptExample }}" aria-label="Design prompt"></textarea>
                    <div class="ai-gen-form__meta">
                        <span id="ai-gen-count">0/{{ $promptMax }}</span>
                        <button type="button" class="btn-outline-petrol" id="ai-gen-improve">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.5 3.2l1.15 3.1 3.15 1.15-3.15 1.15-1.15 3.1-1.15-3.1L5.2 7.45l3.15-1.15L9.5 3.2z"/></svg>
                            AI improve prompt
                        </button>
                    </div>

                    @if (!empty($inspirationPrompts))
                        <div class="ai-gen-inspire">
                            <p class="ai-gen-inspire__label">Need inspiration? Try this prompt</p>
                            <button type="button" id="ai-gen-inspire-btn">{{ $promptExample }}</button>
                        </div>
                    @endif

                    <div class="ai-gen-refs">
                        <h3>Reference images (optional)</h3>
                        <div class="ai-gen-refs__row" id="ai-gen-refs">
                            <label class="ai-gen-refs__add" id="ai-gen-add-ref">
                                <input type="file" id="ai-gen-ref-input" accept="image/png,image/jpeg,image/webp" hidden multiple>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Add image
                            </label>
                        </div>
                    </div>

                    <div class="ai-gen-form__actions">
                        <button type="button" class="btn-cta" id="ai-gen-submit">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.5 3.2l1.15 3.1 3.15 1.15-3.15 1.15-1.15 3.1-1.15-3.1L5.2 7.45l3.15-1.15L9.5 3.2z"/></svg>
                            Generate Design
                        </button>
                        <p class="ai-gen-form__note">Free — AI will create {{ $imageCount }} designs. You’ll see progress while it runs.</p>
                    </div>
                    <div class="ai-gen-toast" id="ai-gen-toast" role="status"></div>
                @endif
            </div>
        </div>
    </div>
</section>

<div class="ai-gen-lightbox" id="ai-gen-lightbox" hidden>
    <button type="button" class="ai-gen-iconbtn" id="ai-gen-lightbox-close" aria-label="Close">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <img id="ai-gen-lightbox-img" alt="Design full size">
</div>

<script>
    window.AI_GEN_ROUTES = {
        improve: @json(route('studio.ai.improve')),
        generate: @json(route('studio.ai.generate')),
        upload: @json(route('studio.upload')),
        studio: @json(route('studio.index')),
        csrf: @json(csrf_token()),
        enabled: @json(!empty($aiEnabled)),
        history: @json($history),
        timeout: {{ (int) (\App\Support\StudioAiSettings::resolved()['timeout'] ?? 180) }},
        promptMax: {{ $promptMax }},
        maxReferences: {{ $maxReferences }},
    };
</script>
<script src="{{ asset('js/studio-ai-progress.js') }}?v={{ @filemtime(public_path('js/studio-ai-progress.js')) }}"></script>
<script src="{{ asset('js/studio-ai-page.js') }}?v={{ @filemtime(public_path('js/studio-ai-page.js')) }}"></script>
@endsection
