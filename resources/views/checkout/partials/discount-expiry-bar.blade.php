@php
    $holdRemaining = (int) ($discountHoldRemaining ?? 0);
    $holdDuration = max(60, (int) config('promo.checkout_hold_seconds', 600));
    $holdMinutes = intdiv($holdRemaining, 60);
    $holdSeconds = $holdRemaining % 60;
@endphp
<div id="discount-expiry-bar"
     class="discount-expiry-bar"
     data-remaining="{{ $holdRemaining }}"
     data-duration="{{ $holdDuration }}"
     @if($holdRemaining <= 0) hidden @endif
     role="status"
     aria-live="polite">
    <span class="discount-expiry-bar__label">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
        </svg>
        Discount expires in
    </span>
    <span class="discount-expiry-bar__timer" id="discount-expiry-timer">{{ sprintf('%02d:%02d', $holdMinutes, $holdSeconds) }}</span>
</div>
@once
<script>
(function () {
    let remaining = 0;
    let duration = 600;
    let timerId = null;

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function formatHold(seconds) {
        const safe = Math.max(0, Math.floor(seconds));
        return pad(Math.floor(safe / 60)) + ':' + pad(safe % 60);
    }

    function tick() {
        const bar = document.getElementById('discount-expiry-bar');
        const label = document.getElementById('discount-expiry-timer');
        if (!bar || !label) return;

        remaining -= 1;
        if (remaining <= 0) {
            remaining = duration;
        }

        label.textContent = formatHold(remaining);
        bar.setAttribute('data-remaining', String(remaining));
    }

    window.syncDiscountExpiryBar = function (seconds, durationSeconds) {
        const bar = document.getElementById('discount-expiry-bar');
        const label = document.getElementById('discount-expiry-timer');
        if (!bar || !label) return;

        duration = Math.max(60, parseInt(durationSeconds || bar.getAttribute('data-duration') || '600', 10));
        remaining = Math.max(0, parseInt(seconds, 10) || 0);
        bar.setAttribute('data-remaining', String(remaining));
        bar.setAttribute('data-duration', String(duration));

        if (remaining > 0) {
            bar.hidden = false;
            label.textContent = formatHold(remaining);
            if (timerId) clearInterval(timerId);
            timerId = setInterval(tick, 1000);
        } else {
            bar.hidden = true;
            if (timerId) {
                clearInterval(timerId);
                timerId = null;
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        const bar = document.getElementById('discount-expiry-bar');
        if (!bar) return;
        window.syncDiscountExpiryBar(
            bar.getAttribute('data-remaining'),
            bar.getAttribute('data-duration')
        );
    });

    const existingBar = document.getElementById('discount-expiry-bar');
    if (existingBar) {
        window.syncDiscountExpiryBar(
            existingBar.getAttribute('data-remaining'),
            existingBar.getAttribute('data-duration')
        );
    }
})();
</script>
@endonce
