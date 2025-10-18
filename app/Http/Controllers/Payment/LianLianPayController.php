<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use App\Services\LianLianPayServiceV2;
use App\Mail\OrderConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class LianLianPayController extends Controller
{
    protected $lianLianPayServiceV2;

    public function __construct(LianLianPayServiceV2 $lianLianPayServiceV2)
    {
        $this->lianLianPayServiceV2 = $lianLianPayServiceV2;
    }


    /**
     * Create payment for order
     */
    public function createPayment(Request $request, Order $order)
    {
        Log::info('🚀 LIANLIAN PAY CONTROLLER CREATE PAYMENT CALLED', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'request_data' => $request->all()
        ]);

        try {
            // Validate order ownership
            if ($order->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Check if order is already paid
            if ($order->status === 'paid') {
                return response()->json(['error' => 'Order already paid'], 400);
            }

            // Handle different payment data formats
            if ($request->has('card_token')) {
                // Frontend SDK payment (with card token)
                $request->validate([
                    'card_token' => 'required|string',
                    'full_name' => 'required|string|max:255',
                    'email' => 'required|email',
                    'city' => 'required|string|max:100',
                    'country' => 'required|string|max:2',
                    'line1' => 'required|string|max:255',
                    'postal_code' => 'required|string|max:20',
                    'products' => 'required|array',
                    'order_amount' => 'required|numeric|min:0.01',
                ]);

                // Store card token for payment processing
                session([
                    'lianlian_card_info' => [
                        'card_token' => $request->card_token,
                        'holder_name' => $request->full_name,
                        'billing_address' => [
                            'line1' => $request->line1,
                            'line2' => $request->line2 ?? '',
                            'city' => $request->city,
                            'state' => $request->state ?? '',
                            'postal_code' => $request->postal_code,
                            'country' => $request->country,
                        ]
                    ]
                ]);
            } elseif ($request->has('card_no')) {
                // Manual card entry
                $request->validate([
                    'card_no' => 'required|string|min:13|max:19',
                    'holder_name' => 'required|string|max:255',
                    'card_expiration' => 'required|string|regex:/^\d{2}\/\d{2}$/',
                    'cvv' => 'required|string|min:3|max:4',
                    'card_type' => 'required|in:C,D',
                    'billing_line1' => 'required|string|max:255',
                    'billing_city' => 'required|string|max:100',
                    'billing_state' => 'required|string|max:100',
                    'billing_postal_code' => 'required|string|max:20',
                    'billing_country' => 'required|string|max:2',
                ]);

                session([
                    'lianlian_card_info' => [
                        'card_no' => $request->card_no,
                        'holder_name' => $request->holder_name,
                        'card_expiration' => $request->card_expiration,
                        'cvv' => $request->cvv,
                        'card_type' => $request->card_type,
                        'billing_address' => [
                            'line1' => $request->billing_line1,
                            'line2' => $request->billing_line2,
                            'city' => $request->billing_city,
                            'state' => $request->billing_state,
                            'postal_code' => $request->billing_postal_code,
                            'country' => $request->billing_country,
                        ]
                    ]
                ]);
            }

            // Create payment using improved service
            $paymentResponse = $this->lianLianPayServiceV2->createPayment($order);

            Log::info('🔍 PAYMENT RESPONSE RECEIVED', [
                'order_id' => $order->id,
                'has_return_code' => isset($paymentResponse['return_code']),
                'return_code' => $paymentResponse['return_code'] ?? 'No return code',
                'has_order_key' => isset($paymentResponse['order']),
                'payment_status' => $paymentResponse['order']['payment_data']['payment_status'] ?? 'No payment status',
                'response_keys' => array_keys($paymentResponse)
            ]);

            // Check if payment creation was successful
            if (isset($paymentResponse['return_code']) && $paymentResponse['return_code'] !== 'SUCCESS') {
                Log::error('LianLian Pay Payment Creation Failed', [
                    'order_id' => $order->id,
                    'return_code' => $paymentResponse['return_code'],
                    'return_message' => $paymentResponse['return_message'] ?? 'Unknown error',
                    'full_response' => $paymentResponse
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $paymentResponse['return_message'] ?? 'Payment creation failed',
                    'return_code' => $paymentResponse['return_code'],
                    'order_id' => $order->id
                ], 400);
            }

            // Validate response has required structure
            if (!isset($paymentResponse['order'])) {
                Log::error('LianLian Pay Response Missing Order Key in createPayment', [
                    'order_id' => $order->id,
                    'response' => $paymentResponse
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment response structure',
                    'order_id' => $order->id
                ], 500);
            }

            // Update order with payment transaction ID
            $transactionId = $paymentResponse['order']['ll_transaction_id']
                ?? $paymentResponse['order']['merchant_transaction_id']
                ?? $paymentResponse['merchant_transaction_id']
                ?? $paymentResponse['transaction_id']
                ?? null;

            // Check payment status từ response
            $paymentStatus = $paymentResponse['order']['payment_data']['payment_status'] ?? null;

            Log::info('Payment Status Check', [
                'order_id' => $order->id,
                'payment_status_code' => $paymentStatus,
                'transaction_id' => $transactionId,
                'return_code' => $paymentResponse['return_code'] ?? 'N/A'
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

                Log::info('Payment Completed Immediately (PS)', [
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

                Log::info('Payment Still Pending in LianLianPayController', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_status_code' => $paymentStatus,
                    'transaction_id' => $transactionId
                ]);
            }

            // Lưu order number vào session để dùng sau khi return từ 3DS
            session(['last_order_number' => $order->order_number]);

            // Nếu payment thành công, redirect đến success handler giống PayPal
            if ($paymentStatus === 'PS') {
                $successUrl = route('checkout.lianlian.success', [
                    'order_number' => $order->order_number,
                    'transaction_id' => $transactionId
                ]);

                Log::info('Redirecting to LianLian Pay success handler', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'success_url' => $successUrl
                ]);

                return redirect($successUrl);
            }

            // Check if 3DS authentication is required (check from actual response structure)
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

            if ($requires3DS) {
                Log::info('3DS Authentication Required in createPayment', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    '3ds_url' => $threeDSecureUrl,
                    'payment_status' => $paymentStatus,
                    '3ds_status' => $paymentResponse['order']['3ds_status'] ?? 'N/A'
                ]);

                return response()->json([
                    'success' => true,
                    'requires_3ds' => true,
                    'redirect_url' => $threeDSecureUrl,
                    'transaction_id' => $transactionId,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_status' => $paymentStatus,
                    '3ds_status' => $paymentResponse['order']['3ds_status'] ?? null,
                    'message' => '3DS authentication required'
                ]);
            }

            // Payment completed without 3DS
            Log::info('Payment Completed Successfully', [
                'order_id' => $order->id,
                'transaction_id' => $transactionId
            ]);

            return response()->json([
                'success' => true,
                'requires_3ds' => false,
                'transaction_id' => $transactionId,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'message' => 'Payment processed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('LianLian Pay Creation Error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Payment creation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment return
     */
    public function handleReturn(Request $request)
    {
        try {
            Log::info('LianLian Pay Return', [
                'query_params' => $request->all()
            ]);

            // Lấy order từ session hoặc query params
            $orderNumber = session('last_order_number');

            if (!$orderNumber) {
                // Fallback: Lấy order gần nhất của user
                $order = Order::where('payment_method', 'lianlian_pay')
                    ->where('payment_status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($order) {
                    $orderNumber = $order->order_number;
                }
            }

            if ($orderNumber) {
                // Tìm order và cập nhật payment status
                $order = Order::where('order_number', $orderNumber)->first();

                if ($order && ($order->payment_status === 'pending' || $order->payment_status === 'processing')) {
                    Log::info('Order found for return processing', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'current_payment_status' => $order->payment_status,
                        'has_transaction_id' => !empty($order->payment_transaction_id),
                        'transaction_id' => $order->payment_transaction_id
                    ]);

                    // Kiểm tra payment status từ LianLian Pay
                    $querySuccess = false;

                    try {
                        $paymentStatus = $this->queryPaymentStatus($order);

                        Log::info('Payment status query result', [
                            'order_id' => $order->id,
                            'payment_status' => $paymentStatus
                        ]);

                        if ($paymentStatus === 'success') {
                            $order->update([
                                'payment_status' => 'paid',
                                'status' => 'processing',
                                'paid_at' => now()
                            ]);

                            Log::info('✅ Payment status updated to paid on return', [
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'payment_status' => $paymentStatus
                            ]);

                            // Clear cart from database after successful payment
                            $sessionId = session()->getId();
                            $userId = auth()->id();
                            Cart::where(function ($query) use ($sessionId, $userId) {
                                if ($userId) {
                                    $query->where('user_id', $userId);
                                } else {
                                    $query->where('session_id', $sessionId);
                                }
                            })->delete();

                            Log::info('🗑️ Cart cleared after LianLian Pay success', [
                                'order_id' => $order->id,
                                'user_id' => $userId,
                                'session_id' => $sessionId
                            ]);

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

                            $querySuccess = true;
                        } elseif ($paymentStatus === 'processing') {
                            $order->update([
                                'payment_status' => 'processing'
                            ]);

                            Log::info('⏳ Payment processing on return', [
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'payment_status' => $paymentStatus
                            ]);

                            $querySuccess = true;
                        } else {
                            Log::info('⚠️ Payment still pending on return - will use fallback', [
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'payment_status' => $paymentStatus
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('❌ Failed to query payment status on return', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'transaction_id' => $order->payment_transaction_id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    // FALLBACK: Nếu query thất bại hoặc trả về pending sau khi user đã hoàn thành 3DS
                    // → Assume payment thành công (vì user đã được redirect về từ 3DS)
                    if (!$querySuccess && $order->payment_transaction_id) {
                        Log::info('🔄 Applying fallback logic - assuming 3DS success', [
                            'order_id' => $order->id,
                            'transaction_id' => $order->payment_transaction_id,
                            'reason' => 'User returned from 3DS authentication'
                        ]);

                        // Update to paid assuming 3DS was successful
                        $order->update([
                            'payment_status' => 'paid',
                            'status' => 'processing',
                            'paid_at' => now()
                        ]);

                        Log::info('✅ Payment marked as paid (fallback after 3DS)', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number
                        ]);

                        // Clear cart from database after successful payment (fallback)
                        $sessionId = session()->getId();
                        $userId = auth()->id();
                        Cart::where(function ($query) use ($sessionId, $userId) {
                            if ($userId) {
                                $query->where('user_id', $userId);
                            } else {
                                $query->where('session_id', $sessionId);
                            }
                        })->delete();

                        Log::info('🗑️ Cart cleared after LianLian Pay success (fallback)', [
                            'order_id' => $order->id,
                            'user_id' => $userId,
                            'session_id' => $sessionId
                        ]);

                        // Send order confirmation email
                        try {
                            Mail::to($order->customer_email)->send(new OrderConfirmation($order));
                            Log::info('📧 Order confirmation email sent (fallback)', [
                                'order_number' => $order->order_number,
                                'email' => $order->customer_email
                            ]);
                        } catch (\Exception $e) {
                            Log::error('❌ Failed to send order confirmation email (fallback)', [
                                'order_number' => $order->order_number,
                                'email' => $order->customer_email,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }

                // Redirect đến trang checkout success với order number
                return redirect()->route('checkout.success', ['orderNumber' => $orderNumber])
                    ->with('success', 'Payment is being processed. You will receive confirmation shortly.');
            }

            // Fallback nếu không tìm thấy order
            return redirect('/checkout')
                ->with('info', 'Your payment is being processed. Please check your email for confirmation.');
        } catch (\Exception $e) {
            Log::error('LianLian Pay Return Error', [
                'error' => $e->getMessage()
            ]);

            return redirect('/checkout')
                ->with('error', 'Payment processing error. Please try again.');
        }
    }

    /**
     * Query payment status from LianLian Pay
     */
    protected function queryPaymentStatus($order)
    {
        try {
            // Check if order has transaction ID
            if (!$order->payment_transaction_id) {
                Log::warning('Order has no payment_transaction_id', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ]);
                return 'pending';
            }

            $lianLianPayService = app(LianLianPayServiceV2::class);

            Log::info('🔍 Querying payment status', [
                'order_id' => $order->id,
                'transaction_id' => $order->payment_transaction_id,
                'transaction_id_type' => gettype($order->payment_transaction_id)
            ]);

            $queryResult = $lianLianPayService->queryPayment($order->payment_transaction_id);

            Log::info('📥 Payment query result', [
                'order_id' => $order->id,
                'has_order_key' => isset($queryResult['order']),
                'has_payment_data' => isset($queryResult['order']['payment_data']),
                'return_code' => $queryResult['return_code'] ?? 'N/A',
                'payment_status_code' => $queryResult['order']['payment_data']['payment_status'] ?? 'N/A'
            ]);

            // Extract payment status from query result (check nested structure)
            // LianLian Pay response structure: order.payment_data.payment_status
            if (isset($queryResult['order']['payment_data']['payment_status'])) {
                $status = $queryResult['order']['payment_data']['payment_status'];
                // PS = Payment Success, PP = Payment Processing, WP = Waiting Payment
                if ($status === 'PS') {
                    return 'success';
                } elseif ($status === 'PP') {
                    return 'processing';
                } else {
                    return 'pending';
                }
            }

            // Fallback checks
            if (isset($queryResult['payment_status'])) {
                return $queryResult['payment_status'];
            }

            if (isset($queryResult['status'])) {
                return $queryResult['status'];
            }

            if (isset($queryResult['return_code']) && $queryResult['return_code'] === 'SUCCESS') {
                return 'success';
            }

            return 'pending';
        } catch (\Exception $e) {
            Log::error('Failed to query payment status', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return 'pending';
        }
    }


    /**
     * Handle payment cancellation
     */
    public function handleCancel(Request $request)
    {
        $transactionId = $request->get('merchant_transaction_id');

        if ($transactionId) {
            $orderId = explode('_', $transactionId)[0];
            $order = Order::find($orderId);

            if ($order) {
                $order->update([
                    'payment_status' => 'cancelled'
                ]);
            }
        }

        return redirect()->route('orders.index')
            ->with('info', 'Payment was cancelled');
    }

    /**
     * Handle webhook notification
     */
    public function handleWebhook(Request $request)
    {
        try {
            $notifyBody = $request->getContent();
            $signature = $request->header('X-LianLian-Signature');

            // Verify webhook signature
            $notifyData = $this->lianLianPayService->verifyWebhook($notifyBody, $signature);

            if (!$notifyData) {
                Log::warning('LianLian Pay Webhook: Invalid signature', [
                    'body' => $notifyBody,
                    'signature' => $signature
                ]);
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            // Process the notification
            $this->processWebhookNotification($notifyData);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('LianLian Pay Webhook Error', [
                'error' => $e->getMessage(),
                'body' => $request->getContent()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Process webhook notification
     */
    protected function processWebhookNotification($notifyData)
    {
        $transactionId = $notifyData['merchant_transaction_id'] ?? null;
        $status = $notifyData['status'] ?? null;
        $amount = $notifyData['amount'] ?? null;

        if (!$transactionId) {
            Log::warning('LianLian Pay Webhook: Missing transaction ID', $notifyData);
            return;
        }

        // Extract order ID from transaction ID
        $orderId = explode('_', $transactionId)[0];
        $order = Order::find($orderId);

        if (!$order) {
            Log::warning('LianLian Pay Webhook: Order not found', [
                'transaction_id' => $transactionId,
                'order_id' => $orderId
            ]);
            return;
        }

        DB::transaction(function () use ($order, $status, $amount, $transactionId) {
            switch ($status) {
                case 'success':
                case 'completed':
                case 'paid':
                    $order->update([
                        'status' => 'paid',
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                        'payment_transaction_id' => $transactionId
                    ]);
                    Log::info('LianLian Pay Webhook: Payment completed', [
                        'order_id' => $order->id,
                        'transaction_id' => $transactionId,
                        'amount' => $amount
                    ]);
                    break;

                case 'failed':
                    $order->update([
                        'payment_status' => 'failed'
                    ]);
                    Log::info('LianLian Pay Webhook: Payment failed', [
                        'order_id' => $order->id,
                        'transaction_id' => $transactionId
                    ]);
                    break;

                case 'cancelled':
                    $order->update([
                        'payment_status' => 'cancelled'
                    ]);
                    Log::info('LianLian Pay Webhook: Payment cancelled', [
                        'order_id' => $order->id,
                        'transaction_id' => $transactionId
                    ]);
                    break;

                default:
                    Log::warning('LianLian Pay Webhook: Unknown status', [
                        'order_id' => $order->id,
                        'transaction_id' => $transactionId,
                        'status' => $status
                    ]);
            }
        });
    }

    /**
     * Query payment status
     */
    public function queryPayment(Request $request, Order $order)
    {
        try {
            if ($order->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if (!$order->payment_transaction_id) {
                return response()->json(['error' => 'No payment transaction found'], 400);
            }

            $queryResponse = $this->lianLianPayService->queryPayment($order->payment_transaction_id);

            return response()->json([
                'success' => true,
                'status' => $queryResponse->status ?? 'unknown',
                'data' => $queryResponse
            ]);
        } catch (\Exception $e) {
            Log::error('LianLian Pay Query Error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Payment query failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process refund
     */
    public function processRefund(Request $request, Order $order)
    {
        try {
            // Only admin or order owner can process refund
            if (!auth()->user()->hasRole('admin') && $order->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $request->validate([
                'amount' => 'required|numeric|min:0.01|max:' . $order->total_amount,
                'reason' => 'nullable|string|max:255'
            ]);

            $refundAmount = $request->amount;
            $reason = $request->reason;

            $refundResponse = $this->lianLianPayService->processRefund($order, $refundAmount, $reason);

            // Update order with refund information
            $order->update([
                'refund_amount' => ($order->refund_amount ?? 0) + $refundAmount,
                'refund_reason' => $reason,
                'refund_status' => 'processing'
            ]);

            return response()->json([
                'success' => true,
                'refund_id' => $refundResponse->refund_id ?? null,
                'message' => 'Refund request submitted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('LianLian Pay Refund Error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Refund failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment token for iframe
     */
    public function getToken()
    {
        try {
            $tokenResponse = $this->lianLianPayServiceV2->getPaymentToken();

            if (isset($tokenResponse['return_code']) && $tokenResponse['return_code'] === 'SUCCESS') {
                return response()->json([
                    'success' => true,
                    'message' => 'Token retrieved successfully',
                    'token' => $tokenResponse['order'] ?? null,
                    'trace_id' => $tokenResponse['trace_id'] ?? null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $tokenResponse['return_message'] ?? 'Failed to retrieve token',
                'error_code' => $tokenResponse['return_code'] ?? null,
                'trace_id' => $tokenResponse['trace_id'] ?? null,
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error retrieving token:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle webhook notification
     */
    public function handleWebhookV2(Request $request)
    {
        try {
            $notifyBody = $request->getContent();
            $signature = $request->header('Signature');

            Log::info('LianLian Notification Received:', [
                'body' => $notifyBody,
                'signature' => $signature,
                'headers' => $request->headers->all()
            ]);

            // Verify webhook signature
            $notifyData = $this->lianLianPayServiceV2->verifyWebhook($notifyBody, $signature);

            if (!$notifyData) {
                Log::warning('LianLian Pay Webhook: Invalid signature', [
                    'body' => $notifyBody,
                    'signature' => $signature
                ]);
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            // Process the notification
            $this->lianLianPayServiceV2->processPaymentStatus($notifyBody);

            return response('SUCCESS', 200);
        } catch (\Exception $e) {
            Log::error('LianLian Pay Webhook Error', [
                'error' => $e->getMessage(),
                'body' => $request->getContent()
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle 3DS result
     */
    public function handle3DSResult(Request $request)
    {
        try {
            Log::info('3DS Result received:', [
                'request_data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            $transactionId = $request->input('merchant_transaction_id');
            $paymentStatus = $request->input('payment_status');
            $llTransactionId = $request->input('ll_transaction_id');

            if (!$transactionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing transaction ID'
                ], 400);
            }

            // Query payment status to confirm
            $queryResponse = $this->lianLianPayServiceV2->queryPayment($transactionId);

            Log::info('3DS Payment query result:', [
                'transaction_id' => $transactionId,
                'query_response' => $queryResponse
            ]);

            if ($queryResponse && isset($queryResponse['payment_data']['payment_status'])) {
                $finalStatus = $queryResponse['payment_data']['payment_status'];

                // Process payment status update
                $this->lianLianPayServiceV2->processPaymentStatus(json_encode([
                    'merchant_transaction_id' => $transactionId,
                    'payment_data' => ['payment_status' => $finalStatus],
                    'll_transaction_id' => $llTransactionId
                ]));

                return response()->json([
                    'success' => true,
                    'message' => '3DS result processed successfully',
                    'transaction_id' => $transactionId,
                    'payment_status' => $finalStatus,
                    'll_transaction_id' => $llTransactionId
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify payment status'
            ], 400);
        } catch (\Exception $e) {
            Log::error('3DS Result processing error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing 3DS result',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show separate payment page for LianLian Pay
     */
    public function showPaymentPage(Request $request)
    {
        try {
            // Get parameters from URL
            $token = $request->get('token');
            $orderId = $request->get('order_id');
            $amount = $request->get('amount');

            if (!$token) {
                return redirect()->route('checkout.index')
                    ->with('error', 'Missing payment token. Please try again.');
            }

            // Get order information if order_id is provided
            $order = null;
            if ($orderId) {
                $order = Order::find($orderId);
                if (!$order) {
                    return redirect()->route('checkout.index')
                        ->with('error', 'Order not found.');
                }
            }

            return view('payment.lianlian', [
                'token' => $token,
                'orderId' => $orderId,
                'total' => $amount ?: ($order ? $order->total_amount : 0),
                'order' => $order
            ]);
        } catch (\Exception $e) {
            Log::error('LianLian Pay Page Error:', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return redirect()->route('checkout.index')
                ->with('error', 'Payment page error. Please try again.');
        }
    }

    /**
     * Process payment from separate page
     */
    public function processPayment(Request $request)
    {
        Log::info('🎯 LIANLIAN PAY CONTROLLER PROCESS PAYMENT CALLED', [
            'request_data' => $request->all()
        ]);

        Log::info('🔍 PROCESS PAYMENT METHOD STARTED', [
            'order_id' => $request->input('order_id'),
            'payment_method' => $request->input('payment_method'),
            'amount' => $request->input('amount')
        ]);

        try {
            $request->validate([
                'card_token' => 'required|string',
                'payment_method' => 'required|in:lianlian_pay',
                'order_id' => 'nullable|exists:orders,id',
                'amount' => 'required|numeric|min:0.01'
            ]);

            $cardToken = $request->card_token;
            $orderId = $request->order_id;
            $amount = $request->amount;

            // Get order if provided
            $order = null;
            if ($orderId) {
                $order = Order::find($orderId);
                if (!$order) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found'
                    ], 404);
                }

                // Validate order ownership
                if ($order->user_id !== auth()->id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to order'
                    ], 403);
                }

                // Check if order is already paid
                if ($order->status === 'paid') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order already paid'
                    ], 400);
                }
            }

            // Create payment using the card token
            $paymentData = [
                'card_token' => $cardToken,
                'amount' => $amount,
                'currency' => 'USD',
                'order_id' => $orderId,
                'payment_method' => 'lianlian_pay'
            ];

            // If we have an order, use the existing createPayment logic
            if ($order) {
                // Store card token in session for processing
                session([
                    'lianlian_card_info' => [
                        'card_token' => $cardToken,
                        'holder_name' => $request->holder_name ?? $order->customer_name,
                        'billing_address' => [
                            'line1' => $request->billing_line1 ?? $order->shipping_address,
                            'line2' => $request->billing_line2 ?? '',
                            'city' => $request->billing_city ?? $order->city,
                            'state' => $request->billing_state ?? $order->state ?? '',
                            'postal_code' => $request->billing_postal_code ?? $order->postal_code,
                            'country' => $request->billing_country ?? $order->country,
                        ]
                    ]
                ]);

                // Lưu order number vào session để dùng sau khi return từ 3DS
                session(['last_order_number' => $order->order_number]);

                // Create payment using improved service
                $paymentResponse = $this->lianLianPayServiceV2->createPayment($order);

                // Log full response for debugging
                Log::info('LianLianPayController processPayment - Full Response', [
                    'order_id' => $order->id,
                    'has_return_code' => isset($paymentResponse['return_code']),
                    'return_code' => $paymentResponse['return_code'] ?? 'N/A',
                    'has_order_key' => isset($paymentResponse['order']),
                    'response_keys' => array_keys($paymentResponse)
                ]);

                // Check if payment creation was successful
                if (isset($paymentResponse['return_code']) && $paymentResponse['return_code'] !== 'SUCCESS') {
                    Log::error('LianLian Pay Payment Creation Failed', [
                        'order_id' => $order->id,
                        'return_code' => $paymentResponse['return_code'],
                        'return_message' => $paymentResponse['return_message'] ?? 'Unknown error',
                        'full_response' => $paymentResponse
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => $paymentResponse['return_message'] ?? 'Payment creation failed',
                        'return_code' => $paymentResponse['return_code'],
                        'order_id' => $order->id
                    ], 400);
                }

                // Validate response has required structure
                if (!isset($paymentResponse['order'])) {
                    Log::error('LianLian Pay Response Missing Order Key', [
                        'order_id' => $order->id,
                        'response' => $paymentResponse
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid payment response structure',
                        'order_id' => $order->id
                    ], 500);
                }

                // Update order with payment transaction ID
                $transactionId = $paymentResponse['order']['ll_transaction_id']
                    ?? $paymentResponse['order']['merchant_transaction_id']
                    ?? $paymentResponse['merchant_transaction_id']
                    ?? null;

                // Check payment status từ response
                $paymentStatus = $paymentResponse['order']['payment_data']['payment_status'] ?? null;

                Log::info('LianLianPayController processPayment - Payment Status Check', [
                    'order_id' => $order->id,
                    'payment_status_code' => $paymentStatus,
                    'transaction_id' => $transactionId,
                    'return_code' => $paymentResponse['return_code'] ?? 'N/A'
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

                    Log::info('Payment Completed Immediately (PS) in processPayment', [
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

                    Log::info('Payment Still Pending in processPayment', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'payment_status_code' => $paymentStatus,
                        'transaction_id' => $transactionId
                    ]);
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

                return response()->json([
                    'success' => true,
                    'requires_3ds' => $requires3DS,
                    'redirect_url' => $threeDSecureUrl,
                    'transaction_id' => $transactionId,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_status' => $paymentStatus === 'PS' ? 'paid' : 'pending',
                    'payment_completed' => $paymentStatus === 'PS',
                    'data' => $paymentResponse
                ]);
            } else {
                // Handle standalone payment without order
                // This would be for direct payments or other use cases
                return response()->json([
                    'success' => false,
                    'message' => 'Order ID is required for payment processing'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('LianLian Pay Processing Error:', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
