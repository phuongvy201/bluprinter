@php
    $aiRedesignEnabled = app(\App\Services\StudioAiService::class)->isEnabled();
    $aiRedesignSettings = \App\Support\StudioAiSettings::resolved();
    $aiRedesignBaseImage = $tryOnMedia['image'] ?? ($firstMediaUrl ?? '');
    $aiRedesignSuggestions = [
        'Rock style, bold & cool',
        'Black and white print',
        'Put my photo on the shirt',
        'Vintage poster look',
        'Minimal line art',
    ];
@endphp

@if($aiRedesignEnabled && filled($aiRedesignBaseImage))
<section
    class="product-show-ai-custom"
    id="product-show-ai-custom"
    data-product-name="{{ $product->name }}"
    data-product-id="{{ $product->id }}"
    data-base-image="{{ $aiRedesignBaseImage }}"
    data-timeout="{{ (int) ($aiRedesignSettings['timeout'] ?? 90) }}"
    data-prompt-max="{{ (int) ($aiRedesignSettings['prompt_max'] ?? 1200) }}"
>
    <div class="product-show-ai-custom__intro">
        <button
            type="button"
            id="ai-redesign-toggle"
            class="product-show-ai-custom__chip"
            aria-expanded="false"
            aria-controls="ai-redesign-panel"
            title="Redesign this product with AI"
        >
            <svg class="product-show-ai-custom__chip-icon" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2 2-6z"/>
            </svg>
            <span class="product-show-ai-custom__chip-label">AI redesign</span>
            <span id="ai-redesign-chip-badge" class="product-show-ai-custom__chip-badge" hidden>On</span>
            <svg class="product-show-ai-custom__chip-caret" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <p class="product-show-ai-custom__blurb">
            Upload your photo — AI redesigns this product’s print for you. Free to try.
        </p>
    </div>

    <div id="ai-redesign-panel" class="product-show-ai-custom__panel" hidden>
        <img id="ai-redesign-mockup-thumb" src="{{ $aiRedesignBaseImage }}" alt="" class="sr-only" aria-hidden="true">

        <div class="product-show-ai-custom__row">
            <span class="product-show-ai-custom__label">Your photo <span class="product-show-ai-custom__opt">optional</span></span>
            <label
                for="ai-redesign-photo"
                id="ai-redesign-dropzone"
                class="product-show-ai-custom__drop"
            >
                <input
                    type="file"
                    id="ai-redesign-photo"
                    accept="image/jpeg,image/jpg,image/png,image/webp"
                    class="product-show-ai-custom__file-input"
                >
                <span class="product-show-ai-custom__drop-idle" data-ai-drop-idle>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Drop photo or tap to upload</span>
                </span>
                <span id="ai-redesign-photo-preview" class="product-show-ai-custom__drop-preview" hidden>
                    <img id="ai-redesign-photo-thumb" src="" alt="Your photo">
                    <button type="button" id="ai-redesign-photo-remove" class="product-show-ai-custom__drop-x" aria-label="Remove photo">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </label>
        </div>

        <div class="product-show-ai-custom__row">
            <label class="product-show-ai-custom__label" for="ai-redesign-prompt">Describe the look</label>
            <div class="product-show-ai-custom__suggestions" role="list">
                @foreach($aiRedesignSuggestions as $suggestion)
                    <button type="button" class="product-show-ai-custom__suggestion" data-ai-suggestion="{{ $suggestion }}" role="listitem">
                        {{ $suggestion }}
                    </button>
                @endforeach
            </div>
            <textarea
                id="ai-redesign-prompt"
                rows="2"
                maxlength="{{ (int) ($aiRedesignSettings['prompt_max'] ?? 1200) }}"
                placeholder="e.g. Put my photo on this shirt in a cool rock style, black and white"
                class="product-show-ai-custom__textarea"
            ></textarea>
        </div>

        <div class="product-show-ai-custom__result" id="ai-redesign-result" hidden>
            <button type="button" class="product-show-ai-custom__result-btn" data-ai-result-open aria-label="View AI result on product">
                <img id="ai-redesign-result-thumb" src="" alt="AI result">
                <span>
                    <strong>New mockup ready</strong>
                    <em>Shown in the gallery — tap to enlarge</em>
                </span>
            </button>
        </div>

        <div class="product-show-ai-custom__actions">
            <button type="button" id="ai-redesign-generate" class="product-show-ai-custom__btn product-show-ai-custom__btn--primary">
                <span class="product-show-ai-custom__btn-main">Generate design</span>
                <span class="product-show-ai-custom__btn-meta">Free</span>
            </button>
            <button
                type="button"
                id="ai-redesign-reset"
                class="product-show-ai-custom__btn product-show-ai-custom__btn--ghost"
                disabled
                title="Available after you generate a design"
            >
                Reset
            </button>
        </div>

        <p id="ai-redesign-status" class="product-show-ai-custom__status" role="status" hidden></p>
    </div>
