@php
    $discountType = $discountType ?? 'none';
    $discountAmount = (float) ($discountAmount ?? 0);
    $discountedSubtotal = (float) ($discountedSubtotal ?? ($subtotal ?? 0));
    $volumeEligible = (bool) ($volumeEligible ?? false);
    $volumeDiscountPercent = (int) ($volumeDiscountPercent ?? ($volumePreview['percent'] ?? 0));
    $totalCartQuantity = (int) ($totalCartQuantity ?? 0);
    $appliedPromoCode = $appliedPromoCode ?? null;
    $volumeTiers = $volumeTiers ?? [];
@endphp

<div class="commerce-card p-4 mb-4 border border-gray-200 min-w-0" id="checkout-discount-panel">
    <h3 class="text-sm font-bold text-gray-900 mb-1">Savings</h3>
    <p class="text-xs text-gray-500 mb-4">Choose <strong>one</strong>: promo code or volume discount (not both).</p>

    <div class="space-y-3 min-w-0">
        @if($volumeEligible)
            <label data-discount-option="volume" class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition {{ $discountType === 'volume' ? 'border-[#005366] bg-[#005366]/5' : 'border-gray-200 hover:border-gray-300' }}">
                <input type="radio" name="checkout_discount_choice" value="volume" class="mt-1" {{ $discountType === 'volume' ? 'checked' : '' }} onchange="setCheckoutDiscountType('volume')">
                <span class="flex-1 min-w-0">
                    <span class="block text-sm font-semibold text-gray-900">Volume discount — <span id="checkout-volume-percent-label">{{ $volumeDiscountPercent }}</span>% off</span>
                    <span class="block text-xs text-gray-500">{{ $totalCartQuantity }} items in cart · Buy more, save more</span>
                </span>
            </label>
        @endif

        <div data-discount-option="promo" class="p-3 rounded-xl border-2 min-w-0 {{ $discountType === 'promo' ? 'border-[#005366] bg-[#005366]/5' : 'border-gray-200' }}">
            <label class="flex items-start gap-3 cursor-pointer mb-3">
                <input type="radio" name="checkout_discount_choice" value="promo" class="mt-1" {{ $discountType === 'promo' ? 'checked' : '' }} onchange="setCheckoutDiscountType('promo')">
                <span class="text-sm font-semibold text-gray-900">Promo code</span>
            </label>
            <div class="checkout-promo-row">
                <input type="text" id="checkout-promo-code-input" value="{{ $appliedPromoCode?->code ?? session('checkout.promo_code') }}" placeholder="Enter code" class="rounded-lg border-gray-300 text-sm font-mono uppercase">
                <button type="button" onclick="applyCheckoutPromoCode()" class="btn-outline-petrol btn-outline-petrol--compact">Apply</button>
            </div>
            <p id="checkout-promo-message" class="text-xs mt-2 {{ $discountType === 'promo' && $appliedPromoCode ? 'text-green-700' : 'text-gray-500' }}">
                @if($discountType === 'promo' && $appliedPromoCode)
                    Applied: {{ $appliedPromoCode->code }}
                @endif
            </p>
        </div>

        <label data-discount-option="none" class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer {{ $discountType === 'none' ? 'border-[#005366] bg-[#005366]/5' : 'border-gray-200 hover:border-gray-300' }}">
            <input type="radio" name="checkout_discount_choice" value="none" class="mt-1" {{ $discountType === 'none' ? 'checked' : '' }} onchange="setCheckoutDiscountType('none')">
            <span class="text-sm text-gray-700">No discount</span>
        </label>
    </div>

    <input type="hidden" name="discount_type" id="checkout-discount-type" value="{{ $discountType }}">
    <input type="hidden" name="promo_code" id="checkout-promo-code-hidden" value="{{ $appliedPromoCode?->code ?? '' }}">
</div>
