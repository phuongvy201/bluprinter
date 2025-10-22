<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class StripePaymentController extends Controller
{
    public function __construct()
    {
        // Set Stripe API Key
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create Payment Intent for Stripe
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'amount' => 'required|numeric|min:0.5',
                'currency' => 'nullable|string|size:3',
            ]);

            $amount = $request->amount;
            $currency = $request->currency ?? 'usd';

            // Create Payment Intent
            $paymentIntent = PaymentIntent::create([
                'amount' => (int)($amount * 100), // Stripe expects amount in cents
                'currency' => strtolower($currency),
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'integration_check' => 'accept_a_payment',
                ],
            ]);

            return response()->json([
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe Payment Intent Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process Stripe Payment
     */
    public function processPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'payment_intent_id' => 'required|string',
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'shipping_address' => 'required|string|max:500',
                'city' => 'required|string|max:100',
                'state' => 'nullable|string|max:100',
                'postal_code' => 'required|string|max:20',
                'country' => 'required|string|max:2',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Retrieve Payment Intent from Stripe
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            // Check payment status
            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment has not been completed yet.',
                ], 400);
            }

            // Get cart from session
            $cart = session('cart', []);

            // Debug cart contents
            Log::info('Stripe Payment - Cart Debug', [
                'cart' => $cart,
                'cart_count' => count($cart),
                'session_id' => session()->getId(),
                'user_id' => auth()->id(),
            ]);

            if (empty($cart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty. Please add products to cart first.',
                ], 400);
            }

            // Calculate totals
            $subtotal = 0;
            $orderItems = [];

            foreach ($cart as $item) {
                $product = \App\Models\Product::find($item['product_id']);
                if (!$product) {
                    continue;
                }

                $price = $product->getEffectivePrice();
                $quantity = $item['quantity'];
                $total = $price * $quantity;

                $subtotal += $total;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $total,
                ];
            }

            // Calculate shipping (implement your logic)
            $shippingCost = $this->calculateShipping($validated['country']);
            $taxAmount = 0; // Implement tax calculation if needed
            $totalAmount = $subtotal + $shippingCost + $taxAmount;

            // Verify amount matches
            $paidAmount = $paymentIntent->amount / 100; // Convert from cents
            if (abs($paidAmount - $totalAmount) > 0.01) {
                Log::warning('Stripe payment amount mismatch', [
                    'paid' => $paidAmount,
                    'expected' => $totalAmount,
                ]);
            }

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $validated['shipping_address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postal_code' => $validated['postal_code'],
                'country' => $validated['country'],
                'notes' => $validated['notes'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => 'stripe',
                'payment_status' => 'paid',
                'payment_id' => $paymentIntent->id,
                'order_status' => 'processing',
            ]);

            // Create order items
            foreach ($orderItems as $itemData) {
                $order->items()->create($itemData);
            }

            // Clear cart
            session()->forget('cart');

            // Log successful payment
            Log::info('Stripe payment successful', [
                'order_id' => $order->id,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $totalAmount,
            ]);

            return response()->json([
                'success' => true,
                'order_number' => $order->order_number,
                'message' => 'Payment successful!',
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe process payment error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Stripe Webhook
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            if ($webhookSecret) {
                $event = Webhook::constructEvent(
                    $payload,
                    $sigHeader,
                    $webhookSecret
                );
            } else {
                $event = json_decode($payload);
            }

            // Handle the event
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $this->handlePaymentIntentSucceeded($paymentIntent);
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    $this->handlePaymentIntentFailed($paymentIntent);
                    break;

                case 'charge.refunded':
                    $charge = $event->data->object;
                    $this->handleChargeRefunded($charge);
                    break;

                default:
                    Log::info('Unhandled Stripe webhook event: ' . $event->type);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle successful payment intent
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        $order = Order::where('payment_id', $paymentIntent->id)->first();

        if ($order && $order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'processing',
            ]);

            Log::info('Payment intent succeeded for order: ' . $order->order_number);
        }
    }

    /**
     * Handle failed payment intent
     */
    protected function handlePaymentIntentFailed($paymentIntent)
    {
        $order = Order::where('payment_id', $paymentIntent->id)->first();

        if ($order) {
            $order->update([
                'payment_status' => 'failed',
                'order_status' => 'cancelled',
            ]);

            Log::warning('Payment intent failed for order: ' . $order->order_number);
        }
    }

    /**
     * Handle charge refunded
     */
    protected function handleChargeRefunded($charge)
    {
        // Find order by payment intent ID
        if (isset($charge->payment_intent)) {
            $order = Order::where('payment_id', $charge->payment_intent)->first();

            if ($order) {
                $order->update([
                    'payment_status' => 'refunded',
                    'order_status' => 'refunded',
                ]);

                Log::info('Charge refunded for order: ' . $order->order_number);
            }
        }
    }

    /**
     * Calculate shipping cost based on country
     */
    protected function calculateShipping($country)
    {
        // Implement your shipping calculation logic
        // This is a simple example
        $shippingRates = [
            'US' => 10.00,
            'GB' => 15.00,
            'default' => 20.00,
        ];

        return $shippingRates[$country] ?? $shippingRates['default'];
    }
}
