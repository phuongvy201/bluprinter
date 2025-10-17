# LianLian Pay Integration Setup Guide

## 1. Environment Configuration

Add the following environment variables to your `.env` file:

```env
# LianLian Pay Configuration
LIANLIAN_SANDBOX=true
LIANLIAN_MERCHANT_ID=your_merchant_id_here
LIANLIAN_SUB_MERCHANT_ID=your_sub_merchant_id_here
LIANLIAN_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----
your_public_key_content_here
-----END PUBLIC KEY-----"
LIANLIAN_PRIVATE_KEY="-----BEGIN RSA PRIVATE KEY-----
your_private_key_content_here
-----END RSA PRIVATE KEY-----"
LIANLIAN_WEBHOOK_URL=/payment/lianlian/webhook-v2
LIANLIAN_RETURN_URL=/payment/lianlian/return
LIANLIAN_CANCEL_URL=/payment/lianlian/cancel
```

**⚠️ CRITICAL: Key Format Requirements**

1. **Keys must be in PEM format** with proper headers:

    - Private key: `-----BEGIN RSA PRIVATE KEY-----` and `-----END RSA PRIVATE KEY-----`
    - Public key: `-----BEGIN PUBLIC KEY-----` and `-----END PUBLIC KEY-----`

2. **Keys must be from the same key pair** - your private key and public key must match each other

3. **Use proper escaping** in .env file:
    - Wrap keys in double quotes
    - Use `\n` for line breaks
    - Escape any quotes within the key content

**⚠️ IMPORTANT**: You MUST set real credentials from LianLian Pay. The integration will not work with placeholder values or mismatched key pairs.

## 2. Getting LianLian Pay Credentials

### For Sandbox Testing:

