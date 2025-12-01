<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Services\ShippingCalculator;
use App\Services\CurrencyService;
use App\Services\TikTokEventsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function add(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'selectedVariant' => 'nullable|array',
                'customizations' => 'nullable|array'
            ]);

            $product = Product::findOrFail($request->id);
            $sessionId = session()->getId();
            $userId = Auth::id();

            // Find existing cart item
            $cartItems = Cart::where('product_id', $request->id)
                ->where(function ($query) use ($sessionId, $userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('session_id', $sessionId);
                    }
                })
                ->get();

            // Check for exact match manually to avoid JSON encoding issues
            $existingCart = null;
            foreach ($cartItems as $item) {
                $variantMatch = $this->compareVariants($item->selected_variant, $request->selectedVariant ?? []);
                $customizationMatch = $this->compareCustomizations($item->customizations, $request->customizations ?? []);

                if ($variantMatch && $customizationMatch) {
                    $existingCart = $item;
                    break;
                }
            }

            if ($existingCart) {
                // Update quantity and price (in case variant price changed)
                $existingCart->increment('quantity', $request->quantity);
                $existingCart->update(['price' => $request->price]);
                $cartItem = $existingCart;
            } else {
                // Create new cart item
                $cartItem = Cart::create([
                    'session_id' => $userId ? null : $sessionId,
                    'user_id' => $userId,
                    'product_id' => $request->id,
                    'variant_id' => $request->selectedVariant['id'] ?? null,
                    'quantity' => $request->quantity,
                    'price' => $request->price,
                    'selected_variant' => $request->selectedVariant,
                    'customizations' => $request->customizations
                ]);
            }

            Log::info('Item added to cart', [
                'cart_id' => $cartItem->id,
                'product_id' => $request->id,
                'user_id' => $userId,
                'session_id' => $sessionId,
                'quantity' => $cartItem->quantity
            ]);

            $this->trackTikTokAddToCartEvent($request, $product, $cartItem->quantity, $request->price);

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'cart_item' => $cartItem->load('product')
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding item to cart', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart'
            ], 500);
        }
    }

    public function get(Request $request)
    {
        try {
            $sessionId = session()->getId();
            $userId = Auth::id();

            $cartItems = Cart::with(['product.shop', 'product.template', 'variant'])
                ->where(function ($query) use ($sessionId, $userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('session_id', $sessionId);
                    }
                })
                ->get();

            // Transform cart items to include media
            $cartItems->each(function ($item) {
                if ($item->product) {
                    $item->product->media = $item->product->getEffectiveMedia();
                }
            });

            $totalItems = $cartItems->sum('quantity');
            $totalPrice = $cartItems->sum(function ($item) {
                return $item->getTotalPriceWithCustomizations();
            });

            // Get currency and rate
            $currency = CurrencyService::getCurrencyForDomain() ?? 'USD';
            $currencyRate = CurrencyService::getCurrencyRateForDomain();

            // If no rate from domain, use default rates
            if (!$currencyRate || $currencyRate == 1.0) {
                $defaultRates = [
                    'USD' => 1.0,
                    'GBP' => 0.79,
                    'EUR' => 0.92,
                    'CAD' => 1.35,
                    'AUD' => 1.52,
                    'JPY' => 150.0,
                    'CNY' => 7.2,
                    'HKD' => 7.8,
                    'SGD' => 1.34,
                ];
                $currencyRate = $defaultRates[$currency] ?? 1.0;
            }

            // Calculate summary (without tax)
            // Note: $totalPrice is already in the current currency (prices in cart are already converted)
            $subtotal = $totalPrice;
            $shipping = 0;
            $shippingDetails = null;

            if (!$cartItems->isEmpty()) {
                // Prepare cart items for shipping calculation
                // Shipping calculator expects USD prices, so we need to convert back to USD
                $items = $cartItems->map(function ($item) use ($currency, $currencyRate) {
                    // Convert price back to USD for shipping calculation
                    $priceInUSD = $currency !== 'USD' ? $item->price / $currencyRate : $item->price;
                    return [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $priceInUSD,
                    ];
                });

                // Calculate shipping for US (default) or from request
                $country = $request->get('country', 'US');
                $calculator = new ShippingCalculator();
                $shippingResult = $calculator->calculateShipping($items, $country);

                if ($shippingResult['success']) {
                    $shipping = $shippingResult['total_shipping'];
                    $shippingDetails = $shippingResult;
                }
            }

            // Convert shipping from USD to current currency (shipping is always calculated in USD)
            $convertedShipping = $currency !== 'USD' ? CurrencyService::convertFromUSDWithRate($shipping, $currency, $currencyRate) : $shipping;

            // Subtotal is already in current currency, no need to convert
            $convertedSubtotal = $subtotal;
            $total = $subtotal + $convertedShipping;
            $convertedTotal = $total;

            return response()->json([
                'success' => true,
                'cart_items' => $cartItems,
                'total_items' => $totalItems,
                'total_price' => $totalPrice,
                'summary' => [
                    'subtotal' => $subtotal,
                    'shipping' => $shipping,
                    'total' => $total,
                    'converted_subtotal' => $convertedSubtotal,
                    'converted_shipping' => $convertedShipping,
                    'converted_total' => $convertedTotal,
                ],
                'shipping_details' => $shippingDetails,
                'currency' => $currency,
                'currency_rate' => $currencyRate,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting cart', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get cart'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $sessionId = session()->getId();
            $userId = Auth::id();

            $cartItem = Cart::with(['product.shop', 'product.template', 'product.variants', 'variant'])
                ->where('id', $id)
                ->where(function ($query) use ($sessionId, $userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('session_id', $sessionId);
                    }
                })
                ->firstOrFail();

            // Transform cart item to include media
            if ($cartItem->product) {
                $cartItem->product->media = $cartItem->product->getEffectiveMedia();
            }

            return response()->json([
                'success' => true,
                'cart_item' => $cartItem
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting cart item', [
                'error' => $e->getMessage(),
                'cart_item_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get cart item'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
                'selected_variant' => 'nullable|array',
                'customizations' => 'nullable|array',
                'price' => 'nullable|numeric|min:0'
            ]);

            $sessionId = session()->getId();
            $userId = Auth::id();

            $cartItem = Cart::where('id', $id)
                ->where(function ($query) use ($sessionId, $userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('session_id', $sessionId);
                    }
                })
                ->firstOrFail();

            $updateData = [
                'quantity' => $request->quantity
            ];

            // Update variant if provided
            if ($request->has('selected_variant')) {
                $updateData['selected_variant'] = $request->selected_variant;
                $updateData['variant_id'] = $request->selected_variant['id'] ?? null;
            }

            // Update customizations if provided
            if ($request->has('customizations')) {
                $updateData['customizations'] = $request->customizations;
            }

            // Update unit price if provided (price includes variant and customization unit total)
            if ($request->has('price')) {
                $updateData['price'] = $request->price;
            }

            $cartItem->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'cart_item' => $cartItem->load('product')
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating cart item', [
                'error' => $e->getMessage(),
                'cart_item_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item'
            ], 500);
        }
    }

    public function remove($id)
    {
        try {
            $sessionId = session()->getId();
            $userId = Auth::id();

            $cartItem = Cart::where('id', $id)
                ->where(function ($query) use ($sessionId, $userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('session_id', $sessionId);
                    }
                })
                ->firstOrFail();

            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing cart item', [
                'error' => $e->getMessage(),
                'cart_item_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove cart item'
            ], 500);
        }
    }

    public function clear()
    {
        try {
            $sessionId = session()->getId();
            $userId = Auth::id();

            Cart::where(function ($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error clearing cart', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart'
            ], 500);
        }
    }

    public function sync(Request $request)
    {
        try {
            $request->validate([
                'cart_items' => 'required|array',
                'cart_items.*.id' => 'required|exists:products,id',
                'cart_items.*.quantity' => 'required|integer|min:1',
                'cart_items.*.price' => 'required|numeric|min:0'
            ]);

            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User must be logged in to sync cart'
                ], 401);
            }

            // Clear existing cart for this user
            Cart::where('user_id', $userId)->delete();

            // Add items from localStorage
            foreach ($request->cart_items as $item) {
                Cart::create([
                    'user_id' => $userId,
                    'product_id' => $item['id'],
                    'variant_id' => $item['selectedVariant']['id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'selected_variant' => $item['selectedVariant'] ?? null,
                    'customizations' => $item['customizations'] ?? null
                ]);
            }

            Log::info('Cart synced for user', [
                'user_id' => $userId,
                'items_count' => count($request->cart_items)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cart synced successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing cart', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync cart'
            ], 500);
        }
    }

    /**
     * Compare two variant objects for equality
     */
    private function compareVariants($variant1, $variant2)
    {
        // Normalize both variants to arrays
        $var1 = is_array($variant1) ? $variant1 : json_decode(json_encode($variant1), true);
        $var2 = is_array($variant2) ? $variant2 : json_decode(json_encode($variant2), true);

        // Compare attributes if both have them
        if (isset($var1['attributes']) && isset($var2['attributes'])) {
            // Sort arrays by keys to ensure consistent comparison
            ksort($var1['attributes']);
            ksort($var2['attributes']);
            return $var1['attributes'] === $var2['attributes'];
        }

        // Fallback: compare the entire arrays
        return $var1 === $var2;
    }

    /**
     * Compare two customization objects for equality
     */
    private function compareCustomizations($custom1, $custom2)
    {
        // Normalize both customizations to arrays
        $c1 = is_array($custom1) ? $custom1 : json_decode(json_encode($custom1), true);
        $c2 = is_array($custom2) ? $custom2 : json_decode(json_encode($custom2), true);

        // If both are empty, they match
        if (empty($c1) && empty($c2)) {
            return true;
        }

        // If one is empty and other is not, they don't match
        if (empty($c1) || empty($c2)) {
            return false;
        }

        // Compare by sorting keys to ensure consistent comparison
        ksort($c1);
        ksort($c2);

        return $c1 === $c2;
    }

    private function trackTikTokAddToCartEvent(Request $request, Product $product, int $quantity, float $unitPrice): void
    {
        /** @var TikTokEventsService $tikTok */
        $tikTok = app(TikTokEventsService::class);

        if (!$tikTok->enabled()) {
            return;
        }

        $user = Auth::user();

        $tikTok->track(
            'AddToCart',
            [
                'value' => round($unitPrice * $quantity, 2),
                'currency' => 'USD',
                'contents' => [[
                    'content_id' => (string) $product->id,
                    'content_type' => 'product',
                    'content_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => round($unitPrice, 2),
                ]],
                'description' => optional($product->template)->name ?? $product->name,
            ],
            $request,
            [
                'email' => $user?->email,
                'phone' => $user?->phone,
                'external_id' => $user?->id,
            ],
            [
                'page' => [
                    'url' => $request->headers->get('referer'),
                ],
            ]
        );
    }
}
