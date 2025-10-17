<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    private $clientId;
    private $clientSecret;
    private $mode;
    private $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->mode = config('services.paypal.mode', 'sandbox');

        // Set base URL based on mode
        $this->baseUrl = $this->mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Get PayPal OAuth access token
     */
    private function getAccessToken()
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post($this->baseUrl . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

        if ($response->failed()) {
            Log::error('PayPal Auth Failed', ['response' => $response->json()]);
            throw new \Exception('Failed to authenticate with PayPal');
        }

        return $response->json()['access_token'];
    }

    /**
     * Create a PayPal payment
     */
    public function createPayment($order, $items)
    {
        $accessToken = $this->getAccessToken();

        // Format amounts
        $subtotal = number_format((float)$order->subtotal, 2, '.', '');
        $tax = number_format((float)$order->tax_amount, 2, '.', '');
        $shipping = number_format((float)$order->shipping_cost, 2, '.', '');
        $total = number_format((float)$order->total_amount, 2, '.', '');

        // Build items
        $paypalItems = [];
        foreach ($items as $item) {
            $paypalItems[] = [
                'name' => substr($item['product']->name, 0, 127),
                'sku' => $item['product']->id,
                'price' => number_format((float)$item['product']->base_price, 2, '.', ''),
                'currency' => 'USD',
                'quantity' => (int)$item['quantity']
            ];
        }

        // Build payment data
        $paymentData = [
            'intent' => 'sale',
            'payer' => [
                'payment_method' => 'paypal'
            ],
            'transactions' => [
                [
                    'amount' => [
                        'total' => $total,
                        'currency' => 'USD',
                        'details' => [
                            'subtotal' => $subtotal,
                            'tax' => $tax,
                            'shipping' => $shipping
                        ]
                    ],
                    'description' => 'Order #' . $order->order_number,
                    'invoice_number' => $order->order_number,
                    'item_list' => [
                        'items' => $paypalItems
                    ]
                ]
            ],
            'redirect_urls' => [
                'return_url' => route('checkout.paypal.success'),
                'cancel_url' => route('checkout.paypal.cancel')
            ]
        ];

        // Create payment via API
        $response = Http::withToken($accessToken)
            ->post($this->baseUrl . '/v1/payments/payment', $paymentData);

        if ($response->failed()) {
            Log::error('PayPal Payment Creation Failed', [
                'response' => $response->json(),
                'payment_data' => $paymentData
            ]);
            throw new \Exception('Failed to create PayPal payment: ' . ($response->json()['message'] ?? 'Unknown error'));
        }

        $payment = $response->json();

        // Create a mock payment object with the approval URL
        $approvalUrl = collect($payment['links'])->firstWhere('rel', 'approval_url')['href'] ?? null;

        if (!$approvalUrl) {
            throw new \Exception('PayPal approval URL not found');
        }

        // Return payment data
        return (object)[
            'id' => $payment['id'],
            'approval_url' => $approvalUrl,
            'state' => $payment['state']
        ];
    }

    /**
     * Execute a PayPal payment
     */
    public function executePayment($paymentId, $payerId)
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post($this->baseUrl . "/v1/payments/payment/{$paymentId}/execute", [
                'payer_id' => $payerId
            ]);

        if ($response->failed()) {
            Log::error('PayPal Payment Execution Failed', [
                'payment_id' => $paymentId,
                'payer_id' => $payerId,
                'response' => $response->json()
            ]);
            throw new \Exception('Failed to execute PayPal payment');
        }

        return $response->json();
    }
}
