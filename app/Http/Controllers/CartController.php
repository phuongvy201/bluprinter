<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Services\ShippingCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $sessionId = session()->getId();
        $userId = Auth::id();

        // Get cart items from database
        $cartItems = Cart::with(['product.shop', 'product.template', 'variant'])
            ->where(function ($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->get();

        // Transform cart items to include media using getEffectiveMedia()
        $cartItems->each(function ($item) {
            if ($item->product) {
                $item->product->media = $item->product->getEffectiveMedia();
            }
        });

        // Calculate totals (without tax) including customizations
        $subtotal = $cartItems->sum(function ($item) {
            return $item->getTotalPriceWithCustomizations();
        });

        // Calculate shipping using ShippingCalculator
        $shipping = 0;
        $shippingDetails = null;

        if (!$cartItems->isEmpty()) {
            // Prepare cart items for shipping calculation
            $items = $cartItems->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            });

            // Calculate shipping for US (default)
            $calculator = new ShippingCalculator();
            $shippingResult = $calculator->calculateShipping($items, 'US');

            if ($shippingResult['success']) {
                $shipping = $shippingResult['total_shipping'];
                $shippingDetails = $shippingResult;
            }
        }

        $total = $subtotal + $shipping;

        return view('cart.index', compact('cartItems', 'subtotal', 'shipping', 'total', 'shippingDetails'));
    }
}