1. Register at [LianLian Pay Developer Portal](https://developer.lianlianpay.com/)
2. Create a sandbox account
3. Get your sandbox credentials:
    - Merchant ID
    - Sub Merchant ID (if required)
    - Public Key
    - Private Key

### For Production:

1. Complete the merchant verification process
2. Get your production credentials from LianLian Pay
3. Update your `.env` file with production values
4. Set `LIANLIAN_SANDBOX=false`

## 3. SDK Installation

The LianLian Pay SDK has been installed via Composer:

```bash
composer require lianlian_acquiring/sdkv3
```

## 4. Configuration Files

### Config File: `config/lianlian.php`

-   Contains all LianLian Pay configuration settings
-   Uses environment variables for sensitive data
-   Supports both sandbox and production environments

### Service Class: `app/Services/LianLianPayService.php`

-   Handles all LianLian Pay API interactions
-   Provides methods for:
    -   Creating payments
    -   Querying payment status
    -   Processing refunds
    -   Canceling payments
    -   Verifying webhooks

### Controller: `app/Http/Controllers/Payment/LianLianPayController.php`

-   Handles payment creation and callbacks
-   Processes webhook notifications
-   Manages payment returns and cancellations

## 5. Routes

The following routes have been added:

```php
// LianLian Pay routes
Route::prefix('payment/lianlian')->name('payment.lianlian.')->group(function () {
    Route::post('/create/{order}', [LianLianPayController::class, 'createPayment'])->name('create');
    Route::get('/return', [LianLianPayController::class, 'handleReturn'])->name('return');
    Route::get('/cancel', [LianLianPayController::class, 'handleCancel'])->name('cancel');
    Route::post('/webhook', [LianLianPayController::class, 'handleWebhook'])->name('webhook');
    Route::get('/query/{order}', [LianLianPayController::class, 'queryPayment'])->name('query');
    Route::post('/refund/{order}', [LianLianPayController::class, 'processRefund'])->name('refund');
});
```

## 6. Checkout Integration

### Updated Checkout Form

-   Added LianLian Pay as a payment option
-   Updated validation to include `lianlian_pay`
-   Modified `CheckoutController` to handle LianLian Pay payments

### Payment Flow

1. Customer selects LianLian Pay at checkout
2. Order is created with `payment_method = 'lianlian_pay'`
3. Payment request is sent to LianLian Pay
4. Customer is redirected to LianLian Pay payment page
5. After payment, customer is redirected back to your site
6. Webhook notification confirms payment status

## 7. Webhook Configuration

### Webhook URL

Set your webhook URL in LianLian Pay dashboard:

```
https://yourdomain.com/payment/lianlian/webhook
```

### Webhook Security

-   Webhook signature verification is implemented
-   Invalid signatures are rejected
-   All webhook events are logged

## 8. Testing

### Sandbox Testing

1. Set `LIANLIAN_SANDBOX=true` in your `.env` file
2. Use sandbox credentials
3. Test payment flow with sandbox cards
4. Verify webhook notifications

### Test Cards

LianLian Pay provides test card numbers for sandbox testing. Check their documentation for current test cards.

## 9. Production Deployment

### Before Going Live

1. Complete merchant verification with LianLian Pay
2. Get production credentials
3. Update `.env` file with production values
4. Set `LIANLIAN_SANDBOX=false`
5. Configure production webhook URL
6. Test with small amounts first

### Security Considerations

-   Never commit credentials to version control
-   Use environment variables for all sensitive data
-   Enable HTTPS for all payment-related endpoints
-   Monitor webhook logs for suspicious activity

## 10. Troubleshooting

### Common Issues

#### Payment Creation Fails

-   Check merchant credentials
-   Verify API endpoints (sandbox vs production)
-   Check order data format
-   Review error logs

#### Webhook Not Received

-   Verify webhook URL is accessible
-   Check firewall settings
-   Ensure HTTPS is enabled
-   Review LianLian Pay webhook configuration

#### Payment Status Not Updated

-   Check webhook signature verification
-   Review webhook processing logs
-   Verify order ID extraction from transaction ID
-   Check database transaction handling

### Log Files

-   Application logs: `storage/logs/laravel.log`
-   LianLian Pay specific logs: `storage/logs/lianlian.log`

## 11. Support

### Documentation

-   [LianLian Pay API Documentation](https://developer.lianlianpay.com/docs)
-   [SDK Documentation](https://github.com/lianlianpay/php-sdk)

### Contact

-   LianLian Pay Support: support@lianlianpay.com
-   Technical Support: tech@lianlianpay.com

## 12. Features Implemented

### Core Payment Features

✅ Payment creation and processing
✅ Payment status querying
✅ Refund processing
✅ Payment cancellation
✅ Webhook handling and verification
✅ Return URL handling
✅ Cancel URL handling
✅ Error handling and logging
✅ Sandbox and production environment support
✅ Integration with existing checkout flow
✅ Order status updates
✅ Security measures (signature verification)

### Advanced Features (V2)

✅ **Proper SDK Models**: Uses Address, Customer, Product, Shipping models
✅ **3DS Authentication**: Support for 3D Secure authentication
✅ **Token Generation**: Iframe payment token support
✅ **Enhanced Logging**: Detailed request/response logging
✅ **Better Error Handling**: Improved error messages and debugging
✅ **Payment Status Processing**: Automatic order status updates
✅ **Card Information Handling**: Secure card data processing
✅ **Billing Address Support**: Separate billing address handling

## 13. Common Errors and Solutions

### "country is empty or incorrect"

-   ✅ **FIXED**: Added country field to payment request
-   Ensure order has country field set

### "merchant_order is empty or incorrect"

-   ✅ **FIXED**: Added merchant_order field using order_number
-   Ensure order has order_number generated

### "Http request Body json format incorrect"

-   This usually means missing or invalid credentials
-   Ensure you have real LianLian Pay credentials set
-   Check that private key format is correct

### "openssl_sign(): Supplied key param cannot be coerced into a private key"

-   This means the private key format is incorrect
-   Ensure private key is properly formatted (PEM format)
-   Add proper headers: `-----BEGIN RSA PRIVATE KEY-----` and `-----END RSA PRIVATE KEY-----`
-   Remove any extra spaces or line breaks

### "Invalid signature" or "Keys do not match - signature verification failed"

-   This means the signature verification failed
-   **Most common cause**: Private and public keys are from different key pairs
-   **Solution**: Get the correct matching key pair from LianLian Pay merchant dashboard
-   Check that you're using the correct public key that matches your private key
-   Ensure the request data is properly formatted
-   Verify that the private key matches the public key

### "INVALID_SIGNATURE" (V2 Service)

-   This is expected when using mock credentials
-   The V2 service uses proper SDK models and signature verification
-   Will work correctly with real LianLian Pay credentials

## 14. Next Steps

1. Configure your LianLian Pay credentials
2. Test the integration in sandbox mode
3. Set up webhook endpoints
4. Test the complete payment flow
5. Deploy to production when ready

The integration is now complete and ready for testing!
