<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google Consent Mode -->
    <script>
        // Define dataLayer and the gtag function.
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}

        // IMPORTANT - DO NOT COPY/PASTE WITHOUT MODIFYING REGION LIST
        // Set default consent for specific regions according to your requirements
        gtag('consent', 'default', {
          'ad_storage': 'denied',
          'ad_user_data': 'denied',
          'ad_personalization': 'denied',
          'analytics_storage': 'denied',
          'regions': ['US', 'GB']
        });

        // Set default consent for all other regions according to your requirements
        gtag('consent', 'default', {
          'ad_storage': 'denied',
          'ad_user_data': 'denied',
          'ad_personalization': 'denied',
          'analytics_storage': 'denied'
        });
    </script>

    @php
        $metaPixelId = \App\Support\Settings::get('analytics.meta_pixel_id', config('services.meta.pixel_id'));
        $tiktokPixelId = \App\Support\Settings::get('analytics.tiktok_pixel_id', config('services.tiktok.pixel_id'));
        $googleTagManagerId = \App\Support\Settings::get('analytics.google_tag_manager_id', config('services.google.tag_manager_id'));
        
        // Currency configuration - available in all views
        $siteCurrency = currency();
        $siteCurrencyRate = currency_rate();
        $siteCurrencySymbol = currency_symbol();
        $freeShippingThresholdUsd = \App\Support\CatalogPageSettings::freeShippingThresholdUsd();
    @endphp
    
    <!-- Currency Configuration for JavaScript -->
    <script>
        window.SITE_CURRENCY = @json($siteCurrency);
        window.SITE_CURRENCY_SYMBOL = @json($siteCurrencySymbol);
        window.SITE_CURRENCY_RATE = {{ (float) ($siteCurrencyRate ?? 1) }};
        window.FREE_SHIPPING_THRESHOLD_USD = {{ (float) $freeShippingThresholdUsd }};
    </script>

    <!-- Cookie Script -->
    <script type="text/javascript" charset="UTF-8" src="//cdn.cookie-script.com/s/4a353d27e80af68f255e8b4bff37f75c.js"></script>

    @if($googleTagManagerId)
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','{{ $googleTagManagerId }}');</script>
        <!-- End Google Tag Manager -->
    @endif
    
    
    @if($metaPixelId)
        <!-- Meta Pixel Code -->
        <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $metaPixelId }}');
        fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"
        /></noscript>
        <!-- End Meta Pixel Code -->
    @endif

    @if($tiktokPixelId)
        <!-- TikTok Pixel Code Start -->
        <script>
        !function (w, d, t) {
          w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(
        var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script")
        ;n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};


          ttq.load('{{ $tiktokPixelId }}');
          ttq.page();
        }(window, document, 'ttq');
        </script>
        <!-- TikTok Pixel Code End -->
    @endif

    @auth
    <script>
    (function () {
        const rawData = {
            email: {!! json_encode(strtolower(trim(auth()->user()->email ?? ''))) !!},
            phone: {!! json_encode(auth()->user()->phone ?? auth()->user()->phone_number ?? '') !!},
            externalId: {!! json_encode((string) auth()->user()->id) !!}
        };

        const canHash = typeof window !== 'undefined'
            && window.crypto
            && window.crypto.subtle
            && typeof TextEncoder !== 'undefined';

        if (!canHash) {
            console.warn('TikTok identify skipped: SubtleCrypto/TextEncoder unavailable');
            return;
        }

        const encoder = new TextEncoder();

        const hashSHA256 = async (value) => {
            const data = encoder.encode(value);
            const hashBuffer = await window.crypto.subtle.digest('SHA-256', data);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            return hashArray.map((b) => b.toString(16).padStart(2, '0')).join('');
        };

        (async () => {
            try {
                const payload = {};

                if (rawData.email) {
                    const normalizedEmail = rawData.email.trim().toLowerCase();
                    if (normalizedEmail) {
                        payload.email = await hashSHA256(normalizedEmail);
                    }
                }

                if (rawData.phone) {
                    const normalizedPhone = String(rawData.phone).replace(/\D+/g, '');
                    if (normalizedPhone) {
                        payload.phone_number = await hashSHA256(normalizedPhone);
                    }
                }

                if (rawData.externalId) {
                    const normalizedId = String(rawData.externalId).trim();
                    if (normalizedId) {
                        payload.external_id = await hashSHA256(normalizedId);
                    }
                }

                if (Object.keys(payload).length > 0 && window.ttq && typeof window.ttq.identify === 'function') {
                    window.ttq.identify(payload);
                }
            } catch (error) {
                console.error('TikTok identify error:', error);
            }
        })();
    })();
    </script>
    @endauth
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="gGIR-fmeNV2oZz1duWvcwwKqTbqtvKM2OsiaTUyiLZc" />

    <title>{{ config('app.name', 'Bluprinter') }} - {{ $title ?? 'Home' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|oswald:500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Global CSS for select styling -->
    <style>
    /* Hide default select arrows globally */
    select {
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
        background-image: none !important;
    }
    
    select::-ms-expand {
        display: none !important;
    }
    
    select::-webkit-appearance {
        -webkit-appearance: none !important;
    }

    :root {
        --petrol: #005366;
        --petrol-dark: #003d4d;
        --brand-red: #e2150c;
        --header-hover: #e2150c;
        --header-blue: #2b7bc0;
        --header-orange: #f26522;
        --header-promo: #c8e600;
    }

    .site-header {
        font-size: 16px;
        transition: box-shadow 0.25s ease;
    }
    .site-header--scrolled {
        box-shadow: 0 4px 16px rgba(17, 24, 39, 0.08);
    }
    .site-header .header-announce {
        overflow: hidden;
    }
    .site-header .header-subnav {
        overflow: visible;
        position: relative;
        z-index: 40;
    }
    .site-header--scrolled .header-announce,
    .site-header--scrolled .header-subnav {
        display: none;
    }
    .header-subnav-item {
        position: relative;
        align-self: stretch;
        display: flex;
        align-items: center;
    }
    .header-subnav-dropdown {
        position: absolute;
        left: 0;
        top: 100%;
        padding-top: 4px;
        width: 18rem;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        z-index: 60;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }
    .header-subnav-item--end .header-subnav-dropdown {
        left: auto;
        right: 0;
    }
    .header-subnav-item:hover .header-subnav-dropdown,
    .header-subnav-item:focus-within .header-subnav-dropdown {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }
    .header-subnav-dropdown__panel {
        padding: 12px;
        background: #fff;
        border: 1px solid #f3f4f6;
        border-radius: 12px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.12);
    }
    .header-subnav-dropdown__panel--scroll {
        max-height: 20rem;
        overflow-y: auto;
    }
    .header-subnav-dropdown__link {
        display: block;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.4;
        color: #111827;
        text-decoration: none;
    }
    .header-subnav-dropdown__link:hover {
        background: #f9fafb;
        color: var(--header-hover);
    }
    .header-subnav-dropdown__link--strong {
        font-weight: 600;
        color: #005366;
    }
    .header-subnav-dropdown__link--strong:hover {
        color: var(--header-hover);
        background: transparent;
    }
    .site-header .header-main-inner {
        transition: none;
    }
    .site-header--scrolled .header-main-inner {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
    .site-header .header-brand-logo {
        transition: none;
    }
    .site-header--scrolled .header-brand-logo {
        height: 36px;
        max-width: 168px;
    }
    @media (min-width: 1024px) {
        .site-header--scrolled .header-brand-logo {
            height: 40px;
            max-width: 200px;
        }
    }
    .site-header .search-input {
        transition: none;
    }
    .site-header--scrolled .search-input {
        min-height: 42px;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
    .site-back-to-top {
        position: fixed;
        z-index: 40;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        bottom: 5.5rem;
        right: 1rem;
        width: 3rem;
        height: 3rem;
        padding: 0;
        border: none;
        border-radius: 9999px;
        background: #005366;
        color: #fff;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.2s ease, visibility 0.2s ease, background 0.2s ease, transform 0.2s ease;
    }
    .site-back-to-top.is-visible {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }
    .site-back-to-top:hover {
        background: #003d4d;
        transform: translateY(-2px);
    }
    .site-back-to-top svg {
        width: 1.25rem;
        height: 1.25rem;
        flex-shrink: 0;
    }
    .site-back-to-top__label {
        display: none;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1;
    }
    @media (min-width: 1024px) {
        .site-back-to-top {
            bottom: 1.5rem;
            right: 1.5rem;
            width: auto;
            height: auto;
            padding: 0.625rem 1rem;
        }
        .site-back-to-top__label {
            display: inline;
        }
    }
    .site-header .search-input,
    #mobile-search-overlay .search-input {
        border: 1.5px solid var(--header-hover);
        border-radius: 8px;
        font-size: 1rem;
        min-height: 48px;
    }
    .site-header .search-input:focus,
    #mobile-search-overlay .search-input:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(226, 21, 12, 0.15);
        border-color: var(--header-hover);
    }
    .site-header .nav-link-sub {
        font-size: 1.125rem; /* 18px — to hơn, mỏng hơn */
        font-weight: 400;
        color: #333;
        white-space: nowrap;
        transition: color 0.15s ease;
    }
    .site-header .nav-link-sub:hover {
        color: var(--header-hover);
    }
    .site-header .nav-link-sub.is-accent {
        font-weight: 500;
        background: linear-gradient(90deg, #f26522 0%, #e2150c 55%, #ff8a3d 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        color: transparent;
    }
    .site-header .nav-link-sub.is-accent svg {
        stroke: url(#nav-accent-gradient);
        color: #f26522;
        -webkit-text-fill-color: initial;
    }
    .site-header .nav-link-sub.is-accent:hover {
        filter: brightness(1.05);
    }
    .site-header .header-categories-btn:hover,
    .site-header .header-panel-btn.text-gray-700:hover,
    .site-header .header-panel-btn.text-gray-800:hover,
    .site-header #mobile-menu-btn:hover,
    .site-header #mobile-search-btn:hover,
    .site-header .header-main-bar a[aria-label="Cart"]:hover,
    .site-header .header-panel a.font-medium:hover,
    #mobile-search-overlay .flex-wrap a:hover {
        color: var(--header-hover);
    }
    .site-header .header-categories-btn {
        font-size: 1.0625rem;
        font-weight: 400; /* Categories: mỏng, không bold */
    }
    .site-header .header-announce {
        font-size: 0.9375rem;
    }
    @media (min-width: 640px) {
        .site-header .header-announce {
            font-size: 1rem;
        }
    }
    .site-header .header-brand-name {
        font-size: 1.75rem;
        line-height: 1.1;
    }
    @media (min-width: 1024px) {
        .site-header .header-brand-name {
            font-size: 2rem;
        }
    }
    .site-header .header-brand-tagline {
        font-size: 0.8125rem;
    }
    .site-header .header-brand-logo {
        display: block;
        width: auto;
        height: 44px;
        max-width: min(200px, 42vw);
        object-fit: contain;
        object-position: center;
    }
    @media (min-width: 1024px) {
        .site-header .header-brand-logo {
            height: 52px;
            max-width: 240px;
        }
    }
    @media (min-width: 1280px) {
        .site-header .header-brand-logo {
            height: 56px;
            max-width: 260px;
        }
    }
    .site-header .header-brand-logo--drawer {
        height: 44px;
        max-width: 200px;
    }
    .site-header .header-brand-link {
        line-height: 0;
    }
    .header-panel.header-panel--sheet {
        padding: 0 !important;
    }
    .header-panel.header-panel--sheet > div {
        border-radius: 1.5rem 1.5rem 0 0;
        border-left: 0;
        border-right: 0;
        border-bottom: 0;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.12);
    }
    .header-panel.header-panel--sheet > div > .absolute.-top-2 {
        display: none;
    }
    .gen-ai-fab {
        box-shadow: 0 8px 24px rgba(226, 21, 12, 0.35);
    }
    .gen-ai-fab:active {
        transform: scale(0.96);
    }
    @media (max-width: 1023px) {
        .site-header .header-brand-name {
            font-size: 1.25rem;
        }
    }
    .site-header .header-cat-item,
    .header-panel .header-cat-item {
        font-size: 1rem;
        font-weight: 500;
    }
    .header-panel .header-menu-title {
        font-size: 1.125rem;
    }
    .header-panel .header-menu-sub {
        font-size: 0.9375rem;
    }
    .header-panel .header-menu-item {
        font-size: 1rem;
    }
    .header-panel .header-studio-title {
        font-size: 1.25rem;
    }
    .header-panel .header-studio-sub {
        font-size: 1rem;
    }
    .header-panel .header-card-title {
        font-size: 1.0625rem;
    }
    .header-panel .header-card-desc {
        font-size: 0.9375rem;
    }

    .font-street {
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
    }

    .btn-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e2150c;
        color: #fff;
        border: none;
        border-radius: 9999px;
        font-weight: 600;
        padding: 0.75rem 1.75rem;
        cursor: pointer;
        transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .btn-cta:hover {
        background: #c0120a;
        color: #fff;
        box-shadow: 0 8px 20px rgba(226, 21, 12, 0.28);
        transform: translateY(-1px);
    }

    .btn-outline-petrol {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        color: #005366;
        border: 1.5px solid #005366;
        border-radius: 9999px;
        font-weight: 600;
        padding: 0.75rem 1.75rem;
        cursor: pointer;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .btn-outline-petrol:hover {
        background: rgba(0, 83, 102, 0.08);
        color: #003d4d;
    }
    .btn-outline-petrol.btn-outline-petrol--compact {
        flex-shrink: 0;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        white-space: nowrap;
    }
    .checkout-promo-row {
        display: flex;
        align-items: stretch;
        gap: 8px;
        min-width: 0;
        width: 100%;
    }
    .checkout-promo-row input {
        min-width: 0;
        flex: 1 1 0%;
        width: auto;
        max-width: 100%;
    }

    /* ── Product card (canonical) ── */
    .product-card {
        display: flex;
        flex-direction: column;
        background: #fff;
        border: none;
        border-radius: 12px;
        overflow: hidden;
        color: inherit;
        height: 100%;
        transition: box-shadow 0.25s ease;
        box-shadow: none;
    }
    .product-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
    }
    .product-card--featured {
        min-height: 100%;
    }
    .product-card__media-shell {
        position: relative;
        flex-shrink: 0;
    }
    .product-card__media {
        display: block;
        position: relative;
        aspect-ratio: 1;
        overflow: hidden;
        background: #f7f7f7;
        border-radius: 16px;
        text-decoration: none;
        color: inherit;
    }
    .product-card__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        transition: transform 0.45s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        will-change: transform;
    }
    .product-card:hover .product-card__media img {
        transform: scale(1.05);
    }
    .product-card__placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f7f7f7;
    }
    .product-card__sale-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 2;
        padding: 4px 10px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        line-height: 1.3;
        color: #fff;
        background: #005366;
        border-radius: 9999px;
        pointer-events: none;
    }
    .product-card__tryon {
        position: absolute;
        bottom: 12px;
        left: 12px;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 10px;
        font-family: inherit;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1;
        color: #111827;
        background: rgba(255, 255, 255, 0.95);
        border: none;
        border-radius: 9999px;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        opacity: 0;
        transform: translateY(6px);
        transition: opacity 0.2s ease, transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
    }
    .product-card__tryon-icon {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: #005366;
    }
    .product-card__tryon:hover {
        background: #fff;
        color: #005366;
    }
    .product-card:hover .product-card__tryon,
    .product-card:focus-within .product-card__tryon {
        opacity: 1;
        transform: translateY(0);
    }
    @media (hover: none) {
        .product-card__tryon {
            opacity: 1;
            transform: none;
        }
    }
    .product-card__media-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 2;
        padding: 4px 10px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        line-height: 1.25;
        color: #fff;
        background: #e2150c;
        border-radius: 9999px;
        text-transform: uppercase;
        pointer-events: none;
    }
    .product-card__body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 12px 4px 4px;
        background: #fff;
        flex-shrink: 0;
    }
    .product-card--featured .product-card__body {
        padding-top: 14px;
    }
    .product-card__title-link {
        text-decoration: none;
        color: inherit;
    }
    .product-card__title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.35;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.2s ease;
    }
    .product-card--featured .product-card__title {
        font-size: 1rem;
    }
    .product-card:hover .product-card__title {
        color: #005366;
    }
    .product-card__desc {
        font-size: 0.8125rem;
        color: #9ca3af;
        margin: 4px 0 0;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-card__shop {
        font-size: 0.8125rem;
        color: #9ca3af;
        margin: 4px 0 0;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-card__meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px 12px;
        margin-top: 8px;
        font-size: 0.75rem;
        line-height: 1.3;
        color: #6b7280;
    }
    .product-card__rating {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-weight: 600;
        color: #4b5563;
    }
    .product-card__rating-icon {
        width: 14px;
        height: 14px;
        color: #d97706;
        flex-shrink: 0;
    }
    .product-card__rating-count {
        font-weight: 500;
        color: #9ca3af;
    }
    .product-card__sold {
        color: #9ca3af;
        font-weight: 500;
    }
    .product-card__footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: auto;
        padding: 10px 4px 4px;
    }
    .product-card__prices {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 6px 8px;
        text-decoration: none;
        color: inherit;
        min-width: 0;
    }
    .product-card__price {
        font-size: 1.125rem;
        font-weight: 800;
        color: #111827;
        line-height: 1.15;
        letter-spacing: -0.01em;
    }
    .product-card__price-original {
        font-size: 0.8125rem;
        color: #9ca3af;
        text-decoration: line-through;
        line-height: 1.25;
    }
    .product-card__actions {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
    }
    .product-card__action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        padding: 0;
        border: none;
        background: transparent;
        color: #6b7280;
        border-radius: 8px;
        text-decoration: none;
        cursor: pointer;
        transition: color 0.2s ease, background-color 0.2s ease;
    }
    .product-card__action-btn svg {
        width: 20px;
        height: 20px;
    }
    .product-card__action-btn:hover {
        color: #005366;
        background: rgba(0, 83, 102, 0.08);
    }
    .product-card__action-btn.wishlist-btn.in-wishlist {
        color: #e2150c;
    }
    .product-card__action-btn.wishlist-btn.in-wishlist svg {
        fill: currentColor;
    }
    .product-card__list-cta {
        display: none;
    }

    /* Collection promo slot (bento grid) */
    .collection-promo-card {
        position: relative;
        display: block;
        border-radius: 12px;
        overflow: hidden;
        min-height: 160px;
        height: 100%;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 50%, #fbbf24 100%);
        text-decoration: none;
        color: inherit;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .collection-promo-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }
    .collection-promo-card__bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .collection-promo-card__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.85) 55%, rgba(255, 255, 255, 0.95) 100%);
    }
    .collection-promo-card__tag {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 2;
        padding: 4px 10px;
        font-size: 0.6875rem;
        font-weight: 600;
        color: #374151;
        background: rgba(255, 255, 255, 0.92);
        border-radius: 9999px;
        border: 1px solid #e5e7eb;
    }
    .collection-promo-card__content {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        height: 100%;
        min-height: 160px;
        padding: 14px;
    }
    .collection-promo-card__title {
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.25;
        margin: 0 0 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .collection-promo-card__subtitle {
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.4;
        margin: 0 0 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .collection-promo-card__cta {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        align-self: flex-start;
        padding: 8px 14px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #fff;
        background: #f26522;
        border-radius: 9999px;
        transition: background-color 0.2s ease;
    }
    .collection-promo-card:hover .collection-promo-card__cta {
        background: #e2150c;
    }
    .collection-promo-card--tall {
        min-height: 300px;
        background: linear-gradient(160deg, #005366 0%, #003d4d 55%, #001f28 100%);
        border-radius: 16px;
    }
    .collection-promo-card--tall .collection-promo-card__overlay {
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.05) 0%, rgba(0, 61, 77, 0.55) 45%, rgba(0, 31, 40, 0.88) 100%);
    }
    .collection-promo-card--tall .collection-promo-card__content {
        justify-content: flex-end;
        min-height: 300px;
        padding: 20px;
    }
    @media (min-width: 768px) {
        .collection-promo-card--tall {
            min-height: 100%;
        }
        .collection-promo-card--tall .collection-promo-card__content {
            min-height: 100%;
            padding: 24px;
        }
    }
    .collection-promo-card--tall .collection-promo-card__title {
        font-size: 1.25rem;
        color: #fff;
    }
    @media (min-width: 768px) {
        .collection-promo-card--tall .collection-promo-card__title {
            font-size: 1.5rem;
        }
    }
    .collection-promo-card--tall .collection-promo-card__subtitle {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.875rem;
        -webkit-line-clamp: 3;
    }
    .collection-promo-card--tall .collection-promo-card__tag {
        color: #005366;
        background: rgba(255, 255, 255, 0.95);
    }
    .collection-promo-card--tall .collection-promo-card__cta {
        background: #e2150c;
    }
    .collection-promo-card--tall:hover .collection-promo-card__cta {
        background: #c0120a;
    }

    /* Shared storefront headings & catalog (home, products, etc.) */
    .gradient-text {
        background: linear-gradient(135deg, #005366 0%, #e2150c 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
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
    .section-heading--catalog {
        text-align: center;
    }
    @media (min-width: 768px) {
        .section-heading--catalog {
            text-align: left;
        }
        .section-heading--catalog .section-heading__sub {
            margin-left: 0;
        }
        .section-heading--catalog .section-heading__accent {
            margin-left: 0;
        }
    }

    .support-page {
        background: #f9fafb;
        padding-bottom: 48px;
    }
    .support-alert {
        margin: 0 0 24px;
        padding: 16px;
        border-radius: 12px;
        font-size: 0.9375rem;
        line-height: 1.5;
    }
    .support-alert--success {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .support-alert--error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .support-form-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
        padding: 24px;
    }
    @media (min-width: 640px) {
        .support-form-card {
            padding: 32px;
        }
    }
    .support-form {
        position: relative;
    }
    .support-form__traps {
        position: absolute;
        left: -9999px;
        width: 1px;
        height: 1px;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
    }
    .support-form__grid {
        display: grid;
        gap: 16px;
        margin-bottom: 16px;
    }
    .support-form__grid--2 {
        grid-template-columns: 1fr;
    }
    @media (min-width: 640px) {
        .support-form__grid--2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    .support-form__field {
        margin-bottom: 16px;
    }
    .support-form__label {
        display: block;
        margin-bottom: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
    }
    .support-form__optional {
        font-weight: 400;
        color: #9ca3af;
    }
    .support-form__input,
    .support-form__file {
        width: 100%;
        min-height: 48px;
        padding: 12px 16px;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        background: #ffffff;
        color: #111827;
        font-size: 1rem;
        line-height: 1.4;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .support-form__textarea {
        min-height: 144px;
        resize: vertical;
    }
    .support-form__input:focus,
    .support-form__file:focus {
        outline: none;
        border-color: #005366;
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.12);
    }
    .support-form__input.is-invalid,
    .support-form__file.is-invalid {
        border-color: #e2150c;
        box-shadow: 0 0 0 3px rgba(226, 21, 12, 0.1);
    }
    .support-form__hint {
        margin-top: 8px;
        font-size: 0.8125rem;
        color: #9ca3af;
    }
    .support-form__error {
        margin-top: 8px;
        font-size: 0.875rem;
        color: #e2150c;
    }
    .support-form__actions {
        padding-top: 8px;
    }
    .support-form__actions .btn-cta {
        width: 100%;
    }
    @media (min-width: 640px) {
        .support-form__actions .btn-cta {
            width: auto;
            min-width: 180px;
        }
    }

    .catalog-page {
        background: #f9fafb;
        padding-bottom: 48px;
        overflow-x: clip;
    }
    .catalog-page--collections {
        padding-bottom: 48px;
    }
    .catalog-collections-hero {
        padding: 8px 0 24px;
        background: linear-gradient(165deg, rgba(0, 83, 102, 0.07) 0%, #f9fafb 45%, rgba(242, 101, 34, 0.05) 100%);
        border-bottom: 1px solid rgba(0, 83, 102, 0.08);
    }
    @media (min-width: 768px) {
        .catalog-collections-hero {
            padding: 12px 0 32px;
        }
    }
    .catalog-breadcrumb--hero {
        padding: 12px 0 0;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .catalog-collections-hero__body {
        max-width: 720px;
        margin: 32px auto 0;
        text-align: center;
    }
    @media (min-width: 768px) {
        .catalog-collections-hero__body {
            margin-top: 40px;
        }
    }
    .catalog-collections-hero__head {
        max-width: 42rem;
        margin: 0 auto;
        text-align: center;
    }
    .catalog-collections-hero__eyebrow {
        margin: 0 0 8px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #005366;
    }
    .catalog-collections-hero__title {
        margin: 0;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(2rem, 5vw, 2.75rem);
        font-weight: 700;
        line-height: 1.1;
        color: #111827;
    }
    .catalog-collections-hero__sub {
        margin: 12px auto 0;
        max-width: 36rem;
        font-size: 1.0625rem;
        font-style: italic;
        line-height: 1.55;
        color: #4b5563;
    }
    @media (min-width: 768px) {
        .catalog-collections-hero__sub {
            font-size: 1.125rem;
        }
    }
    .catalog-collections-hero__search {
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 720px;
        margin: 28px auto 0;
    }
    @media (min-width: 768px) {
        .catalog-collections-hero__search {
            flex-direction: row;
            align-items: stretch;
            justify-content: center;
        }
    }
    .catalog-collections-hero__clear-wrap {
        margin: 12px 0 0;
    }
    .catalog-collections-hero__clear {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #005366;
        text-decoration: none;
    }
    .catalog-collections-hero__clear:hover {
        text-decoration: underline;
    }
    .catalog-collections-hero__search-field {
        position: relative;
        display: flex;
        flex: 1;
        min-width: 0;
    }
    .catalog-collections-hero__search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        width: 20px;
        height: 20px;
        color: #9ca3af;
        transform: translateY(-50%);
        pointer-events: none;
    }
    .catalog-collections-hero__search-input {
        width: 100%;
        min-height: 48px;
        padding: 12px 16px 12px 44px;
        border: 1px solid #e5e7eb;
        border-radius: 9999px;
        background: #fff;
        font-size: 1rem;
        line-height: 1.4;
        color: #111827;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .catalog-collections-hero__search-input::placeholder {
        color: #9ca3af;
    }
    .catalog-collections-hero__search-input:focus {
        outline: none;
        border-color: rgba(0, 83, 102, 0.35);
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.1);
    }
    .catalog-collections-hero__sort {
        min-height: 48px;
        padding: 12px 36px 12px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 9999px;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 12px center / 16px;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #374151;
        appearance: none;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    @media (min-width: 768px) {
        .catalog-collections-hero__sort {
            flex: 0 0 160px;
        }
    }
    .catalog-collections-hero__sort:focus {
        outline: none;
        border-color: rgba(0, 83, 102, 0.35);
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.1);
    }
    .catalog-collections-hero__apply {
        min-height: 48px;
        padding: 12px 28px;
        white-space: nowrap;
    }
    @media (max-width: 767px) {
        .catalog-collections-hero__apply {
            width: 100%;
            justify-content: center;
        }
    }
    .catalog-collections-tabs {
        padding: 16px 0;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
    }
    .catalog-collections-tabs__track {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 2px;
        scrollbar-width: none;
    }
    .catalog-collections-tabs__track::-webkit-scrollbar {
        display: none;
    }
    .catalog-collections-tab {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
        padding: 8px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 9999px;
        background: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        text-decoration: none;
        color: #374151;
        transition: border-color 0.2s ease, background 0.2s ease, color 0.2s ease;
    }
    .catalog-collections-tab:hover {
        border-color: rgba(0, 83, 102, 0.25);
        color: #005366;
    }
    .catalog-collections-tab.is-active {
        border-color: rgba(0, 83, 102, 0.2);
        background: rgba(0, 83, 102, 0.08);
        color: #005366;
    }
    .catalog-collections-tab--all.is-active {
        background: rgba(43, 123, 192, 0.1);
        border-color: rgba(43, 123, 192, 0.25);
        color: #2b7bc0;
    }
    .catalog-collections-tab__count {
        font-weight: 600;
        color: inherit;
        opacity: 0.85;
    }
    .catalog-collections-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 0 8px;
    }
    .catalog-collections-summary__text {
        margin: 0;
        font-size: 0.875rem;
        line-height: 1.4;
        color: #6b7280;
    }
    .catalog-collections-summary__count {
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: 1.125rem;
        font-weight: 700;
        color: #005366;
    }
    .catalog-collections-summary__dot {
        margin: 0 4px;
        color: #d1d5db;
    }
    .catalog-collections-summary__range {
        color: #374151;
        font-weight: 500;
    }
    .catalog-collections-summary__clear {
        flex-shrink: 0;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #005366;
        text-decoration: none;
    }
    .catalog-collections-summary__clear:hover {
        text-decoration: underline;
    }
    .catalog-toolbar--collections-mobile {
        margin: 0 0 16px;
        background: transparent;
        border: 0;
    }
    @media (min-width: 768px) {
        .catalog-toolbar--collections-mobile {
            display: none;
        }
    }
    .catalog-toolbar--collections-mobile .catalog-toolbar__mobile {
        display: block;
    }
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    .catalog-page > .max-w-7xl {
        min-width: 0;
    }
    .catalog-breadcrumb {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        padding: 16px 0 8px;
        font-size: 0.875rem;
        line-height: 1.4;
    }
    .catalog-breadcrumb a {
        color: #4b5563;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .catalog-breadcrumb a:hover {
        color: #005366;
    }
    .catalog-breadcrumb__sep {
        color: #d1d5db;
        user-select: none;
    }
    .catalog-breadcrumb__current {
        color: #111827;
        font-weight: 600;
    }
    .catalog-toolbar {
        margin: 16px 0 20px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }
    @media (min-width: 768px) {
        .catalog-toolbar {
            margin: 20px 0 24px;
            border-radius: 14px;
        }
    }
    .catalog-toolbar__desktop {
        display: none;
    }
    @media (min-width: 768px) {
        .catalog-toolbar__desktop {
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 56px;
            padding: 8px 14px;
        }
    }
    .catalog-toolbar__summary {
        margin: 0;
        flex-shrink: 0;
        font-size: 0.875rem;
        line-height: 1.4;
        color: #6b7280;
        white-space: nowrap;
    }
    .catalog-toolbar__summary-count {
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: 1.125rem;
        font-weight: 700;
        color: #005366;
    }
    .catalog-toolbar__summary-dot {
        margin: 0 4px;
        color: #d1d5db;
    }
    .catalog-toolbar__summary-range {
        color: #374151;
        font-weight: 500;
    }
    .catalog-toolbar__filters {
        display: flex;
        flex: 1;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 0;
    }
    .catalog-toolbar__filters .catalog-filters__select {
        flex: 1;
        max-width: 180px;
        min-height: 40px;
    }
    .catalog-toolbar__actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
        padding-left: 12px;
        border-left: 1px solid #e5e7eb;
    }
    .catalog-toolbar__mobile {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 12px;
    }
    @media (min-width: 768px) {
        .catalog-toolbar__mobile {
            display: none;
        }
    }
    .catalog-toolbar__mobile-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .catalog-toolbar__mobile-actions {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 8px;
    }
    .catalog-toolbar__clear {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #005366;
        text-decoration: none;
        white-space: nowrap;
    }
    .catalog-toolbar__clear:hover {
        color: #003d4d;
        text-decoration: underline;
    }
    .catalog-view-toggle {
        display: inline-flex;
        align-items: center;
        padding: 3px;
        border-radius: 10px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        flex-shrink: 0;
    }
    .catalog-view-toggle__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 34px;
        border: none;
        border-radius: 8px;
        background: transparent;
        color: #6b7280;
        cursor: pointer;
        transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }
    .catalog-view-toggle__btn svg {
        width: 18px;
        height: 18px;
    }
    .catalog-view-toggle__btn.is-active {
        background: #fff;
        color: #005366;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    .catalog-mobile-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 44px;
        padding: 0 12px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        cursor: pointer;
        text-decoration: none;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }
    .catalog-mobile-btn svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        color: #005366;
    }
    .catalog-mobile-btn:hover {
        border-color: #005366;
        background: rgba(0, 83, 102, 0.04);
    }
    .catalog-mobile-btn--ghost {
        color: #005366;
        background: #fff;
    }
    .catalog-mobile-btn__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 9999px;
        background: #f26522;
        color: #fff;
        font-size: 0.6875rem;
        font-weight: 700;
        line-height: 1;
    }
    .catalog-filters {
        display: contents;
    }
    .catalog-filters__select {
        width: 100%;
        min-width: 0;
        min-height: 40px;
        padding: 8px 28px 8px 10px;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #111827;
        background: #f9fafb url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 8px center / 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        outline: none;
        appearance: none;
        cursor: pointer;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }
    @media (min-width: 768px) {
        .catalog-filters__select {
            width: auto;
            min-width: 148px;
            font-size: 0.875rem;
            padding-right: 32px;
            background-color: #fff;
        }
    }
    .catalog-filters__select:focus {
        border-color: #005366;
        background-color: #fff;
        box-shadow: 0 0 0 2px rgba(0, 83, 102, 0.1);
    }
    .catalog-promo {
        position: relative;
        display: block;
        overflow: hidden;
        min-height: 140px;
        margin: 0 0 20px;
        border-radius: 16px;
        text-decoration: none;
        color: #fff;
    }
    .catalog-promo__image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .catalog-promo__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(105deg, rgba(0, 30, 38, 0.72) 0%, rgba(0, 61, 77, 0.45) 55%, rgba(0, 0, 0, 0.15) 100%);
    }
    .catalog-promo__content {
        position: relative;
        z-index: 1;
        padding: 24px 20px;
        max-width: 36rem;
    }
    @media (min-width: 768px) {
        .catalog-promo {
            min-height: 168px;
        }
        .catalog-promo__content {
            padding: 28px 32px;
        }
    }
    .catalog-promo__title {
        margin: 0 0 8px;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(1.25rem, 3vw, 1.75rem);
        font-weight: 700;
        line-height: 1.15;
        color: #fff;
    }
    .catalog-promo__subtitle {
        margin: 0;
        font-size: 0.9375rem;
        line-height: 1.5;
        color: rgba(255, 255, 255, 0.88);
    }
    .catalog-promo__cta {
        display: inline-flex;
        margin-top: 14px;
        padding: 8px 14px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.28);
        font-size: 0.8125rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .catalog-seo {
        margin: 32px 0 8px;
        padding: 24px 18px;
        background: linear-gradient(165deg, rgba(0, 83, 102, 0.06) 0%, #fff 42%, rgba(242, 101, 34, 0.04) 100%);
        border: 1px solid rgba(0, 83, 102, 0.12);
        border-radius: 20px;
        overflow: hidden;
    }
    @media (min-width: 768px) {
        .catalog-seo {
            margin: 40px 0 12px;
            padding: 32px 28px;
            border-radius: 24px;
        }
    }
    .catalog-seo__head {
        max-width: 40rem;
        margin: 0 auto 20px;
        text-align: center;
    }
    @media (min-width: 768px) {
        .catalog-seo__head {
            margin-bottom: 28px;
        }
    }
    .catalog-seo__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 10px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #005366;
    }
    .catalog-seo__eyebrow::before,
    .catalog-seo__eyebrow::after {
        content: "";
        width: 20px;
        height: 2px;
        background: linear-gradient(90deg, transparent, #f26522);
        border-radius: 1px;
    }
    .catalog-seo__eyebrow::after {
        background: linear-gradient(90deg, #f26522, transparent);
    }
    .catalog-seo__title {
        margin: 0 0 10px;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(1.375rem, 3.5vw, 1.75rem);
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: 0.01em;
        color: #111827;
    }
    .catalog-seo__intro {
        margin: 0;
        font-size: 0.9375rem;
        line-height: 1.65;
        color: #4b5563;
    }
    .catalog-seo__grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    @media (min-width: 640px) {
        .catalog-seo__grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }
    }
    .catalog-seo__card {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .catalog-seo__card:hover {
        border-color: rgba(0, 83, 102, 0.2);
        box-shadow: 0 8px 24px rgba(0, 83, 102, 0.08);
        transform: translateY(-2px);
    }
    .catalog-seo__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        flex-shrink: 0;
    }
    .catalog-seo__icon svg {
        width: 22px;
        height: 22px;
    }
    .catalog-seo__icon--petrol {
        color: #005366;
        background: rgba(0, 83, 102, 0.1);
    }
    .catalog-seo__icon--orange {
        color: #f26522;
        background: rgba(242, 101, 34, 0.12);
    }
    .catalog-seo__icon--cta {
        color: #e2150c;
        background: rgba(226, 21, 12, 0.08);
    }
    .catalog-seo__card-title {
        margin: 0;
        font-size: 0.9375rem;
        font-weight: 700;
        line-height: 1.35;
        color: #111827;
    }
    .catalog-seo__card-text {
        margin: 0;
        font-size: 0.8125rem;
        line-height: 1.55;
        color: #6b7280;
    }
    .catalog-seo__content {
        max-width: 42rem;
        margin: 0 auto;
        font-size: 0.9375rem;
        line-height: 1.65;
        color: #4b5563;
    }
    .catalog-seo__content p {
        margin: 0 0 12px;
    }
    .catalog-seo__content p:last-child {
        margin-bottom: 0;
    }
    body.catalog-sheet-open {
        overflow: hidden;
    }
    .catalog-sheet[hidden] {
        display: none !important;
    }
    .catalog-sheet {
        position: fixed;
        inset: 0;
        z-index: 120;
        display: flex;
        align-items: flex-end;
        justify-content: center;
    }
    .catalog-sheet__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }
    .catalog-sheet__panel {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 640px;
        max-height: min(85vh, 560px);
        background: #fff;
        border-radius: 20px 20px 0 0;
        box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        animation: catalogSheetUp 0.28s ease;
    }
    @keyframes catalogSheetUp {
        from { transform: translateY(100%); opacity: 0.6; }
        to { transform: translateY(0); opacity: 1; }
    }
    .catalog-sheet__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid #e5e7eb;
    }
    .catalog-sheet__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
    }
    .catalog-sheet__close {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 9999px;
        background: #f3f4f6;
        color: #4b5563;
        font-size: 1.5rem;
        line-height: 1;
        cursor: pointer;
    }
    .catalog-sheet__body {
        padding: 16px 18px 0;
        overflow-y: auto;
    }
    .catalog-sheet__label {
        display: block;
        margin: 0 0 6px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
    }
    .catalog-sheet__label + .catalog-sheet__select {
        margin-bottom: 14px;
    }
    .catalog-sheet__select {
        width: 100%;
        min-height: 48px;
        font-size: 0.9375rem;
    }
    .catalog-sheet__foot {
        display: flex;
        gap: 10px;
        padding: 16px 18px calc(16px + env(safe-area-inset-bottom, 0px));
        border-top: 1px solid #e5e7eb;
        margin-top: 8px;
    }
    .catalog-sheet__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 0 18px;
        border-radius: 12px;
        font-size: 0.9375rem;
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }
    .catalog-sheet__btn--ghost {
        flex: 1;
        color: #005366;
        background: #fff;
        border: 1px solid #d1d5db;
    }
    .catalog-sheet__btn--primary {
        flex: 1;
        color: #fff;
        background: #005366;
    }
    .catalog-sheet__btn--full {
        width: 100%;
    }
    .catalog-sort-options {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .catalog-sort-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 12px;
        margin-bottom: 6px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        cursor: pointer;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #111827;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }
    .catalog-sort-option.is-selected {
        border-color: #005366;
        background: rgba(0, 83, 102, 0.06);
        color: #005366;
        font-weight: 700;
    }
    .catalog-sort-option input {
        accent-color: #005366;
    }
    .catalog-grid:not(.catalog-grid--list) .product-card__desc {
        display: none;
    }
    .catalog-grid:not(.catalog-grid--list) .product-card__list-cta {
        display: none;
    }
    .catalog-grid--list {
        grid-template-columns: 1fr !important;
        gap: 10px !important;
    }
    .catalog-grid--list .product-card {
        display: grid;
        grid-template-columns: 96px minmax(0, 1fr);
        grid-template-rows: auto auto;
        grid-template-areas:
            "media body"
            "media aside";
        align-items: start;
        gap: 0 14px;
        padding: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .catalog-grid--list .product-card:hover {
        border-color: rgba(0, 83, 102, 0.22);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
    }
    .catalog-grid--list .product-card__media-shell {
        grid-area: media;
        width: 96px;
        height: 96px;
    }
    .catalog-grid--list .product-card__media {
        width: 100%;
        height: 100%;
        aspect-ratio: 1;
        min-height: 0;
        border-radius: 10px;
    }
    .catalog-grid--list .product-card__sale-badge {
        top: 6px;
        left: 6px;
        font-size: 0.625rem;
        padding: 2px 6px;
    }
    .catalog-grid--list .product-card__tryon {
        opacity: 1;
        transform: none;
    }
    .catalog-grid--list .product-card__body {
        grid-area: body;
        padding: 0;
        min-width: 0;
        gap: 0;
    }
    .catalog-grid--list .product-card__title {
        font-size: 0.9375rem;
        line-height: 1.35;
        -webkit-line-clamp: 2;
        margin-bottom: 4px;
    }
    .catalog-grid--list .product-card__shop {
        font-size: 0.75rem;
        color: #005366;
        font-weight: 600;
        margin: 0 0 4px;
    }
    .catalog-grid--list .product-card__desc {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        font-size: 0.8125rem;
        line-height: 1.45;
        color: #6b7280;
        margin: 0 0 6px;
    }
    .catalog-grid--list .product-card__meta {
        font-size: 0.75rem;
        margin-top: 0;
    }
    .catalog-grid--list .product-card__footer {
        grid-area: aside;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
        margin: 0;
        padding: 0;
    }
    .catalog-grid--list .product-card__prices {
        flex-direction: row;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 6px;
    }
    .catalog-grid--list .product-card__price {
        font-size: 1.0625rem;
        font-weight: 800;
    }
    .catalog-grid--list .product-card__actions {
        display: none;
    }
    .catalog-grid--list .product-card__list-cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 0 14px;
        border-radius: 9999px;
        background: #005366;
        color: #fff;
        font-size: 0.8125rem;
        font-weight: 700;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }
    .catalog-grid--list .product-card__list-cta:hover {
        background: #003d4d;
    }
    @media (min-width: 640px) {
        .catalog-grid--list .product-card {
            grid-template-columns: 120px minmax(0, 1fr) auto;
            grid-template-rows: 1fr;
            grid-template-areas: "media body aside";
            align-items: center;
            gap: 0 18px;
            padding: 14px 16px;
        }
        .catalog-grid--list .product-card__media-shell {
            width: 120px;
            height: 120px;
        }
        .catalog-grid--list .product-card__title {
            font-size: 1rem;
            -webkit-line-clamp: 1;
        }
        .catalog-grid--list .product-card__desc {
            -webkit-line-clamp: 2;
        }
        .catalog-grid--list .product-card__footer {
            align-items: flex-end;
            min-width: 132px;
        }
        .catalog-grid--list .product-card__prices {
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
        }
        .catalog-grid--list .product-card__price {
            font-size: 1.25rem;
        }
        .catalog-grid--list .product-card__actions {
            display: flex;
        }
        .catalog-grid--list .product-card__list-cta {
            min-width: 120px;
        }
    }
    .catalog-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        min-width: 0;
    }
    @media (min-width: 640px) {
        .catalog-grid {
            gap: 16px;
        }
    }
    @media (min-width: 768px) {
        .catalog-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }
    }
    @media (min-width: 1024px) {
        .catalog-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 24px;
        }
    }
    @media (max-width: 639px) {
        .catalog-grid .product-card__body {
            padding: 10px 2px 2px;
        }
        .catalog-grid .product-card__title {
            font-size: 0.8125rem;
            -webkit-line-clamp: 2;
        }
        .catalog-grid .product-card__shop,
        .catalog-grid .product-card__meta {
            font-size: 0.6875rem;
        }
        .catalog-grid .product-card__meta {
            gap: 4px 8px;
            margin-top: 6px;
        }
        .catalog-grid .product-card__footer {
            flex-wrap: wrap;
            gap: 6px;
            padding-top: 8px;
        }
        .catalog-grid .product-card__price {
            font-size: 0.9375rem;
        }
        .catalog-grid .product-card__price-original {
            font-size: 0.75rem;
        }
        .catalog-grid .product-card__action-btn {
            width: 32px;
            height: 32px;
        }
        .catalog-grid .product-card__action-btn svg {
            width: 18px;
            height: 18px;
        }
        .catalog-grid .product-card__sale-badge {
            top: 8px;
            left: 8px;
            padding: 3px 8px;
            font-size: 0.6875rem;
        }
        .catalog-grid .product-card__media {
            border-radius: 12px;
        }
    }
    @media (max-width: 767px) {
        .section-heading--catalog .section-heading__title {
            font-size: clamp(1.75rem, 8vw, 2.25rem);
        }
        .section-heading--catalog .section-heading__sub {
            font-size: 0.9375rem;
            padding: 0 4px;
        }
    }
    .catalog-empty {
        text-align: center;
        padding: 48px 24px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
    }
    .catalog-empty__icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 16px;
        color: #9ca3af;
    }
    .catalog-empty__title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #111827;
        margin: 0 0 8px;
    }
    .catalog-empty__sub {
        font-size: 0.9375rem;
        color: #4b5563;
        margin: 0 0 20px;
    }
    .catalog-toolbar__search {
        flex: 1;
        max-width: 200px;
        min-width: 0;
        min-height: 40px;
        padding: 8px 12px;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #111827;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .catalog-toolbar__search::placeholder {
        color: #9ca3af;
    }
    .catalog-toolbar__search:focus {
        border-color: #005366;
        box-shadow: 0 0 0 2px rgba(0, 83, 102, 0.1);
    }
    @media (max-width: 767px) {
        .catalog-toolbar__search--desktop {
            display: none;
        }
    }
    .catalog-subcats {
        margin: 0 0 24px;
        padding: 20px 16px;
        background: linear-gradient(165deg, rgba(0, 83, 102, 0.05) 0%, #fff 50%, rgba(242, 101, 34, 0.04) 100%);
        border: 1px solid rgba(0, 83, 102, 0.1);
        border-radius: 18px;
    }
    @media (min-width: 768px) {
        .catalog-subcats {
            margin-bottom: 28px;
            padding: 24px 20px;
            border-radius: 20px;
        }
    }
    .catalog-subcats__head {
        margin-bottom: 16px;
    }
    .catalog-subcats__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 6px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #005366;
    }
    .catalog-subcats__title {
        margin: 0 0 4px;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.25;
        color: #111827;
    }
    .catalog-subcats__sub {
        margin: 0;
        font-size: 0.875rem;
        line-height: 1.5;
        color: #6b7280;
    }
    .catalog-subcats__track-wrap {
        overflow-x: auto;
        margin: 0 -4px;
        padding: 4px;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .catalog-subcats__track-wrap::-webkit-scrollbar {
        display: none;
    }
    .catalog-subcats__track {
        display: flex;
        gap: 12px;
        list-style: none;
        margin: 0;
        padding: 0;
        width: max-content;
        min-width: 100%;
    }
    @media (min-width: 768px) {
        .catalog-subcats__track {
            gap: 16px;
        }
        .catalog-subcats--few .catalog-subcats__track {
            width: auto;
            min-width: 0;
            flex-wrap: wrap;
        }
    }
    .catalog-subcat {
        display: block;
        position: relative;
        flex: 0 0 132px;
        width: 132px;
        aspect-ratio: 3 / 4;
        overflow: hidden;
        border-radius: 14px;
        text-decoration: none;
        color: #fff;
        box-shadow: 0 4px 14px rgba(0, 83, 102, 0.12);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    @media (min-width: 640px) {
        .catalog-subcat {
            flex: 0 0 148px;
            width: 148px;
        }
    }
    .catalog-subcat:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0, 83, 102, 0.18);
    }
    .catalog-subcat.is-active {
        box-shadow: 0 0 0 3px #005366, 0 8px 20px rgba(0, 83, 102, 0.18);
    }
    .catalog-subcat__media {
        position: absolute;
        inset: 0;
        background: linear-gradient(145deg, #005366 0%, #003d4d 100%);
    }
    .catalog-subcat__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .catalog-subcat:hover .catalog-subcat__media img {
        transform: scale(1.08);
    }
    .catalog-subcat__placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.5);
    }
    .catalog-subcat__placeholder svg {
        width: 32px;
        height: 32px;
    }
    .catalog-subcat__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0) 35%, rgba(0, 30, 38, 0.82) 100%);
        pointer-events: none;
    }
    .catalog-subcat__content {
        position: absolute;
        inset: auto 0 0;
        z-index: 1;
        padding: 12px;
    }
    .catalog-subcat__label {
        display: block;
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1.3;
        color: #fff;
    }
    .catalog-subcat__cta {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.85);
    }
    .catalog-subcat__cta svg {
        width: 12px;
        height: 12px;
        transition: transform 0.2s ease;
    }
    .catalog-subcat:hover .catalog-subcat__cta svg {
        transform: translateX(2px);
    }
    .catalog-recently-viewed {
        margin-top: 36px;
        padding-top: 32px;
        border-top: 1px solid #e5e7eb;
    }
    .catalog-recently-viewed.hidden {
        display: none;
    }
    .catalog-recently-viewed__head {
        margin-bottom: 8px;
    }
    .recently-viewed__slider {
        position: relative;
        margin-top: 16px;
    }
    .recently-viewed__track-wrap {
        overflow-x: auto;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .recently-viewed__track-wrap::-webkit-scrollbar {
        display: none;
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
        padding: 32px 16px;
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
    .catalog-related {
        margin-top: 36px;
        padding-top: 32px;
        border-top: 1px solid #e5e7eb;
    }
    .catalog-related__head {
        margin-bottom: 20px;
    }
    @media (min-width: 768px) {
        .catalog-related__head {
            margin-bottom: 24px;
        }
    }
    .catalog-related__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 6px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #005366;
    }
    .catalog-related__title {
        margin: 0 0 6px;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(1.25rem, 3vw, 1.625rem);
        font-weight: 700;
        line-height: 1.2;
        color: #111827;
    }
    .catalog-related__sub {
        margin: 0;
        font-size: 0.9375rem;
        line-height: 1.5;
        color: #6b7280;
        max-width: 36rem;
    }
    .catalog-related__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    @media (min-width: 768px) {
        .catalog-related__grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }
    }
    .catalog-related-card {
        display: block;
        position: relative;
        overflow: hidden;
        aspect-ratio: 4 / 5;
        border-radius: 16px;
        text-decoration: none;
        color: #fff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .catalog-related-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 32px rgba(0, 83, 102, 0.16);
    }
    .catalog-related-card__media {
        position: absolute;
        inset: 0;
        background: linear-gradient(145deg, #005366 0%, #003d4d 55%, #f26522 140%);
    }
    .catalog-related-card__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.45s ease;
    }
    .catalog-related-card:hover .catalog-related-card__media img {
        transform: scale(1.06);
    }
    .catalog-related-card__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.08) 0%, rgba(0, 30, 38, 0.55) 55%, rgba(0, 20, 26, 0.88) 100%);
        pointer-events: none;
    }
    .catalog-related-card__content {
        position: absolute;
        inset: auto 0 0;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 16px;
    }
    .catalog-related-card__badge {
        align-self: flex-start;
        padding: 4px 10px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.24);
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #fff;
    }
    .catalog-related-card__title {
        margin: 0;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: 1.125rem;
        font-weight: 700;
        line-height: 1.25;
        color: #fff;
    }
    .catalog-related-card__cta {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
    }
    .catalog-related-card__cta svg {
        width: 14px;
        height: 14px;
        transition: transform 0.2s ease;
    }
    .catalog-related-card:hover .catalog-related-card__cta svg {
        transform: translateX(3px);
    }
    .catalog-intro {
        margin: 12px auto 0;
        max-width: 36rem;
    }
    @media (min-width: 768px) {
        .section-heading--catalog .catalog-intro {
            margin-left: 0;
        }
    }
    .catalog-intro__text {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 400;
        line-height: 1.55;
        color: #4b5563;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    @media (min-width: 768px) {
        .catalog-intro__text {
            font-size: 1.25rem;
        }
    }
    .catalog-intro.is-expanded .catalog-intro__text {
        display: block;
        -webkit-line-clamp: unset;
        overflow: visible;
    }
    .catalog-intro__toggle {
        margin-top: 8px;
        padding: 0;
        border: 0;
        background: none;
        font-size: 0.875rem;
        font-weight: 600;
        color: #005366;
        cursor: pointer;
        text-decoration: underline;
        text-underline-offset: 3px;
    }
    .catalog-intro__toggle:hover {
        color: #f26522;
    }
    .catalog-collections-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    @media (min-width: 640px) {
        .catalog-collections-grid {
            gap: 16px;
        }
    }
    @media (min-width: 1024px) {
        .catalog-collections-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }
    }
    .catalog-collection-card {
        display: block;
        position: relative;
        overflow: hidden;
        aspect-ratio: 4 / 5;
        border-radius: 16px;
        text-decoration: none;
        color: #fff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .catalog-collection-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 32px rgba(0, 83, 102, 0.16);
    }
    .catalog-collection-card__media {
        position: absolute;
        inset: 0;
        background: linear-gradient(145deg, #005366 0%, #003d4d 55%, #f26522 140%);
    }
    .catalog-collection-card__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.45s ease;
    }
    .catalog-collection-card:hover .catalog-collection-card__media img {
        transform: scale(1.06);
    }
    .catalog-collection-card__featured {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 2;
        padding: 4px 10px;
        border-radius: 9999px;
        background: rgba(226, 21, 12, 0.92);
        color: #fff;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .catalog-collection-card__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.06) 0%, rgba(0, 30, 38, 0.55) 50%, rgba(0, 20, 26, 0.9) 100%);
        pointer-events: none;
    }
    .catalog-collection-card__content {
        position: absolute;
        inset: auto 0 0;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 16px;
    }
    .catalog-collection-card__badge {
        align-self: flex-start;
        padding: 4px 10px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.24);
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #fff;
    }
    .catalog-collection-card__title {
        margin: 0;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: 1.125rem;
        font-weight: 700;
        line-height: 1.25;
        color: #fff;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .catalog-collection-card__desc {
        font-size: 0.8125rem;
        line-height: 1.45;
        color: rgba(255, 255, 255, 0.82);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .catalog-collection-card__shop {
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.75);
    }
    .catalog-collection-card__cta {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 2px;
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.95);
    }
    .catalog-collection-card__cta svg {
        width: 14px;
        height: 14px;
        transition: transform 0.2s ease;
    }
    .catalog-collection-card:hover .catalog-collection-card__cta svg {
        transform: translateX(3px);
    }
    .catalog-featured-collections {
        margin: 0 0 24px;
        padding: 20px 16px;
        background: linear-gradient(165deg, rgba(0, 83, 102, 0.05) 0%, #fff 50%, rgba(242, 101, 34, 0.04) 100%);
        border: 1px solid rgba(0, 83, 102, 0.1);
        border-radius: 18px;
    }
    .catalog-featured-collections__head {
        margin-bottom: 14px;
    }
    .catalog-featured-collections__eyebrow {
        margin: 0 0 4px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #005366;
    }
    .catalog-featured-collections__title {
        margin: 0;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: 1.125rem;
        font-weight: 700;
        color: #111827;
    }
    .catalog-featured-collections__track-wrap {
        overflow-x: auto;
        margin: 0 -4px;
        padding: 4px;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .catalog-featured-collections__track-wrap::-webkit-scrollbar {
        display: none;
    }
    .catalog-featured-collections__track {
        display: flex;
        gap: 12px;
        list-style: none;
        margin: 0;
        padding: 0;
        width: max-content;
    }
    .catalog-featured-collection {
        display: block;
        position: relative;
        flex: 0 0 148px;
        width: 148px;
        aspect-ratio: 3 / 4;
        overflow: hidden;
        border-radius: 14px;
        text-decoration: none;
        color: #fff;
        box-shadow: 0 4px 14px rgba(0, 83, 102, 0.12);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    @media (min-width: 640px) {
        .catalog-featured-collection {
            flex: 0 0 168px;
            width: 168px;
        }
    }
    .catalog-featured-collection:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0, 83, 102, 0.18);
    }
    .catalog-featured-collection__media {
        position: absolute;
        inset: 0;
        background: linear-gradient(145deg, #005366 0%, #003d4d 100%);
    }
    .catalog-featured-collection__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .catalog-featured-collection:hover .catalog-featured-collection__media img {
        transform: scale(1.08);
    }
    .catalog-featured-collection__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0) 35%, rgba(0, 30, 38, 0.85) 100%);
        pointer-events: none;
    }
    .catalog-featured-collection__content {
        position: absolute;
        inset: auto 0 0;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 12px;
    }
    .catalog-featured-collection__badge {
        align-self: flex-start;
        padding: 3px 8px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.16);
        font-size: 0.625rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #fff;
    }
    .catalog-featured-collection__name {
        margin: 0;
        font-size: 0.8125rem;
        font-weight: 700;
        line-height: 1.3;
        color: #fff;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .catalog-featured-collection__cta {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 0.625rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.85);
    }
    .catalog-featured-collection__cta svg {
        width: 11px;
        height: 11px;
        transition: transform 0.2s ease;
    }
    .catalog-featured-collection:hover .catalog-featured-collection__cta svg {
        transform: translateX(2px);
    }
    .catalog-collection-show-hero {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        margin: 32px 0 24px;
        align-items: center;
    }
    @media (min-width: 768px) {
        .catalog-collection-show-hero {
            grid-template-columns: minmax(0, 1fr) minmax(260px, 360px);
            gap: 32px;
            margin-top: 40px;
            margin-bottom: 28px;
        }
        .catalog-collection-show-hero__media {
            order: 2;
        }
        .catalog-collection-show-hero__content {
            order: 1;
        }
    }
    .catalog-collection-show-hero__content {
        min-width: 0;
        text-align: left;
    }
    .catalog-collection-show-hero__content .catalog-intro {
        margin-left: 0;
        max-width: none;
        text-align: left;
    }
    .catalog-collection-show-hero__content .catalog-intro__text {
        font-size: 1rem;
        font-style: normal;
    }
    @media (min-width: 768px) {
        .catalog-collection-show-hero__content .catalog-intro__text {
            font-size: 1.0625rem;
        }
    }
    .catalog-collection-show-hero__eyebrow {
        margin: 0 0 8px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #005366;
    }
    .catalog-collection-show-hero__title {
        margin: 0;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(1.75rem, 4vw, 2.5rem);
        font-weight: 700;
        line-height: 1.15;
        color: #111827;
    }
    .catalog-collection-show-hero__sub {
        margin: 12px 0 0;
        font-size: 1rem;
        line-height: 1.55;
        color: #4b5563;
    }
    .catalog-collection-show-hero__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-top: 16px;
    }
    .catalog-collection-show-hero__chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 9999px;
        background: #f3f4f6;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
    }
    .catalog-collection-show-hero__chip svg {
        width: 15px;
        height: 15px;
        color: #005366;
        flex-shrink: 0;
    }
    .catalog-collection-show-hero__chip--link {
        color: #005366;
        text-decoration: none;
        transition: background 0.2s ease;
    }
    .catalog-collection-show-hero__chip--link:hover {
        background: rgba(0, 83, 102, 0.08);
    }
    .catalog-collection-show-hero__chip--featured {
        background: rgba(226, 21, 12, 0.1);
        color: #e2150c;
    }
    .catalog-collection-show-hero__chip--soon {
        background: rgba(0, 83, 102, 0.08);
        color: #005366;
    }
    .catalog-collection-show-hero__media {
        aspect-ratio: 4 / 5;
        border-radius: 16px;
        overflow: hidden;
        background: linear-gradient(135deg, #005366 0%, #003d4d 100%);
        box-shadow: 0 8px 24px rgba(0, 83, 102, 0.12);
    }
    @media (min-width: 768px) {
        .catalog-collection-show-hero__media {
            border-radius: 18px;
        }
    }
    .catalog-collection-show-hero__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Shop profile */
    .catalog-page--shop {
        padding-bottom: 48px;
    }
    @media (max-width: 767px) {
        .catalog-page--shop {
            padding-bottom: calc(48px + env(safe-area-inset-bottom, 0px));
        }
        body.shop-sticky-cta-open .catalog-page--shop {
            padding-bottom: calc(120px + env(safe-area-inset-bottom, 0px));
        }
        body:has(.catalog-page--shop) .gen-ai-fab {
            position: fixed !important;
            bottom: calc(1rem + env(safe-area-inset-bottom, 0px));
            right: 1rem;
            z-index: 50;
        }
        body.shop-sticky-cta-open:has(.catalog-page--shop) .gen-ai-fab {
            bottom: calc(5.25rem + env(safe-area-inset-bottom, 0px));
        }
        body:has(.catalog-page--shop) .site-back-to-top {
            bottom: calc(5.5rem + env(safe-area-inset-bottom, 0px));
            z-index: 50;
        }
        body.shop-sticky-cta-open:has(.catalog-page--shop) .site-back-to-top {
            bottom: calc(9.75rem + env(safe-area-inset-bottom, 0px));
        }
    }
    .catalog-page--shop .catalog-breadcrumb {
        padding: 12px 0 4px;
    }
    .catalog-shop-profile {
        margin: 12px 0 24px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    }
    @media (min-width: 768px) {
        .catalog-shop-profile {
            margin-top: 16px;
            margin-bottom: 28px;
            border-radius: 20px;
        }
    }
    .catalog-shop-profile__grid {
        display: grid;
        grid-template-columns: 1fr;
    }
    @media (min-width: 768px) {
        .catalog-shop-profile__grid {
            grid-template-columns: minmax(0, 1.05fr) minmax(260px, 0.95fr);
            align-items: stretch;
        }
        .catalog-shop-panel {
            order: 1;
        }
        .catalog-shop-showcase {
            order: 2;
        }
    }
    .catalog-shop-panel {
        position: relative;
        padding: 20px;
    }
    @media (min-width: 768px) {
        .catalog-shop-panel {
            padding: 24px;
        }
    }
    .catalog-shop-panel__head {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    @media (max-width: 639px) {
        .catalog-shop-panel__head {
            flex-direction: column;
            gap: 12px;
        }
    }
    .catalog-shop-panel__intro .catalog-intro {
        margin: 8px 0 0;
        max-width: none;
    }
    .catalog-shop-panel__meta-scroll {
        margin-top: 16px;
    }
    .catalog-shop-panel__actions {
        margin-top: 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .catalog-shop-panel__cta-main {
        width: 100%;
        justify-content: center;
    }
    .catalog-shop-panel__actions-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 44px;
        gap: 8px;
        align-items: stretch;
    }
    .catalog-shop-panel__action-btn {
        min-height: 44px;
        padding: 10px 12px;
        font-size: 0.875rem;
    }
    .catalog-shop-panel__share-btn {
        width: 44px;
        height: 44px;
        min-height: 44px;
        padding: 0;
    }
    @media (min-width: 768px) {
        .catalog-shop-panel__actions {
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
        }
        .catalog-shop-panel__cta-main {
            width: auto;
            order: -1;
        }
        .catalog-shop-panel__actions-row {
            display: contents;
        }
        .catalog-shop-panel__action-btn {
            min-height: 48px;
            padding: 12px 24px;
            font-size: inherit;
        }
        .catalog-shop-panel__share-btn {
            width: 48px;
            height: 48px;
            min-height: 48px;
        }
    }

    /* Creative cover showcase — right column */
    .catalog-shop-showcase {
        position: relative;
        padding: 16px 16px 20px;
        background: linear-gradient(165deg, rgba(0, 83, 102, 0.04) 0%, #fff 40%, rgba(242, 101, 34, 0.05) 100%);
    }
    @media (min-width: 768px) {
        .catalog-shop-showcase {
            padding: 24px 24px 24px 12px;
            border-left: 1px solid rgba(0, 83, 102, 0.08);
        }
    }
    .catalog-shop-showcase__frame {
        position: relative;
        height: 100%;
        min-height: 220px;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(0, 83, 102, 0.12);
        box-shadow:
            0 12px 32px rgba(0, 83, 102, 0.14),
            inset 0 1px 0 rgba(255, 255, 255, 0.35);
        background: linear-gradient(145deg, #005366 0%, #003d4d 100%);
    }
    @media (min-width: 768px) {
        .catalog-shop-showcase__frame {
            min-height: 100%;
            border-radius: 20px;
        }
    }
    .catalog-shop-showcase__photo {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center center;
        transform: scale(1.02);
        transition: transform 0.6s ease;
    }
    .catalog-shop-showcase__frame:hover .catalog-shop-showcase__photo {
        transform: scale(1.06);
    }
    .catalog-shop-showcase__fallback {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.16) 0%, transparent 40%),
            radial-gradient(circle at 80% 20%, rgba(242, 101, 34, 0.28) 0%, transparent 38%),
            radial-gradient(circle at 60% 85%, rgba(226, 21, 12, 0.18) 0%, transparent 42%),
            linear-gradient(145deg, #005366 0%, #003d4d 100%);
    }
    .catalog-shop-showcase__mesh {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(135deg, rgba(0, 83, 102, 0.72) 0%, rgba(0, 61, 77, 0.45) 45%, rgba(242, 101, 34, 0.32) 100%);
        mix-blend-mode: multiply;
        pointer-events: none;
    }
    .catalog-shop-showcase__grid-pattern {
        position: absolute;
        inset: 0;
        opacity: 0.22;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.14) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.14) 1px, transparent 1px);
        background-size: 24px 24px;
        mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.35) 0%, rgba(0, 0, 0, 0.85) 100%);
        pointer-events: none;
    }
    .catalog-shop-showcase__orb {
        position: absolute;
        border-radius: 9999px;
        filter: blur(0);
        pointer-events: none;
    }
    .catalog-shop-showcase__orb--1 {
        top: 12%;
        right: 10%;
        width: 72px;
        height: 72px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.22);
    }
    .catalog-shop-showcase__orb--2 {
        bottom: 28%;
        left: 8%;
        width: 48px;
        height: 48px;
        background: rgba(242, 101, 34, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    .catalog-shop-showcase__monogram {
        position: absolute;
        right: -8px;
        bottom: -16px;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(5rem, 14vw, 8rem);
        font-weight: 700;
        line-height: 1;
        color: rgba(255, 255, 255, 0.1);
        letter-spacing: -0.04em;
        pointer-events: none;
        user-select: none;
    }
    .catalog-shop-showcase__footer {
        position: absolute;
        left: 16px;
        right: 16px;
        bottom: 16px;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .catalog-shop-showcase__label {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.88);
    }
    .catalog-shop-showcase__pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.92);
        font-size: 0.75rem;
        font-weight: 700;
        color: #166534;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }
    .catalog-shop-showcase__pill svg {
        width: 14px;
        height: 14px;
        color: #16a34a;
    }
    .catalog-shop-showcase__pill--new {
        color: #005366;
    }
    .catalog-shop-showcase__pill--new svg {
        color: #005366;
    }
    @media (max-width: 767px) {
        .catalog-shop-profile {
            border-radius: 16px;
        }
        .catalog-shop-profile__grid {
            display: flex;
            flex-direction: column;
        }
        .catalog-shop-showcase {
            order: -1;
            padding: 0;
            background: transparent;
        }
        .catalog-shop-showcase__frame {
            min-height: 168px;
            border-radius: 0;
            border: none;
            box-shadow: none;
        }
        .catalog-shop-showcase__footer {
            display: none;
        }
        .catalog-shop-showcase__monogram,
        .catalog-shop-showcase__orb {
            display: none;
        }
        .catalog-shop-panel {
            position: relative;
            padding: 0 20px 20px;
            margin-top: 0;
        }
        .catalog-shop-panel__head {
            position: relative;
            flex-direction: column;
            align-items: flex-start;
            gap: 0;
            padding-top: 52px;
        }
        .catalog-shop-hero__logo {
            position: absolute;
            top: -44px;
            left: 0;
            width: 88px;
            height: 88px;
            border-radius: 9999px;
            border: 4px solid #fff;
            box-shadow: 0 8px 24px rgba(0, 83, 102, 0.2);
            z-index: 3;
        }
        .catalog-shop-panel__intro {
            width: 100%;
            padding-top: 4px;
        }
        .catalog-shop-panel__intro .catalog-collection-show-hero__eyebrow {
            margin: 0 0 10px;
        }
        .catalog-shop-panel__intro .catalog-collection-show-hero__title {
            margin-top: 2px;
        }
        .catalog-shop-hero__tagline {
            margin-top: 12px;
            line-height: 1.6;
        }
        .catalog-shop-panel__meta-scroll {
            margin: 16px -20px 0;
            padding: 0 20px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .catalog-shop-panel__meta-scroll::-webkit-scrollbar {
            display: none;
        }
        .catalog-shop-panel__meta {
            flex-wrap: nowrap;
            width: max-content;
            gap: 8px;
            margin-top: 0;
        }
        .catalog-shop-panel__meta .catalog-collection-show-hero__chip {
            flex-shrink: 0;
        }
    }
    .catalog-shop-hero__tagline {
        margin: 8px 0 0;
        font-size: 0.9375rem;
        line-height: 1.5;
        color: #4b5563;
        max-width: 42rem;
    }
    .catalog-shop-hero__logo {
        flex-shrink: 0;
        width: 88px;
        height: 88px;
        overflow: hidden;
        border-radius: 16px;
        border: 3px solid rgba(0, 83, 102, 0.08);
        background: #005366;
        color: #fff;
        font-size: 1.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(0, 83, 102, 0.18);
    }
    @media (min-width: 768px) {
        .catalog-shop-panel__head {
            flex-direction: row;
            align-items: flex-start;
            gap: 16px;
            padding-top: 0;
        }
        .catalog-shop-hero__logo {
            position: static;
            width: 104px;
            height: 104px;
            border-radius: 20px;
            border: 3px solid rgba(0, 83, 102, 0.08);
        }
        .catalog-shop-panel__meta-scroll {
            margin: 16px 0 0;
            padding: 0;
            overflow: visible;
        }
        .catalog-shop-panel__meta {
            flex-wrap: wrap;
            width: auto;
        }
    }
    .catalog-shop-hero__logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .catalog-shop-hero__actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }
    .catalog-shop-hero__actions .btn-cta,
    .catalog-shop-hero__actions .btn-outline-petrol {
        min-height: 48px;
        padding: 12px 24px;
    }
    .catalog-shop-hero__cta-primary {
        order: -1;
    }
    .catalog-shop-hero__share,
    .catalog-shop-panel__share-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        border-radius: 9999px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #4b5563;
        cursor: pointer;
        transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease;
    }
    .catalog-shop-hero__share svg,
    .catalog-shop-panel__share-btn svg {
        width: 18px;
        height: 18px;
    }
    .catalog-shop-hero__share:hover,
    .catalog-shop-panel__share-btn:hover {
        border-color: rgba(0, 83, 102, 0.25);
        background: rgba(0, 83, 102, 0.06);
        color: #005366;
    }
    .catalog-shop-hero__chip--verified {
        background: #f0fdf4;
        color: #166534;
    }
    .catalog-shop-hero__chip--verified svg {
        color: #16a34a;
    }
    .catalog-shop-hero__chip--new {
        background: rgba(0, 83, 102, 0.08);
        color: #005366;
    }
    .catalog-shop-hero__chip--new svg {
        color: #005366;
    }
    .catalog-shop-hero__chip--updating {
        background: #fffbeb;
        color: #d97706;
    }
    .catalog-shop-hero__chip--updating svg {
        color: #d97706;
    }
    .catalog-shop-hero__chip-muted {
        font-weight: 500;
        color: #6b7280;
    }

    /* Shop by type — horizontal accent cards */
    .shop-type-rail {
        margin: 0 0 24px;
        padding: 20px 16px;
        background: linear-gradient(165deg, rgba(0, 83, 102, 0.05) 0%, #fff 50%, rgba(242, 101, 34, 0.04) 100%);
        border: 1px solid rgba(0, 83, 102, 0.1);
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    }
    @media (min-width: 768px) {
        .shop-type-rail {
            margin-bottom: 28px;
            padding: 24px 20px;
            border-radius: 20px;
        }
    }
    .shop-type-rail__head {
        margin-bottom: 16px;
    }
    .shop-type-rail__scroll {
        margin: 0;
    }
    @media (max-width: 639px) {
        .shop-type-rail {
            padding-right: 0;
        }
        .shop-type-rail__scroll {
            overflow-x: auto;
            margin: 0 -16px;
            padding: 4px 16px 8px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .shop-type-rail__scroll::-webkit-scrollbar {
            display: none;
        }
        .shop-type-rail__grid {
            display: flex;
            flex-direction: row;
            gap: 12px;
            width: max-content;
            min-width: 100%;
        }
        .shop-type-rail__grid > li {
            flex: 0 0 min(280px, 78vw);
        }
        .shop-type-card {
            min-height: 72px;
        }
    }
    .shop-type-rail__grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    @media (min-width: 640px) {
        .shop-type-rail__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
    }
    @media (min-width: 1024px) {
        .shop-type-rail__grid:not(.shop-type-rail__grid--compact) {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    .shop-type-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 76px;
        padding: 12px 14px 12px 12px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #fff;
        text-decoration: none;
        color: inherit;
        overflow: hidden;
        transition: transform 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
    }
    .shop-type-card:hover {
        transform: translateY(-2px);
        border-color: rgba(0, 83, 102, 0.22);
        box-shadow: 0 8px 24px rgba(0, 83, 102, 0.1);
    }
    .shop-type-card.is-active {
        border-color: #005366;
        box-shadow: 0 0 0 1px rgba(0, 83, 102, 0.12), 0 8px 20px rgba(0, 83, 102, 0.12);
    }
    .shop-type-card__glow {
        position: absolute;
        top: -24px;
        right: -24px;
        width: 88px;
        height: 88px;
        border-radius: 9999px;
        opacity: 0.18;
        pointer-events: none;
        transition: opacity 0.22s ease, transform 0.22s ease;
    }
    .shop-type-card--accent-0 .shop-type-card__glow { background: #005366; }
    .shop-type-card--accent-1 .shop-type-card__glow { background: #f26522; }
    .shop-type-card--accent-2 .shop-type-card__glow { background: #e2150c; }
    .shop-type-card--accent-3 .shop-type-card__glow { background: #2b7bc0; }
    .shop-type-card:hover .shop-type-card__glow {
        opacity: 0.28;
        transform: scale(1.08);
    }
    .shop-type-card__icon {
        position: relative;
        z-index: 1;
        flex-shrink: 0;
        width: 52px;
        height: 52px;
        border-radius: 14px;
        overflow: hidden;
        background: linear-gradient(145deg, rgba(0, 83, 102, 0.12) 0%, rgba(0, 83, 102, 0.04) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .shop-type-card__icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .shop-type-card__initial {
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: #005366;
    }
    .shop-type-card__body {
        position: relative;
        z-index: 1;
        min-width: 0;
        flex: 1;
    }
    .shop-type-card__label {
        display: block;
        font-size: 0.9375rem;
        font-weight: 700;
        line-height: 1.35;
        color: #111827;
    }
    .shop-type-card__count {
        display: block;
        margin-top: 2px;
        font-size: 0.8125rem;
        line-height: 1.4;
        color: #6b7280;
    }
    .shop-type-card__arrow {
        position: relative;
        z-index: 1;
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: 9999px;
        background: rgba(0, 83, 102, 0.08);
        color: #005366;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease, transform 0.2s ease;
    }
    .shop-type-card__arrow svg {
        width: 16px;
        height: 16px;
    }
    .shop-type-card:hover .shop-type-card__arrow {
        background: rgba(0, 83, 102, 0.14);
        transform: translateX(2px);
    }

    /* Shop page — sticky mobile CTA */
    .shop-sticky-cta {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 45;
        padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px));
        background: rgba(255, 255, 255, 0.96);
        border-top: 1px solid #e5e7eb;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
        backdrop-filter: blur(8px);
        transform: translateY(110%);
        transition: transform 0.25s ease;
        pointer-events: none;
    }
    .shop-sticky-cta.is-visible {
        transform: translateY(0);
        pointer-events: auto;
    }
    .shop-sticky-cta__inner {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr);
        gap: 8px;
        max-width: 640px;
        margin: 0 auto;
    }
    .shop-sticky-cta__primary,
    .shop-sticky-cta__secondary {
        min-height: 44px;
        width: 100%;
        justify-content: center;
        padding: 10px 16px;
        font-size: 0.875rem;
    }
    @media (min-width: 768px) {
        .shop-sticky-cta {
            display: none;
        }
    }

    /* Search results */
    .catalog-page--search .catalog-empty {
        margin-top: 32px;
    }
    .catalog-search-section__head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }
    .catalog-search-section__title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.3;
        color: #111827;
    }
    .catalog-search-section__link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
        font-size: 0.875rem;
        font-weight: 600;
        color: #4b5563;
        text-decoration: none;
        transition: color 0.2s ease, transform 0.2s ease;
    }
    .catalog-search-section__link:hover {
        color: #005366;
        transform: translateX(2px);
    }
    .catalog-shops-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    @media (min-width: 640px) {
        .catalog-shops-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .catalog-shops-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    .catalog-shop-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .catalog-shop-card:hover {
        border-color: rgba(0, 83, 102, 0.25);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
    }
    .catalog-shop-card__logo {
        flex-shrink: 0;
        width: 64px;
        height: 64px;
        overflow: hidden;
        border-radius: 12px;
        background: #005366;
        color: #fff;
        font-size: 1.25rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .catalog-shop-card__logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .catalog-shop-card__body {
        min-width: 0;
        flex: 1;
    }
    .catalog-shop-card__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
        color: #111827;
    }
    .catalog-shop-card:hover .catalog-shop-card__title {
        color: #005366;
    }
    .catalog-shop-card__desc {
        margin: 4px 0 0;
        font-size: 0.8125rem;
        line-height: 1.4;
        color: #6b7280;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .catalog-shop-card__meta {
        margin: 8px 0 0;
        font-size: 0.75rem;
        font-weight: 600;
        color: #005366;
    }
    .catalog-empty__actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 12px;
    }
    .catalog-modal {
        position: fixed;
        inset: 0;
        z-index: 50;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background: rgba(17, 24, 39, 0.5);
    }
    .catalog-modal.hidden {
        display: none;
    }
    .catalog-modal__panel {
        width: 100%;
        max-width: 28rem;
        padding: 24px;
        background: #fff;
        border: 1px solid #f3f4f6;
        border-radius: 16px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.18);
    }
    .catalog-modal__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }
    .catalog-modal__title {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 700;
        color: #111827;
    }
    .catalog-modal__close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        padding: 0;
        border: 0;
        background: transparent;
        color: #6b7280;
        border-radius: 8px;
        cursor: pointer;
    }
    .catalog-modal__close:hover {
        color: #111827;
        background: #f3f4f6;
    }
    .catalog-modal__label {
        display: block;
        margin: 0 0 8px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
    }
    .catalog-modal__input,
    .catalog-modal__textarea {
        width: 100%;
        min-height: 48px;
        margin-bottom: 16px;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        color: #111827;
        background: #fff;
    }
    .catalog-modal__textarea {
        min-height: 120px;
        resize: vertical;
    }
    .catalog-modal__input:focus,
    .catalog-modal__textarea:focus {
        outline: none;
        border-color: #005366;
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.12);
    }
    .catalog-modal__actions {
        display: flex;
        gap: 12px;
    }
    .catalog-modal__actions .btn-cta,
    .catalog-modal__actions .btn-outline-petrol {
        flex: 1;
        min-height: 48px;
        justify-content: center;
    }
    .catalog-page--blog .catalog-blog-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        padding: 24px 0 0;
    }
    @media (min-width: 1024px) {
        .catalog-page--blog .catalog-blog-layout {
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 32px;
        }
    }
    .catalog-page--blog .catalog-toolbar {
        margin-top: 0;
    }
    .catalog-page--blog .catalog-recently-viewed {
        margin-top: 32px;
        padding-top: 32px;
        border-top: 1px solid #e5e7eb;
    }
    .catalog-blog-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    @media (min-width: 640px) {
        .catalog-blog-grid:not(.catalog-blog-grid--list) {
            gap: 20px;
        }
    }
    .catalog-blog-grid--home {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    @media (min-width: 768px) {
        .catalog-blog-grid--home {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }
    }
    .catalog-blog-grid--list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .catalog-blog-grid__item {
        min-width: 0;
    }
    .catalog-blog-grid:not(.catalog-blog-grid--list) .catalog-blog-grid__list {
        display: none;
    }
    .catalog-blog-grid--list .catalog-blog-grid__grid {
        display: none;
    }
    .catalog-page--blog .blog-card {
        min-width: 0;
        height: 100%;
    }
    .catalog-page--blog .blog-card__link {
        display: block;
        height: 100%;
        text-decoration: none;
        color: inherit;
    }
    .catalog-page--blog .blog-card__media {
        position: relative;
        aspect-ratio: 16 / 10;
        border-radius: 14px;
        overflow: hidden;
        background: linear-gradient(135deg, #f7f7f7 0%, #e5e7eb 100%);
        margin-bottom: 12px;
        border: 1px solid #e5e7eb;
    }
    .catalog-page--blog .blog-card__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.35s ease;
    }
    .catalog-page--blog .blog-card__link:hover .blog-card__media img {
        transform: scale(1.04);
    }
    .catalog-page--blog .blog-card__placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .catalog-page--blog .blog-card__category {
        position: absolute;
        bottom: 8px;
        left: 8px;
        padding: 3px 8px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.92);
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #005366;
        line-height: 1.3;
    }
    .catalog-page--blog .blog-card__body {
        padding: 0 2px 4px;
    }
    .catalog-page--blog .blog-card__meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px 6px;
        margin: 0 0 6px;
        font-size: 0.75rem;
        line-height: 1.3;
        color: #9ca3af;
    }
    .catalog-page--blog .blog-card__meta-sep {
        color: #d1d5db;
    }
    .catalog-page--blog .blog-card__title {
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
        color: #111827;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.2s ease;
    }
    .catalog-page--blog .blog-card__link:hover .blog-card__title {
        color: #005366;
    }
    .catalog-blog-post {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    @media (min-width: 640px) {
        .catalog-blog-post {
            grid-template-columns: 220px minmax(0, 1fr);
            gap: 0;
        }
        .catalog-blog-post:not(:has(.catalog-blog-post__media)) {
            grid-template-columns: 1fr;
        }
    }
    @media (min-width: 768px) {
        .catalog-blog-post {
            grid-template-columns: 260px minmax(0, 1fr);
        }
    }
    .catalog-blog-post:hover {
        border-color: rgba(0, 83, 102, 0.2);
        box-shadow: 0 8px 24px rgba(0, 83, 102, 0.08);
    }
    .catalog-blog-post--pinned {
        border-color: rgba(242, 101, 34, 0.25);
    }
    .catalog-blog-post__media {
        display: block;
        aspect-ratio: 16 / 10;
        overflow: hidden;
        background: linear-gradient(135deg, #f7f7f7 0%, #e5e7eb 100%);
    }
    @media (min-width: 640px) {
        .catalog-blog-post__media {
            aspect-ratio: auto;
            min-height: 100%;
        }
    }
    .catalog-blog-post__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.35s ease;
    }
    .catalog-blog-post:hover .catalog-blog-post__media img {
        transform: scale(1.04);
    }
    .catalog-blog-post__body {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 16px;
    }
    @media (min-width: 768px) {
        .catalog-blog-post__body {
            padding: 20px;
        }
    }
    .catalog-blog-post__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        line-height: 1.4;
        color: #6b7280;
    }
    .catalog-blog-post__meta-sep {
        color: #d1d5db;
    }
    .catalog-blog-post__pin {
        padding: 2px 8px;
        border-radius: 9999px;
        background: rgba(242, 101, 34, 0.12);
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #f26522;
    }
    .catalog-blog-post__category {
        font-weight: 600;
        color: #005366;
        text-decoration: none;
    }
    .catalog-blog-post__category:hover {
        color: #f26522;
    }
    .catalog-blog-post__title {
        margin: 0;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.3;
    }
    @media (min-width: 768px) {
        .catalog-blog-post__title {
            font-size: 1.375rem;
        }
    }
    .catalog-blog-post__title a {
        color: #111827;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .catalog-blog-post__title a:hover {
        color: #005366;
    }
    .catalog-blog-post__excerpt {
        margin: 0;
        font-size: 0.9375rem;
        line-height: 1.55;
        color: #4b5563;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .catalog-blog-post__foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: auto;
        padding-top: 4px;
    }
    .catalog-blog-post__shop {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #005366;
        text-decoration: none;
    }
    .catalog-blog-post__shop:hover {
        text-decoration: underline;
    }
    .catalog-blog-post__cta {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-left: auto;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #005366;
        text-decoration: none;
    }
    .catalog-blog-post__cta svg {
        width: 14px;
        height: 14px;
        transition: transform 0.2s ease;
    }
    .catalog-blog-post__cta:hover {
        color: #f26522;
    }
    .catalog-blog-post__cta:hover svg {
        transform: translateX(2px);
    }
    .catalog-blog-sidebar {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    @media (min-width: 1024px) {
        .catalog-blog-sidebar {
            position: sticky;
            top: 88px;
            align-self: start;
        }
    }
    .catalog-blog-widget {
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
    }
    @media (min-width: 768px) {
        .catalog-blog-widget {
            padding: 20px;
            border-radius: 18px;
        }
    }
    .catalog-blog-widget__eyebrow {
        margin: 0 0 4px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #005366;
    }
    .catalog-blog-widget__title {
        margin: 0 0 14px;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: 1.125rem;
        font-weight: 700;
        line-height: 1.25;
        color: #111827;
    }
    .catalog-blog-widget__list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .catalog-blog-widget__link {
        display: block;
        text-decoration: none;
        color: inherit;
    }
    .catalog-blog-widget__link-title {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.4;
        color: #111827;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.2s ease;
    }
    .catalog-blog-widget__link:hover .catalog-blog-widget__link-title {
        color: #005366;
    }
    .catalog-blog-widget__link-meta {
        display: block;
        margin-top: 4px;
        font-size: 0.75rem;
        color: #9ca3af;
    }
    .catalog-blog-widget__chips {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .catalog-blog-widget__chip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 10px;
        background: #f9fafb;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        text-decoration: none;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .catalog-blog-widget__chip span {
        font-size: 0.75rem;
        color: #9ca3af;
    }
    .catalog-blog-widget__chip:hover,
    .catalog-blog-widget__chip.is-active {
        background: rgba(0, 83, 102, 0.08);
        color: #005366;
    }
    .catalog-blog-widget__tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .catalog-blog-widget__tag {
        display: inline-flex;
        padding: 6px 12px;
        border-radius: 9999px;
        border: 1px solid #e5e7eb;
        background: #fff;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #4b5563;
        text-decoration: none;
        transition: border-color 0.2s ease, background 0.2s ease, color 0.2s ease;
    }
    .catalog-blog-widget__tag:hover {
        border-color: rgba(0, 83, 102, 0.25);
        background: rgba(0, 83, 102, 0.06);
        color: #005366;
    }
    .catalog-page--blog-show {
        padding-bottom: 48px;
    }
    .catalog-page--blog-tag > .max-w-7xl {
        padding-top: 24px;
    }
    .catalog-page--blog-tag .catalog-recently-viewed {
        margin-top: 32px;
        padding-top: 32px;
        border-top: 1px solid #e5e7eb;
    }
    .catalog-blog-show-hero {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        margin: 8px 0 24px;
        padding: 24px;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
    }
    @media (min-width: 768px) {
        .catalog-blog-show-hero {
            padding: 32px;
            border-radius: 18px;
            margin-bottom: 32px;
        }
    }
    @media (min-width: 1024px) {
        .catalog-blog-show-hero--has-media {
            grid-template-columns: minmax(0, 1fr) minmax(280px, 420px);
            align-items: center;
            gap: 32px;
        }
    }
    .catalog-blog-show-hero__content {
        min-width: 0;
    }
    .catalog-blog-show-hero__category {
        display: inline-flex;
        margin-bottom: 12px;
        padding: 4px 12px;
        border-radius: 9999px;
        background: rgba(0, 83, 102, 0.08);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #005366;
        text-decoration: none;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .catalog-blog-show-hero__category:hover {
        background: rgba(0, 83, 102, 0.14);
        color: #003d4d;
    }
    .catalog-blog-show-hero__title {
        margin: 0;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: clamp(1.75rem, 4vw, 2.25rem);
        font-weight: 700;
        line-height: 1.2;
        color: #111827;
    }
    .catalog-blog-show-hero__excerpt {
        margin: 12px 0 0;
        font-size: 1rem;
        line-height: 1.55;
        color: #4b5563;
    }
    .catalog-blog-show-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 16px;
    }
    .catalog-blog-show-hero__media {
        overflow: hidden;
        border-radius: 14px;
        aspect-ratio: 16 / 10;
        background: linear-gradient(135deg, #f7f7f7 0%, #e5e7eb 100%);
        border: 1px solid #e5e7eb;
    }
    @media (min-width: 1024px) {
        .catalog-blog-show-hero__media {
            aspect-ratio: 4 / 5;
            min-height: 320px;
        }
    }
    .catalog-blog-show-hero__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .catalog-blog-article {
        max-width: 800px;
        margin: 0 auto;
        padding: 24px;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
    }
    @media (min-width: 768px) {
        .catalog-blog-article {
            padding: 32px;
            border-radius: 18px;
        }
    }
    .catalog-blog-article__author {
        display: flex;
        align-items: center;
        gap: 16px;
        padding-bottom: 24px;
        margin-bottom: 24px;
        border-bottom: 1px solid #e5e7eb;
    }
    .catalog-blog-article__author-avatar {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        overflow: hidden;
        border-radius: 9999px;
        background: #005366;
        color: #fff;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .catalog-blog-article__author-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .catalog-blog-article__author-body {
        flex: 1;
        min-width: 0;
    }
    .catalog-blog-article__author-name {
        display: block;
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        text-decoration: none;
    }
    .catalog-blog-article__author-name:hover {
        color: #005366;
    }
    .catalog-blog-article__author-meta {
        margin: 4px 0 0;
        font-size: 0.8125rem;
        color: #9ca3af;
    }
    .catalog-blog-article__author-cta {
        display: none;
        flex-shrink: 0;
        font-size: 0.875rem;
        padding: 8px 16px;
    }
    @media (min-width: 640px) {
        .catalog-blog-article__author-cta {
            display: inline-flex;
        }
    }
    .catalog-blog-article__content {
        font-size: 1rem;
        line-height: 1.65;
        color: #4b5563;
    }
    .catalog-blog-article__content > :first-child {
        margin-top: 0;
    }
    .catalog-blog-article__content > :last-child {
        margin-bottom: 0;
    }
    .catalog-blog-article__content h2,
    .catalog-blog-article__content h3,
    .catalog-blog-article__content h4 {
        margin: 1.5em 0 0.5em;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-weight: 700;
        line-height: 1.3;
        color: #111827;
    }
    .catalog-blog-article__content h2 { font-size: 1.5rem; }
    .catalog-blog-article__content h3 { font-size: 1.25rem; }
    .catalog-blog-article__content h4 { font-size: 1.125rem; }
    .catalog-blog-article__content p {
        margin: 0 0 1em;
    }
    .catalog-blog-article__content a {
        color: #005366;
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    .catalog-blog-article__content a:hover {
        color: #f26522;
    }
    .catalog-blog-article__content img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin: 1.5em 0;
    }
    .catalog-blog-article__content ul,
    .catalog-blog-article__content ol {
        margin: 0 0 1em;
        padding-left: 1.5em;
    }
    .catalog-blog-article__content blockquote {
        margin: 1.5em 0;
        padding: 16px 20px;
        border-left: 4px solid #005366;
        border-radius: 0 12px 12px 0;
        background: #f9fafb;
        color: #374151;
        font-style: italic;
    }
    .catalog-blog-article__gallery {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }
    .catalog-blog-article__gallery-title {
        margin: 0 0 16px;
        font-family: Oswald, Figtree, ui-sans-serif, system-ui, sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
    }
    .catalog-blog-article__gallery-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    @media (min-width: 768px) {
        .catalog-blog-article__gallery-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }
    }
    .catalog-blog-article__gallery-item {
        margin: 0;
        aspect-ratio: 1;
        overflow: hidden;
        border-radius: 12px;
        background: #f7f7f7;
        border: 1px solid #e5e7eb;
    }
    .catalog-blog-article__gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        margin: 0;
        border-radius: 0;
    }
    .catalog-blog-article__tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }
    .catalog-blog-article__footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }
    .catalog-blog-article__stats {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
    }
    .catalog-blog-article__stat {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #6b7280;
    }
    .catalog-blog-article__stat svg {
        width: 18px;
        height: 18px;
    }
    .catalog-blog-article__share {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .catalog-blog-article__share-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #9ca3af;
    }
    .catalog-blog-article__share-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 9999px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #4b5563;
        text-decoration: none;
        transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease;
    }
    .catalog-blog-article__share-btn svg {
        width: 16px;
        height: 16px;
    }
    .catalog-blog-article__share-btn:hover {
        border-color: rgba(0, 83, 102, 0.25);
        background: rgba(0, 83, 102, 0.06);
        color: #005366;
    }
    .catalog-blog-related {
        margin-top: 48px;
    }
    .catalog-blog-related__head {
        margin-bottom: 24px;
    }
    .catalog-blog-related__grid {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }
    @media (min-width: 640px) {
        .catalog-blog-related__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .catalog-blog-related__grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 24px;
        }
    }
    .catalog-page--blog-show .catalog-blog-related .blog-card__media {
        border-radius: 14px;
    }
    .catalog-blog-show-back {
        display: flex;
        justify-content: center;
        margin-top: 32px;
    }
    .catalog-page--blog-show .catalog-recently-viewed {
        margin-top: 48px;
        padding-top: 32px;
        border-top: 1px solid #e5e7eb;
    }
    .catalog-collection-banner {
        margin: 0 0 20px;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        background: #fff;
    }
    @media (min-width: 768px) {
        .catalog-collection-banner {
            margin-bottom: 24px;
            border-radius: 18px;
        }
    }
    .catalog-collection-banner__media {
        position: relative;
        aspect-ratio: 21 / 9;
        max-height: 280px;
        overflow: hidden;
        background: linear-gradient(135deg, #005366 0%, #003d4d 100%);
    }
    .catalog-collection-banner__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .catalog-collection-banner__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0) 40%, rgba(0, 30, 38, 0.35) 100%);
        pointer-events: none;
    }
    .catalog-collection-banner__meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
    }
    @media (min-width: 768px) {
        .catalog-collection-banner__meta {
            padding: 14px 20px;
        }
    }
    .catalog-collection-banner__chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 9999px;
        background: #f3f4f6;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
    }
    .catalog-collection-banner__chip svg {
        width: 15px;
        height: 15px;
        color: #005366;
        flex-shrink: 0;
    }
    .catalog-collection-banner__chip--link {
        color: #005366;
        text-decoration: none;
        transition: background 0.2s ease;
    }
    .catalog-collection-banner__chip--link:hover {
        background: rgba(0, 83, 102, 0.08);
    }
    .catalog-collection-banner__chip--featured {
        background: rgba(226, 21, 12, 0.1);
        color: #e2150c;
    }
    .catalog-collection-banner__chip--soon {
        background: rgba(0, 83, 102, 0.08);
        color: #005366;
    }
    .catalog-toolbar__price {
        width: 88px;
        min-height: 40px;
        padding: 8px 10px;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #111827;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        outline: none;
    }
    .catalog-toolbar__price:focus {
        border-color: #005366;
        box-shadow: 0 0 0 2px rgba(0, 83, 102, 0.1);
    }
    .catalog-toolbar__price-sep {
        color: #9ca3af;
        font-size: 0.875rem;
        flex-shrink: 0;
    }
    .catalog-toolbar__price-group {
        display: none;
        align-items: center;
        gap: 6px;
    }
    @media (min-width: 768px) {
        .catalog-toolbar__price-group {
            display: flex;
        }
    }
    .catalog-pagination {
        margin-top: 28px;
        display: flex;
        justify-content: flex-end;
    }
    .catalog-paginator__list {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .catalog-paginator__item span,
    .catalog-paginator__item a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1;
        border-radius: 9999px;
        text-decoration: none;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #4b5563;
        transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }
    .catalog-paginator__item a:hover {
        color: #005366;
        border-color: #005366;
        background: rgba(0, 83, 102, 0.06);
    }
    .catalog-paginator__item--active span {
        color: #fff;
        background: #005366;
        border-color: #005366;
    }
    .catalog-paginator__item--dots span {
        border: none;
        background: transparent;
        color: #9ca3af;
        min-width: 28px;
        padding: 0 4px;
    }
    @media (max-width: 639px) {
        .catalog-pagination {
            justify-content: center;
        }
        .catalog-paginator__list {
            justify-content: center;
        }
        .catalog-paginator__item span,
        .catalog-paginator__item a {
            min-width: 34px;
            height: 34px;
            font-size: 0.8125rem;
        }
    }

    /* Site footer */
    .site-footer {
        background: linear-gradient(165deg, #003d4d 0%, #002a35 48%, #001f28 100%);
        color: rgba(255, 255, 255, 0.88);
    }
    .site-footer__newsletter {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .site-footer__newsletter-inner {
        max-width: 80rem;
        margin: 0 auto;
        padding: 32px 16px;
    }
    @media (min-width: 640px) {
        .site-footer__newsletter-inner {
            padding: 40px 24px;
        }
    }
    @media (min-width: 1024px) {
        .site-footer__newsletter-inner {
            padding: 48px 32px;
        }
    }
    .site-footer__newsletter-card {
        display: grid;
        gap: 24px;
        padding: 24px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.12);
    }
    @media (min-width: 768px) {
        .site-footer__newsletter-card {
            grid-template-columns: 1fr 1.1fr;
            align-items: center;
            gap: 32px;
            padding: 32px;
        }
    }
    .site-footer__newsletter-eyebrow {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #f26522;
        margin-bottom: 8px;
    }
    .site-footer__newsletter-title {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.25;
        color: #fff;
        margin: 0 0 8px;
    }
    .site-footer__newsletter-sub {
        font-size: 0.9375rem;
        line-height: 1.5;
        color: rgba(255, 255, 255, 0.72);
        margin: 0;
    }
    .site-footer__newsletter-form {
        display: flex;
        gap: 0;
        max-width: 100%;
    }
    .site-footer__newsletter-input {
        flex: 1;
        min-width: 0;
        padding: 12px 16px;
        font-size: 1rem;
        color: #111827;
        background: #fff;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-right: none;
        border-radius: 9999px 0 0 9999px;
        outline: none;
    }
    .site-footer__newsletter-input:focus {
        box-shadow: 0 0 0 2px rgba(226, 21, 12, 0.35);
    }
    .site-footer__newsletter-input::placeholder {
        color: #9ca3af;
    }
    .site-footer__newsletter-btn {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 20px;
        background: #e2150c;
        color: #fff;
        border: none;
        border-radius: 0 9999px 9999px 0;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .site-footer__newsletter-btn:hover {
        background: #c0120a;
    }
    .site-footer__newsletter-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    .site-footer__newsletter-note {
        font-size: 0.75rem;
        line-height: 1.45;
        color: rgba(255, 255, 255, 0.55);
        margin: 12px 0 0;
    }
    .site-footer__newsletter-note a {
        color: rgba(255, 255, 255, 0.85);
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    .site-footer__newsletter-note a:hover {
        color: #f26522;
    }
    .site-footer__main {
        max-width: 80rem;
        margin: 0 auto;
        padding: 32px 16px 24px;
    }
    @media (min-width: 640px) {
        .site-footer__main {
            padding: 40px 24px 32px;
        }
    }
    @media (min-width: 1024px) {
        .site-footer__main {
            padding: 48px 32px 32px;
        }
    }
    .site-footer__grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0;
    }
    @media (min-width: 768px) {
        .site-footer__grid {
            grid-template-columns: 1.4fr repeat(3, 1fr);
            gap: 32px;
        }
    }
    @media (min-width: 1024px) {
        .site-footer__grid {
            gap: 48px;
        }
    }
    .site-footer__brand {
        padding-bottom: 24px;
        margin-bottom: 4px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    @media (min-width: 768px) {
        .site-footer__brand {
            padding-bottom: 0;
            margin-bottom: 0;
            border-bottom: none;
        }
    }
    .site-footer__brand-logo {
        display: inline-flex;
        align-items: center;
        margin-bottom: 10px;
        text-decoration: none;
        color: inherit;
    }
    .site-footer__brand-logo img {
        width: auto;
        height: 72px;
        max-width: min(240px, 72vw);
        object-fit: contain;
        object-position: left center;
    }
    @media (min-width: 768px) {
        .site-footer__brand-logo img {
            height: 88px;
            max-width: 280px;
        }
    }
    .site-footer__brand-desc {
        font-size: 0.875rem;
        line-height: 1.55;
        color: rgba(255, 255, 255, 0.68);
        margin: 0 0 20px;
        max-width: 28rem;
    }
    .site-footer__social {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }
    .site-footer__social-link {
        width: 36px;
        height: 36px;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.06);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }
    .site-footer__social-link:hover {
        background: rgba(255, 255, 255, 0.14);
        border-color: rgba(255, 255, 255, 0.32);
        transform: translateY(-1px);
    }
    .site-footer__social-link svg {
        width: 16px;
        height: 16px;
    }
    .site-footer__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .site-footer__action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #fff;
        text-decoration: none;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        background: rgba(255, 255, 255, 0.04);
        transition: border-color 0.2s ease, background-color 0.2s ease, color 0.2s ease;
    }
    .site-footer__action:hover {
        border-color: #f26522;
        background: rgba(242, 101, 34, 0.12);
        color: #fff;
    }
    .site-footer__action svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }
    .site-footer__col-title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #fff;
        margin: 0 0 12px;
        letter-spacing: 0.02em;
    }
    .site-footer__nav {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0;
        margin-top: 4px;
        padding-top: 8px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    @media (min-width: 480px) {
        .site-footer__nav {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px 16px;
            padding-top: 16px;
        }
    }
    @media (min-width: 480px) and (max-width: 767.98px) {
        /* Chỉ trên tablet 2 cột: cột lẻ cuối (Shop) full-width.
           Không áp dụng từ 768px — lúc đó nav dùng display:contents trong grid 4 cột. */
        .site-footer__nav-col:last-child:nth-child(odd) {
            grid-column: 1 / -1;
        }
    }
    @media (min-width: 768px) {
        .site-footer__nav {
            display: contents;
            margin: 0;
            padding: 0;
            border: none;
        }
        .site-footer__nav-col {
            grid-column: auto;
        }
    }
    .site-footer__nav-col {
        padding: 16px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .site-footer__nav-col:last-child {
        border-bottom: none;
        padding-bottom: 8px;
    }
    @media (min-width: 480px) {
        .site-footer__nav-col {
            padding: 0;
            border-bottom: none;
        }
        .site-footer__nav-col:last-child {
            padding-bottom: 0;
        }
    }
    @media (min-width: 768px) {
        .site-footer__nav-col {
            padding: 0;
        }
        .site-footer__col-title {
            margin-bottom: 16px;
        }
    }
    .site-footer__links {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .site-footer__links li + li {
        margin-top: 10px;
    }
    .site-footer__links a {
        font-size: 0.875rem;
        line-height: 1.4;
        color: rgba(255, 255, 255, 0.68);
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .site-footer__links a:hover {
        color: #f26522;
    }
    .site-footer__legal {
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    .site-footer__legal summary {
        list-style: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.72);
        cursor: pointer;
        user-select: none;
    }
    .site-footer__legal summary::-webkit-details-marker {
        display: none;
    }
    .site-footer__legal summary::after {
        content: '';
        width: 7px;
        height: 7px;
        border-right: 2px solid rgba(255, 255, 255, 0.5);
        border-bottom: 2px solid rgba(255, 255, 255, 0.5);
        transform: rotate(45deg);
        transition: transform 0.2s ease;
    }
    .site-footer__legal[open] summary::after {
        transform: rotate(-135deg);
    }
    .site-footer__legal-body {
        margin-top: 12px;
        font-size: 0.75rem;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.5);
    }
    .site-footer__legal-body p {
        margin: 0 0 8px;
    }
    .site-footer__legal-body strong {
        color: rgba(255, 255, 255, 0.72);
        font-weight: 600;
    }
    .site-footer__trust {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        margin-top: 24px;
    }
    .site-footer__trust-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: border-color 0.2s ease, color 0.2s ease;
    }
    .site-footer__trust-badge:hover {
        border-color: rgba(255, 255, 255, 0.25);
        color: #fff;
    }
    .site-footer__trust-badge img {
        height: 28px;
        width: auto;
    }
    .site-footer__bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.15);
    }
    .site-footer__bottom-inner {
        max-width: 80rem;
        margin: 0 auto;
        padding: 20px 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        text-align: center;
    }
    @media (min-width: 768px) {
        .site-footer__bottom-inner {
            flex-direction: row;
            justify-content: space-between;
            text-align: left;
            padding: 20px 24px;
        }
    }
    @media (min-width: 1024px) {
        .site-footer__bottom-inner {
            padding: 20px 32px;
        }
    }
    .site-footer__locale {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.65);
    }
    .site-footer__locale img {
        width: 20px;
        height: auto;
        border-radius: 2px;
    }
    .site-footer__copyright {
        font-size: 0.8125rem;
        color: rgba(255, 255, 255, 0.5);
        margin: 0;
    }
    .site-footer__payments {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .site-footer__payment {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 26px;
        padding: 0 8px;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.92);
        font-size: 0.625rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        color: #111827;
    }
    .site-footer__payment--visa { color: #1a1f71; }
    .site-footer__payment--mc { color: #eb001b; }
    .site-footer__payment--amex { color: #006fcf; }
    .site-footer__payment--pp { color: #003087; }
    .site-footer__payment--apple { color: #111827; }

    /* ── Product show page ── */
    .product-show-page {
        padding-bottom: 48px;
        overflow: visible;
    }
    @media (max-width: 1023px) {
        .product-show-page {
            padding-bottom: 100px;
        }
    }
    .product-show-page__body {
        padding-top: 16px;
        overflow: visible;
    }
    @media (min-width: 768px) {
        .product-show-page__body {
            padding-top: 24px;
        }
    }
    .product-show-breadcrumb {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 8px 0 16px;
        font-size: 0.8125rem;
        line-height: 1.4;
        color: #4b5563;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: thin;
    }
    .product-show-breadcrumb a {
        color: #4b5563;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .product-show-breadcrumb a:hover {
        color: #005366;
    }
    .product-show-breadcrumb__current {
        color: #111827;
        font-weight: 600;
    }
    .product-show-breadcrumb__chev {
        width: 14px;
        height: 14px;
        color: #9ca3af;
        flex-shrink: 0;
    }
    /* Sticky column: gallery (short) sticks; purchase (long) scrolls until row ends */
    .product-show-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 24px;
        align-items: start;
        overflow: visible;
    }
    .product-show-purchase__shell {
        display: block;
        min-width: 0;
    }
    .product-show-below {
        margin-top: 32px;
        margin-left: -16px;
        margin-right: -16px;
        width: calc(100% + 32px);
        display: flex;
        flex-direction: column;
        gap: 0;
        min-width: 0;
    }
    @media (min-width: 640px) {
        .product-show-below {
            margin-left: -24px;
            margin-right: -24px;
            width: calc(100% + 48px);
            margin-top: 40px;
        }
    }
    @media (min-width: 1024px) {
        .product-show-below {
            margin-left: -32px;
            margin-right: -32px;
            width: calc(100% + 64px);
        }
    }
    .product-show-below__reviews {
        padding: 0 16px 48px;
    }
    @media (min-width: 640px) {
        .product-show-below__reviews {
            padding: 0 24px 56px;
        }
    }
    @media (min-width: 1024px) {
        .product-show-below__reviews {
            padding: 0 32px 64px;
        }
    }
    .product-show-info__tail {
        margin-top: 0;
        padding: 32px 16px 0;
        background: transparent;
    }
    @media (min-width: 640px) {
        .product-show-info__tail {
            padding: 40px 24px 0;
        }
    }
    @media (min-width: 1024px) {
        .product-show-info__tail {
            padding: 48px 32px 0;
        }
    }

    /* ── PDP recommendation sections (tiered hierarchy) ── */
    .product-show-rec {
        padding: 48px 16px;
    }
    @media (min-width: 640px) {
        .product-show-rec {
            padding: 56px 24px;
        }
    }
    @media (min-width: 1024px) {
        .product-show-rec {
            padding: 64px 32px;
        }
    }
    .product-show-rec.hidden {
        display: none;
    }
    .product-show-rec--fbt {
        background: linear-gradient(180deg, #f8fafb 0%, #ffffff 100%);
    }
    .product-show-rec--ymlt {
        background: #ffffff;
    }
    .product-show-rec--recent {
        padding-top: 32px;
        padding-bottom: 32px;
        background: #fafafa;
    }
    @media (min-width: 1024px) {
        .product-show-rec--recent {
            padding-top: 40px;
            padding-bottom: 40px;
        }
    }
    .product-show-rec__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px 24px;
        margin-bottom: 24px;
    }
    .product-show-rec__head--compact {
        margin-bottom: 16px;
    }
    .product-show-rec__heading {
        flex: 1;
        min-width: 0;
    }
    .product-show-rec__eyebrow {
        margin: 0 0 8px;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #f26522;
    }
    .product-show-rec__title {
        margin: 0;
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 800;
        line-height: 1.15;
        color: #111827;
    }
    .product-show-rec__title--sm {
        font-size: clamp(1.25rem, 2.5vw, 1.5rem);
    }
    .product-show-rec__sub {
        margin: 10px 0 0;
        max-width: 36rem;
        font-size: 0.9375rem;
        line-height: 1.55;
        color: #6b7280;
    }
    .product-show-rec__link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
        margin-top: 4px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #4b5563;
        text-decoration: none;
        transition: color 0.2s ease, transform 0.2s ease;
    }
    .product-show-rec__link:hover {
        color: #005366;
        transform: translateX(2px);
    }

    /* FBT bundle layout */
    .fbt-bundle {
        display: grid;
        gap: 24px;
        align-items: stretch;
    }
    @media (min-width: 1024px) {
        .fbt-bundle {
            grid-template-columns: 1fr minmax(220px, 260px);
            gap: 32px;
        }
    }
    .fbt-bundle__grid {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 12px 8px;
        padding: 20px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
    }
    @media (min-width: 768px) {
        .fbt-bundle__grid {
            flex-wrap: nowrap;
            gap: 12px;
            padding: 24px;
        }
    }
    .fbt-bundle__plus {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        color: #9ca3af;
    }
    .fbt-bundle__plus svg {
        width: 20px;
        height: 20px;
    }
    .fbt-bundle__slot {
        flex: 1 1 140px;
        min-width: 0;
        max-width: 200px;
    }
    @media (min-width: 768px) {
        .fbt-bundle__slot {
            flex: 1 1 0;
            max-width: none;
        }
    }
    .fbt-bundle__checkout {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 8px;
        padding: 24px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
    }
    .fbt-bundle__checkout-label {
        margin: 0;
        font-size: 0.8125rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6b7280;
    }
    .fbt-bundle__checkout-price {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 800;
        color: #111827;
    }
    .fbt-bundle__checkout-note {
        margin: 0 0 8px;
        font-size: 0.8125rem;
        color: #9ca3af;
    }
    .fbt-bundle__checkout-btn {
        width: 100%;
        padding: 14px 20px;
        border: none;
        border-radius: 9999px;
        background: #005366;
        color: #fff;
        font-size: 0.9375rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.15s ease;
    }
    .fbt-bundle__checkout-btn:hover:not(:disabled) {
        background: #003d4d;
        transform: translateY(-1px);
    }
    .fbt-bundle__checkout-btn:disabled {
        opacity: 0.65;
        cursor: wait;
    }

    /* PDP product cards */
    .product-card-pdp {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
    }
    .product-card-pdp__media {
        position: relative;
        display: block;
        aspect-ratio: 1;
        overflow: hidden;
        border-radius: 12px;
        background: linear-gradient(145deg, #f3f4f6 0%, #e5e7eb 100%);
        text-decoration: none;
    }
    .product-card-pdp--large .product-card-pdp__media {
        aspect-ratio: 4 / 5;
        border-radius: 16px;
    }
    .product-card-pdp--mini .product-card-pdp__media {
        aspect-ratio: 1;
        border-radius: 10px;
    }
    .product-card-pdp--bundle .product-card-pdp__media {
        aspect-ratio: 1;
        border-radius: 12px;
    }
    .product-card-pdp__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        transition: transform 0.4s ease;
    }
    .product-card-pdp:hover .product-card-pdp__media img {
        transform: scale(1.06);
    }
    .product-card-pdp__placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }
    .product-card-pdp__badge {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 2;
        padding: 4px 8px;
        font-size: 0.6875rem;
        font-weight: 700;
        color: #fff;
        background: #005366;
        border-radius: 9999px;
        pointer-events: none;
    }
    .product-card-pdp__badge--current {
        background: #f26522;
    }
    .product-card-pdp__body {
        display: flex;
        flex-direction: column;
        flex: 1;
        gap: 6px;
        padding: 10px 2px 0;
    }
    .product-card-pdp--large .product-card-pdp__body {
        padding: 12px 4px 0;
        gap: 8px;
    }
    .product-card-pdp--mini .product-card-pdp__body {
        padding: 8px 0 0;
        gap: 4px;
    }
    .product-card-pdp--bundle .product-card-pdp__body {
        padding: 10px 0 0;
    }
    .product-card-pdp__title {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        overflow: hidden;
        overflow-wrap: break-word;
        min-height: calc(1.35em * 2);
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.35;
        color: #111827;
        text-decoration: none;
    }
    .product-card-pdp--large .product-card-pdp__title {
        font-size: 1rem;
    }
    .product-card-pdp--mini .product-card-pdp__title {
        font-size: 0.75rem;
        -webkit-line-clamp: 2;
    }
    .product-card-pdp__title:hover {
        color: #005366;
    }
    .product-card-pdp__rating {
        display: flex;
        align-items: center;
        gap: 2px;
        margin: 0;
        min-height: 18px;
        font-size: 0.75rem;
        color: #f59e0b;
    }
    .product-card-pdp__rating--empty {
        visibility: hidden;
    }
    .product-card-pdp__rating svg {
        width: 14px;
        height: 14px;
    }
    .product-card-pdp__rating span {
        margin-left: 4px;
        color: #9ca3af;
    }
    .product-card-pdp__footer {
        margin-top: auto;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .product-card-pdp__price-row {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 6px;
        margin: 0;
    }
    .product-card-pdp__price {
        font-size: 1rem;
        font-weight: 800;
        color: #111827;
    }
    .product-card-pdp--large .product-card-pdp__price {
        font-size: 1.125rem;
    }
    .product-card-pdp--mini .product-card-pdp__price {
        font-size: 0.8125rem;
    }
    .product-card-pdp__price-was {
        font-size: 0.8125rem;
        color: #9ca3af;
        text-decoration: line-through;
    }
    .product-card-pdp__cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 8px 12px;
        border: 1.5px solid #005366;
        border-radius: 9999px;
        background: transparent;
        color: #005366;
        font-size: 0.8125rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .product-card-pdp--large .product-card-pdp__cta {
        padding: 10px 14px;
        font-size: 0.875rem;
    }
    .product-card-pdp__cta:hover {
        background: #005366;
        color: #fff;
    }
    .product-card-pdp__cta--link {
        text-align: center;
    }

    /* Recommendation carousels */
    .product-show-rec__track-shell {
        position: relative;
    }
    .product-show-rec__track-wrap {
        container-type: inline-size;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        --rec-gap: 16px;
        --rec-visible: 2;
    }
    .product-show-rec__track-wrap::-webkit-scrollbar {
        display: none;
    }
    .product-show-rec__track-shell--large .product-show-rec__track-wrap {
        --rec-visible: 2.15;
    }
    @media (min-width: 640px) {
        .product-show-rec__track-shell--large .product-show-rec__track-wrap {
            --rec-visible: 2;
        }
        .product-show-rec__track-shell--mini .product-show-rec__track-wrap {
            --rec-visible: 3;
        }
    }
    @media (min-width: 1024px) {
        .product-show-rec__track-shell--large .product-show-rec__track-wrap {
            --rec-visible: 4;
        }
        .product-show-rec__track-shell--mini .product-show-rec__track-wrap {
            --rec-visible: 6;
        }
    }
    .product-show-rec__track {
        display: flex;
        align-items: stretch;
        gap: var(--rec-gap);
        padding-bottom: 4px;
    }
    .product-show-rec__track > .product-card-pdp {
        flex: 0 0 calc((100cqw - (var(--rec-visible) - 1) * var(--rec-gap)) / var(--rec-visible));
        width: calc((100cqw - (var(--rec-visible) - 1) * var(--rec-gap)) / var(--rec-visible));
        min-width: 0;
        align-self: stretch;
        height: auto;
        scroll-snap-align: start;
    }
    .product-show-rec__nav {
        position: absolute;
        top: 38%;
        z-index: 5;
        display: none;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 9999px;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #374151;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        cursor: pointer;
        transition: border-color 0.2s ease, color 0.2s ease;
    }
    .product-show-rec__nav--visible {
        display: flex;
    }
    .product-show-rec__nav:hover:not(:disabled) {
        border-color: #005366;
        color: #005366;
    }
    .product-show-rec__nav:disabled {
        opacity: 0;
        pointer-events: none;
    }
    .product-show-rec__nav--prev { left: -6px; }
    .product-show-rec__nav--next { right: -6px; }

    /* Customize products hero (home + product show) */
    .product-show-customize-hero {
        padding: 48px 0;
        background: #ffffff;
    }
    @media (min-width: 640px) {
        .product-show-customize-hero {
            padding: 56px 0;
        }
    }
    .customize-hero {
        text-align: center;
    }
    .customize-hero__title {
        font-size: clamp(1.5rem, 3.5vw, 2.25rem);
        font-weight: 800;
        line-height: 1.2;
        color: #111827;
        max-width: 52rem;
        margin: 0 auto;
    }
    .customize-hero__title a {
        color: inherit;
        text-decoration: none;
    }
    .customize-hero__title a:hover {
        color: #005366;
    }
    .customize-hero__stage-link {
        position: absolute;
        inset: 0;
        z-index: 5;
        border-radius: 16px;
    }
    .customize-hero__stage {
        position: relative;
        margin-top: 32px;
        min-height: 420px;
        max-width: 1100px;
        margin-left: auto;
        margin-right: auto;
    }
    @media (min-width: 1024px) {
        .customize-hero__stage {
            min-height: 520px;
        }
    }
    .customize-hero__lines {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }
    .customize-hero__float {
        position: absolute;
        z-index: 2;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        background: #fff;
        transition: transform 0.35s ease, box-shadow 0.35s ease;
    }
    .customize-hero__float:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.14);
    }
    .customize-hero__float img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .customize-hero__float--1 { top: 4%; left: 2%; width: 88px; height: 88px; }
    .customize-hero__float--2 { top: 18%; left: 8%; width: 72px; height: 100px; }
    .customize-hero__float--3 { bottom: 18%; left: 4%; width: 96px; height: 96px; }
    .customize-hero__float--4 { bottom: 8%; left: 22%; width: 80px; height: 80px; border-radius: 10px; }
    .customize-hero__float--5 { top: 10%; right: 18%; width: 88px; height: 110px; }
    .customize-hero__float--6 { top: 38%; right: 6%; width: 92px; height: 92px; }
    .customize-hero__float--7 { bottom: 14%; right: 14%; width: 76px; height: 76px; }
    @media (min-width: 1024px) {
        .customize-hero__float--1 { width: 110px; height: 110px; left: 4%; }
        .customize-hero__float--2 { width: 90px; height: 124px; left: 10%; }
        .customize-hero__float--3 { width: 118px; height: 118px; }
        .customize-hero__float--4 { width: 96px; height: 96px; left: 20%; }
        .customize-hero__float--5 { width: 108px; height: 132px; right: 20%; }
        .customize-hero__float--6 { width: 112px; height: 112px; right: 8%; }
        .customize-hero__float--7 { width: 92px; height: 92px; right: 16%; }
    }
    .customize-hero__center {
        position: relative;
        z-index: 3;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 8px;
    }
    .customize-hero__shirt-wrap {
        position: relative;
        width: min(280px, 72vw);
    }
    @media (min-width: 1024px) {
        .customize-hero__shirt-wrap {
            width: 320px;
        }
    }
    .customize-hero__shirt {
        width: 100%;
        display: block;
        filter: drop-shadow(0 12px 28px rgba(0, 0, 0, 0.12));
    }
    .customize-hero__design-box {
        position: absolute;
        left: 28%;
        top: 32%;
        width: 44%;
        aspect-ratio: 1;
        border: 2px dashed #9ca3af;
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.92);
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    .customize-hero__design-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .customize-hero__play {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.25);
        border: none;
        cursor: default;
        pointer-events: none;
    }
    .customize-hero__play svg {
        width: 36px;
        height: 36px;
        color: #fff;
        filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.3));
    }
    .customize-hero__resize {
        position: absolute;
        bottom: -6px;
        right: -6px;
        width: 22px;
        height: 22px;
        pointer-events: none;
    }
    .customize-hero__upload {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
        padding: 14px 32px;
        font-size: 1.0625rem;
        font-weight: 700;
        color: #fff;
        background: #f26522;
        border-radius: 9999px;
        text-decoration: none;
        box-shadow: 0 8px 24px rgba(242, 101, 34, 0.35);
        transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .customize-hero__upload:hover {
        background: #e2150c;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(226, 21, 12, 0.35);
        color: #fff;
    }
    .customize-hero__upload svg {
        width: 22px;
        height: 22px;
        flex-shrink: 0;
    }
    .customize-hero__toolbar {
        position: absolute;
        top: 22%;
        right: 2%;
        z-index: 4;
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 10px 8px;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12);
    }
    @media (min-width: 1024px) {
        .customize-hero__toolbar {
            right: 8%;
        }
    }
    .customize-hero__tool {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        color: #4b5563;
        background: transparent;
        border: none;
        cursor: default;
    }
    .customize-hero__tool svg {
        width: 20px;
        height: 20px;
    }
    .customize-hero__tool--accent {
        background: #f7f7f7;
        color: #f26522;
    }
    @media (max-width: 767px) {
        .customize-hero__float,
        .customize-hero__toolbar,
        .customize-hero__lines {
            display: none;
        }
        .customize-hero__stage {
            min-height: auto;
            margin-top: 20px;
        }
        .customize-hero__title {
            font-size: 1.25rem;
            line-height: 1.25;
            padding: 0 4px;
        }
        .customize-hero__upload {
            width: min(100%, 280px);
            padding: 12px 20px;
            font-size: 0.9375rem;
        }
    }

    /* ── Product show — mobile (≤767px) ── */
    @media (max-width: 767px) {
        .product-show-page__body {
            overflow-x: clip;
        }
        .product-show-below {
            overflow-x: clip;
        }
        .product-show-below__reviews {
            padding: 0 16px 32px;
        }
        .product-show-rec {
            padding: 32px 16px;
        }
        .product-show-rec--recent {
            padding-top: 24px;
            padding-bottom: 24px;
        }
        .product-show-rec__head {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
            margin-bottom: 16px;
        }
        .product-show-rec__link {
            align-self: flex-start;
            margin-top: 0;
        }
        .product-show-rec__title {
            font-size: 1.375rem;
        }
        .product-show-rec__sub {
            font-size: 0.875rem;
            line-height: 1.5;
        }
        .product-show-customize-hero {
            padding: 32px 0;
        }

        /* FBT bundle — swipeable row */
        .fbt-bundle {
            gap: 16px;
        }
        .fbt-bundle__grid {
            flex-wrap: nowrap;
            justify-content: flex-start;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding: 14px;
            gap: 6px;
        }
        .fbt-bundle__grid::-webkit-scrollbar {
            display: none;
        }
        .fbt-bundle__slot {
            flex: 0 0 128px;
            max-width: 128px;
            scroll-snap-align: start;
        }
        .fbt-bundle__plus {
            flex: 0 0 18px;
            width: 18px;
            height: 18px;
            scroll-snap-align: none;
        }
        .fbt-bundle__plus svg {
            width: 14px;
            height: 14px;
        }
        .fbt-bundle__checkout {
            padding: 16px;
        }
        .fbt-bundle__checkout-price {
            font-size: 1.5rem;
        }
        .fbt-bundle__checkout-btn {
            padding: 12px 16px;
            font-size: 0.875rem;
        }
        .product-card-pdp--bundle .product-card-pdp__title {
            font-size: 0.6875rem;
            line-height: 1.3;
        }
        .product-card-pdp--bundle .product-card-pdp__body {
            padding-top: 6px;
            gap: 2px;
        }
        .product-card-pdp--bundle .product-card-pdp__rating {
            display: none;
        }
        .product-card-pdp--bundle .product-card-pdp__price {
            font-size: 0.8125rem;
        }
        .product-card-pdp--bundle .product-card-pdp__price-was {
            font-size: 0.6875rem;
        }
        .product-card-pdp--bundle .product-card-pdp__badge {
            top: 6px;
            left: 6px;
            padding: 2px 6px;
            font-size: 0.5625rem;
        }

        /* Recommendation carousels — 2-up + peek, square thumbs, no arrow overlap */
        .product-show-rec__nav {
            display: none !important;
        }
        .product-show-rec__track-shell--large .product-show-rec__track-wrap {
            --rec-visible: 2.15;
            --rec-gap: 16px;
        }
        .product-show-rec__track-shell--mini .product-show-rec__track-wrap {
            --rec-visible: 2.35;
            --rec-gap: 10px;
        }
        .product-card-pdp--large .product-card-pdp__media {
            aspect-ratio: 1;
            border-radius: 12px;
        }
        .product-card-pdp--large .product-card-pdp__body {
            padding: 8px 0 0;
            gap: 6px;
        }
        .product-card-pdp--large .product-card-pdp__title {
            font-size: 0.875rem;
        }
        .product-card-pdp--large .product-card-pdp__price {
            font-size: 1rem;
        }
        .product-card-pdp--large .product-card-pdp__cta {
            padding: 8px 12px;
            font-size: 0.8125rem;
        }

        /* Gallery */
        .product-show-gallery__thumb {
            width: 88px;
            height: 88px;
        }
        .product-show-gallery__sticky-media {
            gap: 12px;
        }

        /* Purchase header */
        .product-show-purchase__title-row {
            align-items: flex-start;
            gap: 8px;
        }
        .product-show-purchase__title {
            font-size: 1.5rem;
            line-height: 1.25;
        }
        .product-show-purchase__meta-row {
            gap: 6px 8px;
            font-size: 0.75rem;
        }

        /* Reviews */
        .product-show-gallery-reviews__card {
            padding: 16px;
        }
        .product-show-gallery-reviews__top {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        .product-show-gallery-reviews__write-btn {
            width: 100%;
            text-align: center;
        }
        .product-show-gallery-reviews__tabs {
            overflow-x: auto;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }
        .product-show-gallery-reviews__tabs::-webkit-scrollbar {
            display: none;
        }
        .product-show-gallery-reviews__tab {
            flex-shrink: 0;
            white-space: nowrap;
        }
    }
    .product-show-col {
        min-width: 0;
        max-width: 100%;
        height: fit-content;
        align-self: start;
    }
    .product-show-col--gallery {
        min-width: 0;
        max-width: 100%;
    }
    .product-show-col--gallery .product-show-gallery {
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }
    .product-show-col--info,
    .product-show-col--purchase,
    .product-show-col--listing,
    .product-show-col--reviews,
    .product-show-col--fbt,
    .product-show-col--tail {
        min-width: 0;
        max-width: 100%;
    }
    @media (max-width: 767px) {
        .product-show-layout {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
    }
    @media (min-width: 768px) {
        .product-show-layout {
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
            gap: 32px;
            align-items: start;
        }
        /* Stretch gallery cell to full row height so sticky has room while info column scrolls */
        .product-show-col--gallery {
            grid-column: 1;
            grid-row: 1;
            align-self: stretch;
            height: auto;
            min-width: 0;
        }
        .product-show-col--gallery .product-show-gallery {
            min-height: 100%;
        }
        .product-show-layout--sticky-gallery .product-show-gallery__sticky-media {
            position: sticky;
            top: var(--product-show-sticky-top, 88px);
            z-index: 2;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .product-show-col--info {
            grid-column: 2;
            grid-row: 1;
            z-index: 1;
        }
    }
    @media (min-width: 1024px) {
        .product-show-layout {
            grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
            gap: 40px;
        }
    }
    .product-show-purchase__sidebar {
        min-width: 0;
    }
    @media (max-width: 1023px) {
        .product-show-purchase {
            padding-bottom: 88px;
        }
    }
    .product-show-gallery__sticky-media {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .product-show-gallery__frame {
        display: flex;
        flex-direction: column;
        gap: 16px;
        min-width: 0;
    }
    .product-show-gallery__main {
        order: 1;
    }
    .product-show-gallery__nav-block {
        order: 2;
        margin-top: 0;
    }
    .product-show-gallery__meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-top: 2px;
    }
    .product-show-gallery__thumbs-wrap {
        position: relative;
        min-width: 0;
    }
    .product-show-gallery__thumb-scroll {
        display: none;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        border: 1px solid #e5e7eb;
        border-radius: 9999px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        cursor: pointer;
        transition: background 0.2s ease;
    }
    .product-show-gallery__thumb-scroll:hover {
        background: #f9fafb;
    }
    @media (min-width: 768px) {
        .product-show-gallery__sticky-media {
            gap: 20px;
        }
        .product-show-gallery__frame {
            flex-direction: row;
            align-items: stretch;
            gap: 12px;
        }
        .product-show-gallery__nav-block {
            order: -1;
            flex: 0 0 108px;
            width: 108px;
        }
        .product-show-gallery__main {
            order: 0;
            flex: 1;
            min-width: 0;
            aspect-ratio: auto;
            height: min(480px, calc(100dvh - var(--product-show-sticky-top, 88px) - 72px));
            max-height: min(480px, calc(100dvh - var(--product-show-sticky-top, 88px) - 72px));
        }
        .product-show-gallery__thumbs-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            height: 100%;
        }
        .product-show-gallery__thumbs {
            flex-direction: column;
            overflow-x: hidden;
            overflow-y: auto;
            flex: 1 1 auto;
            min-height: 0;
            max-height: min(480px, calc(100dvh - var(--product-show-sticky-top, 88px) - 72px));
            padding-bottom: 0;
            padding-top: 0;
            gap: 10px;
            scrollbar-width: thin;
        }
        .product-show-gallery__thumb {
            width: 100px;
            height: 100px;
        }
        .product-show-gallery__thumb-scroll--up,
        .product-show-gallery__thumb-scroll--down {
            display: inline-flex;
        }
        .product-show-gallery__thumb-scroll--prev,
        .product-show-gallery__thumb-scroll--next {
            display: none;
        }
    }
    @media (max-width: 767px) {
        .product-show-gallery__thumbs {
            flex-direction: row;
        }
        .product-show-gallery__thumb-scroll--prev,
        .product-show-gallery__thumb-scroll--next {
            display: inline-flex;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 2;
        }
        .product-show-gallery__thumb-scroll--prev {
            left: 0;
        }
        .product-show-gallery__thumb-scroll--next {
            right: 0;
        }
        .product-show-gallery__thumb-scroll--up,
        .product-show-gallery__thumb-scroll--down {
            display: none;
        }
    }
    @media (min-width: 768px) {
        .product-show-gallery__sticky-media {
            gap: 20px;
        }
    }
    .product-show-gallery__thumbs {
        gap: 12px;
        padding-bottom: 8px;
        padding-top: 4px;
    }
    .product-show-gallery__thumb {
        width: 88px;
        height: 88px;
    }
    @media (min-width: 768px) {
        .product-show-gallery__thumb {
            width: 100px;
            height: 100px;
        }
    }
    .product-show-gallery__main {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        aspect-ratio: 1;
        border-radius: 16px;
    }
    .product-show-gallery__main img,
    .product-show-gallery__main video {
        display: block;
        width: 100%;
        max-width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .product-show-gallery__wishlist {
        position: absolute;
        bottom: 16px;
        right: 16px;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        color: #4b5563;
        transition: color 0.2s ease, transform 0.2s ease;
    }
    .product-show-gallery__tryon {
        position: absolute;
        bottom: 16px;
        left: 16px;
        z-index: 10;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 40px;
        padding: 8px 12px;
        border: 0;
        border-radius: 9999px;
        background: #fff;
        color: #111827;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }
    .product-show-gallery__tryon svg {
        width: 16px;
        height: 16px;
        color: #005366;
    }
    .product-show-gallery__tryon:hover {
        color: #005366;
        background: #fff;
    }
    .product-show-gallery__wishlist:hover {
        color: #e2150c;
        transform: scale(1.05);
    }
    .product-show-gallery__wishlist .wishlist-btn:hover svg,
    .product-show-gallery__wishlist .wishlist-btn.in-wishlist svg {
        color: #e2150c;
    }
    .product-show-gallery__badge {
        position: absolute;
        top: 16px;
        left: 16px;
        z-index: 10;
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 4px;
        background: #f26522;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #fff;
    }
    .product-show-gallery__flash-badge {
        position: absolute;
        top: 16px;
        right: 16px;
        z-index: 10;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 9999px;
        background: linear-gradient(135deg, #ff2d20 0%, #e2150c 55%, #c0120a 100%);
        font-size: 0.8125rem;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: 0.02em;
        color: #fff;
        box-shadow: 0 6px 18px rgba(226, 21, 12, 0.45);
    }
    .product-show-gallery__flash-badge--live {
        animation: product-show-flash-pulse 2s ease-in-out infinite;
    }
    @keyframes product-show-flash-pulse {
        0%, 100% { box-shadow: 0 6px 18px rgba(226, 21, 12, 0.45); }
        50% { box-shadow: 0 6px 22px rgba(226, 21, 12, 0.65), 0 0 0 4px rgba(226, 21, 12, 0.12); }
    }
    .product-show-gallery__flash-badge svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }
    .product-show-gallery__flash-badge-pct {
        display: inline-flex;
        padding: 2px 6px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.22);
        font-size: 0.75rem;
        font-weight: 800;
    }
    .product-show-flash-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 10px;
        padding: 8px 12px;
        border-radius: 9999px;
        background: linear-gradient(90deg, rgba(226, 21, 12, 0.12) 0%, rgba(226, 21, 12, 0.04) 100%);
        border: 1px solid rgba(226, 21, 12, 0.22);
    }
    .product-show-flash-chip__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 9999px;
        background: #e2150c;
        color: #fff;
    }
    .product-show-flash-chip__icon svg {
        width: 14px;
        height: 14px;
    }
    .product-show-flash-chip__text {
        font-size: 0.8125rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #e2150c;
    }
    .product-show-flash-chip__pct {
        display: inline-flex;
        padding: 2px 8px;
        border-radius: 9999px;
        background: #e2150c;
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
    }
    .product-show-flash-chip__urgency {
        font-size: 0.75rem;
        font-weight: 600;
        color: #991b1b;
    }
    .product-show-purchase__price-panel--flash {
        padding: 0;
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(226, 21, 12, 0.22);
        box-shadow: 0 4px 16px rgba(226, 21, 12, 0.08);
    }
    .product-show-purchase__price-panel--flash .product-show-purchase__price-row,
    .product-show-purchase__price-panel--flash .product-show-flash-sale__savings,
    .product-show-purchase__price-panel--flash .product-show-purchase__stock-row {
        padding-left: 14px;
        padding-right: 14px;
    }
    .product-show-purchase__price-panel--flash .product-show-purchase__price-row {
        padding-top: 14px;
    }
    .product-show-purchase__price-panel--flash .product-show-purchase__stock-row {
        padding-bottom: 14px;
    }
    .product-show-flash-sale {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .product-show-flash-sale__banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 12px 14px;
        background: linear-gradient(90deg, #e2150c 0%, #ff3b30 100%);
        color: #fff;
    }
    .product-show-flash-sale__intro {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 0;
    }
    .product-show-flash-sale__bolt {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.18);
        flex-shrink: 0;
    }
    .product-show-flash-sale__bolt svg {
        width: 20px;
        height: 20px;
    }
    .product-show-flash-sale__copy {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }
    .product-show-flash-sale__title {
        font-size: 0.9375rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        line-height: 1.2;
    }
    .product-show-flash-sale__subtitle {
        font-size: 0.75rem;
        font-weight: 500;
        opacity: 0.92;
        line-height: 1.3;
    }
    .product-show-flash-sale__pct {
        display: inline-flex;
        flex-shrink: 0;
        padding: 6px 10px;
        border-radius: 9999px;
        background: #fff;
        font-size: 0.8125rem;
        font-weight: 800;
        color: #e2150c;
    }
    .product-show-flash-sale__countdown {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        flex-shrink: 0;
    }
    .product-show-flash-sale__countdown-label {
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        opacity: 0.9;
    }
    .product-show-flash-sale__timer {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .product-show-flash-sale__time-unit {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        min-width: 36px;
        padding: 4px 6px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.95);
        color: #e2150c;
    }
    .product-show-flash-sale__time-value {
        font-size: 0.9375rem;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        line-height: 1.1;
    }
    .product-show-flash-sale__time-label {
        font-size: 0.5625rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #991b1b;
        line-height: 1.2;
    }
    .product-show-flash-sale__time-sep {
        font-size: 0.875rem;
        font-weight: 800;
        opacity: 0.85;
        line-height: 1;
    }
    .product-show-flash-sale__savings {
        margin: 0;
        padding-bottom: 4px;
        font-size: 0.8125rem;
        line-height: 1.4;
        color: #991b1b;
    }
    .product-show-flash-sale__savings strong {
        font-weight: 800;
        color: #e2150c;
    }
    .product-show-purchase__price-current--flash {
        font-size: 1.75rem;
        color: #e2150c;
    }
    .product-show-purchase__price-row--flash {
        align-items: center;
    }
    .product-show-purchase__save-badge--flash {
        background: #e2150c;
        color: #fff;
        border-radius: 9999px;
        padding: 5px 10px;
        font-size: 0.8125rem;
    }
    @media (max-width: 639px) {
        .product-show-flash-sale__banner {
            flex-direction: column;
            align-items: stretch;
        }
        .product-show-flash-sale__countdown {
            align-items: flex-start;
        }
        .product-show-flash-sale__intro {
            flex-wrap: wrap;
        }
    }
    .product-show-purchase__engagement {
        margin-top: 4px;
    }
    .product-show-purchase__sale-ends {
        white-space: nowrap;
    }
    .product-show-gallery__card-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
  @media (min-width: 1024px) {
        .product-show-gallery__card-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    .product-show-gallery__card {
        min-width: 0;
    }
    .product-show-volume {
        margin: 0;
        padding: 16px;
        border: 1px solid #eadfce;
        border-radius: 12px;
        background: #faf8f5;
    }
    .product-show-volume__head {
        margin-bottom: 12px;
    }
    .product-show-volume__title {
        margin: 0 0 6px;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.3;
        color: #111827;
    }
    .product-show-volume__note {
        margin: 0;
        font-size: 0.8125rem;
        line-height: 1.45;
        color: #4b5563;
    }
    .product-show-volume__grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }
    .product-show-volume__tier {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        min-height: 64px;
        padding: 10px 8px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }
    .product-show-volume__tier:hover {
        border-color: #005366;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 83, 102, 0.08);
    }
    .product-show-volume__tier--popular {
        border-color: #005366;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 83, 102, 0.1);
    }
    .product-show-volume__popular-badge {
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        padding: 2px 8px;
        border-radius: 9999px;
        background: #005366;
        color: #fff;
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        line-height: 1.4;
        white-space: nowrap;
    }
    .product-show-volume__qty {
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.3;
        color: #374151;
    }
    .product-show-volume__tier--popular .product-show-volume__qty {
        margin-top: 4px;
    }
    .product-show-volume__off {
        font-size: 0.9375rem;
        font-weight: 700;
        line-height: 1.2;
        color: #e2150c;
    }
    .product-show-volume__tier.is-selected {
        border-color: #005366 !important;
        background: rgba(0, 83, 102, 0.06) !important;
        box-shadow: 0 0 0 1px #005366;
    }
    .product-show-customization {
        margin-top: 20px;
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }
    .product-show-customization__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }
    .product-show-customization__head-text {
        min-width: 0;
        flex: 1;
    }
    .product-show-customization__title {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin: 0 0 6px;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.3;
        color: #111827;
    }
    .product-show-customization__required-pill {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 9999px;
        background: #fef2f2;
        color: #e2150c;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        line-height: 1.4;
    }
    .product-show-customization__note {
        margin: 0;
        font-size: 0.8125rem;
        line-height: 1.45;
        color: #4b5563;
    }
    .product-show-customization__toggle {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 9999px;
        background: #fff;
        color: #005366;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.2;
        cursor: pointer;
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }
    .product-show-customization__toggle:hover {
        background: rgba(0, 83, 102, 0.06);
        border-color: #005366;
    }
    .product-show-customization__toggle-icon {
        width: 16px;
        height: 16px;
        transition: transform 0.2s ease;
    }
    .product-show-customization__toggle[aria-expanded="false"] .product-show-customization__toggle-icon {
        transform: rotate(180deg);
    }
    .product-show-customization__body.is-collapsed {
        display: none;
    }
    .product-show-customization__box {
        padding: 14px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fafafa;
    }
    .product-show-customization__box.is-invalid {
        border-color: #e2150c;
        box-shadow: 0 0 0 1px rgba(226, 21, 12, 0.15);
    }
    .product-show-customization__field-head {
        margin-bottom: 10px;
    }
    .product-show-customization__field-title {
        margin: 0 0 4px;
        font-size: 0.9375rem;
        font-weight: 700;
        line-height: 1.35;
        color: #111827;
    }
    .product-show-customization__required {
        color: #e2150c;
    }
    .product-show-customization__price {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #e2150c;
    }
    .product-show-customization__field-desc {
        margin: 0;
        font-size: 0.8125rem;
        line-height: 1.45;
        color: #6b7280;
    }
    .product-show-customization__input {
        width: 100%;
        min-height: 48px;
        padding: 12px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        font-size: 16px;
        line-height: 1.5;
        color: #111827;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .product-show-customization__input:focus {
        outline: none;
        border-color: #005366;
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.12);
    }
    .product-show-customization__input--file {
        min-height: auto;
        padding: 10px 12px;
        font-size: 0.875rem;
    }
    .product-show-customization__options {
        display: grid;
        gap: 8px;
    }
    .product-show-customization__option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }
    .product-show-customization__option:hover {
        border-color: #005366;
        background: rgba(0, 83, 102, 0.03);
    }
    .product-show-customization__option span {
        font-size: 0.875rem;
        line-height: 1.4;
        color: #374151;
    }
    .product-show-customization__option input {
        flex-shrink: 0;
        color: #005366;
    }
    .product-show-customization__error {
        margin: 8px 0 0;
        font-size: 0.8125rem;
        line-height: 1.4;
        color: #e2150c;
    }
    .product-show-customization__error:not(.hidden) {
        display: block;
    }
    .product-show-gallery__nav {
        position: absolute;
        top: 50%;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        border: 1px solid #e5e7eb;
        background: rgba(255, 255, 255, 0.95);
        color: #4b5563;
        transform: translateY(-50%);
        opacity: 0;
        transition: opacity 0.2s ease, background 0.2s ease, color 0.2s ease;
    }
    .product-show-gallery__main:hover .product-show-gallery__nav,
    .product-show-gallery__nav:focus-visible {
        opacity: 1;
    }
    .product-show-gallery__nav:hover {
        background: #fff;
        color: #005366;
    }
    .product-show-gallery__nav svg {
        width: 20px;
        height: 20px;
    }
    .product-show-gallery__nav--prev { left: 12px; }
    .product-show-gallery__nav--next { right: 12px; }

    .product-show-col--reviews {
        min-width: 0;
        max-width: 100%;
        position: relative;
        z-index: 1;
    }
    .product-show-gallery-reviews {
        margin-top: 0;
    }
    .product-show-gallery-reviews__card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
    }
    @media (min-width: 768px) {
        .product-show-gallery-reviews__card {
            padding: 24px;
        }
    }
    .product-show-gallery-reviews__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }
    .product-show-gallery-reviews__heading {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.35;
        color: #111827;
    }
    .product-show-gallery-reviews__write-btn {
        flex-shrink: 0;
        padding: 8px 16px;
        border: 1.5px solid #005366;
        border-radius: 9999px;
        background: transparent;
        color: #005366;
        font-size: 0.8125rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .product-show-gallery-reviews__write-btn:hover {
        background: #005366;
        color: #fff;
    }
    .product-show-gallery-reviews__tabs {
        display: flex;
        align-items: stretch;
        gap: 0;
        margin-bottom: 20px;
        padding: 4px;
        border-radius: 9999px;
        background: #f3f4f6;
    }
    .product-show-gallery-reviews__tab {
        flex: 1;
        min-width: 0;
        border: none;
        border-radius: 9999px;
        padding: 10px 12px;
        background: transparent;
        color: #6b7280;
        font-size: 0.8125rem;
        font-weight: 500;
        line-height: 1.35;
        text-align: center;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }
    @media (min-width: 640px) {
        .product-show-gallery-reviews__tab {
            font-size: 0.875rem;
            padding: 10px 16px;
        }
    }
    .product-show-gallery-reviews__tab:hover:not(.product-show-gallery-reviews__tab--active) {
        color: #374151;
    }
    .product-show-gallery-reviews__tab--active {
        background: #fff;
        color: #111827;
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(17, 24, 39, 0.08), 0 1px 2px rgba(17, 24, 39, 0.04);
    }
    .product-show-gallery-reviews__panel-header {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;
    }
    .product-show-gallery-reviews__panel-header-row {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    @media (min-width: 640px) {
        .product-show-gallery-reviews__panel-header-row {
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }
    }
    .product-show-gallery-reviews__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
        color: #111827;
    }
    .product-show-gallery-reviews__item-product {
        display: block;
        margin-bottom: 8px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #005366;
        text-decoration: none;
        line-height: 1.4;
    }
    .product-show-gallery-reviews__item-product:hover {
        text-decoration: underline;
    }
    .product-show-gallery-reviews__write {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f3f4f6;
    }
    .product-show-gallery-reviews__summary {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
        color: #4b5563;
    }
    .product-show-gallery-reviews__score {
        font-weight: 700;
        color: #111827;
    }
    .product-show-gallery-reviews__count {
        color: #6b7280;
    }
    .product-show-gallery-reviews__stars {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        color: #f59e0b;
    }
    .product-show-gallery-reviews__stars svg {
        width: 16px;
        height: 16px;
    }
    .product-show-gallery-reviews__stars--sm svg {
        width: 14px;
        height: 14px;
    }
    .product-show-gallery-reviews__list {
        display: flex;
        flex-direction: column;
    }
    .product-show-gallery-reviews__item {
        padding: 16px 0;
        border-top: 1px solid #e5e7eb;
    }
    .product-show-gallery-reviews__item:first-child {
        border-top: none;
        padding-top: 0;
    }
    .product-show-gallery-reviews__item-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }
    .product-show-gallery-reviews__item-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .product-show-gallery-reviews__verified {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 9999px;
        background: #16a34a;
        color: #fff;
        font-size: 0.6875rem;
        font-weight: 600;
        line-height: 1.4;
    }
    .product-show-gallery-reviews__author-block {
        text-align: right;
        flex-shrink: 0;
    }
    .product-show-gallery-reviews__author-name {
        display: block;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #111827;
    }
    .product-show-gallery-reviews__date {
        display: block;
        font-size: 0.75rem;
        color: #6b7280;
    }
    .product-show-gallery-reviews__item-body {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 12px;
        align-items: start;
    }
    .product-show-gallery-reviews__item-title {
        margin: 0 0 6px;
        font-size: 0.9375rem;
        font-weight: 700;
        line-height: 1.4;
        color: #111827;
    }
    .product-show-gallery-reviews__item-copy {
        margin: 0;
        font-size: 0.875rem;
        line-height: 1.55;
        color: #374151;
    }
    .product-show-gallery-reviews__item-thumb {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        background: #f3f4f6;
        border: none;
        padding: 0;
        cursor: pointer;
    }
    .product-show-gallery-reviews__item-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .product-show-gallery-reviews__item-avatar {
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        background: rgba(0, 83, 102, 0.1);
        color: #005366;
        font-size: 0.875rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .product-show-gallery-reviews__photos {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }
    .product-show-gallery-reviews__photos-title {
        margin: 0 0 12px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
    }
    .product-show-gallery-reviews__photos-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
    }
    @media (min-width: 768px) {
        .product-show-gallery-reviews__photos-grid {
            grid-template-columns: repeat(6, minmax(0, 1fr));
        }
    }
    .product-show-gallery-reviews__photo {
        aspect-ratio: 1;
        border: none;
        border-radius: 8px;
        overflow: hidden;
        padding: 0;
        cursor: pointer;
        background: #f3f4f6;
    }
    .product-show-gallery-reviews__photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.2s ease;
    }
    .product-show-gallery-reviews__photo:hover img {
        transform: scale(1.03);
    }
    .product-show-review-lightbox {
        position: fixed;
        inset: 0;
        z-index: 60;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(17, 24, 39, 0.92);
    }
    .product-show-review-lightbox.hidden,
    .product-show-review-lightbox[hidden] {
        display: none;
    }
    .product-show-review-lightbox__stage {
        max-width: min(92vw, 960px);
        max-height: 82vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .product-show-review-lightbox__stage img {
        max-width: 100%;
        max-height: 82vh;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 8px;
        background: #111827;
    }
    .product-show-review-lightbox__close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    .product-show-review-lightbox__close svg {
        width: 22px;
        height: 22px;
    }
    .product-show-review-lightbox__close:hover {
        background: rgba(255, 255, 255, 0.22);
    }
    .product-show-review-lightbox__nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 44px;
        height: 44px;
        border: none;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    .product-show-review-lightbox__nav svg {
        width: 22px;
        height: 22px;
    }
    .product-show-review-lightbox__nav:hover {
        background: rgba(255, 255, 255, 0.22);
    }
    .product-show-review-lightbox__nav--prev { left: 16px; }
    .product-show-review-lightbox__nav--next { right: 16px; }
    .product-show-review-lightbox__nav[hidden] {
        display: none;
    }
    .product-show-review-lightbox__counter {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        padding: 6px 12px;
        border-radius: 9999px;
        background: rgba(0, 0, 0, 0.55);
        color: #fff;
        font-size: 0.8125rem;
        font-weight: 600;
    }
    .product-show-gallery-reviews__empty {
        margin: 0;
        font-size: 0.875rem;
        color: #6b7280;
    }
    .product-show-gallery-reviews__write-title {
        margin: 0 0 8px;
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
    }
    .product-show-gallery-reviews__write-text {
        margin: 0;
        font-size: 0.875rem;
        line-height: 1.55;
        color: #4b5563;
    }
    .product-show-gallery-reviews__featured-inline {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }
    .product-show-gallery-reviews__featured-inline-title {
        margin: 0;
        font-size: 0.9375rem;
        font-weight: 700;
        color: #111827;
    }
    .product-show-gallery-reviews__featured-caption {
        margin: 4px 0 0;
        font-size: 0.8125rem;
        color: #6b7280;
    }
    .product-show-gallery-reviews__featured-links {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        flex-shrink: 0;
    }
    .product-show-gallery-reviews__link {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #005366;
        text-decoration: none;
    }
    .product-show-gallery-reviews__link:hover {
        text-decoration: underline;
    }
    .product-show-gallery-reviews__carousel-page {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }
    @media (min-width: 640px) {
        .product-show-gallery-reviews__carousel-page {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .product-show-gallery-reviews__carousel-page {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    .product-show-gallery-reviews__featured-card {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
        padding: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
    }
    .product-show-gallery-reviews__featured-card-top {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .product-show-gallery-reviews__featured-product {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #005366;
        text-decoration: none;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-show-gallery-reviews__featured-product:hover {
        text-decoration: underline;
    }
    .product-show-gallery-reviews__featured-headline {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1.4;
        color: #111827;
    }
    .product-show-gallery-reviews__featured-copy {
        margin: 0;
        font-size: 0.8125rem;
        line-height: 1.5;
        color: #4b5563;
    }
    .product-show-gallery-reviews__featured-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        font-size: 0.75rem;
        color: #6b7280;
    }
    .product-show-gallery-reviews__featured-footer span:first-child {
        font-weight: 600;
        color: #374151;
    }
    .product-show-gallery-reviews__featured-media {
        display: block;
        margin-top: 4px;
        border-radius: 8px;
        overflow: hidden;
        aspect-ratio: 4 / 3;
        background: #f3f4f6;
    }
    .product-show-gallery-reviews__featured-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.2s ease;
    }
    .product-show-gallery-reviews__featured-media:hover img {
        transform: scale(1.02);
    }
    .product-show-gallery-reviews__pager {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }
    .product-show-gallery-reviews__pager-btn {
        width: 36px;
        height: 36px;
        border: 1px solid #d1d5db;
        border-radius: 9999px;
        background: #fff;
        color: #374151;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease;
    }
    .product-show-gallery-reviews__pager-btn svg {
        width: 18px;
        height: 18px;
    }
    .product-show-gallery-reviews__pager-btn:hover:not(:disabled) {
        border-color: #005366;
        color: #005366;
    }
    .product-show-gallery-reviews__pager-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .product-show-gallery-reviews__pager-status {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #4b5563;
        min-width: 40px;
        text-align: center;
    }

    .product-show-purchase__category {
        display: inline-flex;
        margin-bottom: 8px;
        font-size: 0.8125rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #4b5563;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .product-show-purchase__category:hover {
        color: #005366;
    }
    .product-show-purchase__title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px 16px;
    }
    .product-show-purchase__title {
        margin: 0;
        flex: 1;
        min-width: 0;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.25;
        color: #111827;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }
    .product-show-purchase__share {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        margin-top: 2px;
        padding: 0;
        border: none;
        background: none;
        cursor: pointer;
        color: #4b5563;
        font-size: 0.875rem;
        line-height: 1.25;
        white-space: nowrap;
        transition: color 0.2s ease;
    }
    .product-show-purchase__share:hover {
        color: #005366;
    }
    .product-show-purchase__share:hover .product-show-purchase__share-btn {
        border-color: #005366;
        color: #005366;
        background: rgba(0, 83, 102, 0.06);
    }
    .product-show-purchase__share-label {
        font-weight: 500;
    }
    @media (max-width: 639px) {
        .product-show-purchase__share-label {
            display: none;
        }
        .product-show-purchase__share {
            margin-top: 0;
        }
    }
    .product-show-purchase__meta-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 12px;
        margin-top: 8px;
        font-size: 0.8125rem;
        color: #4b5563;
    }
    .product-show-purchase__meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .product-show-purchase__meta-divider {
        width: 1px;
        height: 12px;
        background: #d1d5db;
    }
    .product-show-purchase__stock {
        flex-shrink: 0;
        padding: 4px 12px;
        border-radius: 9999px;
        background: rgba(22, 163, 74, 0.1);
        font-size: 0.75rem;
        font-weight: 700;
        color: #16a34a;
        white-space: nowrap;
    }
    .product-show-purchase__stock.is-out {
        background: rgba(226, 21, 12, 0.1);
        color: #e2150c;
    }
    .product-show-purchase__rating {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
        font-size: 0.9375rem;
        color: #4b5563;
    }
    .product-show-purchase__star {
        width: 16px;
        height: 16px;
        color: #d1d5db;
    }
    .product-show-purchase__star.is-filled {
        color: #d97706;
    }
    .product-show-purchase__excerpt {
        margin: 12px 0 0;
        font-size: 0.9375rem;
        line-height: 1.55;
        color: #4b5563;
    }
    .product-show-purchase__price-panel {
        margin-top: 16px;
        padding: 12px;
        border-radius: 8px;
        background: #f7f7f7;
    }
    .product-show-purchase__price-row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 8px 12px;
    }
    .product-show-purchase__price-current {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
        color: #111827;
    }
    .product-show-purchase__price-original {
        font-size: 1rem;
        color: #9ca3af;
        text-decoration: line-through;
    }
    .product-show-purchase__save-badge {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 4px;
        background: rgba(226, 21, 12, 0.1);
        font-size: 0.75rem;
        font-weight: 700;
        color: #e2150c;
    }
    .product-show-purchase__stock-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        font-size: 0.8125rem;
    }
    .product-show-purchase__stock-dot {
        width: 8px;
        height: 8px;
        border-radius: 9999px;
        background: #16a34a;
        flex-shrink: 0;
    }
    .product-show-purchase__stock-dot.is-out {
        background: #e2150c;
    }
    .product-show-purchase__stock-label {
        font-weight: 600;
        color: #16a34a;
    }
    .product-show-purchase__stock-label.is-out {
        color: #e2150c;
    }
    .product-show-purchase__stock-qty {
        color: #4b5563;
    }
    .product-show-purchase__divider {
        margin: 12px 0;
        border: none;
        border-top: 1px solid #e5e7eb;
    }
    .product-show-purchase {
        min-width: 0;
    }
    .product-show-purchase__tryon {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        min-height: 44px;
        margin-top: 12px;
        padding: 10px 16px;
        border: 1.5px solid #005366;
        border-radius: 9999px;
        background: #fff;
        color: #005366;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.3;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .product-show-purchase__tryon:hover {
        background: rgba(0, 83, 102, 0.06);
        color: #003d4d;
    }
    .product-show-purchase__tryon svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }
    .product-show-purchase__tryon-body,
    .product-show-purchase__tryon-title,
    .product-show-purchase__tryon-text,
    .product-show-purchase__tryon-cta,
    .product-show-purchase__tryon-media {
        display: none;
    }
    .product-show-options {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .product-show-options__attrs {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .product-show-options__group {
        min-width: 0;
    }
    .product-show-options__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 4px;
    }
    .product-show-options__label {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1.4;
        color: #111827;
    }
    .product-show-options__label-value,
    .product-show-options__label span {
        font-weight: 700;
        color: #111827;
    }
    .product-show-options__guide {
        padding: 0;
        border: 0;
        background: none;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #005366;
        cursor: pointer;
        text-decoration: none;
    }
    .product-show-options__guide:hover {
        text-decoration: underline;
    }
    body.size-guide-open {
        overflow: hidden;
    }
    .size-guide-modal {
        position: fixed;
        inset: 0;
        z-index: 120;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 0;
        background: rgba(17, 24, 39, 0.5);
    }
    .size-guide-modal.hidden {
        display: none;
    }
    @media (min-width: 768px) {
        .size-guide-modal {
            align-items: center;
            padding: 16px;
        }
    }
    .size-guide-modal__panel {
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 720px;
        max-height: min(92vh, 820px);
        background: #fff;
        border: 1px solid #f3f4f6;
        border-radius: 20px 20px 0 0;
        box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }
    @media (min-width: 768px) {
        .size-guide-modal__panel {
            max-height: min(90vh, 820px);
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.18);
            animation: sizeGuideModalIn 0.22s ease;
        }
    }
    @keyframes sizeGuideModalIn {
        from { opacity: 0; transform: translateY(12px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .size-guide-modal__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
        padding: 16px 16px 12px;
        border-bottom: 1px solid #e5e7eb;
    }
    @media (min-width: 768px) {
        .size-guide-modal__head {
            padding: 20px 24px 16px;
        }
    }
    .size-guide-modal__title {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.25;
        color: #111827;
    }
    .size-guide-modal__close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        padding: 0;
        border: 0;
        border-radius: 9999px;
        background: #f3f4f6;
        color: #4b5563;
        cursor: pointer;
        flex-shrink: 0;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .size-guide-modal__close:hover {
        background: #e5e7eb;
        color: #111827;
    }
    .size-guide-modal__body {
        flex: 1 1 auto;
        min-height: 0;
        padding: 16px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    @media (min-width: 768px) {
        .size-guide-modal__body {
            padding: 20px 24px 24px;
        }
    }
    .size-guide-modal__intro {
        display: flex;
        gap: 10px;
        margin: 0 0 16px;
        padding: 12px 14px;
        border-radius: 12px;
        background: rgba(0, 83, 102, 0.06);
        border: 1px solid rgba(0, 83, 102, 0.12);
        font-size: 0.8125rem;
        line-height: 1.45;
        color: #4b5563;
    }
    .size-guide-modal__intro svg {
        width: 18px;
        height: 18px;
        color: #005366;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .size-guide-modal__section {
        margin-bottom: 16px;
    }
    .size-guide-modal__section:last-child {
        margin-bottom: 0;
    }
    .size-guide-modal__label {
        display: block;
        margin: 0 0 8px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #111827;
    }
    .size-guide-gender {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 2px;
    }
    .size-guide-gender::-webkit-scrollbar {
        display: none;
    }
    .size-guide-chip {
        flex-shrink: 0;
        padding: 8px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 9999px;
        background: #fff;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.2;
        color: #4b5563;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }
    .size-guide-chip:hover {
        border-color: #d1d5db;
        background: #f9fafb;
    }
    .size-guide-chip.is-active {
        border-color: #005366;
        background: #005366;
        color: #fff;
    }
    .size-guide-modal__select {
        width: 100%;
        min-height: 48px;
        padding: 12px 40px 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        color: #111827;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%234b5563'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 12px center / 20px;
        appearance: none;
        cursor: pointer;
    }
    .size-guide-modal__select:focus {
        outline: none;
        border-color: #005366;
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.12);
    }
    .size-guide-table-card {
        padding: 14px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #f9fafb;
    }
    @media (min-width: 768px) {
        .size-guide-table-card {
            padding: 16px;
        }
    }
    .size-guide-table-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .size-guide-table-card__title {
        margin: 0;
        font-size: 0.9375rem;
        font-weight: 700;
        color: #111827;
    }
    .size-guide-unit {
        display: inline-flex;
        padding: 4px;
        border: 1px solid #e5e7eb;
        border-radius: 9999px;
        background: #fff;
    }
    .size-guide-unit__btn {
        padding: 6px 14px;
        border: 0;
        border-radius: 9999px;
        background: transparent;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #4b5563;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .size-guide-unit__btn.is-active {
        background: #005366;
        color: #fff;
    }
    .size-guide-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0 -2px;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #e5e7eb;
    }
    .size-guide-table {
        width: 100%;
        min-width: 520px;
        border-collapse: collapse;
        font-size: 0.8125rem;
    }
    .size-guide-table thead th {
        padding: 10px 12px;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: #6b7280;
        white-space: nowrap;
    }
    .size-guide-table thead th:first-child {
        text-align: left;
        position: sticky;
        left: 0;
        z-index: 2;
        background: #f3f4f6;
    }
    .size-guide-table__row td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        text-align: center;
        color: #4b5563;
        white-space: nowrap;
    }
    .size-guide-table__row:last-child td {
        border-bottom: 0;
    }
    .size-guide-table__measure {
        position: sticky;
        left: 0;
        z-index: 1;
        text-align: left !important;
        font-weight: 600;
        color: #111827 !important;
        background: #fff;
        box-shadow: 4px 0 8px -4px rgba(0, 0, 0, 0.08);
    }
    .size-guide-table__empty {
        padding: 24px 16px;
        text-align: center;
        font-size: 0.875rem;
        color: #6b7280;
    }
    .size-guide-table-scroll-hint {
        display: block;
        margin-top: 8px;
        font-size: 0.75rem;
        color: #9ca3af;
        text-align: center;
    }
    @media (min-width: 768px) {
        .size-guide-table-scroll-hint {
            display: none;
        }
    }
    .product-show-options__swatches,
    .product-show-options__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .product-show-purchase .color-swatch {
        position: relative;
        box-sizing: border-box;
        width: 32px;
        height: 32px;
        padding: 0;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: transparent;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .product-show-purchase .color-swatch:hover {
        transform: translateY(-1px);
    }
    .product-show-purchase .color-swatch.is-selected,
    .product-show-purchase .color-swatch.border-\[\#005366\] {
        border-color: #e5e7eb;
    }
    .product-show-purchase .color-swatch > span {
        display: block;
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 8px;
    }
    .product-show-purchase .color-swatch__check {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 14px;
        height: 14px;
        color: #fff;
        opacity: 0;
        pointer-events: none;
        transform: translate(-50%, -50%);
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.35));
    }
    .product-show-purchase .color-swatch.is-selected .color-swatch__check,
    .product-show-purchase .color-swatch.border-\[\#005366\] .color-swatch__check {
        opacity: 1;
    }
    .product-show-purchase .attribute-option {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        min-height: 32px;
        padding: 0 8px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.3;
        color: #111827;
        cursor: pointer;
        flex: none;
        transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }
    .product-show-purchase .attribute-option:hover {
        border-color: #005366;
    }
    .product-show-purchase .attribute-option.border-\[\#005366\] {
        border-color: #005366;
        background: #005366;
        color: #fff;
    }
    .product-show-options__summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 12px;
        border-radius: 8px;
        background: #f7f7f7;
    }
    .product-show-options__summary-name {
        margin: 0;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #111827;
    }
    .product-show-options__summary-meta {
        margin: 2px 0 0;
        font-size: 0.75rem;
        color: #4b5563;
    }
    .product-show-options__summary-price {
        font-size: 0.8125rem;
        font-weight: 700;
        color: #e2150c;
        white-space: nowrap;
    }
    .product-show-purchase__panel {
        margin-top: 0;
        padding: 0;
        border: none;
        border-radius: 0;
        background: transparent;
    }
    .product-show-purchase__shop {
        margin: 0 0 16px;
        font-size: 0.875rem;
        color: #4b5563;
    }
    .product-show-purchase__shop a {
        color: #005366;
        font-weight: 600;
        text-decoration: none;
    }
    .product-show-purchase__shop a:hover {
        text-decoration: underline;
    }
    .product-show-purchase__price {
        margin-bottom: 0;
    }
    .product-show-purchase__actions-bar {
        margin-top: 16px;
    }
    @media (max-width: 1023px) {
        .product-show-purchase__actions-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            margin: 0;
            padding: 16px;
            background: #fff;
            border-top: 1px solid #e5e7eb;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.08);
        }
    }
    .product-show-purchase__actions-inner {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .product-show-purchase__qty {
        display: flex;
        align-items: center;
        height: 48px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        flex-shrink: 0;
    }
    .product-show-purchase__qty-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 100%;
        color: #4b5563;
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease, background 0.2s ease;
    }
    .product-show-purchase__qty-btn:hover {
        color: #005366;
        background: #f7f7f7;
    }
    .product-show-purchase__qty-value {
        width: 32px;
        text-align: center;
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
    }
    .product-show-purchase__actions {
        display: flex;
        flex: 1;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }
    .product-show-purchase__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex: 1;
        min-width: 0;
        min-height: 48px;
        padding: 0 16px;
        border-radius: 9999px;
        font-size: 0.9375rem;
        font-weight: 600;
        white-space: nowrap;
        transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .product-show-purchase__btn--cart {
        background: #fff;
        color: #005366;
        border: 1.5px solid #005366;
        flex: 0 1 38%;
    }
    .product-show-purchase__btn--cart:hover {
        background: rgba(0, 83, 102, 0.06);
        color: #003d4d;
    }
    .product-show-purchase__btn--buy {
        background: #e2150c;
        color: #fff;
        border: none;
        border-radius: 9999px;
        flex: 1 1 62%;
    }
    .product-show-purchase__btn--buy:hover {
        background: #c0120a;
        box-shadow: 0 4px 12px rgba(226, 21, 12, 0.28);
    }
    .product-show-purchase__wishlist-inline {
        display: none;
    }
    .product-show-purchase__shop-divider {
        margin: 16px 0 0;
        border: 0;
        border-top: 1px solid #e5e7eb;
    }
    .product-show-purchase__promo-divider {
        margin: 20px 0 16px;
        border: 0;
        border-top: 1px solid #e5e7eb;
    }
    .product-show-purchase .product-show-listing {
        margin-top: 16px;
    }
    .product-show-purchase .product-show-facts {
        margin-top: 0;
        padding-top: 0;
    }
    .product-show-purchase__shop-card {
        margin-top: 16px;
        padding: 14px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fafafa;
        min-width: 0;
    }
    @media (max-width: 1023px) {
        .product-show-purchase__shop-card {
            margin-bottom: 8px;
            background: #fff;
        }
    }
    .product-show-purchase__shop-eyebrow {
        margin: 0 0 10px;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #6b7280;
    }
    .product-show-purchase__shop-body {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        min-width: 0;
    }
    .product-show-purchase__shop-info-link {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1 1 auto;
        min-width: 0;
        text-decoration: none;
        color: inherit;
    }
    .product-show-purchase__shop-info-link:hover .product-show-purchase__shop-name {
        color: #005366;
    }
    .product-show-purchase__shop-copy {
        min-width: 0;
        flex: 1;
    }
    .product-show-purchase__shop-name {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.3;
        color: #111827;
        overflow-wrap: anywhere;
        transition: color 0.15s ease;
    }
    .product-show-purchase__shop-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .product-show-purchase__shop-message {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 40px;
        height: 40px;
        padding: 0;
        border: 1px solid #d1d5db;
        border-radius: 9999px;
        background: #fff;
        color: #005366;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }
    .product-show-purchase__shop-message-label {
        display: none;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1;
    }
    .product-show-purchase__shop-message:hover {
        background: #f0f9fb;
        border-color: #005366;
    }
    .product-show-purchase__shop-message svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }
    .product-show-purchase__shop-visit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        flex-shrink: 0;
        padding: 0.5rem 0.875rem;
        min-height: 40px;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.2;
        color: #005366;
        background: #fff;
        border: 1.5px solid #005366;
        border-radius: 9999px;
        text-decoration: none;
        white-space: nowrap;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .product-show-purchase__shop-visit:hover {
        background: #005366;
        color: #fff;
    }
    .product-show-purchase__shop-visit svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }
    .product-show-purchase__shop-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 9999px;
        background: #005366;
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }
    .product-show-purchase__shop-avatar--photo {
        object-fit: cover;
        background: #f3f4f6;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #e5e7eb;
    }
    .product-show-purchase__shop-stats {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px 6px;
        margin-top: 3px;
        font-size: 0.8125rem;
        color: #4b5563;
        line-height: 1.35;
    }
    .product-show-purchase__shop-rating {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-weight: 600;
        color: #111827;
    }
    .product-show-purchase__shop-star {
        width: 14px;
        height: 14px;
        color: #f59e0b;
    }
    .product-show-purchase__shop-rating-count {
        font-weight: 500;
        color: #6b7280;
    }
    .product-show-purchase__shop-stat-sep {
        color: #9ca3af;
    }
    .product-show-purchase__shop-trust {
        margin: 4px 0 0;
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.35;
    }
    @media (max-width: 767px) {
        .product-show-purchase__shop-card {
            padding: 12px;
            border-radius: 14px;
        }
        .product-show-purchase__shop-eyebrow {
            margin-bottom: 8px;
        }
        .product-show-purchase__shop-body {
            flex-direction: column;
            align-items: stretch;
            gap: 0;
        }
        .product-show-purchase__shop-info-link {
            align-items: center;
            gap: 10px;
        }
        .product-show-purchase__shop-avatar {
            width: 44px;
            height: 44px;
            font-size: 0.9375rem;
        }
        .product-show-purchase__shop-name {
            font-size: 0.9375rem;
        }
        .product-show-purchase__shop-stats {
            font-size: 0.75rem;
        }
        .product-show-purchase__shop-sold-detail {
            display: none;
        }
        .product-show-purchase__shop-actions {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 8px;
            width: 100%;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
        }
        .product-show-purchase__shop-message {
            width: auto;
            min-width: 44px;
            height: 44px;
            padding: 0 14px;
        }
        .product-show-purchase__shop-message-label {
            display: inline;
        }
        .product-show-purchase__shop-visit {
            width: 100%;
            min-height: 44px;
            padding: 0.625rem 1rem;
        }
    }
    .product-show-purchase__inline-section {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }
    .product-show-purchase__section-title {
        margin: 0 0 12px;
        font-size: 1.125rem;
        font-weight: 700;
        color: #111827;
    }
    .product-show-purchase__shipping {
        margin-top: 12px;
        padding: 12px;
        border-radius: 12px;
        background: #f7f7f7;
    }
    @media (min-width: 768px) {
        .product-show-purchase__shipping {
            background: #fff;
            border: 1px solid #e5e7eb;
        }
    }
    .product-show-purchase__meta-list {
        margin: 16px 0 0;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }
    .product-show-purchase__meta-list-row {
        display: grid;
        grid-template-columns: 72px 1fr;
        gap: 8px 16px;
        align-items: start;
        margin-bottom: 12px;
    }
    .product-show-purchase__meta-list-row:last-child {
        margin-bottom: 0;
    }
    .product-show-purchase__meta-list-row dt {
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
    }
    .product-show-purchase__meta-list-row dd {
        margin: 0;
        font-size: 0.875rem;
        color: #4b5563;
    }
    .product-show-purchase__meta {
        margin: 20px 0 0;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }
    .product-show-purchase__tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .product-show-purchase__tag {
        display: inline-flex;
        padding: 4px 10px;
        border-radius: 9999px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        font-size: 0.8125rem;
        color: #4b5563;
        text-decoration: none;
        transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease;
    }
    .product-show-purchase__tag:hover {
        border-color: rgba(0, 83, 102, 0.25);
        background: rgba(0, 83, 102, 0.06);
        color: #005366;
    }
    .product-show-purchase__share-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        border: 1.5px solid #d1d5db;
        background: #fff;
        color: #4b5563;
        transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease;
    }
    .product-show-purchase__share-btn:hover {
        border-color: #005366;
        color: #005366;
        background: rgba(0, 83, 102, 0.06);
    }
    .product-show-tabs {
        margin-top: 32px;
        padding: 24px;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
    }
    .product-show-tabs--reviews-only {
        margin-top: 32px;
        padding: 24px;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
    }
    @media (min-width: 768px) {
        .product-show-tabs,
        .product-show-tabs--reviews-only {
            padding: 32px;
            border-radius: 16px;
        }
    }
    .product-show-tabs__tab {
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
        cursor: pointer;
        transition: color 0.2s ease, border-color 0.2s ease;
    }
    .product-show-tabs__tab--active {
        font-weight: 600;
    }
    .product-show-listing {
        margin-top: 8px;
        position: relative;
        z-index: 1;
        background: #fff;
        min-width: 0;
        max-width: 100%;
        overflow-x: hidden;
    }
    .product-show-facts {
        margin: 0;
        padding: 20px 0;
        list-style: none;
        border-top: 1px dashed #e5e7eb;
        border-bottom: 1px dashed #e5e7eb;
    }
    .product-show-facts__row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 8px 0;
        font-size: 1rem;
        line-height: 1.5;
        color: #111827;
    }
    .product-show-facts__row p {
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
    }
    .product-show-facts__row--deliver {
        width: 100%;
    }
    .product-show-facts__row--deliver p {
        flex: 1;
    }
    .product-show-facts__row strong {
        font-weight: 700;
    }
    .product-show-facts__icon {
        width: 20px;
        height: 20px;
        margin-top: 2px;
        flex-shrink: 0;
        color: #4b5563;
    }
    .product-show-facts__info {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        padding: 0;
        border: none;
        background: none;
        color: #4b5563;
        cursor: pointer;
    }
    .product-show-facts__info:hover,
    .product-show-facts__info:focus-visible {
        color: #005366;
    }
    .product-show-facts__info svg {
        width: 16px;
        height: 16px;
    }
    .product-show-facts__row--popup {
        display: block;
        padding: 0;
    }
    .product-show-facts__popup {
        margin: 0 0 8px 32px;
        padding: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        font-size: 0.875rem;
        line-height: 1.5;
        color: #4b5563;
    }
    .product-show-facts__popup a {
        display: inline-block;
        margin-top: 8px;
        color: #005366;
        font-weight: 600;
        text-decoration: none;
    }
    .product-show-facts__popup a:hover {
        text-decoration: underline;
    }
    .product-show-listing-tabs {
        padding: 8px 0 24px;
        border-bottom: 1px dashed #e5e7eb;
    }
    .product-show-listing-tabs__nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0 24px;
        border-bottom: 1px solid #e5e7eb;
    }
    .product-show-listing-tabs__tab {
        margin-bottom: -1px;
        padding: 12px 0;
        border: none;
        border-bottom: 2px solid transparent;
        background: none;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #4b5563;
        cursor: pointer;
    }
    .product-show-listing-tabs__tab:hover {
        color: #111827;
    }
    .product-show-listing-tabs__tab--active {
        font-weight: 700;
        color: #111827;
        border-bottom-color: #005366;
    }
    .product-show-listing-tabs__panels {
        padding-top: 16px;
        min-width: 0;
        max-width: 100%;
        overflow-x: hidden;
    }
    .product-show-description,
    .product-show-description__inner,
    .product-show-description__preview,
    .product-show-description__full,
    .product-show-description__section,
    .product-show-description__section-title,
    .product-show-description__section-text,
    .product-show-description__paragraph {
        min-width: 0;
        max-width: 100%;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .product-show-listing-tabs__copy,
    .product-show-listing-tabs .product-show-description__preview,
    .product-show-listing-tabs .product-show-description__paragraph,
    .product-show-listing-tabs .product-show-description__section-text {
        margin: 0;
        font-size: 1rem;
        line-height: 1.6;
        color: #111827;
    }
    .product-show-listing-tabs__guide {
        margin-top: 12px;
        padding: 0;
        border: none;
        background: none;
        font-size: 0.875rem;
        font-weight: 600;
        color: #005366;
        cursor: pointer;
        text-decoration: none;
    }
    .product-show-listing-tabs__guide:hover {
        text-decoration: underline;
    }

    /* ── Commerce pages (cart, checkout, success) ── */
    .commerce-page {
        min-width: 0;
        padding: 24px 0 48px;
        background: #f9fafb;
    }
    .commerce-page > .max-w-7xl {
        min-width: 0;
    }
    .commerce-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        transition: box-shadow 0.2s ease;
    }
    .commerce-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    }
    .commerce-summary {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
    }
    .discount-expiry-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 12px;
        padding: 8px 12px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 12px;
    }
    .discount-expiry-bar[hidden] {
        display: none;
    }
    .discount-expiry-bar__label {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        color: #9b2c2c;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.4;
    }
    .discount-expiry-bar__label svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: #e2150c;
    }
    .discount-expiry-bar__timer {
        flex-shrink: 0;
        background: #fff;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 4px 10px;
        color: #e2150c;
        font-size: 0.875rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        line-height: 1.3;
        min-width: 3.5rem;
        text-align: center;
    }
    .commerce-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }
    .commerce-panel__head {
        background: #005366;
        color: #fff;
        padding: 20px 24px;
    }
    .commerce-panel__head h2 {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.3;
        margin: 0;
    }
    .commerce-panel__head p {
        font-size: 0.875rem;
        opacity: 0.85;
        margin: 4px 0 0;
    }
    .commerce-panel__body {
        padding: 24px;
    }
    .commerce-section-title {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 16px;
    }
    .commerce-section-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(0, 83, 102, 0.1);
        color: #005366;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .commerce-field {
        width: 100%;
        min-height: 48px;
        padding: 12px 16px;
        font-size: 16px;
        line-height: 1.5;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .commerce-field:focus {
        outline: none;
        border-color: #005366;
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.15);
    }
    textarea.commerce-field {
        min-height: 100px;
        resize: vertical;
    }
    .commerce-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #f3f4f6;
        color: #4b5563;
    }
    .commerce-empty {
        text-align: center;
        padding: 48px 24px;
    }
    .btn-cta--block {
        display: flex;
        width: 100%;
        justify-content: center;
        align-items: center;
        gap: 8px;
        border-radius: 9999px;
        padding: 1rem 1.75rem;
        text-decoration: none;
    }
    .btn-outline-petrol--block {
        display: flex;
        width: 100%;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        border-radius: 9999px;
        padding: 0.875rem 1.75rem;
    }
    .commerce-success-hero {
        text-align: center;
        margin-bottom: 32px;
    }
    .commerce-success-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #16a34a;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
    }
    .commerce-order-chip {
        display: inline-block;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px 32px;
    }
    .commerce-info-panel {
        background: rgba(0, 83, 102, 0.06);
        border: 1px solid rgba(0, 83, 102, 0.12);
        border-radius: 12px;
        padding: 24px;
    }
    .commerce-line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .commerce-page .checkout-container,
    .commerce-page .order-summary-container {
        box-shadow: none;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }
    .commerce-page .StripeElement--focus {
        border-color: #005366 !important;
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.15) !important;
    }
    .commerce-security-note {
        margin-top: 24px;
        padding: 16px 20px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 16px;
        color: #166534;
    }
    .commerce-security-note__icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #dcfce7;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #16a34a;
    }
    @media (max-width: 640px) {
        .commerce-panel__body,
        .commerce-summary {
            padding: 16px;
        }
        .commerce-card {
            padding: 16px;
        }
    }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    @if($googleTagManagerId)
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $googleTagManagerId }}"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
    @endif
    
    <div class="min-h-screen" style="overflow-x: clip;">
        <!-- Header Component -->
        <x-header />

        <!-- Email Verification Notice -->
        @auth
            @if(!auth()->user()->hasVerifiedEmail())
                <div class="bg-[#e2150c] text-white">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                        <div class="flex items-center justify-between flex-wrap">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-sm md:text-base font-medium">
                                    Please verify your email address to access all features.
                                </p>
                            </div>
                            <div class="flex items-center space-x-3 mt-2 sm:mt-0">
                                <a href="{{ route('verification.notice') }}" class="text-sm font-semibold underline hover:text-white/80 transition">
                                    Click here to verify
                                </a>
                                <form method="POST" action="{{ route('verification.send') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm font-semibold bg-white text-[#005366] px-4 py-1.5 rounded-full hover:bg-white/90 transition">
                                        Resend Email
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endauth

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <button type="button" id="site-back-to-top" class="site-back-to-top" aria-label="Back to top">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
            <span class="site-back-to-top__label">Back to top</span>
        </button>

        <!-- Footer -->
        @php
            $footerNav = [
                'Company' => [
                    ['label' => 'About Us', 'url' => '/page/about-us'],
                    ['label' => 'Privacy Policy', 'url' => '/page/privacy-policy'],
                    ['label' => 'Terms of Service', 'url' => '/page/terms-of-service'],
                    ['label' => 'Secure Payments', 'url' => '/page/secure-payments'],
                    ['label' => 'Contact Us', 'url' => '/contact-us'],
                    ['label' => 'Help Center', 'url' => '/help-center'],
                    ['label' => 'Sitemap', 'url' => '/sitemap'],
                ],
                'Get Help' => [
                    ['label' => 'FAQs', 'url' => '/page/faqs'],
                    ['label' => 'Order Tracking', 'url' => '/order-tracking'],
                    ['label' => 'Shipping & Delivery', 'url' => '/shipping-delivery'],
                    ['label' => 'Cancel/Change Order', 'url' => '/page/cancelchange-order'],
                    ['label' => 'Refund Policy', 'url' => '/page/refund-policy'],
                    ['label' => 'Returns & Exchanges', 'url' => '/page/returns-exchanges-policy'],
                    ['label' => 'DMCA', 'url' => '/page/dmca'],
                    ['label' => 'IP Policy', 'url' => '/page/our-intellectual-property-policy'],
                ],
                'Shop' => [
                    ['label' => 'Bulk Order', 'url' => '/bulk-order'],
                    ['label' => 'Promo Code', 'url' => '/promo-code'],
                    ['label' => 'Sell on Bluprinter', 'url' => route('seller.apply')],
                ],
            ];

            $footerSocial = [
                ['label' => 'Facebook', 'url' => 'https://www.facebook.com/profile.php?id=61571564261584', 'icon' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
                ['label' => 'Instagram', 'url' => 'https://www.instagram.com/blu.printer', 'icon' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z'],
                ['label' => 'YouTube', 'url' => 'https://www.youtube.com/@BLUPRINTER', 'icon' => 'M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z'],
                ['label' => 'TikTok', 'url' => 'https://www.tiktok.com/@blu.printer', 'icon' => 'M16.5 3.5c.8 1.6 2.2 2.8 4 3.2v3.4c-1.5-.1-2.9-.6-4.1-1.4v6.3c0 3.4-2.8 6.2-6.2 6.2S4 18.4 4 15s2.8-6.2 6.2-6.2c.3 0 .7 0 1 .1v3.5c-.3-.1-.6-.1-.9-.1-1.5 0-2.7 1.2-2.7 2.7s1.2 2.7 2.7 2.7 2.7-1.2 2.7-2.7V3.5h2.5z'],
                ['label' => 'Pinterest', 'url' => 'https://www.pinterest.com/bluprinter/', 'icon' => 'M12 2a10 10 0 00-3.5 19.4c-.1-.9-.2-2.2.5-3.2.5-.8 1.6-3.4 1.6-3.4s-.4-.8-.4-2c0-1.9 1.1-3.3 2.5-3.3 1.2 0 1.7.9 1.7 2s-.6 2.6-.9 4c-.3 1.2.6 2.2 1.8 2.2 2.1 0 3.8-2.7 3.8-6.6 0-2.7-1.8-4.7-5.1-4.7-3.8 0-6.1 2.8-6.1 6 0 1.1.4 2.3 1 2.9.1.1.1.2.1.3l-.4 1.5c0 .2-.1.2-.3.1-1.1-.5-1.8-2.1-1.8-3.8 0-3.1 2.6-7 7.7-7 4.2 0 6.9 3 6.9 6.9 0 4.3-2.7 7.6-6.5 7.6-1.3 0-2.5-.7-2.9-1.5l-.8 3c-.3 1.1-1.1 2.5-1.6 3.3A10 10 0 1012 2z'],
                ['label' => 'X', 'url' => 'https://x.com/Bluprinter25', 'icon' => 'M4 4l7.2 9.4L4.5 20h2.3l5.4-6.3L16.8 20H20l-7.6-9.9L19 4h-2.3l-5 5.8L7.5 4H4z'],
            ];
        @endphp

        <footer class="site-footer">
            {{-- Newsletter --}}
            <div class="site-footer__newsletter">
                <div class="site-footer__newsletter-inner">
                    <div class="site-footer__newsletter-card">
                        <div>
                            <span class="site-footer__newsletter-eyebrow">Stay in the loop</span>
                            <h2 class="site-footer__newsletter-title">Never miss out on a moment</h2>
                            <p class="site-footer__newsletter-sub">
                                Get trends, exclusive offers, and secret perks delivered straight to your inbox.
                            </p>
                        </div>
                        <div>
                            <form class="site-footer__newsletter-form" id="newsletter-form">
                                @csrf
                                <input type="email"
                                       name="email"
                                       id="newsletter-email"
                                       placeholder="Your email address"
                                       required
                                       class="site-footer__newsletter-input"
                                       autocomplete="email">
                                <button type="submit" id="newsletter-submit" class="site-footer__newsletter-btn" aria-label="Subscribe to newsletter">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </form>
                            <div id="newsletter-message" class="mt-2 text-sm hidden"></div>
                            <p class="site-footer__newsletter-note">
                                By subscribing, you agree to our
                                <a href="/page/privacy-policy">Privacy Policy</a>
                                and promotional emails (opt out anytime).
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main --}}
            <div class="site-footer__main">
                <div class="site-footer__grid">
                    {{-- Brand --}}
                    <div class="site-footer__brand">
                        <a href="{{ url('/') }}" class="site-footer__brand-logo">
                            <img src="{{ asset('images/logo-header.png') }}"
                                 alt="Bluprinter"
                                 onerror="this.src='{{ asset('images/logo nhỏ.png') }}'">
                        </a>
                        <p class="site-footer__brand-desc">
                            A global marketplace where makers and buyers connect. No warehouse — just independent sellers and unique designs you'll love.
                        </p>

                        <div class="site-footer__social" aria-label="Social media">
                            @foreach ($footerSocial as $social)
                                <a href="{{ $social['url'] }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="site-footer__social-link"
                                   aria-label="{{ $social['label'] }}">
                                    <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="{{ $social['icon'] }}"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>

                        <div class="site-footer__actions">
                            <a href="/support/ticket" class="site-footer__action">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 19h14M5 15h14"/></svg>
                                Submit Ticket
                            </a>
                            <a href="/support/request" class="site-footer__action">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg>
                                Submit Request
                            </a>
                            <a href="/bulk-order" class="site-footer__action">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                                Bulk Order
                            </a>
                        </div>
                    </div>

                    {{-- Link columns --}}
                    <div class="site-footer__nav">
                        @foreach ($footerNav as $title => $links)
                            <div class="site-footer__nav-col">
                                <h3 class="site-footer__col-title">{{ $title }}</h3>
                                <ul class="site-footer__links">
                                    @foreach ($links as $link)
                                        <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="site-footer__trust">
                    <a href="https://www.dmca.com/Protection/Status.aspx?id=7afce096-ea62-47a0-8c3b-a3fbd663bf4d&refurl=https%3a%2f%2fbluprinter.com%2f&rlo=true"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="site-footer__trust-badge">
                        <img src="https://images.dmca.com/Badges/DMCA_logo-grn-btn150w.png?ID=005e124c-c682-4f1d-a564-1bc657921504" alt="DMCA Protected">
                    </a>
                    <a href="https://www.trustpilot.com/review/bluprinter.com"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="site-footer__trust-badge">
                        ★ Trustpilot Reviews
                    </a>
                </div>

                <details class="site-footer__legal">
                    <summary>Company &amp; warehouse information</summary>
                    <div class="site-footer__legal-body">
                        <p><strong>The website is jointly operated by:</strong></p>
                        <p>• HM FULFILL COMPANY LIMITED – 63/9Đ, Ap Chanh 1, Tan Xuan, Hoc Mon, Ho Chi Minh City 700000, Vietnam</p>
                        <p>• BLUE STAR TRADING LIMITED – RM C, 6/F, WORLD TRUST TOWER, 50 STANLEY STREET, CENTRAL, HONG KONG</p>
                        <p>• Bluprinter LTD (UK) – Company No. 16342615, 71-75 Shelton Street, Covent Garden, London WC2H 9JQ</p>
                        <p>• Bluprinter LLC (US) – 5900 BALCONES DR STE 100, AUSTIN, TX 78731, USA</p>
                        <p><strong>US Warehouse:</strong> 1301 E ARAPAHO RD, STE 101 RICHARDSON, TX 75081, USA</p>
                        <p><strong>UK Warehouse:</strong> 3 Kincraig Rd, Blackpool FY2 0FY, United Kingdom</p>
                    </div>
                </details>
            </div>

            {{-- Bottom bar --}}
            <div class="site-footer__bottom">
                <div class="site-footer__bottom-inner">
                    <div class="site-footer__locale">
                        <img src="https://flagcdn.com/w20/vn.png" alt="" width="20" height="15" loading="lazy">
                        <span>Vietnam</span>
                    </div>

                    <p class="site-footer__copyright">© {{ date('Y') }} Bluprinter. All rights reserved.</p>

                    <div class="site-footer__payments" aria-label="Accepted payment methods">
                        <span class="site-footer__payment site-footer__payment--amex">AMEX</span>
                        <span class="site-footer__payment site-footer__payment--visa">VISA</span>
                        <span class="site-footer__payment site-footer__payment--mc">MC</span>
                        <span class="site-footer__payment site-footer__payment--pp">PayPal</span>
                        <span class="site-footer__payment site-footer__payment--apple">Apple Pay</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Newsletter Subscription JavaScript -->
    <script>
        (function () {
            var form = document.getElementById('newsletter-form');
            if (!form) return;

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                var emailInput = document.getElementById('newsletter-email');
                var button = document.getElementById('newsletter-submit');
                var email = (emailInput && emailInput.value ? emailInput.value : '').trim();
                var originalText = button.innerHTML;
                var csrfMeta = document.querySelector('meta[name="csrf-token"]');

                if (!email || !/\S+@\S+\.\S+/.test(email)) {
                    showMessage('Please enter a valid email address', 'error');
                    return;
                }

                if (!csrfMeta || !csrfMeta.content) {
                    showMessage('Session expired. Please refresh the page and try again.', 'error');
                    return;
                }

                button.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                button.disabled = true;

                fetch('{{ route("newsletter.subscribe") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfMeta.content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ email: email })
                })
                .then(function (response) {
                    return response.json().catch(function () {
                        return { success: false, message: 'Unexpected server response. Please try again.' };
                    }).then(function (data) {
                        if (!response.ok && data && data.message) {
                            data.success = false;
                        }
                        return data;
                    });
                })
                .then(function (data) {
                    if (data.success) {
                        var message = data.message || 'Thank you for subscribing!';
                        if (data.code) {
                            message += ' Your code: ' + data.code;
                        }
                        showMessage(message, 'success');
                        if (emailInput) emailInput.value = '';
                    } else {
                        showMessage(data.message || 'Something went wrong. Please try again later.', 'error');
                    }
                })
                .catch(function (error) {
                    console.error('Newsletter subscription error:', error);
                    showMessage('Something went wrong. Please try again later.', 'error');
                })
                .finally(function () {
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            });

            function showMessage(message, type) {
                var messageDiv = document.getElementById('newsletter-message');
                if (!messageDiv) return;
                messageDiv.textContent = message;
                messageDiv.className = 'mt-2 text-sm ' + (type === 'success' ? 'text-green-400' : 'text-red-400');
                messageDiv.classList.remove('hidden');
                setTimeout(function () {
                    messageDiv.classList.add('hidden');
                }, 8000);
            }
        })();
    </script>

    <script>
    (function () {
        var btn = document.getElementById('site-back-to-top');
        if (!btn) return;

        var showThreshold = 320;
        var ticking = false;

        function updateBackToTop() {
            var visible = window.scrollY > showThreshold;
            btn.classList.toggle('is-visible', visible);
        }

        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    updateBackToTop();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        updateBackToTop();
    })();
    </script>
    
    <!-- Wishlist JavaScript -->
    <script src="{{ asset('js/wishlist.js') }}"></script>
    <x-try-on-modal />
</body>
</html>