</section>

<style>
    .product-show-ai-custom {
        margin-top: 0.875rem;
    }
    .product-show-ai-custom__intro {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.35rem;
    }
    .product-show-ai-custom__blurb {
        margin: 0;
        max-width: 22rem;
        font-size: 0.75rem;
        line-height: 1.4;
        color: #6b7280;
    }
    .product-show-ai-custom__chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        min-height: 2rem;
        padding: 0.25rem 0.7rem 0.25rem 0.55rem;
        border-radius: 9999px;
        border: 1px solid rgba(0, 83, 102, 0.35);
        background: linear-gradient(135deg, #f0f9fb 0%, #ffffff 55%, #fff5f4 100%);
        color: #005366;
        font-size: 0.8125rem;
        font-weight: 700;
        cursor: pointer;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
    }
    .product-show-ai-custom__chip:hover,
    .product-show-ai-custom__chip[aria-expanded="true"] {
        border-color: #005366;
        box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.12);
    }
    .product-show-ai-custom__chip.is-active {
        border-color: #005366;
        background: #005366;
        color: #fff;
    }
    .product-show-ai-custom__chip.is-active .product-show-ai-custom__chip-badge {
        background: #fff;
        color: #005366;
    }
    .product-show-ai-custom__chip-icon { flex-shrink: 0; }
    .product-show-ai-custom__chip-caret {
        flex-shrink: 0;
        margin-left: 0.1rem;
        transition: transform 0.15s ease;
    }
    .product-show-ai-custom__chip[aria-expanded="true"] .product-show-ai-custom__chip-caret {
        transform: rotate(180deg);
    }
    .product-show-ai-custom__chip-badge {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 0.1rem 0.35rem;
        border-radius: 9999px;
        background: #005366;
        color: #fff;
    }
    .product-show-ai-custom__chip-badge[hidden] { display: none !important; }

    .product-show-ai-custom__panel {
        margin-top: 0.65rem;
        padding: 0.85rem;
        border: 1px solid rgba(0, 83, 102, 0.22);
        border-left: 3px solid #005366;
        border-radius: 0.85rem;
        background:
            linear-gradient(180deg, rgba(0, 83, 102, 0.06) 0%, rgba(255, 255, 255, 0.95) 40%),
            #fff;
        box-shadow: 0 4px 14px rgba(0, 83, 102, 0.06);
    }
    .product-show-ai-custom__panel[hidden] { display: none !important; }

    .product-show-ai-custom__row { margin-bottom: 0.7rem; }
    .product-show-ai-custom__label {
        display: block;
        margin-bottom: 0.35rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #111827;
    }
    .product-show-ai-custom__opt {
        font-weight: 500;
        color: #9ca3af;
    }

    .product-show-ai-custom__drop {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 3.5rem;
        padding: 0.5rem;
        border: 1.5px dashed rgba(0, 83, 102, 0.35);
        border-radius: 0.65rem;
        background: rgba(255, 255, 255, 0.85);
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease;
    }
    .product-show-ai-custom__drop:hover,
    .product-show-ai-custom__drop.is-dragover {
        border-color: #005366;
        background: #f0f9fb;
    }
    .product-show-ai-custom__drop.has-photo {
        justify-content: flex-start;
        border-style: solid;
        border-color: rgba(0, 83, 102, 0.25);
        padding: 0.4rem;
        min-height: 0;
        cursor: default;
    }
    .product-show-ai-custom__drop.has-photo .product-show-ai-custom__file-input {
        pointer-events: none;
    }
    .product-show-ai-custom__file-input {
        position: absolute;
        inset: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
        font-size: 0;
        z-index: 1;
    }
    .product-show-ai-custom__drop-idle {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #4b5563;
        pointer-events: none;
    }
    .product-show-ai-custom__drop.has-photo .product-show-ai-custom__drop-idle {
        display: none;
    }
    .product-show-ai-custom__drop-preview {
        position: relative;
        display: inline-flex;
        pointer-events: none;
    }
    .product-show-ai-custom__drop-preview[hidden] { display: none !important; }
    .product-show-ai-custom__drop-preview img {
        width: 3rem;
        height: 3rem;
        object-fit: cover;
        border-radius: 0.45rem;
        border: 1px solid #e5e7eb;
        display: block;
        pointer-events: auto;
        cursor: pointer;
    }
    .product-show-ai-custom__drop-x {
        position: absolute;
        top: -0.35rem;
        right: -0.35rem;
        width: 1.25rem;
        height: 1.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        border: 1px solid #e5e7eb;
        background: #111827;
        color: #fff;
        cursor: pointer;
        pointer-events: auto;
        padding: 0;
        z-index: 3;
    }
    .product-show-ai-custom__drop-x:hover { background: #e2150c; border-color: #e2150c; }

    .product-show-ai-custom__suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 0.45rem;
    }
    .product-show-ai-custom__suggestion {
        padding: 0.28rem 0.55rem;
        border-radius: 9999px;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #374151;
        font-size: 0.7rem;
        font-weight: 600;
        line-height: 1.2;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease;
    }
    .product-show-ai-custom__suggestion:hover,
    .product-show-ai-custom__suggestion.is-selected {
        border-color: #005366;
        background: #f0f9fb;
        color: #005366;
    }

    .product-show-ai-custom__textarea {
        width: 100%;
        min-height: 3.1rem;
        padding: 0.55rem 0.65rem;
        border: 1px solid #d1d5db;
        border-radius: 0.55rem;
        font-size: 0.8125rem;
        line-height: 1.4;
        color: #111827;
        background: #fff;
        resize: vertical;
    }
    .product-show-ai-custom__textarea:focus {
        outline: none;
        border-color: #005366;
        box-shadow: 0 0 0 2px rgba(0, 83, 102, 0.12);
    }

    .product-show-ai-custom__result {
        margin-bottom: 0.7rem;
    }
    .product-show-ai-custom__result[hidden] { display: none !important; }
    .product-show-ai-custom__result-btn {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        width: 100%;
        padding: 0.4rem;
        text-align: left;
        border-radius: 0.65rem;
        border: 1px solid rgba(0, 83, 102, 0.25);
        background: #f0f9fb;
        cursor: pointer;
    }
    .product-show-ai-custom__result-btn img {
        width: 3rem;
        height: 3rem;
        object-fit: cover;
        border-radius: 0.45rem;
        border: 1px solid #e5e7eb;
        flex-shrink: 0;
        background: #fff;
    }
    .product-show-ai-custom__result-btn span {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        min-width: 0;
    }
    .product-show-ai-custom__result-btn strong {
        font-size: 0.75rem;
        color: #005366;
    }
    .product-show-ai-custom__result-btn em {
        font-style: normal;
        font-size: 0.7rem;
        color: #6b7280;
    }

    .product-show-ai-custom__actions {
        display: flex;
        gap: 0.45rem;
        align-items: stretch;
    }
    .product-show-ai-custom__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        min-height: 2.4rem;
        padding: 0.4rem 0.7rem;
        border-radius: 0.55rem;
        font-size: 0.8125rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, opacity 0.15s ease, color 0.15s ease;
    }
    .product-show-ai-custom__btn--primary {
        flex: 1;
        flex-direction: column;
        gap: 0.05rem;
        background: #005366;
        color: #fff;
        border: 1px solid #005366;
        line-height: 1.15;
    }
    .product-show-ai-custom__btn--primary:hover:not(:disabled) { background: #003d4d; }
    .product-show-ai-custom__btn--primary:disabled { opacity: 0.55; cursor: wait; }
    .product-show-ai-custom__btn-main { font-size: 0.8125rem; }
    .product-show-ai-custom__btn-meta {
        font-size: 0.65rem;
        font-weight: 600;
        opacity: 0.85;
        letter-spacing: 0.02em;
    }
    .product-show-ai-custom__btn--ghost {
        flex: 0 0 auto;
        min-width: 4.75rem;
        background: #fff;
        color: #374151;
        border: 1px solid #d1d5db;
    }
    .product-show-ai-custom__btn--ghost:hover:not(:disabled) {
        border-color: #005366;
        color: #005366;
        background: #f0f9fb;
    }
    .product-show-ai-custom__btn--ghost:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .product-show-ai-custom__btn--ghost.is-ready {
        opacity: 1;
        border-color: #005366;
        color: #005366;
    }

    .product-show-ai-custom__status {
        margin: 0.55rem 0 0;
        font-size: 0.75rem;
        line-height: 1.35;
    }
    .product-show-ai-custom__status[hidden] { display: none !important; }
    .product-show-ai-custom__status.is-error { color: #b91c1c; }
    .product-show-ai-custom__status.is-success { color: #047857; }

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
</style>
@endif
