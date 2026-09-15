@extends('layouts.app')

@section('content')
@php
    $homeSections = $homeSettings['sections'] ?? [];
    $heroAutoplayMs = (int) ($homeSettings['hero']['autoplay_ms'] ?? 5000);
    $whyChooseSection = $homeSections['why_choose'] ?? [];
    $whyChooseFeatures = $whyChooseSection['features'] ?? [];
    $whyChooseAutoplayMs = (int) ($whyChooseSection['autoplay_ms'] ?? 4500);
    $customizeSection = $homeSections['customize_hero'] ?? [];
    $leftHeroSlides = $leftHeroSlides ?? [];
    $rightHeroSlides = $rightHeroSlides ?? [];
    $mobileHeroSlides = collect($leftHeroSlides)
        ->values()
        ->map(fn (array $slide, int $i) => array_merge($slide, ['_side' => 'left', '_side_index' => $i]))
        ->concat(
            collect($rightHeroSlides)
                ->values()
                ->map(fn (array $slide, int $i) => array_merge($slide, ['_side' => 'right', '_side_index' => $i]))
        )
        ->values()
        ->all();
@endphp

@include('home.partials.inline-editor')
<script>
// Track Facebook Pixel ViewContent for home page
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_name: 'Home Page',
            content_type: 'home'
        });
    }
});
</script>
<style>
    /* Home page colors — DESIGN.md tokens (see layouts/app.blade.php :root) */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-fadeInUp {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .animate-fadeIn {
        animation: fadeIn 0.6s ease-out forwards;
    }

    .animate-scaleIn {
        animation: scaleIn 0.5s ease-out forwards;
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }

    .animate-rotate {
        animation: rotate 20s linear infinite;
    }

    .animate-pulse {
        animation: pulse 2s ease-in-out infinite;
    }

    .animate-slideInLeft {
        animation: slideInLeft 0.6s ease-out forwards;
    }

    .animate-slideInRight {
        animation: slideInRight 0.6s ease-out forwards;
    }

    .stagger-1 { animation-delay: 0.1s; opacity: 0; }
    .stagger-2 { animation-delay: 0.2s; opacity: 0; }
    .stagger-3 { animation-delay: 0.3s; opacity: 0; }
    .stagger-4 { animation-delay: 0.4s; opacity: 0; }
    .stagger-5 { animation-delay: 0.5s; opacity: 0; }
    .stagger-6 { animation-delay: 0.6s; opacity: 0; }

    .scroll-reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    .scroll-reveal.revealed {
        opacity: 1;
        transform: translateY(0);
    }

    .gradient-text {
        background: linear-gradient(135deg, #005366 0%, #e2150c 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .section-heading {
        text-align: center;
    }
    .section-heading__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        font-size: 0.8125rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #f26522;
    }
    .section-heading__eyebrow::before,
    .section-heading__eyebrow::after {
        content: '';
        display: block;
        width: 28px;
        height: 2px;
        border-radius: 9999px;
        background: linear-gradient(90deg, #f26522, #e2150c);
    }
    .section-heading__title {
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(2rem, 4.5vw, 3.25rem);
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: 0.02em;
        color: #111827;
        margin: 0;
    }
    .section-heading__title .gradient-text {
        background: linear-gradient(105deg, #005366 0%, #f26522 45%, #e2150c 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .section-heading__sub {
        margin: 12px auto 0;
        max-width: 36rem;
        font-size: 1.125rem;
        font-weight: 400;
        line-height: 1.55;
        color: #4b5563;
    }
    @media (min-width: 768px) {
        .section-heading__sub {
            font-size: 1.25rem;
        }
    }
    .section-heading__accent {
        display: block;
        width: 64px;
        height: 4px;
        margin: 16px auto 0;
        border-radius: 9999px;
        background: linear-gradient(90deg, #005366, #f26522, #e2150c);
    }

    /* Pick a Gift — circular collection slider */
    .pick-a-gift__slider {
        position: relative;
        margin-top: 1.25rem;
        min-width: 0;
        max-width: 100%;
    }
    @media (min-width: 768px) {
        .pick-a-gift__slider {
            margin-top: 1.5rem;
        }
    }
    .pick-a-gift__track-wrap {
        overflow-x: auto;
        overflow-y: hidden;
        scroll-behavior: smooth;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        min-width: 0;
        max-width: 100%;
        width: 100%;
    }
    .pick-a-gift__track-wrap::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }
    @media (min-width: 768px) {
        /* Desktop: cuộn bằng nút, không hiện thanh cuộn */
        .pick-a-gift__track-wrap {
            overflow-x: hidden;
        }
    }
    .pick-a-gift__track {
        display: flex;
        flex-wrap: nowrap;
        gap: 28px;
        padding: 8px 0 16px;
        width: max-content;
        max-width: none;
    }
    @media (min-width: 768px) {
        .pick-a-gift__track {
            gap: 40px;
        }
    }
    .pick-a-gift__item {
        flex: 0 0 auto;
        width: clamp(120px, 22vw, 200px);
        scroll-snap-align: start;
        min-width: 0;
    }
    .pick-a-gift__nav {
        position: absolute;
        top: 50%;
        z-index: 10;
        display: none;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 9999px;
        background: #fff;
        color: #4b5563;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        border: 1px solid #e5e7eb;
        transform: translateY(-50%);
        transition: background 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    }
    @media (min-width: 768px) {
        .pick-a-gift__nav.pick-a-gift__nav--visible {
            display: flex;
        }
    }
    .pick-a-gift__nav:hover {
        background: #f9fafb;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.14);
    }
    .pick-a-gift__nav:disabled {
        opacity: 0;
        pointer-events: none;
    }
    .pick-a-gift__nav--prev {
        left: 4px;
    }
    .pick-a-gift__nav--next {
        right: 4px;
    }
    @media (min-width: 1280px) {
        .pick-a-gift__nav--prev {
            left: 0;
        }
        .pick-a-gift__nav--next {
            right: 0;
        }
    }
    .pick-a-gift__circle {
        position: relative;
        display: block;
        width: 100%;
        aspect-ratio: 1;
        border-radius: 50%;
        overflow: hidden;
        background: #f7f7f7;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        transition: box-shadow 0.3s ease, transform 0.3s ease;
    }
    .pick-a-gift__circle:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }
    .pick-a-gift__circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.45s ease;
    }
    .pick-a-gift__circle:hover img {
        transform: scale(1.06);
    }
    .pick-a-gift__circle__placeholder {
        position: absolute;
        inset: 0;
        background: linear-gradient(145deg, #005366 0%, #003d4d 100%);
    }
    .pick-a-gift__label {
        position: absolute;
        inset: 0;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px;
        text-align: center;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(0.75rem, 2.2vw, 1.0625rem);
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #fff;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.55);
        pointer-events: none;
    }
    .pick-a-gift__label::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: -1;
        border-radius: 50%;
        background: radial-gradient(circle at center, rgba(0, 0, 0, 0.08) 0%, rgba(0, 0, 0, 0.35) 100%);
        transition: background 0.3s ease;
    }
    .pick-a-gift__circle:hover .pick-a-gift__label::before {
        background: radial-gradient(circle at center, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.45) 100%);
    }

    /* Asymmetric collections mosaic — desktop only (mobile uses carousel) */
    .collections-mosaic {
        display: none;
        flex-direction: column;
        gap: 16px;
    }
    @media (min-width: 768px) {
        .collections-mosaic {
            display: flex;
        }
    }
    .collections-mosaic-row {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    @media (min-width: 768px) {
        .collections-mosaic-row {
            flex-direction: row;
            gap: 24px;
        }
    }
    .collections-mosaic-tile {
        position: relative;
        display: block;
        min-height: 180px;
        border-radius: 12px;
        overflow: hidden;
        background: #003d4d;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    @media (min-width: 768px) {
        .collections-mosaic-tile {
            min-height: 220px;
        }
    }
    .collections-mosaic-tile:hover {
        box-shadow: 0 10px 24px rgba(0, 83, 102, 0.18);
        transform: translateY(-2px);
    }
    .collections-mosaic-tile img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.35s ease;
    }
    .collections-mosaic-tile:hover img {
        transform: scale(1.05);
    }
    .collections-mosaic-tile__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 61, 77, 0.25) 0%, rgba(0, 61, 77, 0.72) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        text-align: center;
    }
    .collections-mosaic-tile__title {
        font-size: 1.125rem;
        font-weight: 700;
        line-height: 1.25;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        max-width: 100%;
    }
    @media (min-width: 768px) {
        .collections-mosaic-tile__title {
            font-size: 1.25rem;
        }
    }

    /* Latest Collections — mobile carousel */
    .collections-carousel {
        position: relative;
        margin-top: 1.25rem;
        min-width: 0;
    }
    .collections-carousel__slider {
        position: relative;
    }
    .collections-carousel__track-wrap {
        overflow-x: auto;
        overflow-y: hidden;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding: 12px 0 8px;
        perspective: 1200px;
    }
    .collections-carousel__track-wrap::-webkit-scrollbar {
        display: none;
    }
    .collections-carousel__track {
        display: flex;
        gap: 16px;
        padding: 0 calc((100% - min(82vw, 320px)) / 2);
        width: max-content;
    }
    .collections-carousel__slide {
        flex: 0 0 min(82vw, 320px);
        scroll-snap-align: center;
        scroll-snap-stop: always;
        transition: transform 0.45s cubic-bezier(0.34, 1.2, 0.64, 1), opacity 0.45s ease, filter 0.45s ease;
        transform: scale(0.88) translateY(8px);
        opacity: 0.55;
        filter: brightness(0.82);
    }
    .collections-carousel__slide.is-active {
        transform: scale(1) translateY(0);
        opacity: 1;
        filter: brightness(1);
    }
    .collections-carousel__slide.is-adjacent {
        transform: scale(0.94) translateY(4px);
        opacity: 0.78;
        filter: brightness(0.92);
    }
    .collections-carousel__tile {
        position: relative;
        display: block;
        min-height: 220px;
        border-radius: 16px;
        overflow: hidden;
        background: #003d4d;
        box-shadow: 0 8px 24px rgba(0, 61, 77, 0.15);
        transition: box-shadow 0.45s ease;
    }
    .collections-carousel__slide.is-active .collections-carousel__tile {
        box-shadow: 0 16px 40px rgba(0, 83, 102, 0.28), 0 0 0 2px rgba(242, 101, 34, 0.35);
    }
    .collections-carousel__tile img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    .collections-carousel__slide.is-active .collections-carousel__tile img {
        animation: collectionsKenBurns 10s ease-in-out infinite alternate;
    }
    @keyframes collectionsKenBurns {
        from { transform: scale(1); }
        to { transform: scale(1.1); }
    }
    .collections-carousel__tile__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 61, 77, 0.2) 0%, rgba(0, 61, 77, 0.78) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        text-align: center;
    }
    .collections-carousel__slide.is-active .collections-carousel__tile__overlay::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(105deg, transparent 40%, rgba(255, 255, 255, 0.12) 50%, transparent 60%);
        animation: collectionsShimmer 3s ease-in-out infinite;
        pointer-events: none;
    }
    @keyframes collectionsShimmer {
        0%, 100% { transform: translateX(-120%); opacity: 0; }
        50% { transform: translateX(120%); opacity: 1; }
    }
    .collections-carousel__tile__title {
        position: relative;
        z-index: 1;
        font-size: 1.125rem;
        font-weight: 700;
        line-height: 1.25;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        transform: translateY(12px);
        opacity: 0.7;
        transition: transform 0.45s cubic-bezier(0.34, 1.2, 0.64, 1), opacity 0.45s ease;
    }
    .collections-carousel__slide.is-active .collections-carousel__tile__title {
        transform: translateY(0);
        opacity: 1;
    }
    .collections-carousel__nav {
        position: absolute;
        top: 50%;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        background: #fff;
        color: #4b5563;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        transform: translateY(-50%);
        transition: background 0.2s ease, opacity 0.2s ease;
    }
    .collections-carousel__nav:disabled {
        opacity: 0;
        pointer-events: none;
    }
    .collections-carousel__nav--prev { left: 4px; }
    .collections-carousel__nav--next { right: 4px; }
    .collections-carousel__dots {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 20px;
    }
    .collections-carousel__dot {
        width: 8px;
        height: 8px;
        border-radius: 9999px;
        background: #d1d5db;
        border: none;
        padding: 0;
        cursor: pointer;
        transition: width 0.35s cubic-bezier(0.34, 1.2, 0.64, 1), background 0.25s ease;
    }
    .collections-carousel__dot.is-active {
        width: 28px;
        background: linear-gradient(90deg, #005366, #f26522);
    }
    @media (prefers-reduced-motion: reduce) {
        .collections-carousel__slide.is-active .collections-carousel__tile img {
            animation: none;
        }
        .collections-carousel__slide.is-active .collections-carousel__tile__overlay::after {
            animation: none;
        }
        .collections-carousel__track-wrap {
            scroll-behavior: auto;
        }
    }

    /* Latest collections mosaic — see app layout for customize-hero */
    /* Flash Sale — compact header + asymmetric carousel */
    .flash-deal {
        background: linear-gradient(135deg, var(--petrol) 0%, var(--petrol-dark) 55%, var(--petrol) 100%);
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 8px 28px rgba(0, 83, 102, 0.22);
        overflow: hidden;
    }
    @media (min-width: 768px) {
        .flash-deal {
            padding: 20px;
        }
    }
    .flash-deal__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
        color: #fff;
        text-align: left;
    }
    .flash-deal__header-left {
        min-width: 0;
        flex: 1;
    }
    .flash-deal__title {
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(1.25rem, 3.5vw, 1.75rem);
        font-weight: 700;
        line-height: 1.15;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        margin: 0;
    }
    .flash-deal__tagline {
        margin-top: 4px;
        font-size: 0.8125rem;
        font-weight: 500;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.92);
        line-height: 1.35;
    }
    .flash-deal__countdown-wrap {
        flex-shrink: 0;
        text-align: right;
    }
    .flash-deal__countdown-label {
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 4px;
    }
    .flash-deal__countdown {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .flash-deal__countdown-segment {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        padding: 6px 8px;
        background: var(--petrol-dark);
        color: #fff;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: 1.125rem;
        font-weight: 700;
        line-height: 1;
        border-radius: 8px;
        font-variant-numeric: tabular-nums;
    }
    @media (min-width: 768px) {
        .flash-deal__countdown-segment {
            min-width: 48px;
            padding: 8px 10px;
            font-size: 1.375rem;
        }
    }
    .flash-deal__countdown-colon {
        font-size: 1rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.9);
    }
    @media (min-width: 768px) {
        .flash-deal__countdown-colon {
            font-size: 1.125rem;
        }
    }
    .flash-deal__slider {
        position: relative;
    }
    .flash-deal__track-wrap {
        overflow-x: auto;
        overflow-y: hidden;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding: 4px 0 8px;
    }
    .flash-deal__track-wrap::-webkit-scrollbar {
        display: none;
    }
    .flash-deal__track {
        display: flex;
        align-items: stretch;
        gap: 12px;
        width: max-content;
        padding: 0 4px;
    }
    @media (min-width: 768px) {
        .flash-deal__track {
            gap: 16px;
        }
    }
    .flash-deal__card {
        flex: 0 0 140px;
        width: 140px;
        scroll-snap-align: start;
    }
    @media (min-width: 768px) {
        .flash-deal__card {
            flex: 0 0 180px;
            width: 180px;
        }
    }
    .flash-deal__card-inner {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .flash-deal__card-inner:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
    }
    .flash-deal__media {
        position: relative;
        aspect-ratio: 1;
        overflow: hidden;
        background: #f7f7f7;
    }
    .flash-deal__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.35s ease;
    }
    .flash-deal__card-inner:hover .flash-deal__media img {
        transform: scale(1.05);
    }
    .flash-deal__badge {
        position: absolute;
        top: 8px;
        left: 8px;
        padding: 4px 8px;
        background: #e2150c;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.3;
        border-radius: 9999px;
    }
    .flash-deal__body {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 8px;
    }
    .flash-deal__name {
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.3;
        color: #111827;
        margin: 0 0 6px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .flash-deal__prices {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 4px 8px;
        margin-top: auto;
    }
    .flash-deal__price {
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
    }
    .flash-deal__price-old {
        font-size: 0.8125rem;
        color: #9ca3af;
        text-decoration: line-through;
    }
    .flash-deal__off-tag {
        font-size: 0.75rem;
        font-weight: 600;
        color: #e2150c;
        background: rgba(226, 21, 12, 0.08);
        padding: 2px 8px;
        border-radius: 9999px;
    }
    .flash-deal__stock {
        margin-top: 6px;
    }
    .flash-deal__stock-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #d97706;
        margin-bottom: 4px;
    }
    .flash-deal__stock-bar {
        height: 6px;
        background: #e5e7eb;
        border-radius: 9999px;
        overflow: hidden;
    }
    .flash-deal__stock-fill {
        height: 100%;
        background: linear-gradient(90deg, #f26522, #e2150c);
        border-radius: 9999px;
        transition: width 0.4s ease;
    }
    .flash-deal__nav {
        position: absolute;
        top: 50%;
        z-index: 5;
        display: none;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.95);
        color: #4b5563;
        border: none;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
        transform: translateY(-50%);
        transition: background 0.2s ease, opacity 0.2s ease;
    }
    @media (min-width: 768px) {
        .flash-deal__nav.flash-deal__nav--visible {
            display: flex;
        }
    }
    .flash-deal__nav:disabled {
        opacity: 0;
        pointer-events: none;
    }
    .flash-deal__nav--prev { left: -4px; }
    .flash-deal__nav--next { right: -4px; }
    .flash-deal__footer {
        margin-top: 12px;
        text-align: center;
    }
    .flash-deal__view-all {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 20px;
        background: #fff;
        color: #005366;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 9999px;
        border: 2px solid #fff;
        transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }
    .flash-deal__view-all:hover {
        background: #005366;
        color: #fff;
        transform: translateY(-1px);
    }

    /* Top Picks — asymmetric bento + uniform row */
    .top-picks {
        --top-picks-gap: 16px;
    }
    .top-picks__header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }
    .top-picks__header-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #4b5563;
        transition: color 0.2s ease, transform 0.2s ease;
        flex-shrink: 0;
    }
    .top-picks__header-link:hover {
        color: #005366;
        transform: translateX(2px);
    }
    .top-picks-bento {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--top-picks-gap);
        align-items: stretch;
    }
    @media (min-width: 768px) {
        .top-picks-bento {
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: auto auto auto;
        }
    }
    .top-picks-bento__featured {
        grid-column: 1 / -1;
        display: flex;
        min-height: 300px;
    }
    @media (min-width: 768px) {
        .top-picks-bento__featured {
            grid-column: 1;
            grid-row: 1 / span 2;
            min-height: 0;
        }
    }
    .top-picks-bento__featured .collection-promo-card {
        flex: 1;
        width: 100%;
    }
    .top-picks-bento__promo {
        grid-column: 1 / -1;
        display: flex;
        min-height: 160px;
    }
    @media (min-width: 768px) {
        .top-picks-bento__promo {
            grid-column: 1 / span 2;
            grid-row: 3;
        }
    }
    .top-picks-bento__promo .collection-promo-card {
        flex: 1;
        width: 100%;
    }
    .top-picks-bento__slot {
        min-width: 0;
    }
    @media (min-width: 768px) {
        .top-picks-bento__slot--r3-1 {
            grid-column: 3;
            grid-row: 3;
        }
        .top-picks-bento__slot--r3-2 {
            grid-column: 4;
            grid-row: 3;
        }
    }
    .top-picks-uniform {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--top-picks-gap);
        margin-top: var(--top-picks-gap);
    }
    @media (min-width: 768px) {
        .top-picks-uniform {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .product-price {
        color: #003d4d;
    }

    /* Blog posts — compact editorial cards */
    .blog-posts__top {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }
    @media (min-width: 768px) {
        .blog-posts__top {
            flex-direction: row;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 28px;
        }
        .blog-posts__top .section-heading {
            text-align: left;
            flex: 1;
        }
        .blog-posts__top .section-heading__sub {
            margin-left: 0;
        }
        .blog-posts__top .section-heading__accent {
            margin-left: 0;
        }
    }
    .blog-posts__see-all {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
        font-size: 0.9375rem;
        font-weight: 600;
        color: #005366;
        text-decoration: none;
        white-space: nowrap;
        transition: color 0.2s ease;
    }
    .blog-posts__see-all:hover {
        color: #003d4d;
    }
    .blog-posts__see-all svg {
        width: 16px;
        height: 16px;
        transition: transform 0.2s ease;
    }
    .blog-posts__see-all:hover svg {
        transform: translateX(2px);
    }

    /* Recently viewed */
    .recently-viewed__slider {
        position: relative;
        margin-top: 24px;
    }
    .recently-viewed__track-wrap {
        overflow-x: auto;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }
    .recently-viewed__track {
        display: flex;
        align-items: stretch;
        gap: 16px;
        padding-bottom: 4px;
    }
    .recently-viewed__track > .product-card,
    .recently-viewed__track > .product-card-pdp {
        flex: 0 0 172px;
        width: 172px;
        min-width: 0;
        align-self: stretch;
        height: auto;
    }
    @media (min-width: 640px) {
        .recently-viewed__track > .product-card,
        .recently-viewed__track > .product-card-pdp {
            flex: 0 0 200px;
            width: 200px;
        }
    }
    @media (min-width: 1024px) {
        .recently-viewed__track > .product-card,
        .recently-viewed__track > .product-card-pdp {
            flex: 0 0 220px;
            width: 220px;
        }
    }
    .recently-viewed__nav {
        position: absolute;
        top: 40%;
        z-index: 5;
        display: none;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.95);
        color: #4b5563;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        transform: translateY(-50%);
        transition: border-color 0.2s ease, color 0.2s ease, opacity 0.2s ease;
        cursor: pointer;
    }
    .recently-viewed__nav.recently-viewed__nav--visible {
        display: flex;
    }
    .recently-viewed__nav:hover:not(:disabled) {
        border-color: #005366;
        color: #005366;
    }
    .recently-viewed__nav:disabled {
        opacity: 0;
        pointer-events: none;
    }
    .recently-viewed__nav--prev { left: -8px; }
    .recently-viewed__nav--next { right: -8px; }
    .recently-viewed__empty {
        text-align: center;
        padding: 40px 16px;
        border: 1px dashed #e5e7eb;
        border-radius: 16px;
        background: #fff;
    }
    .recently-viewed__empty-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        color: #d1d5db;
    }
    .recently-viewed__empty-title {
        font-size: 1rem;
        font-weight: 600;
        color: #4b5563;
        margin: 0 0 4px;
    }
    .recently-viewed__empty-sub {
        font-size: 0.875rem;
        color: #9ca3af;
        margin: 0;
    }

    /* Why Choose Bluprinter — mobile tab panels */
    .why-choose__mobile {
        display: block;
        margin-top: 20px;
    }
    .why-choose__tab-list {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .why-choose__tab-list::-webkit-scrollbar {
        display: none;
    }
    .why-choose__tab {
        flex: 0 0 auto;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #374151;
        border-radius: 9999px;
        padding: 8px 14px;
        font-size: 0.8125rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.25s ease;
        white-space: nowrap;
    }
    .why-choose__tab.is-active {
        background: #005366;
        border-color: #005366;
        color: #fff;
        box-shadow: 0 8px 20px rgba(0, 83, 102, 0.25);
    }
    .why-choose__tab-panels {
        position: relative;
        margin-top: 14px;
        min-height: 180px;
    }
    .why-choose__tab-panel {
        position: absolute;
        inset: 0;
        opacity: 0;
        visibility: hidden;
        transform: translateY(12px);
        transition: opacity 0.35s ease, transform 0.35s ease, visibility 0.35s;
        pointer-events: none;
    }
    .why-choose__tab-panel.is-active {
        position: relative;
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: auto;
    }
    .why-choose__tab-panel .why-choose__card {
        min-height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: linear-gradient(135deg, rgba(0,83,102,0.06), rgba(242,101,34,0.08));
        border: 1px solid rgba(0,83,102,0.12);
    }
    .why-choose__tab-panel .why-choose__icon {
        margin-bottom: 12px;
    }
    .why-choose__grid {
        display: none;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-top: 32px;
    }
    @media (min-width: 768px) {
        .why-choose__mobile {
            display: none;
        }
        .why-choose__grid {
            display: grid;
        }
    }
    @media (min-width: 1024px) {
        .why-choose__grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }
    }
    .why-choose__card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 24px 20px;
        text-align: center;
        height: 100%;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .why-choose__card:hover {
        border-color: #d1d5db;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }
    .why-choose__icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }
    .why-choose__icon svg {
        width: 26px;
        height: 26px;
    }
    .why-choose__icon--petrol {
        background: rgba(0, 83, 102, 0.1);
        color: #005366;
    }
    .why-choose__icon--cta {
        background: rgba(226, 21, 12, 0.08);
        color: #e2150c;
    }
    .why-choose__icon--orange {
        background: rgba(242, 101, 34, 0.1);
        color: #f26522;
    }
    .why-choose__title {
        font-size: 1.0625rem;
        font-weight: 700;
        line-height: 1.35;
        color: #111827;
        margin: 0 0 8px;
    }
    .why-choose__desc {
        font-size: 0.875rem;
        line-height: 1.55;
        color: #4b5563;
        margin: 0;
    }

    /* Mobile scrollbar hiding for horizontal carousels */
    @media (max-width: 1023px) {
        .mobile-scroll-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .mobile-scroll-hide::-webkit-scrollbar {
            display: none;
        }
    }

    .product-badge {
        background: #e2150c;
        color: #fff;
        border-radius: 4px;
        font-weight: 600;
    }

    .hero-banner-title {
        font-family: Oswald, Figtree, sans-serif;
        font-weight: 700;
        font-style: italic;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .hero-overlay {
        background: linear-gradient(180deg, transparent 40%, rgba(0, 0, 0, 0.45) 100%);
    }

    /* Hero banners — fade autoplay slideshow (left / right columns) */
    .hero-carousel {
        position: relative;
        min-width: 0;
        height: 100%;
    }
    .hero-carousel__viewport {
        position: relative;
        height: 100%;
        min-height: inherit;
        border-radius: 16px;
        overflow: hidden;
    }
    .hero-carousel__slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.65s ease, visibility 0.65s ease;
        pointer-events: none;
    }
    .hero-carousel__slide.is-active {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        z-index: 2;
    }
    .hero-carousel__card {
        position: relative;
        display: block;
        height: 100%;
        min-height: inherit;
        border-radius: 16px;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
    }
    .hero-carousel__card img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.8s ease;
    }
    .hero-carousel__slide.is-active .hero-carousel__card img {
        transform: scale(1.03);
    }
    .hero-carousel__vignette {
        position: absolute;
        inset: 0;
        z-index: 1;
        background: linear-gradient(180deg, transparent 42%, rgba(0, 0, 0, 0.5) 100%);
        pointer-events: none;
    }
    .hero-carousel__content {
        position: relative;
        z-index: 10;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        height: 100%;
        min-height: inherit;
        padding: 24px;
    }
    .hero-carousel__progress {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 3px;
        background: rgba(255,255,255,0.25);
        z-index: 12;
    }
    .hero-carousel__progress-bar {
        display: block;
        height: 100%;
        width: 0;
        background: #f26522;
        transition: width linear;
    }
    .hero-carousel__dots {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 12px;
    }
    .hero-carousel--desktop .hero-carousel__dots {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 14px;
        margin-top: 0;
        z-index: 14;
    }
    .hero-carousel--desktop .hero-carousel__progress {
        z-index: 13;
    }
    .hero-carousel.is-single .hero-carousel__dots,
    .hero-carousel.is-single .hero-carousel__progress {
        display: none;
    }
    .hero-carousel__dot {
        width: 8px;
        height: 8px;
        border-radius: 9999px;
        border: none;
        padding: 0;
        background: #d1d5db;
        cursor: pointer;
        transition: width 0.25s ease, background-color 0.25s ease;
    }
    .hero-carousel__dot.is-active {
        width: 24px;
        background: #005366;
    }
    .hero-carousel--desktop .hero-carousel__dot {
        background: rgba(255,255,255,0.5);
    }
    .hero-carousel--desktop .hero-carousel__dot.is-active {
        background: #ffffff;
    }
    .hero-carousel--mobile-left .hero-carousel__viewport,
    .hero-carousel--mobile-left .hero-carousel__card,
    .hero-carousel--mobile-left .hero-carousel__content,
    .hero-carousel--mobile-merged .hero-carousel__viewport,
    .hero-carousel--mobile-merged .hero-carousel__card,
    .hero-carousel--mobile-merged .hero-carousel__content {
        min-height: 300px;
    }
    .hero-carousel--mobile-right .hero-carousel__viewport,
    .hero-carousel--mobile-right .hero-carousel__card,
    .hero-carousel--mobile-right .hero-carousel__content {
        min-height: 220px;
    }

    /* Customer reviews */
    .customer-reviews__header {
        text-align: center;
        max-width: 40rem;
        margin: 0 auto 32px;
    }
    .customer-reviews__score {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }
    .customer-reviews__stars {
        display: inline-flex;
        gap: 2px;
        color: #005366;
    }
    .customer-reviews__stars svg {
        width: 18px;
        height: 18px;
    }
    .customer-reviews__score-value {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #111827;
    }
    .customer-reviews__title {
        font-size: clamp(1.5rem, 4vw, 2rem);
        font-weight: 700;
        line-height: 1.2;
        color: #111827;
        margin: 0 0 16px;
    }
    .customer-reviews__view-all {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.625rem 1.25rem;
        font-size: 0.9375rem;
        text-decoration: none;
    }

    /* Mobile carousel */
    .customer-reviews__mobile {
        display: block;
    }
    .customer-reviews__desktop {
        display: none;
    }
    .customer-reviews__carousel {
        overflow: hidden;
    }
    .customer-reviews__track {
        display: flex;
        transition: transform 0.35s ease;
        will-change: transform;
    }
    .customer-reviews__slide {
        flex: 0 0 100%;
        min-width: 0;
    }
    .customer-reviews__mobile-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }
    .customer-reviews__mobile-top {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }
    .customer-reviews__mobile-media {
        flex-shrink: 0;
        width: 88px;
        height: 88px;
        border-radius: 12px;
        overflow: hidden;
        background: #f7f7f7;
        display: block;
    }
    .customer-reviews__mobile-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .customer-reviews__mobile-media .customer-reviews__media-placeholder {
        width: 88px;
        height: 88px;
    }
    .customer-reviews__mobile-text {
        flex: 1;
        min-width: 0;
    }
    .customer-reviews__mobile-headline {
        font-size: 0.9375rem;
        font-weight: 700;
        line-height: 1.4;
        color: #111827;
        margin: 0 0 8px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .customer-reviews__card-stars {
        display: inline-flex;
        gap: 1px;
        color: #005366;
    }
    .customer-reviews__card-stars svg {
        width: 14px;
        height: 14px;
    }
    .customer-reviews__divider {
        height: 1px;
        background: #f7f7f7;
        margin: 14px 0;
    }
    .customer-reviews__author--mobile {
        margin-top: 0;
    }
    .customer-reviews__verified--mobile {
        color: #9ca3af;
        font-weight: 400;
    }
    .customer-reviews__verified--mobile svg {
        color: #9ca3af;
    }
    .customer-reviews__dots {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    .customer-reviews__dot {
        width: 8px;
        height: 8px;
        border-radius: 9999px;
        border: none;
        padding: 0;
        background: #d1d5db;
        cursor: pointer;
        transition: width 0.25s ease, background-color 0.25s ease;
    }
    .customer-reviews__dot.is-active {
        width: 24px;
        background: #005366;
    }

    /* Desktop grid */
    @media (min-width: 1024px) {
        .customer-reviews__mobile {
            display: none;
        }
        .customer-reviews__desktop {
            display: block;
        }
    }
    .customer-reviews__grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 32px;
    }
    .customer-reviews__card {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .customer-reviews__media {
        display: block;
        border-radius: 16px;
        overflow: hidden;
        aspect-ratio: 1;
        background: #f7f7f7;
        margin-bottom: 16px;
    }
    .customer-reviews__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.35s ease;
    }
    .customer-reviews__media:hover img {
        transform: scale(1.03);
    }
    .customer-reviews__media-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f7f7f7;
    }
    .customer-reviews__headline {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.4;
        color: #111827;
        margin: 0 0 8px;
    }
    .customer-reviews__body {
        font-size: 0.875rem;
        line-height: 1.5;
        color: #4b5563;
        font-style: italic;
        margin: 0 0 16px;
    }
    .customer-reviews__author {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: auto;
    }
    .customer-reviews__avatar {
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        background: #f7f7f7;
        color: #4b5563;
        font-size: 0.8125rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .customer-reviews__author-meta {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }
    .customer-reviews__name {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.3;
    }
    .customer-reviews__verified {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #005366;
        line-height: 1.3;
    }
    .customer-reviews__verified svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }
    .customer-reviews__pager {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        margin-top: 32px;
    }
    .customer-reviews__pager-btn {
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #4b5563;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color 0.2s ease, color 0.2s ease, background-color 0.2s ease;
    }
    .customer-reviews__pager-btn:hover:not(:disabled) {
        border-color: #005366;
        color: #005366;
        background: rgba(0, 83, 102, 0.06);
    }
    .customer-reviews__pager-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .customer-reviews__pager-status {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #4b5563;
        min-width: 3rem;
        text-align: center;
    }
