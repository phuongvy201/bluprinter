# LianLian Pay Production Test Guide

## 🚀 Cấu hình Production

### 1. Cập nhật file .env

Thêm hoặc cập nhật các biến sau trong file `.env`:

```env
# LianLian Pay Production Configuration
LIANLIAN_SANDBOX=false
LIANLIAN_MERCHANT_ID=your_production_merchant_id
LIANLIAN_SUB_MERCHANT_ID=your_production_sub_merchant_id

# Lấy từ LianLian Pay production dashboard
LIANLIAN_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----
[YOUR_PRODUCTION_PUBLIC_KEY_CONTENT]
-----END PUBLIC KEY-----"

LIANLIAN_PRIVATE_KEY="-----BEGIN RSA PRIVATE KEY-----
[YOUR_PRODUCTION_PRIVATE_KEY_CONTENT]
-----END RSA PRIVATE KEY-----"
```

### 2. Clear và cache config

```bash
php artisan config:clear
php artisan config:cache
```

### 3. Test cấu hình

#### A. Chạy script test

```bash
php setup_lianlian_production.php
```

#### B. Kiểm tra qua web

Truy cập URL: `http://your-domain.com/payment/lianlian/test-config`

Response sẽ trả về:

```json
{
    "success": true,
    "config": {
        "sandbox_mode": false,
        "merchant_id": "your_merchant_id",
        "base_url": "https://gpapi.lianlianpay.com/v3",
        "is_production": true,
        "public_key_configured": true,
        "private_key_configured": true
    },
    "token_test": {
        "success": true,
        "return_code": "SUCCESS"
    },
    "environment": {
        "app_env": "production",
        "sandbox_config": false,
        "production_url": "https://gpapi.lianlianpay.com/v3"
    }
}
```

### 4. Kiểm tra logs

Theo dõi log để đảm bảo mọi thứ hoạt động đúng:

```bash
tail -f storage/logs/lianlian.log
tail -f storage/logs/laravel.log
```

### 5. URLs được sử dụng trong Production

-   **API Base URL**: `https://gpapi.lianlianpay.com/v3`
-   **SDK URL**: `https://gacashier.lianlianpay-inc.com/llpay.min.js`
-   **Iframe URL**: `https://gpapi.lianlianpay.com/v3/merchants/<merchant_id>/payments`

### 6. Kiểm tra Frontend

Khi truy cập trang payment, kiểm tra console để thấy:

```
🔧 Loading Production SDK
✅ SDK loaded successfully
🔧 Creating card element with merchantUrl: https://your-domain.com
```

### 7. Troubleshooting

#### Nếu gặp lỗi "Request argument invalid":

1. Kiểm tra `merchant_id` có đúng không
2. Kiểm tra keys có đúng format không
3. Kiểm tra URL domain có được whitelist trong LianLian Pay dashboard không

#### Nếu SDK không load:

1. Kiểm tra network có thể truy cập `https://gacashier.lianlianpay-inc.com/llpay.min.js`
2. Kiểm tra CSP (Content Security Policy) settings

#### Kiểm tra production credentials:

-   Đăng nhập vào LianLian Pay production dashboard
-   Verify merchant_id và keys đúng với môi trường production
-   Đảm bảo domain của bạn được whitelist

### 8. Test Payment Flow

1. Tạo order test với số tiền nhỏ
2. Chuyển đến payment page
3. Kiểm tra iframe load đúng từ production URL
4. Test với thẻ test từ LianLian Pay production test cards

---

**Lưu ý**: Chỉ test với số tiền nhỏ và thẻ test khi ở môi trường production!
