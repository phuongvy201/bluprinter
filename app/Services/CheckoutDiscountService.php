<?php

namespace App\Services;

use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Support\Collection;

class CheckoutDiscountService
{
    public const TYPE_NONE = 'none';
    public const TYPE_PROMO = 'promo';
    public const TYPE_VOLUME = 'volume';

    public function __construct(
        protected PromoCodeService $promoCodeService,
        protected VolumeDiscountService $volumeDiscountService,
    ) {}

    /**
     * @param  Collection<int, \App\Models\Cart>  $cartItems
     * @return array<string, mixed>
     */
    public function resolve(
        Collection $cartItems,
        ?User $user = null,
        ?string $email = null,
        ?string $discountType = null,
        ?string $promoCodeInput = null,
    ): array {
        $subtotal = round((float) $cartItems->sum(fn ($item) => $item->getTotalPrice()), 2);

        $volume = $this->volumeDiscountService->calculate($cartItems, $subtotal);
        $volumeEligible = $volume['percent'] > 0;

        $sessionType = session('checkout.discount_type');
        $promoCodeInput = $promoCodeInput !== null && $promoCodeInput !== ''
            ? strtoupper(trim($promoCodeInput))
            : (($code = session('checkout.promo_code')) ? strtoupper(trim((string) $code)) : null);

        $discountType = $this->resolveDiscountType(
            $discountType,
            $sessionType,
            $volumeEligible,
            $promoCodeInput,
        );

        $promoCode = null;
        $discountAmount = 0.0;
        $message = null;

        if ($discountType === self::TYPE_PROMO && $promoCodeInput) {
            $promoCode = $this->promoCodeService->findByCode($promoCodeInput);

            if (! $promoCode) {
                $message = 'Promo code not found.';
                $discountType = $this->fallbackAfterPromoFailure($volumeEligible);
            } else {
                $promoValidation = $this->promoCodeService->validateForCheckout(
                    $promoCode,
                    $cartItems,
                    $subtotal,
                    $user,
                    $email
                );

                if (! $promoValidation['valid']) {
                    $message = $promoValidation['message'];
                    $promoCode = null;
                    $discountType = $this->fallbackAfterPromoFailure($volumeEligible);
                } else {
                    $discountAmount = (float) $promoValidation['discount_amount'];
                }
            }
        }

        if ($discountType === self::TYPE_VOLUME && $volumeEligible) {
            $discountAmount = (float) $volume['amount'];
            $this->storeSession(self::TYPE_VOLUME);
        } elseif ($discountType === self::TYPE_VOLUME) {
            $discountType = self::TYPE_NONE;
        }

        $discountedSubtotal = max(0, round($subtotal - $discountAmount, 2));
        $holdRemaining = $discountAmount > 0
            ? $this->ensureHold()
            : $this->clearHold();

        return [
            'subtotal' => $subtotal,
            'discount_type' => $discountType,
            'discount_amount' => $discountAmount,
            'discounted_subtotal' => $discountedSubtotal,
            'promo_code' => $promoCode,
            'promo_code_string' => $promoCode?->code,
            'volume_discount_percent' => (int) $volume['percent'],
            'volume_eligible' => $volumeEligible,
            'volume_preview' => $volume,
            'message' => $message,
            'hold_remaining_seconds' => $holdRemaining,
        ];
    }

    protected function resolveDiscountType(
        ?string $requestedType,
        ?string $sessionType,
        bool $volumeEligible,
        ?string $promoCodeInput,
    ): string {
        if ($requestedType !== null && $requestedType !== '') {
            return $requestedType;
        }

        if ($sessionType === self::TYPE_PROMO && $promoCodeInput) {
            return self::TYPE_PROMO;
        }

        if ($sessionType === self::TYPE_NONE) {
            return self::TYPE_NONE;
        }

        if ($volumeEligible) {
            return self::TYPE_VOLUME;
        }

        return self::TYPE_NONE;
    }

    protected function fallbackAfterPromoFailure(bool $volumeEligible): string
    {
        if ($volumeEligible) {
            return self::TYPE_VOLUME;
        }

        $this->storeSession(self::TYPE_NONE);

        return self::TYPE_NONE;
    }

    public function storeSession(string $discountType, ?string $promoCode = null): void
    {
        $previousType = session('checkout.discount_type');
        $previousCode = session('checkout.promo_code');
        $normalizedCode = $discountType === self::TYPE_PROMO
            ? strtoupper(trim((string) $promoCode))
            : null;

        session([
            'checkout.discount_type' => $discountType,
            'checkout.promo_code' => $normalizedCode,
        ]);

        if ($discountType === self::TYPE_NONE) {
            $this->clearHold();

            return;
        }

        $isNewPromo = $discountType === self::TYPE_PROMO
            && ($previousType !== self::TYPE_PROMO || $previousCode !== $normalizedCode);

        $this->ensureHold($isNewPromo);
    }

    public function clearSession(): void
    {
        session()->forget(['checkout.discount_type', 'checkout.promo_code']);
        $this->clearHold();
    }

    public function holdRemainingSeconds(): int
    {
        $until = session('checkout.discount_hold_until');
        if (! $until) {
            return 0;
        }

        return (int) max(0, \Illuminate\Support\Carbon::parse($until)->getTimestamp() - now()->getTimestamp());
    }

    public function holdDurationSeconds(): int
    {
        return max(60, (int) config('promo.checkout_hold_seconds', 600));
    }

    /**
     * Start or keep the checkout urgency countdown. Returns remaining seconds.
     */
    public function ensureHold(bool $forceRefresh = false): int
    {
        $existing = session('checkout.discount_hold_until');
        $expired = $existing && $this->holdRemainingSeconds() <= 0;

        if ($forceRefresh || ! $existing || $expired) {
            session([
                'checkout.discount_hold_until' => now()->addSeconds($this->holdDurationSeconds()),
            ]);
        }

        return $this->holdRemainingSeconds();
    }

    public function clearHold(): int
    {
        session()->forget('checkout.discount_hold_until');

        return 0;
    }
}
