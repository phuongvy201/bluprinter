<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use lianlianpay\v3sdk\core\PaySDK;
use lianlianpay\v3sdk\model\Address;
use lianlianpay\v3sdk\model\Card;
use lianlianpay\v3sdk\model\Customer;
use lianlianpay\v3sdk\model\MerchantOrder;
use lianlianpay\v3sdk\model\PayRequest;
use lianlianpay\v3sdk\model\Product;
use lianlianpay\v3sdk\model\RequestPaymentData;
use lianlianpay\v3sdk\model\Shipping;
use lianlianpay\v3sdk\service\Payment;
use lianlianpay\v3sdk\service\Notification;

class LianLianPayServiceV2
{
    protected $merchantId;
    protected $subMerchantId;
    protected $publicKey;
    protected $privateKey;
    protected $sandbox;
    protected $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('lianlian.merchant_id');
        $this->subMerchantId = config('lianlian.sub_merchant_id');
        $this->publicKey = config('lianlian.public_key');
        $this->privateKey = config('lianlian.private_key');
        $this->sandbox = config('lianlian.sandbox');
        $this->baseUrl = $this->sandbox ? config('lianlian.sandbox_url') : config('lianlian.production_url');
    }

    /**
     * Create payment request using proper SDK models
     * Tận dụng tối đa SDK LianLian Pay
     */
    public function createPayment(Order $order)
    {
        Log::info('🔧 LIANLIAN PAY SERVICE CREATE PAYMENT CALLED', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total_amount' => $order->total_amount
        ]);

        try {
            // Log input data
            Log::info('LianLian Pay Input Data:', [
                'order_id' => $order->id,
                'order_amount' => $order->total_amount,
                'customer_name' => $order->customer_name,
                'country' => $order->country
            ]);

            // Initialize SDK
            $paySdk = PaySDK::getInstance();
            $paySdk->init($this->sandbox);
            $paySdk->setKey($this->privateKey, $this->publicKey);

            // Log SDK configuration
            Log::info('LianLian SDK Config:', [
                'sandbox_mode' => $this->sandbox,
                'merchant_id' => $this->merchantId,
                'private_key_length' => strlen($this->privateKey),
                'public_key_length' => strlen($this->publicKey)
            ]);

            // Tạo PayRequest object theo code mẫu
            $payRequest = new PayRequest();

            // Basic payment information - chỉ những trường cần thiết
            $payRequest->merchant_id = $this->merchantId;
            $payRequest->biz_code = 'EC';
            $payRequest->country = $order->country ?? 'US';

            // Set URLs - chỉ những trường cần thiết
            $payRequest->redirect_url = url('/payment/lianlian/return');
            $payRequest->notification_url = url('/payment/lianlian/webhook-v2');

            // Generate transaction ID
            $time = now()->format('YmdHis');
            $merchantTransactionId = 'Order-' . $time;
            $payRequest->merchant_transaction_id = $merchantTransactionId;
            $payRequest->payment_method = 'inter_credit_card';

            // Create address object - chỉ những trường cần thiết
            $address = new Address();
            $address->city = $order->city;
            $address->country = $order->country ?? 'US';
            $address->line1 = $order->shipping_address;
            $address->line2 = '';
            $address->state = $order->state ?? '';
            $address->postal_code = $order->postal_code;

            // Create customer object - chỉ những trường cần thiết
            $customer = new Customer();
            $customer->address = $address;
            $customer->customer_type = 'I';
            $customer->full_name = $order->customer_name;
            $customer->email = $order->customer_email;
            $customer->first_name = $this->getFirstName($order->customer_name);
            $customer->last_name = $this->getLastName($order->customer_name);
            $payRequest->customer = $customer;

            // Create products array - chỉ những trường cần thiết
            $products = [];
            foreach ($order->items as $item) {
                $product = new Product();
                $product->category = 'general';
                $product->name = $item->product_name;
                $product->price = $item->unit_price;
                $product->product_id = (string)$item->product_id;
                $product->quantity = $item->quantity;
                $product->shipping_provider = 'other';
                $product->sku = 'SKU-' . $item->product_id;
                $product->url = url('/products/' . $item->product_id);
                $products[] = $product;
            }

            // Create shipping object - chỉ những trường cần thiết
            $shipping = new Shipping();
            $shipping->address = $address;
            $shipping->name = $order->customer_name;
            $shipping->phone = $order->customer_phone ?? '';
            $shipping->cycle = '48h';

            // Create merchant order object - chỉ những trường cần thiết
            $merchantOrder = new MerchantOrder();
            $merchantOrder->merchant_order_id = $merchantTransactionId;
            $merchantOrder->merchant_order_time = $time;
            $merchantOrder->order_amount = $order->total_amount;
            $merchantOrder->order_currency_code = 'USD';
            $merchantOrder->order_description = 'Order from Bluprinter';
            $merchantOrder->products = $products;
            $merchantOrder->shipping = $shipping;

            $payRequest->merchant_order = $merchantOrder;

            // Add card information if available in session - chỉ những trường cần thiết
            $cardInfo = session('lianlian_card_info');
            if ($cardInfo) {
                $paymentData = new RequestPaymentData();
                $card = new Card();
                $card->holder_name = $cardInfo['holder_name'];

                // Check if using tokenization or manual card entry
                if (isset($cardInfo['card_token']) && $cardInfo['card_token']) {
                    // Using tokenization (from iframe binding card)
                    $card->card_token = $cardInfo['card_token'];
                    Log::info('Using card token for payment', ['token' => substr($cardInfo['card_token'], 0, 10) . '...']);
                } else {
                    // Manual card entry (fallback)
                    $card->card_no = str_replace(' ', '', $cardInfo['card_no']);
                    $card->card_type = $cardInfo['card_type'];
                    $card->card_expiration_year = '20' . substr($cardInfo['card_expiration'], 3, 2);
                    $card->card_expiration_month = substr($cardInfo['card_expiration'], 0, 2);
                    $card->cvv = $cardInfo['cvv'];
                }

                $paymentData->card = $card;
                $payRequest->payment_data = $paymentData;
            }

            // Log request details
            Log::info('LianLian Pay Request Details:', [
                'merchant_id' => $payRequest->merchant_id,
                'transaction_id' => $merchantTransactionId,
                'amount' => $merchantOrder->order_amount,
                'currency' => $merchantOrder->order_currency_code,
                'customer_name' => $customer->full_name,
                'country' => $address->country,
                'products_count' => count($products)
            ]);

            // Debug: Log PayRequest object structure
            Log::info('LianLian PayRequest Object:', [
                'merchant_id' => $payRequest->merchant_id,
                'biz_code' => $payRequest->biz_code,
                'country' => $payRequest->country,
                'merchant_order' => [
                    'merchant_order_id' => $payRequest->merchant_order->merchant_order_id,
                    'order_amount' => $payRequest->merchant_order->order_amount,
                    'order_currency_code' => $payRequest->merchant_order->order_currency_code,
                    'products_count' => count($payRequest->merchant_order->products),
                    'shipping' => $payRequest->merchant_order->shipping ? 'present' : 'missing'
                ],
                'payment_data' => $payRequest->payment_data ? 'present' : 'missing',
                'customer' => $payRequest->customer ? 'present' : 'missing'
            ]);

            // Log PayRequest object để debug
            Log::info('LianLian PayRequest Object:', [
                'merchant_id' => $payRequest->merchant_id,
                'merchant_transaction_id' => $payRequest->merchant_transaction_id,
                'payment_method' => $payRequest->payment_method,
                'country' => $payRequest->country,
                'terminal_data' => [
                    'user_order_ip' => $payRequest->terminal_data->user_order_ip ?? 'missing',
                    'user_client_mode' => $payRequest->terminal_data->user_client_mode ?? 'missing',
                    'user_client_app_type' => $payRequest->terminal_data->user_client_app_type ?? 'missing'
                ]
            ]);

            // Tạo payment object và gọi pay method theo code mẫu
            $payment = new Payment();

            // Gọi payment->pay method với đầy đủ tham số
            $payResponse = $payment->pay($payRequest, $this->privateKey, $this->publicKey);

            // Log response theo format SDK
            $payResponseJson = json_encode($payResponse, JSON_PRETTY_PRINT);
            Log::info('LianLian Pay Response JSON:', ['response' => $payResponseJson]);

            // Log the response
            Log::info('LianLian Pay Response', [
                'order_id' => $order->id,
                'return_code' => $payResponse['return_code'] ?? 'No return code',
                'return_message' => $payResponse['return_message'] ?? 'No message',
                'trace_id' => $payResponse['trace_id'] ?? 'No trace ID',
                'full_response' => $payResponse
            ]);

            // Xử lý 3DS authentication
            if (isset($payResponse['return_code']) && $payResponse['return_code'] === 'SUCCESS') {
                // Kiểm tra xem có yêu cầu 3DS authentication không
                $requires3DS = false;
                $threeDSecureUrl = null;
                $paymentStatus = null;
                $threeDSStatus = null;

                // Kiểm tra 3DS status và payment_url trong order object
                if (
                    isset($payResponse['order']['3ds_status']) &&
                    $payResponse['order']['3ds_status'] === 'CHALLENGE' &&
                    isset($payResponse['order']['payment_url']) &&
                    !empty($payResponse['order']['payment_url'])
                ) {
                    $requires3DS = true;
                    $threeDSecureUrl = $payResponse['order']['payment_url'];
                    $paymentStatus = $payResponse['order']['payment_data']['payment_status'] ?? null;
                    $threeDSStatus = $payResponse['order']['3ds_status'];
                }
                // Fallback: Kiểm tra các trường khác có thể chứa 3DS URL
                elseif (isset($payResponse['3ds_url']) && !empty($payResponse['3ds_url'])) {
                    $requires3DS = true;
                    $threeDSecureUrl = $payResponse['3ds_url'];
                } elseif (isset($payResponse['redirect_url']) && !empty($payResponse['redirect_url'])) {
                    $requires3DS = true;
                    $threeDSecureUrl = $payResponse['redirect_url'];
                } elseif (isset($payResponse['payment_data']['3ds_url']) && !empty($payResponse['payment_data']['3ds_url'])) {
                    $requires3DS = true;
                    $threeDSecureUrl = $payResponse['payment_data']['3ds_url'];
                }

                Log::info('3DS Authentication Check:', [
                    'order_id' => $order->id,
                    'requires_3ds' => $requires3DS,
                    '3ds_url' => $threeDSecureUrl,
                    'payment_status' => $paymentStatus,
                    '3ds_status' => $threeDSStatus
                ]);

                // Luôn trả về cấu trúc thống nhất từ $payResponse
                // Không cần return riêng cho 3DS vì controller sẽ tự check
            }

            // Luôn return $payResponse để giữ cấu trúc thống nhất
            return $payResponse;
        } catch (\Exception $e) {
            Log::error('LianLian Pay Error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception('Payment creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get payment token for iframe
     */
    public function getPaymentToken()
    {
        try {
            // Initialize SDK với keys
            $paySdk = PaySDK::getInstance();
            $paySdk->init($this->sandbox);
            $paySdk->setKey($this->privateKey, $this->publicKey);

            // Log token request
            Log::info('LianLian Pay Token Request:', [
                'merchant_id' => $this->merchantId,
                'sandbox_mode' => $this->sandbox,
                'timestamp' => now()->format('YmdHis')
            ]);

            // Tạo payment object và gọi get_token method
            $payment = new Payment();
            $payTokenResponse = $payment->get_token($this->merchantId, $this->privateKey, $this->publicKey);

            // Log response theo format SDK
            $tokenResponseJson = json_encode($payTokenResponse, JSON_PRETTY_PRINT);
            Log::info('LianLian Pay Token Response JSON:', ['response' => $tokenResponseJson]);

            // Log chi tiết response
            Log::info('LianLian Pay Token Response', [
                'return_code' => $payTokenResponse['return_code'] ?? 'No return code',
                'return_message' => $payTokenResponse['return_message'] ?? 'No message',
                'trace_id' => $payTokenResponse['trace_id'] ?? 'No trace ID',
                'sign_verify' => $payTokenResponse['sign_verify'] ?? false,
                'token' => isset($payTokenResponse['order']) ? substr($payTokenResponse['order'], 0, 10) . '...' : 'No token'
            ]);

            return $payTokenResponse;
        } catch (\Exception $e) {
            Log::error('LianLian Pay Token Error', [
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Token generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Query payment status
     */
    public function queryPayment($merchantTransactionId)
    {
        $transactionId = null;

        try {
            // Handle both Order object and transaction ID string
            if (is_object($merchantTransactionId)) {
                $transactionId = $merchantTransactionId->payment_transaction_id ?? $merchantTransactionId->id;
            } else {
                $transactionId = $merchantTransactionId;
            }

            if (!$transactionId) {
                throw new \Exception('No transaction ID provided');
            }

            $paySdk = PaySDK::getInstance();
            $paySdk->init($this->sandbox);

            $payment = new Payment();

            // Suppress PHP warnings/errors from SDK logging issues
            set_error_handler(function ($errno, $errstr) {
                // Ignore "Array to string conversion" from SDK logging
                if (strpos($errstr, 'Array to string conversion') !== false) {
                    return true;
                }
                return false;
            });

            try {
                $queryResponse = $payment->pay_query(
                    $this->merchantId,
                    $transactionId,
                    $this->privateKey,
                    $this->publicKey
                );
            } finally {
                restore_error_handler();
            }

            // Convert response to array if it's an object
            $responseArray = is_array($queryResponse) ? $queryResponse : json_decode(json_encode($queryResponse), true);

            Log::info('LianLian Pay Query Response', [
                'transaction_id' => $transactionId,
                'response' => $responseArray
            ]);

            return $responseArray;
        } catch (\Exception $e) {
            Log::error('LianLian Pay Query Error', [
                'transaction_id' => $transactionId ?? null,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Payment query failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhook($notifyBody, $signature)
    {
        try {
            $notification = new Notification();
            $notifyData = $notification->payment_notify($notifyBody, $signature, $this->publicKey);

            Log::info('LianLian Pay Webhook Verified', [
                'notify_data' => $notifyData
            ]);

            return $notifyData;
        } catch (\Exception $e) {
            Log::error('LianLian Pay Webhook Verification Error', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Process payment status update
     */
    public function processPaymentStatus($notifyBody)
    {
        try {
            $data = json_decode($notifyBody, true);

            if (!$data) {
                Log::warning('Invalid notification body:', ['body' => $notifyBody]);
                return;
            }

            $merchantTransactionId = $data['merchant_transaction_id'] ?? null;
            $paymentStatus = $data['payment_data']['payment_status'] ?? null;
            $llTransactionId = $data['ll_transaction_id'] ?? null;

            Log::info('Processing payment status update:', [
                'merchant_transaction_id' => $merchantTransactionId,
                'payment_status' => $paymentStatus,
                'll_transaction_id' => $llTransactionId
            ]);

            if (!$merchantTransactionId || !$paymentStatus) {
                Log::warning('Missing required fields in notification:', [
                    'merchant_transaction_id' => $merchantTransactionId,
                    'payment_status' => $paymentStatus
                ]);
                return;
            }

            // Extract timestamp from merchant_transaction_id (format: "Order-20251001094538")
            $timestamp = substr($merchantTransactionId, 6); // Remove "Order-" prefix
            $orderTime = \Carbon\Carbon::createFromFormat('YmdHis', $timestamp);
            $startTime = $orderTime->copy()->subMinutes(10);
            $endTime = $orderTime->copy()->addMinutes(10);

            // Find order in time range
            $order = Order::whereBetween('created_at', [$startTime, $endTime])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$order) {
                $order = Order::whereBetween('created_at', [$startTime, $endTime])
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            if (!$order) {
                Log::warning('Order not found for merchant_transaction_id:', [
                    'merchant_transaction_id' => $merchantTransactionId
                ]);
                return;
            }

            // Update order status based on payment status
            switch ($paymentStatus) {
                case 'PS': // Payment Success
                    $order->update([
                        'status' => 'paid',
                        'payment_status' => 'completed',
                        'paid_at' => now(),
                        'payment_transaction_id' => $llTransactionId
                    ]);
                    Log::info("Order {$order->id} status updated to paid (PS)");

                    // Clear cart from database for logged-in users only
                    // For guest users, cart will be cleared in frontend (checkout.success.blade.php)
                    if ($order->user_id) {
                        \App\Models\Cart::where('user_id', $order->user_id)->delete();
                        Log::info('🗑️ Cart cleared after LianLian Pay webhook success', [
                            'order_id' => $order->id,
                            'user_id' => $order->user_id
                        ]);
                    } else {
                        Log::info('ℹ️ Guest order - cart will be cleared in frontend', [
                            'order_id' => $order->id
                        ]);
                    }

                    // Send order confirmation email
                    try {
                        \Illuminate\Support\Facades\Mail::to($order->customer_email)
                            ->send(new \App\Mail\OrderConfirmation($order));
                        Log::info('📧 Order confirmation email sent (webhook)', [
                            'order_number' => $order->order_number,
                            'email' => $order->customer_email
                        ]);
                    } catch (\Exception $e) {
                        Log::error('❌ Failed to send order confirmation email (webhook)', [
                            'order_number' => $order->order_number,
                            'error' => $e->getMessage()
                        ]);
                    }
                    break;

                case 'PP': // Payment Processing
                    $order->update(['payment_status' => 'processing']);
                    Log::info("Order {$order->id} payment processing (PP)");
                    break;

                case 'WP': // Waiting Payment
                    $order->update(['payment_status' => 'pending']);
                    Log::info("Order {$order->id} waiting payment (WP)");
                    break;

                case 'declined':
                case 'failed':
                case 'cancelled':
                    $order->update([
                        'status' => 'failed',
                        'payment_status' => 'failed'
                    ]);
                    Log::info("Order {$order->id} status updated to failed ({$paymentStatus})");
                    break;

                case 'timeout':
                case 'expired':
                    $order->update([
                        'status' => 'expired',
                        'payment_status' => 'expired'
                    ]);
                    Log::info("Order {$order->id} status updated to expired");
                    break;

                default:
                    Log::info("Order {$order->id} unknown payment status: {$paymentStatus}");
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Error processing payment status:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get first name from full name
     */
    private function getFirstName($fullName)
    {
        $parts = explode(' ', trim($fullName));
        return $parts[0] ?? 'Customer';
    }

    /**
     * Get last name from full name
     */
    private function getLastName($fullName)
    {
        $parts = explode(' ', trim($fullName));
        return count($parts) > 1 ? end($parts) : '';
    }
}
