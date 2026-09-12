@once
<div class="studio-ai-progress" id="studio-ai-progress" hidden>
    <div class="studio-ai-progress__card" role="status" aria-live="polite">
        <p class="studio-ai-progress__title" id="studio-ai-progress-title">Generating your design</p>
        <p class="studio-ai-progress__sub">This can take up to a couple of minutes. Please keep this tab open.</p>
        <div class="studio-ai-progress__track" aria-hidden="true">
            <span class="studio-ai-progress__fill" id="studio-ai-progress-fill"></span>
        </div>
        <p class="studio-ai-progress__pct" id="studio-ai-progress-pct">0%</p>
    </div>
</div>
<style>
    .studio-ai-progress {
        position: fixed;
        inset: 0;
        z-index: 240;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(17, 24, 39, 0.45);
    }
    .studio-ai-progress[hidden] { display: none !important; }
    .studio-ai-progress__card {
        width: min(420px, 100%);
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        text-align: center;
    }
    .studio-ai-progress__title {
        margin: 0 0 8px;
        font-size: 1.125rem;
        font-weight: 700;
        color: #111827;
    }
    .studio-ai-progress__sub {
        margin: 0 0 16px;
        font-size: 0.875rem;
        line-height: 1.45;
        color: #4b5563;
    }
    .studio-ai-progress__track {
        height: 8px;
        border-radius: 9999px;
        background: #e5e7eb;
        overflow: hidden;
    }
    .studio-ai-progress__fill {
        display: block;
        height: 100%;
        width: 0%;
        border-radius: inherit;
        background: linear-gradient(90deg, #005366, #e2150c);
        transition: width 0.35s ease;
    }
    .studio-ai-progress__pct {
        margin: 12px 0 0;
        font-size: 1.25rem;
        font-weight: 700;
        color: #005366;
    }
</style>
@endonce
