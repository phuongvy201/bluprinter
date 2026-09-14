@php
    $thresholdUsd = (float) ($freeShippingThresholdUsd ?? \App\Support\CatalogPageSettings::freeShippingThresholdUsd());
    $subtotalUsd = (float) ($freeShippingSubtotalUsd ?? 0);
    $currency = $currency ?? currency();
    $currencyRate = (float) ($currencyRate ?? currency_rate() ?? 1);
    $progress = $thresholdUsd > 0 ? min(100, ($subtotalUsd / $thresholdUsd) * 100) : 100;
    $unlocked = $subtotalUsd >= $thresholdUsd;
    $remainingUsd = max(0, $thresholdUsd - $subtotalUsd);
    $remainingDisplay = $currency !== 'USD' && $currencyRate > 0
        ? $remainingUsd * $currencyRate
        : $remainingUsd;
    $thresholdDisplay = $currency !== 'USD' && $currencyRate > 0
        ? $thresholdUsd * $currencyRate
        : $thresholdUsd;
    $idPrefix = $idPrefix ?? 'freeship';
@endphp

<div class="freeship-progress mb-4 {{ $unlocked ? 'freeship-progress--done' : '' }}"
     id="{{ $idPrefix }}-progress"
     data-threshold-usd="{{ $thresholdUsd }}"
     data-subtotal-usd="{{ round($subtotalUsd, 2) }}"
     role="status"
     aria-live="polite">
    @if($unlocked)
        <div class="freeship-progress__done">
            <span class="freeship-progress__icon" aria-hidden="true">✓</span>
            <span>You've unlocked free shipping!</span>
        </div>
    @else
        <p class="freeship-progress__text" id="{{ $idPrefix }}-text">
            Add <strong id="{{ $idPrefix }}-remaining">{{ \App\Services\CurrencyService::formatPrice($remainingDisplay, $currency) }}</strong>
            more for <strong>FREE SHIPPING</strong>
            <span class="freeship-progress__goal">({{ \App\Services\CurrencyService::formatPrice($thresholdDisplay, $currency) }})</span>
        </p>
        <div class="freeship-progress__track" aria-hidden="true">
            <span class="freeship-progress__fill" id="{{ $idPrefix }}-fill" style="width: {{ number_format($progress, 1, '.', '') }}%"></span>
        </div>
    @endif
</div>

@once
<style>
.freeship-progress {
    padding: 12px 14px;
    border-radius: 12px;
    background: #fffbeb;
    border: 1px solid #fde68a;
}
.freeship-progress--done {
    background: #ecfdf5;
    border-color: #bbf7d0;
}
.freeship-progress__done {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #166534;
}
.freeship-progress__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 9999px;
    background: #22c55e;
    color: #fff;
    font-size: 11px;
    flex-shrink: 0;
}
.freeship-progress__text {
    margin: 0 0 8px;
    font-size: 12px;
    line-height: 1.4;
    color: #78350f;
}
.freeship-progress__goal {
    color: #a16207;
    font-weight: 500;
}
.freeship-progress__track {
    height: 8px;
    border-radius: 9999px;
    background: #fde68a;
    overflow: hidden;
}
.freeship-progress__fill {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, #f59e0b, #ea580c);
    transition: width 0.25s ease;
}
</style>
<script>
window.syncFreeShippingProgress = function (subtotalUsd, options) {
    options = options || {};
    const prefix = options.idPrefix || 'freeship';
    const root = document.getElementById(prefix + '-progress');
    if (!root) return;

    const threshold = parseFloat(options.thresholdUsd != null ? options.thresholdUsd : root.getAttribute('data-threshold-usd')) || 0;
    const currency = options.currency || window.SITE_CURRENCY || 'USD';
    const rate = parseFloat(options.currencyRate != null ? options.currencyRate : (window.CURRENT_CURRENCY_RATE || window.SITE_CURRENCY_RATE || 1)) || 1;
    const symbol = options.currencySymbol || window.CURRENCY_SYMBOL || window.SITE_CURRENCY_SYMBOL || '$';
    const amount = Math.max(0, parseFloat(subtotalUsd) || 0);

    root.setAttribute('data-subtotal-usd', amount.toFixed(2));

    const unlocked = threshold > 0 && amount >= threshold;
    const progress = threshold > 0 ? Math.min(100, (amount / threshold) * 100) : 100;
    const remainingUsd = Math.max(0, threshold - amount);
    const remainingDisplay = currency !== 'USD' && rate > 0 ? remainingUsd * rate : remainingUsd;
    const thresholdDisplay = currency !== 'USD' && rate > 0 ? threshold * rate : threshold;
    const fmt = (n) => symbol + (parseFloat(n) || 0).toFixed(2);

    root.classList.toggle('freeship-progress--done', unlocked);

    if (unlocked) {
        root.innerHTML = '<div class="freeship-progress__done">' +
            '<span class="freeship-progress__icon" aria-hidden="true">✓</span>' +
            '<span>You&apos;ve unlocked free shipping!</span></div>';
        return;
    }

    root.innerHTML =
        '<p class="freeship-progress__text" id="' + prefix + '-text">' +
            'Add <strong id="' + prefix + '-remaining">' + fmt(remainingDisplay) + '</strong> more for <strong>FREE SHIPPING</strong> ' +
            '<span class="freeship-progress__goal">(' + fmt(thresholdDisplay) + ')</span>' +
        '</p>' +
        '<div class="freeship-progress__track" aria-hidden="true">' +
            '<span class="freeship-progress__fill" id="' + prefix + '-fill" style="width: ' + progress.toFixed(1) + '%"></span>' +
        '</div>';
};
</script>
@endonce