</style>



<!-- Hero banners: left (large) + right (small) carousels -->
@if(count($leftHeroSlides) > 0 || count($rightHeroSlides) > 0)
<section class="bg-white pt-4 sm:pt-5 pb-0" data-home-edit-section="hero" data-home-edit-label="Hero">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
        {{-- Mobile: one carousel (left slides then right) --}}
        <div class="lg:hidden">
            @include('home.partials.hero-carousel', [
                'slides' => $mobileHeroSlides,
                'side' => 'merged',
                'variant' => 'mobile',
                'autoplayMs' => $heroAutoplayMs,
            ])
        </div>

        {{-- Desktop: asymmetric dual carousels --}}
        <div class="hidden lg:grid grid-cols-3 gap-3 sm:gap-4 lg:h-[440px]">
            <div class="lg:col-span-2 min-h-0 h-full">
                @include('home.partials.hero-carousel', [
                    'slides' => $leftHeroSlides,
                    'side' => 'left',
                    'variant' => 'desktop',
                    'autoplayMs' => $heroAutoplayMs,
                ])
            </div>
            <div class="min-h-0 h-full">
                @include('home.partials.hero-carousel', [
                    'slides' => $rightHeroSlides,
                    'side' => 'right',
                    'variant' => 'desktop',
                    'autoplayMs' => $heroAutoplayMs,
                ])
            </div>
        </div>
    </div>
