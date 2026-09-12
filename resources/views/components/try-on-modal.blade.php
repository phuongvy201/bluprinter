@php
    $tryOnAi = \App\Support\StudioAiSettings::resolved();
    $tryOnEnabled = app(\App\Services\StudioAiService::class)->isEnabled() && ! empty($tryOnAi['try_on_enabled']);
    $tryOnTimeout = (int) ($tryOnAi['timeout'] ?? 90);
    $stockModels = \App\Support\StudioAiSettings::tryOnModels();
    $bannerModelSrc = $stockModels[1]['src'] ?? ($stockModels[0]['src'] ?? '');
@endphp

<link rel="stylesheet" href="{{ asset('css/try-on-modal.css') }}?v={{ @filemtime(public_path('css/try-on-modal.css')) }}">

<div class="vto-modal" id="vto-modal" hidden>
    <div class="vto-modal__backdrop" data-vto-close></div>
    <div class="vto-modal__panel" role="dialog" aria-modal="true" aria-labelledby="vto-title" tabindex="-1">
        <div class="vto-modal__head">
            <h2 class="vto-modal__title" id="vto-title">Start Virtual Try-On</h2>
            <button type="button" class="vto-modal__close" data-vto-close aria-label="Close">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="vto-modal__body">
        <div class="vto-banner">
            <div class="vto-banner__art" aria-hidden="true">
                <span class="vto-banner__shot vto-banner__shot--product">
                    <img id="vto-banner-product" src="{{ $stockModels[0]['src'] ?? '' }}" alt="">
                </span>
                <span class="vto-banner__shot vto-banner__shot--model">
                    <img src="{{ $bannerModelSrc }}" alt="">
                </span>
            </div>
            <ol class="vto-steps">
                <li>
                    <span class="vto-steps__n" aria-hidden="true">1</span>
                    <div>
                        <strong>Select or Upload a Model</strong>
                        <p>Choose a model or upload your own to try on the outfit.</p>
                    </div>
                </li>
                <li>
                    <span class="vto-steps__n" aria-hidden="true">2</span>
                    <div>
                        <strong>Generate the Outfit Preview</strong>
                        <p>Click “Generate” to see the outfit come to life on the model.</p>
                    </div>
                </li>
            </ol>
        </div>

        <div class="vto-models">
            <div class="vto-models__track" id="vto-models-track">
                <button type="button" class="vto-tile vto-tile--upload" id="vto-upload">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14M5 12h14"/></svg>
                    Upload now
                </button>
                @foreach ($stockModels as $model)
                    <button type="button" class="vto-tile" data-photo="{{ $model['src'] }}" title="{{ $model['label'] }}">
                        <img src="{{ $model['src'] }}" alt="{{ $model['label'] }}">
                        <span class="vto-tile__mark" aria-hidden="true">&times;</span>
                    </button>
                @endforeach
            </div>
            <button type="button" class="vto-models__next" id="vto-models-next" aria-label="More models">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
            <input type="file" id="vto-file" accept="image/png,image/jpeg,image/webp" hidden>
        </div>

        <h3 class="vto-section-title">Try-On History</h3>
        <div class="vto-history">
            <p class="vto-history__empty" id="vto-history-empty">Your generated looks will show up here.</p>
            <div class="vto-history__row" id="vto-history-row"></div>
        </div>

        <h3 class="vto-section-title">Tips for uploading images</h3>
        <div class="vto-tips-grid">
            <figure class="vto-tips-card">
                <div class="vto-tips-card__media">
                    <img src="{{ asset('images/studio/tips/full-body.png') }}" alt="Full-body example">
                    <span class="vto-tips-card__badge vto-tips-card__badge--yes" aria-hidden="true">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L8.75 11.836l6.543-6.543a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </span>
                </div>
                <figcaption>Full-body</figcaption>
            </figure>
            <figure class="vto-tips-card">
                <div class="vto-tips-card__media">
                    <img src="{{ asset('images/studio/tips/multiple-people.png') }}" alt="Multiple people example">
                    <span class="vto-tips-card__badge vto-tips-card__badge--no" aria-hidden="true">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </span>
                </div>
                <figcaption>Multiple People</figcaption>
            </figure>
        </div>
        </div>

        <div class="vto-result-stage" id="vto-result" hidden>
            <img id="vto-result-img" alt="Try-on result">
        </div>

        <div class="vto-modal__foot" id="vto-setup-foot">
        <div class="vto-view" id="vto-view" hidden role="radiogroup" aria-label="Garment side">
            <label>
                <input type="radio" name="vto-view" value="front" checked>
                Front
            </label>
            <label>
                <input type="radio" name="vto-view" value="back">
                Back
            </label>
        </div>

        @if ($tryOnEnabled)
            <button type="button" class="btn-cta vto-generate" id="vto-generate">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2.2l1.35 4.1 4.15 1.25-4.15 1.25L12 12.9l-1.35-4.1L6.5 7.55l4.15-1.25L12 2.2zM18.5 13.2l.7 2.1 2.1.65-2.1.65-.7 2.1-.7-2.1-2.1-.65 2.1-.65.7-2.1zM5.4 14.4l.85 2.55 2.55.8-2.55.8-.85 2.55-.85-2.55-2.55-.8 2.55-.8.85-2.55z"/>
                </svg>
                GENERATE
            </button>
        @else
            <p class="vto-offline">AI try-on is currently turned off.</p>
            <button type="button" class="btn-cta vto-generate" id="vto-generate" disabled>GENERATE</button>
        @endif
        <div class="vto-toast" id="vto-toast" role="status"></div>
        </div>

        <div class="vto-modal__foot vto-result-foot" id="vto-result-foot" hidden>
            <button type="button" class="vto-result-back" id="vto-result-back">BACK</button>
            <button type="button" class="btn-cta vto-result-cart" id="vto-result-cart">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13l-1.2 6h12.4M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"/>
                </svg>
                ADD TO CART
            </button>
        </div>
    </div>
</div>
@include('studio.partials.ai-progress')

<script>
    window.TRYON_MODAL = {
        upload: @json(route('studio.upload')),
        generate: @json(route('studio.try-on.generate')),
        csrf: @json(csrf_token()),
        enabled: @json($tryOnEnabled),
        timeout: {{ $tryOnTimeout }},
    };
</script>
<script src="{{ asset('js/studio-ai-progress.js') }}?v={{ @filemtime(public_path('js/studio-ai-progress.js')) }}"></script>
<script src="{{ asset('js/try-on-modal.js') }}?v={{ @filemtime(public_path('js/try-on-modal.js')) }}"></script>
