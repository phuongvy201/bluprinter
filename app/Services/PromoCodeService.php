<?php

namespace App\Services;

use App\Mail\PromoCodeIssuedMail;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PromoCodeService
{
    /**
     * @return array{valid: bool, message: string, promo_code: ?PromoCode, discount_amount: float}
     */
    public function validateForCheckout(
        PromoCode $promoCode,
        Collection $cartItems,
        float $subtotal,
        ?User $user = null,
        ?string $email = null,
    ): array {
        $email = strtolower(trim((string) $email));

        if (! $promoCode->is_active) {
            return $this->fail('This promo code is not active.');
        }

        if ($promoCode->isExpired()) {
            return $this->fail('This promo code has expired.');
        }

        if ($promoCode->isNotStarted()) {
            return $this->fail('This promo code is not available yet.');
        }

        if (! $promoCode->hasRemainingUses()) {
            return $this->fail('This promo code has reached its usage limit.');
        }

        if ($subtotal < (float) $promoCode->min_order_amount) {
            return $this->fail(sprintf(
                'Minimum order amount is %s.',
                CurrencyService::formatPrice((float) $promoCode->min_order_amount)
            ));
        }

        if ($promoCode->assigned_email && $email !== '' && strtolower($promoCode->assigned_email) !== $email) {
            return $this->fail('This promo code is not valid for your account.');
        }

        if ($promoCode->assigned_user_id && $user && (int) $promoCode->assigned_user_id !== (int) $user->id) {
            return $this->fail('This promo code is not valid for your account.');
        }

        if (! $this->audienceAllowsUse($promoCode, $user, $email)) {
            return $this->fail('You are not eligible to use this promo code.');
        }

        $usedByCustomer = $promoCode->customerUsageCount($user?->id, $email);
        if ($usedByCustomer >= (int) $promoCode->per_customer_limit) {
            return $this->fail('You have already used this promo code.');
        }

        $discountAmount = $promoCode->calculateDiscount($subtotal);

        if ($discountAmount <= 0) {
            return $this->fail('This promo code does not apply to your cart.');
        }

        return [
            'valid' => true,
            'message' => 'Promo code applied.',
            'promo_code' => $promoCode,
            'discount_amount' => $discountAmount,
        ];
    }

    public function findByCode(string $code): ?PromoCode
    {
        return PromoCode::query()
            ->where('code', strtoupper(trim($code)))
            ->first();
    }

    /**
     * @return Collection<int, PromoCode>
     */
    public function publicPromoCodes(): Collection
    {
        $excluded = $this->configuredActionCodes();

        return PromoCode::query()
            ->active()
            ->where('show_on_promo_page', true)
            ->when($excluded->isNotEmpty(), fn ($q) => $q->whereNotIn('code', $excluded))
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Codes unlocked by storefront actions (add to cart, wishlist, sign up).
     *
     * @return list<array{key: string, action: string, hint: string, cta: ?string, route: ?string, promo: PromoCode}>
     */
    public function actionPromoOffers(): array
    {
        $definitions = config('promo.action_codes', []);
        $codes = collect($definitions)
            ->pluck('code')
            ->filter()
            ->map(fn ($code) => strtoupper((string) $code))
            ->values();

        $promos = PromoCode::query()
            ->active()
            ->whereIn('code', $codes)
            ->get()
            ->keyBy(fn (PromoCode $promo) => strtoupper($promo->code));

        $offers = [];
        foreach ($definitions as $key => $definition) {
            $code = strtoupper((string) ($definition['code'] ?? ''));
            $promo = $promos->get($code);
            if (! $promo) {
                continue;
            }

            $offers[] = [
                'key' => (string) $key,
                'action' => (string) ($definition['action'] ?? $promo->title),
                'hint' => (string) ($definition['hint'] ?? $promo->description ?? ''),
                'cta' => $definition['cta'] ?? null,
                'route' => $definition['route'] ?? null,
                'promo' => $promo,
            ];
        }

        return $offers;
    }

    /**
     * @return Collection<int, string>
     */
    protected function configuredActionCodes(): Collection
    {
        return collect(config('promo.action_codes', []))
            ->pluck('code')
            ->filter()
            ->map(fn ($code) => strtoupper((string) $code))
            ->values();
    }

    /**
     * Send a fixed promo code (from config) to an email address.
     *
     * @return array{success: bool, already_sent?: bool, code?: string, title?: string, message: string}
     */
    public function claimAndSendFixedPromo(
        string $codeKey,
        string $email,
        ?string $name = null,
        ?int $userId = null,
    ): array {
        $email = strtolower(trim($email));
        if ($email === '') {
            return ['success' => false, 'message' => 'Email is required.'];
        }

        if ($codeKey === 'newsletter') {
            $this->ensureNewsletterPromo();
        }

        $code = config("promo.fixed_codes.{$codeKey}", strtoupper($codeKey));
        $promo = $this->findByCode($code);

        if (! $promo || ! $promo->is_active || $promo->isExpired() || $promo->isNotStarted()) {
            return ['success' => false, 'message' => 'This promo code is not available.'];
        }

        if (! $promo->hasRemainingUses()) {
            return ['success' => false, 'message' => 'This promo code has reached its usage limit.'];
        }

        $user = $userId ? User::find($userId) : null;

        if (! $this->audienceAllowsUse($promo, $user, $email)) {
            return ['success' => false, 'message' => 'You are not eligible for this promo code.'];
        }

        $cacheKey = $this->promoEmailSentCacheKey($email, $promo->code);

        if (Cache::has($cacheKey)) {
            return [
                'success' => true,
                'already_sent' => true,
                'code' => $promo->code,
                'title' => $promo->title,
                'message' => 'Promo code was already sent to your email.',
            ];
        }

        try {
            Mail::to($email)->send(new PromoCodeIssuedMail($promo, $name ?? ''));
        } catch (\Throwable $e) {
            Log::error('Promo email failed', [
                'code' => $promo->code,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Could not send email. Please try again later.'];
        }

        Cache::put($cacheKey, true, now()->addYears(2));

        return [
            'success' => true,
            'already_sent' => false,
            'code' => $promo->code,
            'title' => $promo->title,
            'message' => 'Promo code sent to your email.',
        ];
    }

    protected function promoEmailSentCacheKey(string $email, string $code): string
    {
        return 'promo_email_sent:' . strtoupper($code) . ':' . strtolower(trim($email));
    }

    public function issueWelcomeCode(string $email, ?string $name = null): ?PromoCode
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return null;
        }

        if ($this->emailHasCompletedOrders($email)) {
            return null;
        }

        $code = config('promo.fixed_codes.welcome', 'FIRSTSALE');
        $promo = $this->findByCode($code);

        if (! $promo || ! $promo->is_active || $promo->isExpired() || $promo->isNotStarted()) {
            return null;
        }

        if (! $this->audienceAllowsUse($promo, null, $email)) {
            return null;
        }

        return $promo;
    }

    public function ensureNewsletterPromo(): PromoCode
    {
        $code = strtoupper((string) config('promo.fixed_codes.newsletter', 'NEWSLETTER10'));
        $cfg = config('promo.newsletter', []);

        return PromoCode::updateOrCreate(
            ['code' => $code],
            [
                'title' => 'Newsletter — 10% off',
                'description' => 'Thanks for subscribing! Enjoy 10% off your next order.',
                'type' => 'percent',
                'value' => (float) ($cfg['percent'] ?? 10),
                'min_order_amount' => (float) ($cfg['min_order_amount'] ?? 0),
                'max_discount_amount' => null,
                'audience' => PromoCode::AUDIENCE_WELCOME,
                'usage_limit' => null,
                'usage_count' => 0,
                'per_customer_limit' => 1,
                'starts_at' => now(),
                'expires_at' => now()->addDays((int) ($cfg['expires_days'] ?? 30)),
                'is_active' => true,
                'show_on_promo_page' => false,
                'is_auto_generated' => false,
            ]
        );
    }

    public function claimNewsletterPromo(string $email): array
    {
        return $this->claimAndSendFixedPromo('newsletter', $email);
    }

    public function issueThankYouCode(Order $order): ?PromoCode
    {
        $email = strtolower(trim($order->customer_email));
        if ($email === '') {
            return null;
        }

        $completedCount = Order::query()
            ->where('customer_email', $email)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->where('id', '!=', $order->id)
            ->count();

        if ($completedCount > 0) {
            return null;
        }

        $code = config('promo.fixed_codes.thank_you', 'THANKYOU10');
        $promo = $this->findByCode($code);

        if (! $promo || ! $promo->is_active || $promo->isExpired() || $promo->isNotStarted()) {
            return null;
        }

        return $promo;
    }

    public function issueWinBackCode(string $email, ?int $userId = null, ?int $inactiveDays = null): ?PromoCode
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return null;
        }

        $recentWinBack = PromoCode::query()
            ->where('audience', PromoCode::AUDIENCE_WIN_BACK)
            ->where('assigned_email', $email)
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();

        if ($recentWinBack) {
            return null;
        }

        $cfg = config('promo.win_back', []);

        return PromoCode::create([
            'code' => PromoCode::generateUniqueCode('COMEBACK'),
            'title' => 'We miss you!',
            'description' => 'Come back and enjoy an exclusive discount on your next order.',
            'type' => 'percent',
            'value' => (float) ($cfg['percent'] ?? 18),
            'min_order_amount' => (float) ($cfg['min_order_amount'] ?? 0),
            'audience' => PromoCode::AUDIENCE_WIN_BACK,
            'usage_limit' => 1,
            'per_customer_limit' => 1,
            'expires_at' => now()->addDays((int) ($cfg['expires_days'] ?? 14)),
            'is_active' => true,
            'show_on_promo_page' => false,
            'is_auto_generated' => true,
            'assigned_email' => $email,
            'assigned_user_id' => $userId,
            'meta' => ['inactive_days' => $inactiveDays],
        ]);
    }

    protected function audienceAllowsUse(PromoCode $promoCode, ?User $user, string $email): bool
    {
        return match ($promoCode->audience) {
            PromoCode::AUDIENCE_PUBLIC => true,
            PromoCode::AUDIENCE_WELCOME => ! $this->emailHasCompletedOrders($email),
            PromoCode::AUDIENCE_THANK_YOU => $this->emailHasCompletedOrders($email),
            PromoCode::AUDIENCE_WIN_BACK => $this->emailHasCompletedOrders($email),
            PromoCode::AUDIENCE_VIP => $this->emailIsVip($email, $user?->id),
            default => true,
        };
    }

    protected function emailHasCompletedOrders(string $email): bool
    {
        if ($email === '') {
            return false;
        }

        return Order::query()
            ->where('customer_email', $email)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->exists();
    }

    protected function emailIsVip(string $email, ?int $userId = null): bool
    {
        $minOrders = (int) config('promo.vip.min_completed_orders', 3);

        $query = Order::query()->whereIn('payment_status', ['paid', 'completed']);

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($email !== '') {
            $query->where('customer_email', $email);
        } else {
            return false;
        }

        return $query->count() >= $minOrders;
    }

    /**
     * @return array{valid: bool, message: string, promo_code: ?PromoCode, discount_amount: float}
     */
    protected function fail(string $message): array
    {
        return [
            'valid' => false,
            'message' => $message,
            'promo_code' => null,
            'discount_amount' => 0.0,
        ];
    }

    /**
     * Record promo usage and issue thank-you code after successful payment.
     */
    public function finalizePaidOrder(Order $order): ?PromoCode
    {
        if ($order->promo_code_id && (float) $order->discount_amount > 0) {
            $promo = PromoCode::find($order->promo_code_id);
            if ($promo && ! $promo->usages()->where('order_id', $order->id)->exists()) {
                $promo->recordUsage($order, (float) $order->discount_amount);
            }
        }

        return $this->issueThankYouCode($order);
    }
}