</section>
@endif

@php
    $pickGiftItems = $homeSections['pick_a_gift']['items'] ?? [];
    $pickGiftVisibleCount = collect($pickGiftItems)->filter(function ($item) {
        return trim((string) ($item['label'] ?? '')) !== '' || trim((string) ($item['image'] ?? '')) !== '';
    })->count();
@endphp

@if (($homeSections['pick_a_gift']['enabled'] ?? true) && ($pickGiftVisibleCount > 0 || ($homeEditMode ?? false)))
<!-- Pick a Gift — circular gift slider -->
<section class="pt-12 sm:pt-16 pb-8 sm:pb-10 border-t border-gray-200 overflow-x-hidden" style="background: {{ $homeSections['pick_a_gift']['background'] ?? '#ffffff' }};" aria-labelledby="pick-a-gift-heading" data-home-edit-section="pick_a_gift" data-home-edit-label="Pick a Gift">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 min-w-0">
        <div class="section-heading scroll-reveal">
            <p class="section-heading__eyebrow" data-home-preview="sections.pick_a_gift.eyebrow">{{ $homeSections['pick_a_gift']['eyebrow'] ?? 'Gift ideas' }}</p>
            <h2 id="pick-a-gift-heading" class="section-heading__title" data-home-preview="sections.pick_a_gift.title_html">
                {!! $homeSections['pick_a_gift']['title_html'] ?? 'Pick a <span class="gradient-text">Gift</span>' !!}
            </h2>
            <p class="section-heading__sub" data-home-preview="sections.pick_a_gift.subtitle">
                {{ $homeSections['pick_a_gift']['subtitle'] ?? 'Browse curated collections for every occasion' }}
            </p>
            <span class="section-heading__accent" aria-hidden="true"></span>
        </div>

        <div class="pick-a-gift__slider scroll-reveal" id="pick-a-gift-slider">
            <button type="button"
                    id="pickAGiftPrevBtn"
                    class="pick-a-gift__nav pick-a-gift__nav--prev"
                    onclick="scrollPickAGift('prev')"
                    aria-label="Previous collections"
                    disabled>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <div id="pickAGiftTrackWrap" class="pick-a-gift__track-wrap mobile-scroll-hide">
                <div id="pickAGiftTrack" class="pick-a-gift__track">
                    @for ($gi = 0; $gi < 12; $gi++)
                        @php
                            $giftItem = $pickGiftItems[$gi] ?? ['image' => '', 'url' => '', 'label' => ''];
                            $giftLabel = trim((string) ($giftItem['label'] ?? ''));
                            $giftImage = trim((string) ($giftItem['image'] ?? ''));
                            $giftUrl = trim((string) ($giftItem['url'] ?? ''));
                            $showGiftItem = $giftLabel !== '' || $giftImage !== '' || ($homeEditMode ?? false);
                        @endphp
                        @if ($showGiftItem)
                            <div class="pick-a-gift__item" data-home-pick-gift-index="{{ $gi }}">
                                <a href="{{ $giftUrl !== '' ? $giftUrl : '#' }}"
                                   class="pick-a-gift__circle group"
                                   aria-label="{{ $giftLabel !== '' ? $giftLabel : 'Gift item ' . ($gi + 1) }}">
                                    @if ($giftImage !== '')
                                        <img src="{{ $giftImage }}"
                                             alt="{{ $giftLabel }}"
                                             loading="lazy"
                                             width="200"
                                             height="200"
                                             data-home-preview="sections.pick_a_gift.items.{{ $gi }}.image">
                                    @else
                                        <span class="pick-a-gift__circle__placeholder" aria-hidden="true"
                                              data-home-preview="sections.pick_a_gift.items.{{ $gi }}.image"></span>
                                    @endif
                                    <span class="pick-a-gift__label" data-home-preview="sections.pick_a_gift.items.{{ $gi }}.label">
                                        {{ $giftLabel !== '' ? \Illuminate\Support\Str::upper($giftLabel) : 'GIFT ' . ($gi + 1) }}
                                    </span>
                                </a>
                            </div>
                        @endif
                    @endfor
                </div>
            </div>

            <button type="button"
                    id="pickAGiftNextBtn"
                    class="pick-a-gift__nav pick-a-gift__nav--next"
                    onclick="scrollPickAGift('next')"
                    aria-label="Next collections">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>
