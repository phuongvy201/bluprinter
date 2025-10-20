<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart;
use App\Services\PayPalService;
use App\Services\ShippingCalculator;
use App\Mail\OrderConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    public function index()
    {
        $sessionId = session()->getId();
        $userId = Auth::id();

        // Get cart items from database
        $cartItems = Cart::with(['product.shop', 'product.template'])
            ->where(function ($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Calculate totals
        $subtotal = 0;
        $products = [];

        foreach ($cartItems as $item) {
            $itemTotal = $item->getTotalPrice();
            $subtotal += $itemTotal;

            $products[] = [
                'product' => $item->product,
                'quantity' => $item->quantity,
                'total' => $itemTotal
            ];
        }

        // Get default shipping (US) or from session
        $defaultCountry = 'US';
        $shippingCost = 0;
        $shippingDetails = session()->get('shipping_details');

        // Calculate default shipping for US if not in session
        if (!$shippingDetails) {
            $items = $cartItems->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->base_price,
                ];
            });

            $calculator = new ShippingCalculator();
            $shippingDetails = $calculator->calculateShipping($items, $defaultCountry);

            if ($shippingDetails['success']) {
                $shippingCost = $shippingDetails['total_shipping'];
                session()->put('shipping_details', $shippingDetails);
            }
        } else {
            $shippingCost = $shippingDetails['total_shipping'] ?? 0;
        }

        $taxAmount = 0; // No tax
        $total = $subtotal + $shippingCost;

        return view('checkout.index', compact('products', 'subtotal', 'shippingCost', 'taxAmount', 'total', 'shippingDetails'));
    }

    public function process(Request $request)
    {
        Log::info('🔍 CHECKOUT PROCESS STARTED', [
            'method' => $request->method(),
            'url' => $request->url(),
            'payment_method' => $request->input('payment_method'),
            'data' => $request->all()
        ]);

        // Add detailed validation logging for debugging
        Log::info('🔍 VALIDATION DATA CHECK', [
            'customer_name' => $request->input('customer_name'),
            'customer_email' => $request->input('customer_email'),
            'shipping_address' => $request->input('shipping_address'),
            'city' => $request->input('city'),
            'postal_code' => $request->input('postal_code'),
            'country' => $request->input('country'),
            'has_paypal_order_id' => $request->has('paypal_order_id'),
            'paypal_order_id' => $request->input('paypal_order_id'),
        ]);

        $validationRules = [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'shipping_address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'payment_method' => 'required|in:paypal,lianlian_pay,stripe',
        ];

        // Add PayPal SDK specific validation if present
        if ($request->has('paypal_order_id')) {
            $validationRules['paypal_order_id'] = 'required|string|max:255';
            $validationRules['paypal_payer_id'] = 'required|string|max:255';
        }

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('🔍 VALIDATION FAILED', [
                'errors' => $e->errors(),
                'input_data' => $request->all()
            ]);
            throw $e;
        }

        $sessionId = session()->getId();
        $userId = Auth::id();

        // Get cart items from database
        $cartItems = Cart::with(['product.shop', 'product.template'])
            ->where(function ($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Get shipping details from session (calculated earlier via AJAX)
        $shippingDetails = session()->get('shipping_details');

        // If no shipping details, calculate now
        if (!$shippingDetails || !isset($shippingDetails['success']) || !$shippingDetails['success']) {
            // Prepare items for calculator
            $items = $cartItems->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->base_price,
                ];
            });

            $calculator = new ShippingCalculator();
            $shippingDetails = $calculator->calculateShipping($items, $request->country);

            if (!$shippingDetails['success']) {
                return back()->withInput()->withErrors(['shipping' => $shippingDetails['message']]);
            }
        }

        // Calculate totals
        $subtotal = 0;
        $shippingCost = $shippingDetails['total_shipping'];
        $products = [];

        foreach ($cartItems as $item) {
            $itemTotal = $item->getTotalPrice();
            $subtotal += $itemTotal;

            $products[] = [
                'product' => $item->product,
                'quantity' => $item->quantity,
                'total' => $itemTotal
            ];
        }

        $taxAmount = 0; // No tax
        $total = $subtotal + $shippingCost;

        // Create order
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'user_id' => Auth::id(),
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'shipping_address' => $request->shipping_address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_cost' => $shippingCost,
            'total_amount' => $total,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
        ]);

        // Create order items with shipping details
        foreach ($products as $item) {
            // Find shipping details for this product
            $itemShipping = collect($shippingDetails['items'])->firstWhere('product_id', $item['product']->id);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product']->id,
                'product_name' => $item['product']->name,
                'product_description' => $item['product']->description,
                'unit_price' => $item['product']->base_price,
                'quantity' => $item['quantity'],
                'total_price' => $item['total'],
                'shipping_cost' => $itemShipping['shipping_cost'] ?? 0,
                'is_first_item' => $itemShipping['is_first_item'] ?? false,
                'shipping_notes' => $itemShipping ? "Rate: {$itemShipping['shipping_rate_name']}" : null,
            ]);
        }

        // NOTE: Không xóa cart ở đây vì user chưa thanh toán!
        // Cart sẽ được xóa trong paypalSuccess() hoặc lianlianSuccess() sau khi payment thành công

        Log::info('Order created, waiting for payment confirmation', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_method' => $request->payment_method,
            'user_id' => $userId,
            'session_id' => $sessionId
        ]);

        // Check if this is an AJAX request (for LianLian Pay redirect)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'payment_method' => $order->payment_method
            ]);
        }

        // Handle payment based on method
        if ($request->payment_method === 'paypal') {
            Log::info('PayPal Payment Method Detected', [
                'has_paypal_order_id' => $request->has('paypal_order_id'),
                'has_paypal_payer_id' => $request->has('paypal_payer_id'),
                'paypal_order_id' => $request->input('paypal_order_id'),
                'paypal_payer_id' => $request->input('paypal_payer_id')
            ]);

            // Check if this is from PayPal SDK (has paypal_order_id)
            if ($request->has('paypal_order_id') && $request->has('paypal_payer_id')) {
                // This is from PayPal SDK - payment already completed on client side
                try {
                    Log::info('PayPal SDK Payment Processing', [
                        'order_id' => $order->id,
                        'paypal_order_id' => $request->paypal_order_id,
                        'paypal_payer_id' => $request->paypal_payer_id
                    ]);

                    // For PayPal SDK payments, the payment was already captured on the client side
                    // We should update the order status immediately since we have valid order_id and payer_id

                    Log::info('Updating order for PayPal SDK payment', [
                        'order_id' => $order->id,
                        'current_payment_status' => $order->payment_status,
                        'current_status' => $order->status,
                        'paypal_order_id' => $request->paypal_order_id
                    ]);

                    // Update order status to paid immediately (PayPal SDK confirmed the payment)
                    $updateResult = $order->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                        'payment_id' => $request->paypal_order_id,
                        'payment_transaction_id' => $request->paypal_payer_id,
                        'paid_at' => now()
                    ]);

                    Log::info('PayPal SDK Order Update Result', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'update_result' => $updateResult,
                        'original_payment_status' => $request->input('original_payment_status', 'unknown'),
                        'new_payment_status' => $order->fresh()->payment_status,
                        'new_status' => $order->fresh()->status,
                        'paypal_payment_id' => $request->paypal_order_id
                    ]);

                    // Try to verify with PayPal API (optional, for logging)
                    try {
                        $paypalService = new PayPalService();
                        $payment = $paypalService->capturePayment($request->paypal_order_id);

                        Log::info('PayPal API Verification Result', [
                            'order_id' => $order->id,
                            'payment_status_from_api' => $payment ? $payment->status : 'null'
                        ]);
                    } catch (\Exception $e) {
                        Log::warning('PayPal API verification failed but continuing', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    // Clear cart after successful payment
                    Cart::where('user_id', $userId)->orWhere('session_id', $sessionId)->delete();

                    // Clear shipping session
                    Session::forget('shipping_details');

                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Payment completed successfully',
                            'order_number' => $order->order_number,
                            'payment_completed' => true
                        ]);
                    }

                    return redirect()->route('checkout.success', $order->order_number)
                        ->with('success', 'Payment completed successfully!');
                } catch (\Exception $e) {
                    Log::error('PayPal SDK Payment Error', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                        'paypal_order_id' => $request->paypal_order_id
                    ]);

                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Payment processing failed: ' . $e->getMessage()
                        ], 400);
                    }

                    return redirect()->back()->with('error', 'Payment processing failed: ' . $e->getMessage());
                }
            } else {
                // This is the old PayPal flow (redirect-based)
                try {
                    $paypalService = new PayPalService();
                    $payment = $paypalService->createPayment($order, $products);

                    // Store order in session for PayPal callback
                    Session::put('pending_order', $order->id);

                    // Redirect to PayPal approval URL
                    return redirect($payment->approval_url);
                } catch (\Exception $e) {
                    Log::error('PayPal Initialization Error', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                    return redirect()->back()->with('error', 'Payment initialization failed: ' . $e->getMessage());
                }
            }
        } elseif ($request->payment_method === 'lianlian_pay') {
            Log::info('🎯 LIANLIAN PAY SECTION ENTERED', [
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);

            Log::info('🔍 CHECKOUT CONTROLLER LIANLIAN PAY FLOW', [
                'order_id' => $order->id,
                'has_card_token' => $request->has('card_token'),
                'request_data' => $request->all()
            ]);

            // Store order in session for LianLian Pay callback
            Session::put('pending_order', $order->id);

            // Check if card token is provided (from iframe binding card)
            if ($request->has('card_token')) {
                // Store card token and card type for payment processing
                session([
                    'lianlian_card_info' => [
                        'card_token' => $request->card_token,
                        'card_type' => $request->card_type ?? 'PAYMENT_TOKEN', // PAYMENT_TOKEN or LIANLIAN_TOKEN
                        'holder_name' => $order->customer_name,
                        'billing_address' => [
                            'line1' => $order->shipping_address,
                            'line2' => '',
                            'city' => $order->city,
                            'state' => $order->state ?? '',
                            'postal_code' => $order->postal_code,
                            'country' => $order->country,
                        ]
                    ]
                ]);

                // Create payment using LianLian Pay
                try {
                    $lianLianPayService = new \App\Services\LianLianPayServiceV2();
                    $paymentResponse = $lianLianPayService->createPayment($order);

                    // Check if payment was successful
                    if (isset($paymentResponse['return_code']) && $paymentResponse['return_code'] !== 'SUCCESS') {
                        Log::error('LianLian Pay Payment Failed', [
                            'order_id' => $order->id,
                            'return_code' => $paymentResponse['return_code'],
                            'return_message' => $paymentResponse['return_message'] ?? 'Unknown error',
                            'response' => $paymentResponse
                        ]);

                        return response()->json([
                            'success' => false,
                            'error' => 'Payment failed',
                            'message' => $paymentResponse['return_message'] ?? 'Payment processing failed',
                            'return_code' => $paymentResponse['return_code']
                        ], 400);
                    }

                    // Check if 3DS authentication is required
                    $requires3DS = false;
                    $threeDSecureUrl = null;

                    if (
                        isset($paymentResponse['order']['3ds_status']) &&
                        $paymentResponse['order']['3ds_status'] === 'CHALLENGE' &&
                        isset($paymentResponse['order']['payment_url'])
                    ) {
                        $requires3DS = true;
                        $threeDSecureUrl = $paymentResponse['order']['payment_url'];
                    } elseif (isset($paymentResponse['3ds_url'])) {
                        $requires3DS = true;
                        $threeDSecureUrl = $paymentResponse['3ds_url'];
                    } elseif (isset($paymentResponse['redirect_url'])) {
                        $requires3DS = true;
                        $threeDSecureUrl = $paymentResponse['redirect_url'];
                    }

                    // Update order with payment transaction ID
                    $transactionId = $paymentResponse['order']['ll_transaction_id']
                        ?? $paymentResponse['merchant_transaction_id']
                        ?? null;

                    // Check payment status từ response
                    $paymentStatus = $paymentResponse['order']['payment_data']['payment_status'] ?? null;

                    Log::info('CheckoutController Payment Status Check', [
                        'order_id' => $order->id,
                        'payment_status_code' => $paymentStatus,
                        'transaction_id' => $transactionId,
                        'return_code' => $paymentResponse['return_code']
                    ]);

                    // Nếu payment_status = "PS" (Payment Success), mark order as paid ngay
                    if ($paymentStatus === 'PS') {
                        $order->update([
                            'payment_method' => 'lianlian_pay',
                            'payment_transaction_id' => $transactionId,
                            'payment_status' => 'paid',
                            'status' => 'processing',
                            'paid_at' => now()
                        ]);

                        Log::info('Payment Completed Immediately (PS) in CheckoutController', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'transaction_id' => $transactionId
                        ]);
                    } else {
                        // Nếu chưa paid, set pending
                        $order->update([
                            'payment_method' => 'lianlian_pay',
                            'payment_transaction_id' => $transactionId,
                            'payment_status' => 'pending'
                        ]);

                        Log::info('Payment Still Pending', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'payment_status_code' => $paymentStatus,
                            'transaction_id' => $transactionId
                        ]);
                    }

                    // NOTE: Không xóa cart ở đây, chờ đến khi payment thành công
                    // Cart sẽ được xóa trong lianlianSuccess() sau khi verify payment

                    // Return JSON response for frontend
                    return response()->json([
                        'success' => true,
                        'requires_3ds' => $requires3DS,
                        'redirect_url' => $threeDSecureUrl,
                        'transaction_id' => $paymentResponse['merchant_transaction_id'] ?? null,
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'data' => $paymentResponse
                    ]);
                } catch (\Exception $e) {
                    Log::error('LianLian Pay Creation Error', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);

                    return response()->json([
                        'success' => false,
                        'error' => 'Payment creation failed',
                        'message' => $e->getMessage()
                    ], 500);
                }
            } else {
                // No card token provided - this should not happen with iframe integration
                return response()->json([
                    'success' => false,
                    'error' => 'No card token provided',
                    'message' => 'Please enter your card information in the payment form'
                ], 400);
            }
        }

        // For other payment methods (should not reach here normally)
        // Cart will be cleared in respective success callbacks
        return redirect()->route('checkout.success', $order->order_number);
    }

    public function success($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        return view('checkout.success', compact('order'));
    }

    public function paypalSuccess(Request $request)
    {
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');

        if (!$paymentId || !$payerId) {
            return redirect()->route('checkout.index')->with('error', 'Payment failed.');
        }

        try {
            $paypalService = new PayPalService();
            $payment = $paypalService->executePayment($paymentId, $payerId);

            // Get pending order from session
            $orderId = Session::get('pending_order');
            if (!$orderId) {
                return redirect()->route('checkout.index')->with('error', 'Order not found.');
            }

            $order = Order::findOrFail($orderId);

            // Update payment status
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'payment_id' => $paymentId,
                'paid_at' => now()
            ]);

            // Clear session and cart from database
            $sessionId = session()->getId();
            $userId = Auth::id();

            Session::forget('pending_order');
            Cart::where(function ($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })->delete();

            // Send order confirmation email
            try {
                Mail::to($order->customer_email)->send(new OrderConfirmation($order));
                Log::info('📧 Order confirmation email sent', [
                    'order_number' => $order->order_number,
                    'email' => $order->customer_email
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Failed to send order confirmation email', [
                    'order_number' => $order->order_number,
                    'email' => $order->customer_email,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('checkout.success', $order->order_number)
                ->with('success', 'Payment completed successfully!');
        } catch (\Exception $e) {
            return redirect()->route('checkout.index')->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    public function paypalCancel()
    {
        return redirect()->route('checkout.index')
            ->with('error', 'Payment was cancelled.');
    }

    public function lianlianSuccess(Request $request)
    {
        $orderNumber = $request->get('order_number');
        $transactionId = $request->get('transaction_id');

        if (!$orderNumber) {
            return redirect()->route('checkout.index')->with('error', 'Order not found.');
        }

        try {
            $order = Order::where('order_number', $orderNumber)->firstOrFail();

            // Update payment status giống PayPal
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'payment_transaction_id' => $transactionId,
                'paid_at' => now()
            ]);

            // Clear session
            Session::forget('pending_order');

            // Clear cart from database after successful payment
            $sessionId = session()->getId();
            $userId = Auth::id();
            Cart::where(function ($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })->delete();

            // Send order confirmation email
            try {
                Mail::to($order->customer_email)->send(new OrderConfirmation($order));
                Log::info('📧 Order confirmation email sent', [
                    'order_number' => $order->order_number,
                    'email' => $order->customer_email
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Failed to send order confirmation email', [
                    'order_number' => $order->order_number,
                    'email' => $order->customer_email,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('checkout.success', $order->order_number)
                ->with('success', 'Payment completed successfully!');
        } catch (\Exception $e) {
            return redirect()->route('checkout.index')->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    public function lianlianCancel()
    {
        return redirect()->route('checkout.index')
            ->with('error', 'Payment was cancelled.');
    }

    /**
     * Calculate shipping cost via AJAX
     */
    public function calculateShipping(Request $request)
    {
        $request->validate([
            'country' => 'required|string|size:2',
        ]);

        $sessionId = session()->getId();
        $userId = Auth::id();

        // Check if items are provided directly (from product page)
        if ($request->has('items') && is_array($request->items)) {
            $items = $request->items;
        } else {
            // Get cart items (from cart/checkout page)
            $cartItems = Cart::with(['product.template.category'])
                ->where(function ($query) use ($sessionId, $userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('session_id', $sessionId);
                    }
                })
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty'
                ], 400);
            }

            // Prepare cart items for calculator
            $items = $cartItems->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->base_price,
                ];
            });
        }

        // Calculate shipping
        $calculator = new ShippingCalculator();
        $shippingResult = $calculator->calculateShipping($items, $request->country);

        if (!$shippingResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $shippingResult['message']
            ], 400);
        }

        // Store shipping details in session
        session()->put('shipping_details', $shippingResult);

        return response()->json([
            'success' => true,
            'shipping' => $shippingResult
        ]);
    }
}
