<?php

namespace App\Http\Controllers;

use App\Services\CheckoutDiscountService;
use App\Services\PromoCodeService;
use App\Services\VolumeDiscountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutDiscountController extends Controller
{
    public function __construct(
        protected CheckoutDiscountService $checkoutDiscount,
        protected PromoCodeService $promoCodeService,
    ) {}

    public function preview(Request $request)
    {
        $cartItems = $this->getCartItems();

        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 422);
        }

        $result = $this->checkoutDiscount->resolve(
            $cartItems,
            Auth::user(),
            $request->input('email', Auth::user()?->email),
            $request->input('discount_type'),
            $request->input('promo_code'),
        );

        return response()->json([
            'success' => true,
            'discount' => $this->formatDiscountResponse($result),
        ]);
    }

    public function applyPromo(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:64',
            'email' => 'nullable|email|max:255',
        ]);

        $cartItems = $this->getCartItems();
        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 422);
        }

        $code = strtoupper(trim($request->input('code')));
        $promoCode = $this->promoCodeService->findByCode($code);

        if (! $promoCode) {
            return response()->json(['success' => false, 'message' => 'Promo code not found.'], 422);
        }

        $subtotal = (float) $cartItems->sum(fn ($item) => $item->getTotalPrice());
        $validation = $this->promoCodeService->validateForCheckout(
            $promoCode,
            $cartItems,
            $subtotal,
            Auth::user(),
            $request->input('email', Auth::user()?->email),
        );

        if (! $validation['valid']) {
            return response()->json(['success' => false, 'message' => $validation['message']], 422);
        }

        $this->checkoutDiscount->storeSession(CheckoutDiscountService::TYPE_PROMO, $code);

        $result = $this->checkoutDiscount->resolve(
            $cartItems,
            Auth::user(),
            $request->input('email', Auth::user()?->email),
            CheckoutDiscountService::TYPE_PROMO,
            $code,
        );

        return response()->json([
            'success' => true,
            'message' => 'Promo code applied.',
            'discount' => $this->formatDiscountResponse($result),
        ]);
    }

    public function setDiscountType(Request $request)
    {
        $request->validate([
            'discount_type' => 'required|in:none,promo,volume',
            'email' => 'nullable|email|max:255',
        ]);

        $type = $request->input('discount_type');
        $promoCode = session('checkout.promo_code');

        if ($type === CheckoutDiscountService::TYPE_VOLUME) {
            $this->checkoutDiscount->storeSession(CheckoutDiscountService::TYPE_VOLUME);
        } elseif ($type === CheckoutDiscountService::TYPE_PROMO && $promoCode) {
            $this->checkoutDiscount->storeSession(CheckoutDiscountService::TYPE_PROMO, $promoCode);
        } else {
            $this->checkoutDiscount->storeSession(CheckoutDiscountService::TYPE_NONE);
        }

        $cartItems = $this->getCartItems();
        $result = $this->checkoutDiscount->resolve(
            $cartItems,
            Auth::user(),
            $request->input('email', Auth::user()?->email),
            $type,
            $promoCode,
        );

        return response()->json([
            'success' => true,
            'discount' => $this->formatDiscountResponse($result),
        ]);
    }

    public function removePromo()
    {
        $cartItems = $this->getCartItems();

        if (app(VolumeDiscountService::class)->isEligible($cartItems)) {
            $this->checkoutDiscount->storeSession(CheckoutDiscountService::TYPE_VOLUME);
        } else {
            $this->checkoutDiscount->storeSession(CheckoutDiscountService::TYPE_NONE);
        }

        $result = $this->checkoutDiscount->resolve($cartItems, Auth::user());

        return response()->json([
            'success' => true,
            'message' => 'Promo code removed.',
            'discount' => $this->formatDiscountResponse($result),
        ]);
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    protected function formatDiscountResponse(array $result): array
    {
        return [
            'subtotal' => $result['subtotal'],
            'discount_type' => $result['discount_type'],
            'discount_amount' => $result['discount_amount'],
            'discounted_subtotal' => $result['discounted_subtotal'],
            'promo_code' => $result['promo_code_string'],
            'volume_discount_percent' => (int) ($result['volume_discount_percent'] ?? $result['volume_preview']['percent'] ?? 0),
            'volume_eligible' => $result['volume_eligible'],
            'volume_preview' => $result['volume_preview'],
            'message' => $result['message'],
            'hold_remaining_seconds' => (int) ($result['hold_remaining_seconds'] ?? 0),
            'hold_duration_seconds' => $this->checkoutDiscount->holdDurationSeconds(),
        ];
    }

    protected function getCartItems()
    {
        $sessionId = session()->getId();
        $userId = Auth::id();

        return \App\Models\Cart::with(['product.shop', 'product.template', 'variant'])
            ->where(function ($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->get();
    }
}
