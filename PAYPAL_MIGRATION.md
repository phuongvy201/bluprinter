# PayPal Integration - Migration to Direct API

## Vấn đề với PayPal SDK cũ

PayPal REST API SDK (`paypal/rest-api-sdk-php` v1.6.4) đã **deprecated** và không tương thích với PHP 8.x do bug với hàm `sizeof()`.

**Lỗi:**

```
TypeError: sizeof(): Argument #1 ($value) must be of type Countable|array, string given
```

## Giải pháp mới

Đã migrate sang **PayPal REST API trực tiếp** sử dụng **Laravel HTTP Client (Guzzle)**.

### Ưu điểm:

✅ **Tương thích PHP 8.x** - không có bug sizeof()  
✅ **Không cần SDK cũ** - sử dụng HTTP client có sẵn  
✅ **Dễ maintain** - code rõ ràng, dễ debug  
✅ **Linh hoạt** - dễ dàng custom requests  
✅ **Modern** - sử dụng PayPal API v1 trực tiếp

### Cấu trúc mới:

**File:** `app/Services/PayPalService.php`

```php
class PayPalService
{
    // Sử dụng Laravel HTTP Client
    private function getAccessToken() { }
    public function createPayment($order, $items) { }
    public function executePayment($paymentId, $payerId) { }
}
```

### API Endpoints:

-   **Sandbox:** `https://api-m.sandbox.paypal.com`
-   **Live:** `https://api-m.paypal.com`

### API Calls:

1. **OAuth Token:**

    ```
    POST /v1/oauth2/token
    Authorization: Basic {client_id:client_secret}
    ```

2. **Create Payment:**

    ```
    POST /v1/payments/payment
    Authorization: Bearer {access_token}
    ```

3. **Execute Payment:**
    ```
    POST /v1/payments/payment/{payment_id}/execute
    Authorization: Bearer {access_token}
    ```

## Setup

### 1. Environment Variables

Đảm bảo có các biến sau trong `.env`:

```env
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_CLIENT_SECRET=your_client_secret
PAYPAL_MODE=sandbox  # hoặc 'live' cho production
```

### 2. Config

File `config/services.php`:

```php
'paypal' => [
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    'mode' => env('PAYPAL_MODE', 'sandbox'),
],
```

## Testing

### Sandbox Accounts

1. Đăng nhập vào https://developer.paypal.com
2. Vào **Dashboard > Sandbox > Accounts**
3. Tạo hoặc sử dụng test accounts (Personal & Business)
4. Sử dụng credentials của Business account cho API

### Test Flow

1. **Checkout** → Điền thông tin → Chọn PayPal
2. **Redirect** → PayPal sandbox login page
3. **Login** → Sử dụng Personal sandbox account
4. **Approve** → Confirm payment
5. **Return** → Redirect về success page

## Logs

Tất cả PayPal errors được log vào:

-   `storage/logs/laravel.log`

Debug info:

```php
Log::error('PayPal Error', ['response' => $response->json()]);
```

## Troubleshooting

### Lỗi Authentication

-   Kiểm tra `PAYPAL_CLIENT_ID` và `PAYPAL_CLIENT_SECRET`
-   Đảm bảo sử dụng credentials của **sandbox** account nếu mode là sandbox

### Lỗi Amount Mismatch

-   Tổng amount phải bằng subtotal + tax + shipping
-   Format: 2 chữ số thập phân (VD: "10.00")
-   Sử dụng dấu chấm (.) không phải dấu phẩy

### Redirect không hoạt động

-   Kiểm tra `return_url` và `cancel_url` trong payment data
-   Đảm bảo routes đã được define trong `web.php`

## Future Improvements

### Option 1: PayPal Checkout SDK v2

```bash
composer require paypal/paypal-checkout-sdk
```

### Option 2: Stripe

Cân nhắc thêm Stripe payment gateway như alternative.

## References

-   [PayPal REST API Documentation](https://developer.paypal.com/docs/api/overview/)
-   [PayPal Sandbox Testing](https://developer.paypal.com/tools/sandbox/)
-   [Laravel HTTP Client](https://laravel.com/docs/http-client)