</section>
@endif

@if(isset($flashDeals) && $flashDeals->count() > 0)
<!-- Flash Sale -->
<section class="py-8 sm:py-10 bg-white border-t border-gray-200" aria-labelledby="flash-deal-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flash-deal scroll-reveal">
            <header class="flash-deal__header">
                <div class="flash-deal__header-left">
                    <h2 id="flash-deal-heading" class="flash-deal__title">Today's Big Deals</h2>
                    <p class="flash-deal__tagline">
                        Limited time &bull; Up to {{ $flashDealMaxDiscount }}% off
                    </p>
                </div>
                <div class="flash-deal__countdown-wrap">
                    <p class="flash-deal__countdown-label">Ends in</p>
                    <div class="flash-deal__countdown"
                         id="flashDealCountdown"
                         data-ends-at="{{ $flashSaleEndsAt->toIso8601String() }}"
                         role="timer"
                         aria-live="polite"
                         aria-atomic="true">
                        <span class="flash-deal__countdown-segment" id="flashDealHours">00</span>
                        <span class="flash-deal__countdown-colon" aria-hidden="true">:</span>
                        <span class="flash-deal__countdown-segment" id="flashDealMinutes">00</span>
                        <span class="flash-deal__countdown-colon" aria-hidden="true">:</span>
                        <span class="flash-deal__countdown-segment" id="flashDealSeconds">00</span>
                    </div>
                </div>
            </header>

            <div class="flash-deal__slider">
                <button type="button"
                        id="flashDealPrev"
                        class="flash-deal__nav flash-deal__nav--prev"
                        onclick="scrollFlashDeal('prev')"
                        aria-label="Previous deal"
                        disabled>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <div id="flashDealWrap" class="flash-deal__track-wrap mobile-scroll-hide">
                    <div id="flashDealTrack" class="flash-deal__track">
                        @foreach ($flashDeals as $index => $deal)
                            @php
                                $product = $deal->product;
                                $media = $product->getEffectiveMedia();
                                $imageUrl = null;
                                if ($media && count($media) > 0) {
                                    if (is_string($media[0])) {
                                        $imageUrl = $media[0];
                                    } elseif (is_array($media[0])) {
                                        $imageUrl = $media[0]['url'] ?? $media[0]['path'] ?? reset($media[0]) ?? null;
                                    }
                                }
                                $originalPrice = (float) $deal->original_price;
                                $salePrice = (float) $deal->sale_price;
                                $discountPct = $deal->discount_percent;
                                $stockLeft = (int) $product->quantity;
                                $showLowStock = $stockLeft > 0 && $stockLeft <= 25;
                                $stockBarPct = $showLowStock ? min(100, max(8, ($stockLeft / 25) * 100)) : 0;
                            @endphp
                            <article class="flash-deal__card">
                                <a href="{{ route('products.show', $product->slug) }}"
                                   class="flash-deal__card-inner group"
                                   aria-label="{{ $product->name }}, {{ $discountPct }}% off">
                                    <div class="flash-deal__media">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy">
                                        @else
                                            <div class="w-full h-full bg-[#f7f7f7] flex items-center justify-center">
                                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        @if ($discountPct > 0)
                                            <span class="flash-deal__badge">-{{ $discountPct }}%</span>
                                        @endif
                                    </div>
                                    <div class="flash-deal__body">
                                        <h3 class="flash-deal__name">{{ $product->name }}</h3>
                                        <div class="flash-deal__prices">
                                            <span class="flash-deal__price">{{ format_price_usd($salePrice) }}</span>
                                            @if ($originalPrice > $salePrice)
                                                <span class="flash-deal__price-old">{{ format_price_usd($originalPrice) }}</span>
                                            @endif
                                            @if ($discountPct > 0)
                                                <span class="flash-deal__off-tag">{{ $discountPct }}% OFF</span>
                                            @endif
                                        </div>
                                        @if ($showLowStock)
                                            <div class="flash-deal__stock">
                                                <p class="flash-deal__stock-label">Only {{ $stockLeft }} left</p>
                                                <div class="flash-deal__stock-bar" role="progressbar"
                                                     aria-valuenow="{{ $stockLeft }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="25"
                                                     aria-label="Stock remaining: {{ $stockLeft }} units">
                                                    <div class="flash-deal__stock-fill" style="width: {{ $stockBarPct }}%"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>

                <button type="button"
                        id="flashDealNext"
                        class="flash-deal__nav flash-deal__nav--next"
                        onclick="scrollFlashDeal('next')"
                        aria-label="Next deal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <div class="flash-deal__footer">
                <a href="{{ route('products.index') }}" class="flash-deal__view-all">
                    View all deals
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Top Pick for You — asymmetric bento grid -->
@if($homeSections['top_picks']['enabled'] ?? true)
<section class="py-8 sm:py-10 border-t border-gray-200" style="background: {{ $homeSections['top_picks']['background'] ?? '#ffffff' }};" aria-labelledby="top-picks-heading" data-home-edit-section="top_picks" data-home-edit-label="Top Picks">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 top-picks">
        <div class="top-picks__header">
            <div class="section-heading section-heading--compact">
                <p class="section-heading__eyebrow" data-home-preview="sections.top_picks.eyebrow">{{ $homeSections['top_picks']['eyebrow'] ?? 'Personalized' }}</p>
                <h2 id="top-picks-heading" class="section-heading__title" data-home-preview="sections.top_picks.title_html">
                    {!! $homeSections['top_picks']['title_html'] ?? 'Top Picks <span class="gradient-text">For You</span>' !!}
                </h2>
                <p class="section-heading__sub" data-home-preview="sections.top_picks.subtitle">{{ $homeSections['top_picks']['subtitle'] ?? 'Curated from what’s trending and what fits your style' }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="top-picks__header-link" aria-label="View all products">
                See more
                <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="top-picks-bento">
            @php
                $topPicksBanners = $homeSections['top_picks']['banners'] ?? [];
                $featuredBanner = $topPicksBanners['featured'] ?? [];
                $promoBanner = $topPicksBanners['promo'] ?? [];
            @endphp
            {{-- Banner dọc (cột trái, span 2 hàng) --}}
            <div class="top-picks-bento__featured">
                <x-collection-promo-card
                    :collection="$topPickBannerCollection"
                    layout="tall"
                    :banner-image="$featuredBanner['image'] ?? null"
                    :banner-url="$featuredBanner['url'] ?? null"
                    :banner-title="$featuredBanner['title'] ?? null"
                    :banner-subtitle="$featuredBanner['subtitle'] ?? null"
                    fallback-title="Create Your Own"
                    fallback-subtitle="Design custom products with AI and print on demand"
                    :fallback-url="route('products.index')"
                    preview-image="sections.top_picks.banners.featured.image"
                    preview-title="sections.top_picks.banners.featured.title"
                    preview-subtitle="sections.top_picks.banners.featured.subtitle"
                />
            </div>

            @forelse ($topPickBentoSmall as $product)
                <x-product-card :product="$product" :show-wishlist="false" />
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-gray-200 bg-gray-50 px-6 py-10 text-center md:col-span-3">
                    <p class="text-base font-semibold text-gray-900">No products yet</p>
                    <p class="mt-2 text-sm text-gray-600">Product picks will appear here once published.</p>
                    <a href="{{ route('products.index') }}" class="btn-cta mt-4 inline-flex">Browse products</a>
                </div>
            @endforelse

            {{-- Banner ngang (2 cột, hàng 3) --}}
            <div class="top-picks-bento__promo">
                <x-collection-promo-card
                    :collection="$topPickPromoCollection"
                    :ends-at="$topPickPromoEndsAt ?? null"
                    :banner-image="$promoBanner['image'] ?? null"
                    :banner-url="$promoBanner['url'] ?? null"
                    :banner-title="$promoBanner['title'] ?? null"
                    :banner-subtitle="$promoBanner['subtitle'] ?? null"
                    :banner-tag="$promoBanner['tag'] ?? null"
                    fallback-title="Shop all collections"
                    fallback-subtitle="Curated designs for every occasion"
                    :fallback-url="route('collections.index')"
                    preview-image="sections.top_picks.banners.promo.image"
                    preview-title="sections.top_picks.banners.promo.title"
                    preview-subtitle="sections.top_picks.banners.promo.subtitle"
                    preview-tag="sections.top_picks.banners.promo.tag"
                />
            </div>

            @foreach ($topPickRowThree as $index => $product)
                <div class="top-picks-bento__slot top-picks-bento__slot--r3-{{ $index + 1 }}">
                    <x-product-card :product="$product" :show-wishlist="false" />
                </div>
            @endforeach
        </div>

        @if ($topPickMore->isNotEmpty())
            <div class="top-picks-uniform">
                @foreach ($topPickMore as $product)
                    <x-product-card :product="$product" :show-wishlist="false" />
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif

<!-- Latest Collections — asymmetric mosaic -->
@if($homeSections['latest_collections']['enabled'] ?? true)
<div class="py-8 sm:py-10" style="background: {{ $homeSections['latest_collections']['background'] ?? '#ffffff' }};" data-home-edit-section="latest_collections" data-home-edit-label="Collections">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="section-heading scroll-reveal">
            <p class="section-heading__eyebrow" data-home-preview="sections.latest_collections.eyebrow">{{ $homeSections['latest_collections']['eyebrow'] ?? 'Curated picks' }}</p>
            <h2 class="section-heading__title" data-home-preview="sections.latest_collections.title_html">
                {!! $homeSections['latest_collections']['title_html'] ?? 'Latest <span class="gradient-text">Collections</span>' !!}
            </h2>
            <p class="section-heading__sub" data-home-preview="sections.latest_collections.subtitle">
                {{ $homeSections['latest_collections']['subtitle'] ?? 'Discover our newest collections and trending designs' }}
            </p>
            <span class="section-heading__accent" aria-hidden="true"></span>
        </div>

        @php
            $mosaicCollections = \App\Models\Collection::global()
                ->where('status', 'active')
                ->where('admin_approved', true)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get()
                ->values();

            // Bất đối xứng theo hàng: lớn–nhỏ / nhỏ–lớn / lớn–rộng hẹp (ảnh 2)
            $mosaicRows = [
                [66.666, 33.333],
                [40, 60],
                [75, 25],
            ];
        @endphp

        @if ($mosaicCollections->count() > 0)
            {{-- Mobile: carousel with depth + Ken Burns --}}
            <div class="collections-carousel md:hidden scroll-reveal" id="collections-carousel">
                <div class="collections-carousel__slider">
                    <button type="button"
                            id="collectionsCarouselPrev"
                            class="collections-carousel__nav collections-carousel__nav--prev"
                            onclick="scrollCollectionsCarousel('prev')"
                            aria-label="Previous collection"
                            disabled>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <div id="collectionsCarouselWrap" class="collections-carousel__track-wrap mobile-scroll-hide">
                        <div id="collectionsCarouselTrack" class="collections-carousel__track">
                            @foreach ($mosaicCollections as $index => $collection)
                                <div class="collections-carousel__slide {{ $index === 0 ? 'is-active' : '' }}" data-slide-index="{{ $index }}">
                                    <a href="{{ route('collections.show', $collection->slug) }}"
                                       class="collections-carousel__tile"
                                       aria-label="View collection {{ $collection->name }}">
                                        @if ($collection->image)
                                            <img src="{{ $collection->image }}" alt="{{ $collection->name }}" loading="lazy">
                                        @else
                                            <div class="absolute inset-0 bg-gradient-to-br from-[#005366] to-[#003d4d]"></div>
                                        @endif
                                        <div class="collections-carousel__tile__overlay">
                                            <h3 class="collections-carousel__tile__title line-clamp-2">{{ $collection->name }}</h3>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="button"
                            id="collectionsCarouselNext"
                            class="collections-carousel__nav collections-carousel__nav--next"
                            onclick="scrollCollectionsCarousel('next')"
                            aria-label="Next collection">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <div class="collections-carousel__dots" id="collectionsCarouselDots" role="tablist" aria-label="Collection slides">
                    @foreach ($mosaicCollections as $index => $collection)
                        <button type="button"
                                class="collections-carousel__dot {{ $index === 0 ? 'is-active' : '' }}"
                                data-dot-index="{{ $index }}"
                                onclick="goToCollectionsSlide({{ $index }})"
                                aria-label="Go to {{ $collection->name }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"></button>
                    @endforeach
                </div>
            </div>

            {{-- Desktop: asymmetric mosaic --}}
            <div class="collections-mosaic mt-6 sm:mt-8 scroll-reveal">
                @foreach ($mosaicRows as $rowIndex => $widths)
                    @php
                        $left = $mosaicCollections->get($rowIndex * 2);
                        $right = $mosaicCollections->get($rowIndex * 2 + 1);
                    @endphp
                    @if ($left || $right)
                        <div class="collections-mosaic-row">
                            @foreach ([['item' => $left, 'w' => $widths[0]], ['item' => $right, 'w' => $widths[1]]] as $cell)
                                @if ($cell['item'])
                                    @php $collection = $cell['item']; @endphp
                                    <a href="{{ route('collections.show', $collection->slug) }}"
                                       class="collections-mosaic-tile group"
                                       style="flex: 1 1 {{ $cell['w'] }}%; max-width: 100%;"
                                       aria-label="View collection {{ $collection->name }}">
                                        @if ($collection->image)
                                            <img src="{{ $collection->image }}" alt="{{ $collection->name }}" loading="lazy">
                                        @else
                                            <div class="absolute inset-0 bg-gradient-to-br from-[#005366] to-[#003d4d]"></div>
                                        @endif
                                        <div class="collections-mosaic-tile__overlay">
                                            <h3 class="collections-mosaic-tile__title line-clamp-2">{{ $collection->name }}</h3>
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="mt-6 flex justify-center">
                <a href="{{ route('collections.index') }}" class="btn-outline-petrol">
                    View all collections
                </a>
            </div>
        @else
            <div class="mt-8 text-center py-8">
                <p class="text-base text-gray-600">No collections yet.</p>
            </div>
        @endif
    </div>
</div>
@endif

<!-- New Products -->
@if(($homeSections['new_products']['enabled'] ?? true) && isset($newProducts) && $newProducts->isNotEmpty())
<section class="py-8 sm:py-10 border-t border-gray-200" style="background: {{ $homeSections['new_products']['background'] ?? '#ffffff' }};" aria-labelledby="new-products-heading" data-home-edit-section="new_products" data-home-edit-label="New Products">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="top-picks__header scroll-reveal">
            <div class="section-heading section-heading--compact">
                <p class="section-heading__eyebrow" data-home-preview="sections.new_products.eyebrow">{{ $homeSections['new_products']['eyebrow'] ?? 'Just dropped' }}</p>
                <h2 id="new-products-heading" class="section-heading__title" data-home-preview="sections.new_products.title_html">
                    {!! $homeSections['new_products']['title_html'] ?? 'New <span class="gradient-text">Products</span>' !!}
                </h2>
                <p class="section-heading__sub" data-home-preview="sections.new_products.subtitle">
                    {{ $homeSections['new_products']['subtitle'] ?? 'Fresh designs and latest additions to our catalog' }}
                </p>
                <span class="section-heading__accent" aria-hidden="true"></span>
            </div>
            <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="top-picks__header-link" aria-label="View all new products">
                See more
                <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="catalog-grid scroll-reveal mt-6 sm:mt-8">
            @foreach ($newProducts as $product)
                <x-product-card :product="$product" :show-wishlist="false" />
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Customize products hero -->
@if($homeSections['customize_hero']['enabled'] ?? true)
@include('partials.customize-hero-section', [
    'customizeSection' => $customizeSection,
    'showHomeEdit' => true,
])
@endif

<!-- Latest Blog Posts -->
@php
    $recentPosts = \App\Models\Post::with(['user', 'category'])
        ->published()
        ->orderByDesc('published_at')
        ->orderByDesc('created_at')
        ->limit(3)
        ->get();
@endphp

@if ($recentPosts->count() > 0 && ($homeSections['blog']['enabled'] ?? true))
<section class="catalog-page--blog py-8 sm:py-10 border-t border-gray-200" style="background: {{ $homeSections['blog']['background'] ?? '#ffffff' }};" aria-labelledby="blog-posts-heading" data-home-edit-section="blog" data-home-edit-label="Blog">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="blog-posts__top scroll-reveal">
            <div class="section-heading">
                <p class="section-heading__eyebrow" data-home-preview="sections.blog.eyebrow">{{ $homeSections['blog']['eyebrow'] ?? 'Inspiration' }}</p>
                <h2 id="blog-posts-heading" class="section-heading__title" data-home-preview="sections.blog.title_html">
                    {!! $homeSections['blog']['title_html'] ?? 'Latest <span class="gradient-text">Blog Posts</span>' !!}
                </h2>
                <p class="section-heading__sub" data-home-preview="sections.blog.subtitle">
                    {{ $homeSections['blog']['subtitle'] ?? 'Design tips, trends, and creative inspiration' }}
                </p>
                <span class="section-heading__accent md:hidden" aria-hidden="true"></span>
            </div>
            <a href="{{ route('blog.index') }}" class="blog-posts__see-all md:self-end">
                View all posts
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="catalog-blog-grid catalog-blog-grid--home scroll-reveal">
            @foreach ($recentPosts as $post)
                <x-catalog-blog-post-item :post="$post" view="grid" />
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Recently Viewed -->
@if($homeSections['recently_viewed']['enabled'] ?? true)
<section class="py-8 sm:py-10 border-t border-gray-200" style="background: {{ $homeSections['recently_viewed']['background'] ?? '#f9fafb' }};" aria-labelledby="recently-viewed-heading" data-home-edit-section="recently_viewed" data-home-edit-label="Recently Viewed">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="section-heading scroll-reveal">
            <p class="section-heading__eyebrow" data-home-preview="sections.recently_viewed.eyebrow">{{ $homeSections['recently_viewed']['eyebrow'] ?? 'Continue browsing' }}</p>
            <h2 id="recently-viewed-heading" class="section-heading__title" data-home-preview="sections.recently_viewed.title_html">
                {!! $homeSections['recently_viewed']['title_html'] ?? 'Recently <span class="gradient-text">Viewed</span>' !!}
            </h2>
            <p class="section-heading__sub" data-home-preview="sections.recently_viewed.subtitle">
                {{ $homeSections['recently_viewed']['subtitle'] ?? "Pick up where you left off with products you've explored" }}
            </p>
            <span class="section-heading__accent" aria-hidden="true"></span>
        </div>

        <div class="recently-viewed scroll-reveal">
            <div class="recently-viewed__slider hidden" id="recently-viewed-wrapper">
                <button type="button"
                        id="recentlyViewedPrevBtn"
                        class="recently-viewed__nav recently-viewed__nav--prev"
                        onclick="scrollRecentlyViewed('prev')"
                        aria-label="Previous products"
                        disabled>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <div id="recentlyViewedWrap" class="recently-viewed__track-wrap mobile-scroll-hide">
                    <div id="recently-viewed-container" class="recently-viewed__track"></div>
                </div>

                <button type="button"
                        id="recentlyViewedNextBtn"
                        class="recently-viewed__nav recently-viewed__nav--next"
                        onclick="scrollRecentlyViewed('next')"
                        aria-label="Next products">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <div id="recently-viewed-empty" class="recently-viewed__empty hidden">
                <svg class="recently-viewed__empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <p class="recently-viewed__empty-title">No products viewed yet</p>
                <p class="recently-viewed__empty-sub">Products you browse will appear here for quick access</p>
            </div>
        </div>
    </div>
</section>
@endif

<x-customer-reviews
    :average-rating="$customerReviewAverage"
    :happy-customers-label="$customerHappyCustomersLabel"
    :pages="$customerReviewPages"
/>

<!-- Why Choose Bluprinter -->
@if(($whyChooseSection['enabled'] ?? true) && count($whyChooseFeatures) > 0)
<section class="why-choose py-6 sm:py-10 border-t border-gray-200" style="background: {{ $whyChooseSection['background'] ?? '#f9fafb' }};" aria-labelledby="why-choose-heading" id="why-choose-section" data-autoplay-ms="{{ $whyChooseAutoplayMs }}" data-home-edit-section="why_choose" data-home-edit-label="Why Choose">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="section-heading scroll-reveal">
            <p class="section-heading__eyebrow" data-home-preview="sections.why_choose.eyebrow">{{ $whyChooseSection['eyebrow'] ?? 'Our promise' }}</p>
            <h2 id="why-choose-heading" class="section-heading__title" data-home-preview="sections.why_choose.title_html">
                {!! $whyChooseSection['title_html'] ?? 'Why Choose <span class="gradient-text">Bluprinter</span>?' !!}
            </h2>
            <p class="section-heading__sub" data-home-preview="sections.why_choose.subtitle">
                {{ $whyChooseSection['subtitle'] ?? 'Professional customization with cutting-edge technology and support you can count on' }}
            </p>
            <span class="section-heading__accent" aria-hidden="true"></span>
        </div>

        {{-- Mobile: pill tabs + fade panels --}}
        <div class="why-choose__mobile scroll-reveal lg:hidden" id="why-choose-mobile">
            <div class="why-choose__tab-list" role="tablist" aria-label="Why choose us">
                @foreach ($whyChooseFeatures as $index => $feature)
                    <button type="button"
                            class="why-choose__tab {{ $index === 0 ? 'is-active' : '' }}"
                            data-why-tab="{{ $index }}"
                            onclick="goToWhyChooseTab({{ $index }}, true)"
                            role="tab"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                        {{ $feature['title'] }}
                    </button>
                @endforeach
            </div>
            <div class="why-choose__tab-panels">
                @foreach ($whyChooseFeatures as $index => $feature)
                    <article class="why-choose__tab-panel {{ $index === 0 ? 'is-active' : '' }}" data-why-panel="{{ $index }}" role="tabpanel">
                        <div class="why-choose__card">
                            <div class="why-choose__icon why-choose__icon--{{ $feature['accent'] }}" aria-hidden="true">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $feature['icon'] !!}
                                </svg>
                            </div>
                            <h3 class="why-choose__title" data-home-preview="sections.why_choose.features.{{ $index }}.title">{{ $feature['title'] }}</h3>
                            <p class="why-choose__desc" data-home-preview="sections.why_choose.features.{{ $index }}.description">{{ $feature['description'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        {{-- Desktop: 4-column grid --}}
        <div class="why-choose__grid scroll-reveal">
            @foreach ($whyChooseFeatures as $feature)
                <article class="why-choose__card">
                    <div class="why-choose__icon why-choose__icon--{{ $feature['accent'] }}" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $feature['icon'] !!}
                        </svg>
                    </div>
                    <h3 class="why-choose__title" data-home-preview="sections.why_choose.features.{{ $loop->index }}.title">{{ $feature['title'] }}</h3>
                    <p class="why-choose__desc" data-home-preview="sections.why_choose.features.{{ $loop->index }}.description">{{ $feature['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- CTA Section -->


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load recently viewed products
        loadRecentlyViewed();
        
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all scroll-reveal elements
        document.querySelectorAll('.scroll-reveal').forEach(element => {
            observer.observe(element);
        });
    });

    const RECENTLY_VIEWED_CARDS_URL = @json(route('products.recently-viewed-cards'));

    // Recently Viewed Functions
    async function loadRecentlyViewed() {
        const recentlyViewed = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
        const container = document.getElementById('recently-viewed-container');
        const emptyState = document.getElementById('recently-viewed-empty');
        const wrapper = document.getElementById('recently-viewed-wrapper');

        if (!container) return;

        const ids = recentlyViewed
            .slice(0, 12)
            .map(function (product) { return product.id; })
            .filter(Boolean);

        if (ids.length === 0) {
            if (wrapper) wrapper.classList.add('hidden');
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (wrapper) wrapper.classList.remove('hidden');
        if (emptyState) emptyState.classList.add('hidden');

        try {
            const params = new URLSearchParams();
            ids.forEach(function (id) { params.append('ids[]', id); });

            const response = await fetch(RECENTLY_VIEWED_CARDS_URL + '?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to load recently viewed products');
            }

            const data = await response.json();

            if (!data.html) {
                if (wrapper) wrapper.classList.add('hidden');
                if (emptyState) emptyState.classList.remove('hidden');
                return;
            }

            container.innerHTML = data.html;
            initRecentlyViewedCarousel();
        } catch (error) {
            if (wrapper) wrapper.classList.add('hidden');
            if (emptyState) emptyState.classList.remove('hidden');
        }
    }

    function getRecentlyViewedMetrics() {
        const wrap = document.getElementById('recentlyViewedWrap');
        const track = document.getElementById('recently-viewed-container');
        if (!wrap || !track || !track.children.length) return null;

        const firstItem = track.children[0];
        const itemWidth = firstItem.offsetWidth;
        const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '16');
        const step = itemWidth + gap;

        return { wrap, track, itemWidth, gap, step };
    }

    function updateRecentlyViewedNav() {
        const metrics = getRecentlyViewedMetrics();
        const prevBtn = document.getElementById('recentlyViewedPrevBtn');
        const nextBtn = document.getElementById('recentlyViewedNextBtn');
        if (!metrics || !prevBtn || !nextBtn) return;

        const { wrap } = metrics;
        const hasOverflow = wrap.scrollWidth > wrap.clientWidth + 2;
        const atStart = wrap.scrollLeft <= 1;
        const atEnd = wrap.scrollLeft >= wrap.scrollWidth - wrap.clientWidth - 2;

        prevBtn.disabled = atStart;
        nextBtn.disabled = atEnd;

        prevBtn.classList.toggle('recently-viewed__nav--visible', hasOverflow);
        nextBtn.classList.toggle('recently-viewed__nav--visible', hasOverflow);
    }

    function scrollRecentlyViewed(direction) {
        const metrics = getRecentlyViewedMetrics();
        if (!metrics) return;

        const { wrap, step } = metrics;
        const delta = direction === 'next' ? step : -step;
        wrap.scrollBy({ left: delta, behavior: 'smooth' });
    }

    function initRecentlyViewedCarousel() {
        const wrap = document.getElementById('recentlyViewedWrap');
        if (!wrap) return;

        wrap.addEventListener('scroll', updateRecentlyViewedNav, { passive: true });
        updateRecentlyViewedNav();
    }

    // Pick a Gift carousel — full-width, chỉ hiện vòng tròn nguyên (không lộ mép / không thanh cuộn)
    function getPickAGiftMetrics() {
        const wrap = document.getElementById('pickAGiftTrackWrap');
        const track = document.getElementById('pickAGiftTrack');
        if (!wrap || !track || !track.children.length) {
            return null;
        }

        const firstItem = track.children[0];
        const itemWidth = firstItem.offsetWidth;
        const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '28');
        const step = itemWidth + gap;

        return { wrap, track, itemWidth, gap, step };
    }

    function fitPickAGiftViewport() {
        const metrics = getPickAGiftMetrics();
        if (!metrics) return;

        const { wrap, track, gap } = metrics;

        // Luôn full-width — không thu hẹp wrap (tránh khoảng trắng bên phải)
        wrap.style.width = '';
        wrap.style.maxWidth = '100%';
        Array.from(track.children).forEach((item) => {
            item.style.width = '';
        });

        const preferredWidth = track.children[0].offsetWidth || 160;
        const available = wrap.clientWidth;
        if (available < 80) return;

        const visibleCount = Math.max(1, Math.floor((available + gap) / (preferredWidth + gap)));
        const itemWidth = Math.floor((available - (visibleCount - 1) * gap) / visibleCount);

        Array.from(track.children).forEach((item) => {
            item.style.width = itemWidth + 'px';
        });
    }

    function updatePickAGiftNav() {
        const metrics = getPickAGiftMetrics();
        const prevBtn = document.getElementById('pickAGiftPrevBtn');
        const nextBtn = document.getElementById('pickAGiftNextBtn');
        if (!metrics || !prevBtn || !nextBtn) return;

        const { wrap } = metrics;
        const hasOverflow = wrap.scrollWidth > wrap.clientWidth + 2;
        const atStart = wrap.scrollLeft <= 1;
        const atEnd = wrap.scrollLeft >= wrap.scrollWidth - wrap.clientWidth - 2;

        prevBtn.disabled = atStart;
        nextBtn.disabled = atEnd;

        prevBtn.classList.toggle('pick-a-gift__nav--visible', hasOverflow);
        nextBtn.classList.toggle('pick-a-gift__nav--visible', hasOverflow);
    }

    function scrollPickAGift(direction) {
        const metrics = getPickAGiftMetrics();
        if (!metrics) return;

        const { wrap, step } = metrics;
        const delta = direction === 'next' ? step : -step;
        wrap.scrollBy({ left: delta, behavior: 'smooth' });
    }

    function refreshPickAGiftCarousel() {
        fitPickAGiftViewport();
        updatePickAGiftNav();
    }

    function initPickAGiftCarousel() {
        const wrap = document.getElementById('pickAGiftTrackWrap');
        if (!wrap) return;

        wrap.addEventListener('scroll', updatePickAGiftNav, { passive: true });
        refreshPickAGiftCarousel();
    }

    // Hero banners — independent left/right fade carousels
    const heroCarouselControllers = [];

    function createHeroCarouselController(root) {
        if (!root || root.dataset.heroInit === '1') return null;
        root.dataset.heroInit = '1';

        let activeIndex = 0;
        let autoplayTimer = null;
        const viewport = root.querySelector('[data-hero-viewport]');
        const progressBar = root.querySelector('[data-hero-progress]');
        const slides = () => root.querySelectorAll('.hero-carousel__slide');
        const dots = () => root.querySelectorAll('[data-hero-dot]');

        function getAutoplayMs() {
            return parseInt(root.getAttribute('data-autoplay-ms') || '5000', 10);
        }

        function updateState() {
            const slideNodes = slides();
            const dotNodes = dots();
            if (!slideNodes.length) return;

            slideNodes.forEach(function (slide, index) {
                slide.classList.toggle('is-active', index === activeIndex);
            });

            dotNodes.forEach(function (dot, index) {
                const active = index === activeIndex;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        }

        function resetProgressBar() {
            if (!progressBar) return;
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';
            void progressBar.offsetWidth;
            progressBar.style.transition = 'width ' + getAutoplayMs() + 'ms linear';
            progressBar.style.width = '100%';
        }

        function goTo(index, manual) {
            const slideNodes = slides();
            if (!slideNodes.length) return;

            activeIndex = ((index % slideNodes.length) + slideNodes.length) % slideNodes.length;
            updateState();
            resetProgressBar();

            if (manual) {
                start();
            }
        }

        function stop() {
            if (autoplayTimer) {
                clearInterval(autoplayTimer);
                autoplayTimer = null;
            }
        }

        function start() {
            stop();
            const slideNodes = slides();
            if (slideNodes.length <= 1) return;

            resetProgressBar();
            autoplayTimer = setInterval(function () {
                goTo(activeIndex + 1, false);
            }, getAutoplayMs());
        }

        root.querySelectorAll('[data-hero-dot]').forEach(function (dot) {
            dot.addEventListener('click', function () {
                const index = parseInt(dot.getAttribute('data-hero-dot') || '0', 10);
                goTo(index, true);
            });
        });

        if (viewport) {
            viewport.addEventListener('mouseenter', stop);
            viewport.addEventListener('mouseleave', start);
            viewport.addEventListener('touchstart', stop, { passive: true });
            viewport.addEventListener('touchend', function () {
                setTimeout(start, 1200);
            }, { passive: true });
        }

        updateState();
        start();

        return { root, goTo, start, stop, updateState };
    }

    function initHeroCarousel() {
        document.querySelectorAll('[data-hero-carousel]').forEach(function (root) {
            // Avoid double-init when both mobile + desktop markup exist: only init visible ones initially,
            // but keep all running — CSS hides with display none so both can autoplay fine.
            const controller = createHeroCarouselController(root);
            if (controller) {
                heroCarouselControllers.push(controller);
            }
        });

        document.addEventListener('visibilitychange', function () {
            heroCarouselControllers.forEach(function (controller) {
                if (document.hidden) {
                    controller.stop();
                } else {
                    controller.start();
                }
            });
        });
    }

    function refreshHeroCarousel() {
        heroCarouselControllers.forEach(function (controller) {
            controller.updateState();
        });
    }

    // Back-compat for any leftover callers
    function goToHeroSlide(index, manual) {
        if (heroCarouselControllers[0]) {
            heroCarouselControllers[0].goTo(index, manual);
        }
    }

    // Latest Collections mobile carousel
    let collectionsActiveIndex = 0;
    let collectionsAutoplayTimer = null;

    function getCollectionsCarouselMetrics() {
        const wrap = document.getElementById('collectionsCarouselWrap');
        const track = document.getElementById('collectionsCarouselTrack');
        if (!wrap || !track || !track.children.length) return null;

        const slide = track.children[0];
        const slideWidth = slide.offsetWidth;
        const gap = parseFloat(getComputedStyle(track).gap || '16');
        const step = slideWidth + gap;

        return { wrap, track, slideWidth, gap, step };
    }

    function updateCollectionsCarouselState() {
        const metrics = getCollectionsCarouselMetrics();
        const prevBtn = document.getElementById('collectionsCarouselPrev');
        const nextBtn = document.getElementById('collectionsCarouselNext');
        const dots = document.querySelectorAll('.collections-carousel__dot');
        if (!metrics) return;

        const { wrap, track, step } = metrics;
        const slides = Array.from(track.children);
        const maxIndex = slides.length - 1;

        collectionsActiveIndex = Math.max(0, Math.min(maxIndex, Math.round(wrap.scrollLeft / step)));

        slides.forEach(function (slide, index) {
            slide.classList.remove('is-active', 'is-adjacent');
            if (index === collectionsActiveIndex) {
                slide.classList.add('is-active');
            } else if (Math.abs(index - collectionsActiveIndex) === 1) {
                slide.classList.add('is-adjacent');
            }
        });

        dots.forEach(function (dot, index) {
            const active = index === collectionsActiveIndex;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        if (prevBtn) prevBtn.disabled = collectionsActiveIndex <= 0;
        if (nextBtn) nextBtn.disabled = collectionsActiveIndex >= maxIndex;
    }

    function scrollCollectionsCarousel(direction) {
        const metrics = getCollectionsCarouselMetrics();
        if (!metrics) return;

        const { wrap, step } = metrics;
        const delta = direction === 'next' ? step : -step;
        wrap.scrollBy({ left: delta, behavior: 'smooth' });
    }

    function goToCollectionsSlide(index) {
        const metrics = getCollectionsCarouselMetrics();
        if (!metrics) return;

        const { wrap, step } = metrics;
        wrap.scrollTo({ left: index * step, behavior: 'smooth' });
    }

    function startCollectionsAutoplay() {
        stopCollectionsAutoplay();
        const metrics = getCollectionsCarouselMetrics();
        if (!metrics || metrics.track.children.length <= 1) return;

        collectionsAutoplayTimer = setInterval(function () {
            const total = metrics.track.children.length;
            const next = (collectionsActiveIndex + 1) % total;
            goToCollectionsSlide(next);
        }, 5000);
    }

    function stopCollectionsAutoplay() {
        if (collectionsAutoplayTimer) {
            clearInterval(collectionsAutoplayTimer);
            collectionsAutoplayTimer = null;
        }
    }

    function initCollectionsCarousel() {
        const wrap = document.getElementById('collectionsCarouselWrap');
        if (!wrap) return;

        let scrollTimer;
        wrap.addEventListener('scroll', function () {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(updateCollectionsCarouselState, 40);
        }, { passive: true });

        wrap.addEventListener('touchstart', stopCollectionsAutoplay, { passive: true });
        wrap.addEventListener('mouseenter', stopCollectionsAutoplay);
        wrap.addEventListener('mouseleave', startCollectionsAutoplay);

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stopCollectionsAutoplay();
            } else if (document.getElementById('collectionsCarouselWrap')) {
                startCollectionsAutoplay();
            }
        });

        updateCollectionsCarouselState();
        startCollectionsAutoplay();
    }

    function refreshCollectionsCarousel() {
        updateCollectionsCarouselState();
    }

    // Flash Sale countdown + carousel
    let flashDealCountdownTimer = null;

    function padFlashTime(n) {
        return String(Math.max(0, n)).padStart(2, '0');
    }

    function initFlashDealCountdown() {
        const root = document.getElementById('flashDealCountdown');
        if (!root) return;

        const endsAt = new Date(root.dataset.endsAt).getTime();
        const hoursEl = document.getElementById('flashDealHours');
        const minutesEl = document.getElementById('flashDealMinutes');
        const secondsEl = document.getElementById('flashDealSeconds');

        function tick() {
            const diff = endsAt - Date.now();
            if (diff <= 0) {
                if (hoursEl) hoursEl.textContent = '00';
                if (minutesEl) minutesEl.textContent = '00';
                if (secondsEl) secondsEl.textContent = '00';
                if (flashDealCountdownTimer) clearInterval(flashDealCountdownTimer);
                return;
            }

            const totalSeconds = Math.floor(diff / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            if (hoursEl) hoursEl.textContent = padFlashTime(hours);
            if (minutesEl) minutesEl.textContent = padFlashTime(minutes);
            if (secondsEl) secondsEl.textContent = padFlashTime(seconds);
        }

        tick();
        flashDealCountdownTimer = setInterval(tick, 1000);
    }

    function getFlashDealMetrics() {
        const wrap = document.getElementById('flashDealWrap');
        const track = document.getElementById('flashDealTrack');
        if (!wrap || !track || !track.children.length) return null;

        const cards = Array.from(track.children);
        const refCard = cards[0];
        const step = refCard.offsetWidth + parseFloat(getComputedStyle(track).gap || '12');

        return { wrap, track, step };
    }

    function updateFlashDealNav() {
        const metrics = getFlashDealMetrics();
        const prevBtn = document.getElementById('flashDealPrev');
        const nextBtn = document.getElementById('flashDealNext');
        if (!metrics || !prevBtn || !nextBtn) return;

        const { wrap } = metrics;
        const hasOverflow = wrap.scrollWidth > wrap.clientWidth + 2;
        const atStart = wrap.scrollLeft <= 1;
        const atEnd = wrap.scrollLeft >= wrap.scrollWidth - wrap.clientWidth - 2;

        prevBtn.disabled = atStart;
        nextBtn.disabled = atEnd;
        prevBtn.classList.toggle('flash-deal__nav--visible', hasOverflow);
        nextBtn.classList.toggle('flash-deal__nav--visible', hasOverflow);
    }

    function scrollFlashDeal(direction) {
        const metrics = getFlashDealMetrics();
        if (!metrics) return;

        const { wrap, step } = metrics;
        wrap.scrollBy({ left: direction === 'next' ? step : -step, behavior: 'smooth' });
    }

    function initFlashDealCarousel() {
        const wrap = document.getElementById('flashDealWrap');
        if (!wrap) return;

        wrap.addEventListener('scroll', updateFlashDealNav, { passive: true });
        updateFlashDealNav();
    }

    function initCustomerReviewsPager() {
        const panels = document.querySelectorAll('.customer-reviews__page');
        const prevBtn = document.getElementById('customerReviewsPrev');
        const nextBtn = document.getElementById('customerReviewsNext');
        const statusEl = document.getElementById('customerReviewsStatus');

        if (!panels.length || !prevBtn || !nextBtn || !statusEl) return;

        let currentPage = 0;
        const totalPages = panels.length;

        function renderPage() {
            panels.forEach(function (panel, index) {
                const isActive = index === currentPage;
                panel.classList.toggle('is-active', isActive);
                panel.hidden = !isActive;
            });

            statusEl.textContent = (currentPage + 1) + '/' + totalPages;
            prevBtn.disabled = currentPage === 0;
            nextBtn.disabled = currentPage >= totalPages - 1;
        }

        prevBtn.addEventListener('click', function () {
            if (currentPage > 0) {
                currentPage--;
                renderPage();
            }
        });

        nextBtn.addEventListener('click', function () {
            if (currentPage < totalPages - 1) {
                currentPage++;
                renderPage();
            }
        });

        renderPage();
    }

    function initCustomerReviewsMobileCarousel() {
        const track = document.getElementById('customerReviewsTrack');
        const dots = document.querySelectorAll('.customer-reviews__dot');

        if (!track || !dots.length) return;

        let currentSlide = 0;
        const totalSlides = dots.length;
        let touchStartX = 0;

        function goToSlide(index) {
            currentSlide = Math.max(0, Math.min(index, totalSlides - 1));
            track.style.transform = 'translateX(-' + (currentSlide * 100) + '%)';

            dots.forEach(function (dot, i) {
                const isActive = i === currentSlide;
                dot.classList.toggle('is-active', isActive);
                dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                goToSlide(parseInt(dot.getAttribute('data-index'), 10));
            });
        });

        track.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].clientX;
        }, { passive: true });

        track.addEventListener('touchend', function (e) {
            const diff = e.changedTouches[0].clientX - touchStartX;
            if (Math.abs(diff) < 40) return;

            if (diff < 0 && currentSlide < totalSlides - 1) {
                goToSlide(currentSlide + 1);
            } else if (diff > 0 && currentSlide > 0) {
                goToSlide(currentSlide - 1);
            }
        }, { passive: true });

        goToSlide(0);
    }

    // Why Choose — mobile tab panels
    let whyChooseActiveIndex = 0;
    let whyChooseAutoplayTimer = null;

    function getWhyChooseAutoplayMs() {
        const root = document.getElementById('why-choose-section');
        return root ? parseInt(root.getAttribute('data-autoplay-ms') || '4500', 10) : 4500;
    }

    function goToWhyChooseTab(index, manual) {
        const tabs = document.querySelectorAll('.why-choose__tab');
        const panels = document.querySelectorAll('.why-choose__tab-panel');
        if (!tabs.length) return;

        whyChooseActiveIndex = ((index % tabs.length) + tabs.length) % tabs.length;

        tabs.forEach(function (tab, i) {
            const active = i === whyChooseActiveIndex;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        panels.forEach(function (panel, i) {
            panel.classList.toggle('is-active', i === whyChooseActiveIndex);
        });

        if (manual) {
            startWhyChooseAutoplay();
        }
    }

    function startWhyChooseAutoplay() {
        stopWhyChooseAutoplay();
        const tabs = document.querySelectorAll('.why-choose__tab');
        if (tabs.length <= 1) return;

        whyChooseAutoplayTimer = setInterval(function () {
            goToWhyChooseTab(whyChooseActiveIndex + 1, false);
        }, getWhyChooseAutoplayMs());
    }

    function stopWhyChooseAutoplay() {
        if (whyChooseAutoplayTimer) {
            clearInterval(whyChooseAutoplayTimer);
            whyChooseAutoplayTimer = null;
        }
    }

    function initWhyChooseCarousel() {
        const mobile = document.getElementById('why-choose-mobile');
        if (!mobile) return;

        mobile.addEventListener('mouseenter', stopWhyChooseAutoplay);
        mobile.addEventListener('mouseleave', startWhyChooseAutoplay);
        mobile.addEventListener('touchstart', stopWhyChooseAutoplay, { passive: true });
        mobile.addEventListener('touchend', function () {
            setTimeout(startWhyChooseAutoplay, 1200);
        }, { passive: true });

        goToWhyChooseTab(0, false);
        startWhyChooseAutoplay();
    }

    function refreshWhyChooseCarousel() {
        goToWhyChooseTab(whyChooseActiveIndex, false);
    }

    // Add event listeners for Load More buttons
    document.addEventListener('DOMContentLoaded', function() {
        initHeroCarousel();
        initPickAGiftCarousel();
        initCollectionsCarousel();
        initFlashDealCountdown();
        initFlashDealCarousel();
        initCustomerReviewsPager();
        initCustomerReviewsMobileCarousel();
        initWhyChooseCarousel();
        window.addEventListener('resize', function () {
            refreshHeroCarousel();
            refreshPickAGiftCarousel();
            refreshCollectionsCarousel();
            refreshWhyChooseCarousel();
            updateFlashDealNav();
            updateRecentlyViewedNav();
            updateBlogPostsNav();
        });
    });
</script>
@endsection