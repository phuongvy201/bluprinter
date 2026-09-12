@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/cart-drawer.css') }}?v={{ @filemtime(public_path('css/cart-drawer.css')) }}">
@php
    $studio = $studio ?? ['products' => [], 'designs' => [], 'categories' => [], 'tags' => [], 'tag_colors' => [], 'inspiration_prompts' => [], 'ai_enabled' => false];
    $firstProduct = $studio['products'][0] ?? null;
@endphp

<style>
    body:has(.studio-app),
    body.studio-page {
        background: #f9fafb;
    }
    /* overflow-x: clip on .min-h-screen turns overflow-y into auto, which breaks sticky ancestors */
    body:has(.studio-app) .min-h-screen {
        overflow-x: visible !important;
    }
    body:has(.studio-app) .site-footer,
    body:has(.studio-app) .site-back-to-top,
    body:has(.studio-app) #gen-ai-fab,
    body.studio-page .site-footer,
    body.studio-page .site-back-to-top,
    body.studio-page #gen-ai-fab {
        display: none !important;
    }

    .studio-shell {
        max-width: 1400px;
        margin: 0 auto;
        padding: 8px 16px 32px;
    }
    .studio-pagehead {
        margin: 0 0 20px;
        padding: 8px 4px 20px;
    }
    .studio-pagehead .section-heading__title {
        font-size: clamp(1.75rem, 3vw, 2.25rem);
    }
    .studio-pagehead .section-heading__sub {
        margin-top: 8px;
        font-size: 1rem;
    }
    .studio-app {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: visible;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        position: relative;
    }

    .studio-rail,
    .studio-stage-wrap {
        position: sticky;
        top: var(--product-show-sticky-top, 88px);
        align-self: flex-start;
        height: fit-content;
    }
    .studio-rail {
        z-index: 4;
        flex: 0 0 88px;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 4px;
        padding: 16px 8px;
        border-right: 1px solid #e5e7eb;
        background: #ffffff;
        border-radius: 16px 0 0 16px;
    }
    .studio-rail__btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 64px;
        padding: 8px 4px;
        border: 0;
        background: transparent;
        color: #4b5563;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 12px;
        cursor: pointer;
        text-decoration: none;
    }
    .studio-rail__btn svg {
        width: 24px;
        height: 24px;
    }
    .studio-rail__btn:hover,
    .studio-rail__btn.is-active {
        color: #005366;
        background: #f0f7f8;
    }

    .studio-stage-wrap {
        z-index: 3;
        flex: 1 1 0;
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 24px 32px 32px;
        background: #ffffff;
    }
    .studio-product-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.3;
        margin: 0 0 16px;
        text-align: center;
    }
    .studio-stage {
        position: relative;
        width: min(100%, 560px);
        aspect-ratio: 1;
        background: #f7f7f7;
        border-radius: 12px;
        overflow: hidden;
        flex: 0 1 auto;
    }
    .studio-stage .canvas-container {
        position: absolute !important;
        inset: 0;
        width: 100% !important;
        height: 100% !important;
    }
    .studio-stage .lower-canvas,
    .studio-stage .upper-canvas {
        width: 100% !important;
        height: 100% !important;
    }
    .studio-print-frame {
        position: absolute;
        z-index: 2;
        border: 1px dashed #d1d5db;
        box-sizing: border-box;
        pointer-events: none;
        border-radius: 4px;
    }
    .studio-mockup-thumbs {
        display: flex;
        gap: 8px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 16px;
    }
    .studio-mockup-thumb {
        width: 48px;
        height: 48px;
        padding: 4px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #ffffff;
        cursor: pointer;
    }
    .studio-mockup-thumb.is-active {
        border-color: #005366;
        box-shadow: 0 0 0 2px rgba(0, 83, 102, 0.2);
    }
    .studio-mockup-thumb img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .studio-side {
        display: flex;
        flex-direction: column;
        flex: 0 0 320px;
        align-self: flex-start;
        height: fit-content;
        border-left: 1px solid #e5e7eb;
        background: #ffffff;
        min-width: 0;
        border-radius: 0 16px 16px 0;
    }
    .studio-side__block {
        padding: 20px 20px 24px;
        border-bottom: 1px solid #e5e7eb;
    }
    .studio-side__block--checkout {
        border-bottom: 0;
        margin-top: auto;
    }
    /* Desktop: AI → color/size → checkout */
    .studio-side__block--ai { order: 1; }
    .studio-side__block--options { order: 2; }
    .studio-side__block--checkout { order: 3; }
    .studio-side__heading {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9375rem;
        font-weight: 700;
        color: #111827;
        margin: 0 0 12px;
    }
    .studio-side__heading svg {
        width: 16px;
        height: 16px;
        color: #005366;
        flex-shrink: 0;
    }
    .studio-meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 12px;
        font-size: 0.9375rem;
        color: #4b5563;
    }
    .studio-meta-row strong {
        color: #111827;
        font-weight: 700;
    }
    .studio-price-note {
        font-size: 0.75rem;
        color: #4b5563;
        line-height: 1.4;
        margin: 8px 0 0;
    }
    .studio-price-break {
        margin: 0 0 16px;
        padding: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #f9fafb;
    }
    .studio-price-break__row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 0.8125rem;
        color: #4b5563;
        line-height: 1.45;
    }
    .studio-price-break__row + .studio-price-break__row {
        margin-top: 6px;
    }
    .studio-price-break__row.is-total {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid #e5e7eb;
        font-weight: 700;
        color: #111827;
        font-size: 0.9375rem;
    }
    .studio-price-break__row.is-total span:last-child {
        color: #e2150c;
    }
    .studio-label {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .studio-swatches {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0 0 16px;
    }
    .studio-swatch {
        width: 28px;
        height: 28px;
        border-radius: 9999px;
        border: 1px solid #d1d5db;
        cursor: pointer;
        position: relative;
        padding: 0;
        flex-shrink: 0;
    }
    .studio-swatch.is-active {
        box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #005366;
    }
    .studio-select,
    .studio-input,
    .studio-textarea {
        width: 100%;
        min-height: 48px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 12px;
        font-size: 1rem;
        color: #111827;
        background: #ffffff;
    }
    .studio-textarea {
        min-height: 96px;
        resize: vertical;
        line-height: 1.45;
        margin-bottom: 12px;
    }
    .studio-link {
        color: #005366;
        font-size: 0.8125rem;
        font-weight: 600;
        text-decoration: underline;
    }
    .studio-charcount {
        font-size: 0.75rem;
        color: #9ca3af;
        text-align: right;
        margin: -8px 0 8px;
    }
    .studio-btn-improve {
        margin-top: 4px;
    }
    .studio-ai-note {
        font-size: 0.8125rem;
        color: #4b5563;
        margin-top: 8px;
    }
    .studio-btn-generate,
    .studio-btn-improve {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        min-height: 48px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.9375rem;
        cursor: pointer;
    }
    .studio-btn-generate {
        background: #ffffff;
        color: #005366;
        border: 1.5px solid #005366;
    }
    .studio-btn-generate:hover {
        background: #f0f7f8;
    }
    .studio-btn-generate:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .studio-btn-improve {
        background: #ffffff;
        color: #f26522;
        border: 1.5px solid #f26522;
    }
    .studio-btn-improve:hover {
        background: #fff4e8;
    }
    .studio-btn-improve:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .studio-btn-generate {
        margin-top: 8px;
    }
    .studio-ref-note {
        font-size: 0.8125rem;
        color: #4b5563;
        margin: -4px 0 12px;
        line-height: 1.4;
    }
    .studio-ref-drop {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        min-height: 96px;
        padding: 16px 12px;
        border: 1.5px dashed #d1d5db;
        border-radius: 8px;
        background: #f9fafb;
        color: #005366;
        font-size: 0.875rem;
        font-weight: 700;
        cursor: pointer;
        text-align: center;
        overflow: hidden;
    }
    .studio-ref-drop:hover,
    .studio-ref-drop.is-dragover {
        border-color: #005366;
        background: #f0f7f8;
    }
    .studio-ref-drop.is-busy {
        opacity: 0.65;
        pointer-events: none;
    }
    .studio-ref-drop span {
        font-size: 0.75rem;
        font-weight: 500;
        color: #6b7280;
    }
    .studio-ref-drop__title {
        font-size: 0.875rem !important;
        font-weight: 700 !important;
        color: #005366 !important;
    }
    .studio-ref-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        font-size: 0;
    }
    .studio-ref-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }
    .studio-ref-item {
        position: relative;
        width: 64px;
        height: 64px;
    }
    .studio-ref-thumb {
        width: 64px;
        height: 64px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        background: #f7f7f7;
        display: block;
    }
    .studio-ref-remove {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 22px;
        height: 22px;
        border: 0;
        border-radius: 9999px;
        background: #111827;
        color: #ffffff;
        font-size: 14px;
        line-height: 1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .studio-results {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 12px;
    }
    .studio-history {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }
    .studio-history__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #005366;
    }
    .studio-history__grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .studio-history__item {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: #f7f7f7;
        cursor: pointer;
        padding: 6px;
    }
    .studio-history__item img {
        width: 100%;
        aspect-ratio: 1;
        object-fit: contain;
        display: block;
    }
    .studio-result {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: #f7f7f7;
        cursor: pointer;
        padding: 8px;
    }
    .studio-result img {
        width: 100%;
        aspect-ratio: 1;
        object-fit: contain;
    }
    .studio-inspo {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 12px;
    }
    .studio-inspo summary {
        padding: 12px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.875rem;
        color: #111827;
    }
    .studio-inspo button {
        display: block;
        width: 100%;
        text-align: left;
        padding: 10px 12px;
        border: 0;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
        color: #4b5563;
        font-size: 0.8125rem;
        cursor: pointer;
    }
    .studio-inspo button:hover {
        background: #f7f7f7;
        color: #111827;
    }
    .studio-help {
        margin-top: 12px;
        font-size: 0.8125rem;
        color: #4b5563;
    }
    .studio-layer-actions {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        margin-bottom: 20px;
    }
    .studio-icon-btn {
        height: 40px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
        color: #111827;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .studio-icon-btn:hover { background: #f7f7f7; }
    .studio-icon-btn svg { width: 18px; height: 18px; }
    .studio-range {
        width: 100%;
        accent-color: #005366;
        margin: 8px 0 16px;
    }
    .studio-drawer {
        position: absolute;
        left: 8px;
        top: 72px;
        z-index: 8;
        width: min(300px, calc(100% - 16px));
        max-height: calc(100% - 96px);
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
    }
    .studio-drawer__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 4px;
    }
    .studio-drawer__head .studio-panel__title {
        margin: 0;
    }
    .studio-drawer__close {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        margin: -8px -8px 0 0;
        border: 0;
        background: transparent;
        border-radius: 9999px;
        cursor: pointer;
        color: #111827;
        font-size: 1.5rem;
        line-height: 1;
    }
    .studio-drawer__close:hover {
        background: #f7f7f7;
    }
    .studio-drawer[hidden] { display: none !important; }
    .studio-panel__title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.3;
    }
    .studio-panel__subtitle {
        font-size: 0.8125rem;
        color: #4b5563;
        margin: 4px 0 16px;
    }

    .studio-toast {
        position: fixed;
        top: 16px;
        right: 16px;
        z-index: 80;
        display: none;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        color: #111827;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .studio-toast.is-error { border-color: #fecaca; background: #fef2f2; color: #e2150c; }
    .studio-toast.is-success { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
    .studio-modal {
        position: fixed;
        inset: 0;
        z-index: 70;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(17, 24, 39, 0.45);
    }
    .studio-modal.is-open { display: flex; }
    .studio-modal__box {
        width: min(1100px, 100%);
        max-height: min(86vh, 820px);
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 16px 48px rgba(0,0,0,0.18);
    }
    .studio-modal__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
    }
    .studio-modal__head h2 { font-size: 1.25rem; font-weight: 700; color: #111827; }
    .studio-modal__close {
        width: 40px;
        height: 40px;
        border: 0;
        background: transparent;
        border-radius: 9999px;
        cursor: pointer;
        color: #111827;
    }
    .studio-modal__body {
        display: grid;
        grid-template-columns: 220px minmax(0, 1fr);
        min-height: 0;
        flex: 1;
    }
    .studio-modal__side {
        border-right: 1px solid #e5e7eb;
        overflow-y: auto;
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        background: #ffffff;
    }
    .studio-cat {
        text-align: left;
        border: 0;
        background: transparent;
        padding: 10px 12px;
        border-radius: 8px;
        color: #111827;
        font-weight: 600;
        font-size: 0.9375rem;
        cursor: pointer;
    }
    .studio-cat.is-active,
    .studio-cat:hover { background: #f7f7f7; }
    .studio-tag {
        border: 0;
        border-radius: 9999px;
        padding: 8px 12px;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.8125rem;
        cursor: pointer;
        text-align: left;
    }
    .studio-modal__main { padding: 16px; overflow-y: auto; min-width: 0; }
    .studio-search { position: relative; margin-bottom: 16px; }
    .studio-search input {
        width: 100%;
        min-height: 48px;
        border: 1.5px solid #f26522;
        border-radius: 8px;
        padding: 12px 44px 12px 12px;
        font-size: 1rem;
    }
    .studio-search svg {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        color: #f26522;
    }
    .studio-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 16px;
    }
    .studio-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: #ffffff;
        cursor: pointer;
        text-align: left;
        padding: 0;
    }
    .studio-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.07); }
    .studio-card__media {
        aspect-ratio: 1;
        background: #f7f7f7;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
    }
    .studio-card__media img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        -webkit-user-drag: none;
        user-select: none;
        pointer-events: none;
    }
    .studio-card__body { padding: 8px 12px 12px; }
    .studio-card__name {
        font-size: 0.8125rem;
        color: #111827;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.2em;
    }
    .studio-card__price { color: #e2150c; font-weight: 700; margin-top: 4px; font-size: 0.9375rem; }
    .studio-empty { padding: 48px 16px; text-align: center; color: #4b5563; }
    .studio-mobile-cta { display: none; }

    .studio-recently-viewed {
        padding: 32px 0 48px;
        background: #f9fafb;
    }
    .studio-recently-viewed:has(.catalog-recently-viewed.hidden) { display: none; }
    .studio-recently-viewed .catalog-recently-viewed { margin-top: 0; padding-top: 0; border-top: 0; }
    .studio-recently-viewed .catalog-recently-viewed__head { margin-bottom: 8px; text-align: center; }
    .studio-recently-viewed .catalog-recently-viewed__head .section-heading__sub,
    .studio-recently-viewed .catalog-recently-viewed__head .section-heading__accent {
        margin-left: auto;
        margin-right: auto;
    }

    @media (max-width: 1024px) {
        .studio-rail { flex-basis: 72px; border-radius: 16px 0 0 0; }
        .studio-side {
            flex: 1 1 100%;
            border-left: 0;
            border-top: 1px solid #e5e7eb;
            border-radius: 0 0 16px 16px;
        }
        /* Dưới canvas: color/size trước, AI sau */
        .studio-side__block--options { order: 1; }
        .studio-side__block--ai { order: 2; }
        .studio-side__block--checkout { order: 3; }
        .studio-drawer { left: 8px; width: min(300px, calc(100% - 16px)); }
        .studio-modal__body { grid-template-columns: 160px minmax(0, 1fr); }
    }
    @media (max-width: 720px) {
        .studio-shell { padding: 12px 12px 24px; }
        .studio-app { border-radius: 12px; }
        .studio-rail,
        .studio-stage-wrap {
            position: static;
        }
        .studio-rail {
            flex: 1 1 100%;
            flex-direction: row;
            overflow-x: auto;
            border-right: 0;
            border-bottom: 1px solid #e5e7eb;
            padding: 8px;
            border-radius: 12px 12px 0 0;
            z-index: auto;
        }
        .studio-rail__btn { min-width: 72px; min-height: 56px; }
        .studio-stage-wrap { flex: 1 1 100%; padding: 16px; }
        .studio-drawer {
            left: 12px;
            right: 12px;
            width: auto;
            top: 72px;
        }
        .studio-mobile-cta {
            display: flex;
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 40;
            gap: 12px;
            align-items: center;
            padding: 12px 16px;
            background: #ffffff;
            border-top: 1px solid #e5e7eb;
        }
        .studio-mobile-cta .studio-price { font-size: 1.25rem; }
        .studio-side__block--checkout .btn-cta { display: none; }
        .studio-modal { padding: 0; align-items: stretch; }
        .studio-modal__box { max-height: 100vh; height: 100%; border-radius: 0; width: 100%; }
        .studio-modal__head { padding: 12px 14px; }
        .studio-modal__head h2 { font-size: 1.0625rem; }
        .studio-modal__body {
            grid-template-columns: 1fr;
            grid-template-rows: auto minmax(0, 1fr);
            align-content: start;
        }
        .studio-modal__side {
            flex-direction: row;
            flex-wrap: nowrap;
            align-items: center;
            align-self: start;
            width: 100%;
            height: auto;
            max-height: none;
            gap: 8px;
            overflow-x: auto;
            overflow-y: hidden;
            border-right: 0;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 12px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .studio-modal__side::-webkit-scrollbar { display: none; }
        .studio-cat,
        .studio-tag {
            flex: 0 0 auto;
            align-self: center;
            height: auto;
            line-height: 1.25;
            white-space: nowrap;
            padding: 8px 12px;
            font-size: 0.8125rem;
        }
        .studio-modal__main { padding: 12px; }
        .studio-search { margin-bottom: 12px; }
        .studio-search input { min-height: 42px; font-size: 0.9375rem; }

        /* Mobile: 2-column card grid — avoid one huge image per row */
        .studio-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .studio-card {
            border-radius: 10px;
            min-width: 0;
        }
        .studio-card__media {
            aspect-ratio: 1;
            padding: 6px;
            max-height: none;
        }
        .studio-card__media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .studio-card__body { padding: 6px 8px 10px; }
        .studio-card__name {
            font-size: 0.75rem;
            line-height: 1.3;
            min-height: 2em;
            -webkit-line-clamp: 2;
        }
        .studio-card__price { font-size: 0.8125rem; margin-top: 2px; }
        .studio-empty { padding: 32px 12px; font-size: 0.875rem; }
        .studio-recently-viewed { padding-bottom: 96px; }
    }

    @media (min-width: 480px) and (max-width: 720px) {
        .studio-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
    }

    @media (max-width: 360px) {
        .studio-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }
        .studio-card__name { font-size: 0.6875rem; }
        .studio-card__price { font-size: 0.75rem; }
    }
</style>

@include('studio.partials.ai-progress')

<div class="studio-shell">
    <header class="studio-pagehead section-heading section-heading--catalog">
        <p class="section-heading__eyebrow">Creator Studio</p>
        <h1 class="section-heading__title">Create Your Own</h1>
        <p class="section-heading__sub">Pick a garment, add artwork, then add it to your cart.</p>
        <span class="section-heading__accent" aria-hidden="true"></span>
    </header>
    <div class="studio-app" id="studio-app">
        <aside class="studio-rail" aria-label="Studio tools">
            <button type="button" class="studio-rail__btn is-active" data-rail="custom">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Custom
            </button>
            <button type="button" class="studio-rail__btn" data-rail="products">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Products
            </button>
            <button type="button" class="studio-rail__btn" data-rail="designs">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Designs
            </button>
            <button type="button" class="studio-rail__btn" data-rail="text" aria-expanded="false">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V4h16v3M9 20h6M12 4v16"/></svg>
                Text
            </button>
            <button type="button" class="studio-rail__btn" data-rail="upload">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Upload
            </button>
            <button type="button" class="studio-rail__btn" data-rail="layers" aria-expanded="false">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l9 5-9 5-9-5 9-5zm0 10l9 5-9 5-9-5 9-5z"/></svg>
                Layers
            </button>
        </aside>

        <section class="studio-stage-wrap">
            <p class="studio-product-title" id="studio-title">{{ $firstProduct['name'] ?? $firstProduct['title'] ?? 'T-Shirts' }}</p>
            <div class="studio-stage" id="studio-stage">
                <canvas id="studio-canvas"></canvas>
                <div class="studio-print-frame" id="studio-print-frame"></div>
            </div>
            <div class="studio-mockup-thumbs" id="studio-mockup-thumbs"></div>

            <aside class="studio-drawer" id="studio-panel-layers" hidden>
                <div class="studio-drawer__head">
                    <h2 class="studio-panel__title">Layers</h2>
                    <button type="button" class="studio-drawer__close" data-close-drawer aria-label="Close layers">&times;</button>
                </div>
                <p class="studio-panel__subtitle">Scale, rotate or remove the selected design.</p>
                <div class="studio-layer-actions">
                    <button type="button" class="studio-icon-btn" data-layer-action="front" aria-label="Bring to front">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                    </button>
                    <button type="button" class="studio-icon-btn" data-layer-action="back" aria-label="Send to back">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <button type="button" class="studio-icon-btn" data-layer-action="duplicate" aria-label="Duplicate">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                    <button type="button" class="studio-icon-btn" data-layer-action="delete" aria-label="Delete">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
                <div class="studio-label">Scale <span id="studio-scale-value">72</span></div>
                <input class="studio-range" id="studio-scale" type="range" min="20" max="100" value="72">
                <div class="studio-label">Rotate <span id="studio-rotate-value">0</span></div>
                <input class="studio-range" id="studio-rotate" type="range" min="-180" max="180" value="0">
                <label class="studio-label" style="justify-content:flex-start;gap:8px;font-weight:500;">
                    <input type="checkbox" id="studio-pattern"> Pattern
                </label>
            </aside>

            <aside class="studio-drawer" id="studio-panel-text" hidden>
                <div class="studio-drawer__head">
                    <h2 class="studio-panel__title">Add text</h2>
                    <button type="button" class="studio-drawer__close" data-close-drawer aria-label="Close text panel">&times;</button>
                </div>
                <p class="studio-panel__subtitle">Place custom type in the print area.</p>
                <label class="studio-label" for="studio-text-input">Your text</label>
                <input class="studio-input" id="studio-text-input" maxlength="40" placeholder="Your text here">
                <div class="studio-label" style="margin-top:16px;">Color</div>
                <div class="studio-swatches" id="studio-text-colors">
                    <button type="button" class="studio-swatch is-active" data-text-color="#111827" style="background:#111827" aria-label="Black"></button>
                    <button type="button" class="studio-swatch is-light" data-text-color="#ffffff" style="background:#ffffff" aria-label="White"></button>
                    <button type="button" class="studio-swatch" data-text-color="#e2150c" style="background:#e2150c" aria-label="Red"></button>
                    <button type="button" class="studio-swatch" data-text-color="#005366" style="background:#005366" aria-label="Petrol"></button>
                    <button type="button" class="studio-swatch" data-text-color="#f26522" style="background:#f26522" aria-label="Orange"></button>
                </div>
                <button type="button" class="btn-outline-petrol" id="studio-add-text" style="margin-top:16px;">Add text</button>
            </aside>
        </section>

        <aside class="studio-side" id="studio-panel-custom">
            <div class="studio-side__block studio-side__block--options">
                <h2 class="studio-side__heading">Color and size</h2>
                <div id="studio-color-block">
                    <div class="studio-meta-row">
                        <span>Color</span>
                        <strong id="studio-color-name">—</strong>
                    </div>
                    <div class="studio-swatches" id="studio-swatches" role="listbox" aria-label="Garment color"></div>
                </div>
                <div id="studio-size-block">
                    <select class="studio-select" id="studio-size" aria-label="Size">
                        <option value="">Select size</option>
                    </select>
                    <p class="studio-price-note" id="studio-size-price-note">Each size has its own garment price.</p>
                    <a class="studio-link" id="studio-size-guide" href="#" target="_blank" rel="noopener" style="display:inline-block;margin-top:8px;">Size guide</a>
                </div>
            </div>

            <div class="studio-side__block studio-side__block--ai">
                <h2 class="studio-side__heading">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2 2-6z"/></svg>
                    1. Describe your design
                </h2>
                <textarea class="studio-textarea" id="studio-prompt" maxlength="{{ (int) ($studio['prompt_max'] ?? 1000) }}" placeholder="Example: vintage camping tee, mountains, campfire, retro colors" aria-label="Describe your design"></textarea>
                <div class="studio-charcount"><span id="studio-prompt-count">0</span>/{{ (int) ($studio['prompt_max'] ?? 1000) }}</div>
                <button type="button" class="studio-btn-improve" id="studio-improve">Write prompt</button>

                <h2 class="studio-side__heading" style="margin-top:16px;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    2. Reference images
                </h2>
                <p class="studio-ref-note">Upload up to 4 photos. Write prompt and Generate design both send these photos to the AI so the graphic follows the same subject, colors, and style.</p>
                <label class="studio-ref-drop" id="studio-add-ref">
                    <span class="studio-ref-drop__title">Add reference photos</span>
                    <span>PNG, JPG, or WEBP · drag and drop or browse</span>
                    <input class="studio-ref-input" type="file" id="studio-ref-input" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" multiple>
                </label>
                <div class="studio-ref-list" id="studio-ref-list"></div>

                <button type="button" class="studio-btn-generate" id="studio-generate">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2 2-6z"/></svg>
                    Generate design
                </button>
                <p class="studio-price-note" id="studio-ai-fee-note">Generating and uploading artwork is free. You only pay the garment size price.</p>
                <details class="studio-inspo">
                    <summary>AI options</summary>
                    <div id="studio-inspo-list"></div>
                    <p class="studio-ai-note" id="studio-ai-note">{{ !empty($studio['ai_enabled']) ? 'Write your idea, tap Write prompt, then Generate design to create '.(int) \App\Support\StudioAiSettings::resolved()['image_count'].' graphics.' : 'AI is not configured yet. Upload a file or pick a library design.' }}</p>
                </details>
                <div class="studio-results" id="studio-results"></div>
                <div class="studio-history" id="studio-history" hidden></div>
            </div>

            <div class="studio-side__block studio-side__block--checkout">
                <label class="studio-label" for="studio-product-name">Product name</label>
                <input class="studio-input" id="studio-product-name" maxlength="120" placeholder="e.g. My camping wolf tee" aria-label="Product name">
                <p class="studio-ref-note" style="margin:8px 0 16px;">This name appears in your cart. It is your custom product — not a catalog listing.</p>
                <div class="studio-meta-row" style="margin-bottom:8px;">
                    <span>Total</span>
                    <p class="studio-price" id="studio-price">{{ $firstProduct ? format_price((float) $firstProduct['price']) : format_price(0) }}</p>
                </div>
                <div class="studio-price-break" id="studio-price-breakdown"></div>
                <button type="button" class="btn-cta js-studio-add-cart" id="studio-add-cart" style="width:100%;">Add to cart</button>
                <p class="studio-help">Having trouble? <a class="studio-link" href="{{ $studio['support_url'] ?? route('support.ticket.create') }}">Submit a ticket</a></p>
            </div>
        </aside>
    </div>
</div>

<section class="studio-recently-viewed" aria-label="Recently viewed">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @include('partials.recently-viewed-section', [
            'recentlyViewedId' => 'studio-recently-viewed',
            'hideWhenEmpty' => false,
        ])
    </div>
</section>

<div class="studio-mobile-cta">
    <div class="studio-price" id="studio-price-mobile">{{ $firstProduct ? format_price((float) $firstProduct['price']) : format_price(0) }}</div>
    <button type="button" class="btn-cta" id="studio-add-cart-mobile" style="flex:1;">Add to cart</button>
</div>

<div class="studio-toast" id="studio-toast" role="status"></div>
<input type="file" id="studio-upload-input" accept="image/*" hidden>
<button type="button" id="studio-ai-toggle" hidden aria-hidden="true"></button>
<div id="studio-panel-ai" hidden></div>

<div class="studio-modal" id="studio-modal-products" role="dialog" aria-modal="true" aria-labelledby="studio-modal-products-title">
    <div class="studio-modal__box">
        <div class="studio-modal__head">
            <h2 id="studio-modal-products-title">Choose Product</h2>
            <button type="button" class="studio-modal__close" data-close-modal aria-label="Close">&times;</button>
        </div>
        <div class="studio-modal__body">
            <div class="studio-modal__side" id="studio-product-cats"></div>
            <div class="studio-modal__main">
                <div class="studio-search">
                    <input type="search" id="studio-product-search" placeholder="Find Products" autocomplete="off">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                </div>
                <div class="studio-grid" id="studio-product-grid"></div>
            </div>
        </div>
    </div>
</div>

<div class="studio-modal" id="studio-modal-designs" role="dialog" aria-modal="true" aria-labelledby="studio-modal-designs-title">
    <div class="studio-modal__box">
        <div class="studio-modal__head">
            <h2 id="studio-modal-designs-title">Choose a design</h2>
            <button type="button" class="studio-modal__close" data-close-modal aria-label="Close">&times;</button>
        </div>
        <div class="studio-modal__body">
            <div class="studio-modal__side" id="studio-design-tags"></div>
            <div class="studio-modal__main">
                <div class="studio-search">
                    <input type="search" id="studio-design-search" placeholder="Find Designs" autocomplete="off">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                </div>
                <div class="studio-grid" id="studio-design-grid"></div>
            </div>
        </div>
    </div>
</div>

<script>
    window.STUDIO_DATA = @json($studio);
    window.STUDIO_ROUTES = {
        upload: @json(route('studio.upload')),
        media: @json(route('studio.media')),
        improve: @json(route('studio.ai.improve')),
        generate: @json(route('studio.ai.generate')),
        history: @json(route('studio.history')),
        cartAdd: @json(route('api.cart.add')),
        cartGet: @json(url('/api/cart/get')),
        s3Base: @json(\App\Support\S3Media::PUBLIC_BASE),
        csrf: @json(csrf_token())
    };
    window.CURRENT_CURRENCY = @json(currency());
    window.CURRENCY_SYMBOL = @json(currency_symbol());
    window.CURRENT_CURRENCY_RATE = {{ currency_rate() ?? 1 }};
    window.AUTH_USER = {!! json_encode(auth()->check() ? [
        'email' => auth()->user()->email,
        'name' => auth()->user()->name,
        'country' => auth()->user()->country ?? null,
    ] : null) !!};
    window.CART_CHECKOUT_URL = @json(route('checkout.index'));
    window.CART_INDEX_URL = @json(route('cart.index'));
    window.PRODUCTS_INDEX_URL = @json(route('products.index'));
    window.CART_CROSS_SELL_PRODUCTS = [];
</script>
<script src="{{ asset('js/studio-ai-progress.js') }}?v={{ @filemtime(public_path('js/studio-ai-progress.js')) }}"></script>
<script src="{{ asset('js/vendor/fabric.min.js') }}"></script>
<script src="{{ asset('js/studio-cart-drawer.js') }}?v={{ @filemtime(public_path('js/studio-cart-drawer.js')) }}"></script>
<script src="{{ asset('js/studio.js') }}?v={{ @filemtime(public_path('js/studio.js')) }}"></script>
@endsection
