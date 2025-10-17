# ⚙️ Hướng Dẫn: Cấu Hình Default Shop Toàn Hệ Thống (Cách 3)

## 🎯 Tổng Quan

Cách này đơn giản nhất - tất cả sản phẩm tạo qua API sẽ tự động vào 1 shop mặc định.

✅ **Phù hợp với:** Hệ thống có 1 shop duy nhất hoặc muốn tất cả API products vào 1 shop cụ thể.

---

## 📋 Các Bước Thực Hiện

### Bước 1️⃣: Xác Định Shop ID

Trước tiên, xác định shop nào bạn muốn làm mặc định:

```sql
-- Xem tất cả shops
SELECT id, shop_name, shop_status FROM shops;
```

**Hoặc dùng Laravel Tinker:**

```bash
php artisan tinker
```

```php
DB::table('shops')->select('id', 'shop_name', 'shop_status')->get();
```

**Ví dụ kết quả:**

```
id | shop_name          | shop_status
---+-------------------+-------------
1  | Main Store        | active
5  | Electronics Shop  | active
8  | Fashion Store     | active
```

📝 **Ghi nhớ Shop ID** bạn muốn sử dụng (ví dụ: `5`)

---

### Bước 2️⃣: Cấu Hình File .env

**Cách A: Thêm vào file `.env` hiện có**

Mở file `.env` trong thư mục gốc của project và thêm dòng này:

```env
# API Configuration
API_DEFAULT_SHOP_ID=5
```

> **Lưu ý:** Thay `5` bằng Shop ID bạn muốn

**Cách B: Nếu không có file `.env`**

Copy từ `.env.example` (nếu có):

```bash
copy .env.example .env
```

Sau đó thêm dòng cấu hình ở trên.

---

### Bước 3️⃣: Clear Cache Laravel

**Quan trọng!** Phải clear cache để Laravel nhận config mới:

```bash
php artisan config:cache
```

Nếu gặp lỗi, chạy thêm:

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

### Bước 4️⃣: Verify Cấu Hình

Kiểm tra xem config đã được load chưa:

```bash
php artisan tinker
```

```php
// Xem giá trị config
config('api.default_shop_id');

// Kết quả mong đợi: 5 (hoặc giá trị bạn đã set)
```

---

### Bước 5️⃣: Test API

Tạo product mà **không** cần truyền `shop_id`:

```bash
curl -X POST http://localhost/api/products/create \
  -H "X-API-Token: YOUR_API_TOKEN" \
  -F "name=Test Product" \
  -F "template_id=1" \
  -F "images=@/path/to/image.jpg"
```

**Kết quả mong đợi:**

```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "product_id": 123,
    "name": "Test Product",
    "shop_id": 5,  // ← Tự động gán = API_DEFAULT_SHOP_ID
    ...
  }
}
```

---

## ✅ Xác Minh Kết Quả

### Kiểm tra trong Database:

```sql
-- Xem products mới tạo
SELECT
    id,
    name,
    shop_id,
    created_by,
    created_at
FROM products
WHERE created_by = 'api'
ORDER BY created_at DESC
LIMIT 10;
```

**Tất cả products phải có `shop_id = 5`** (hoặc giá trị bạn đã set)

---

## 🔄 Thay Đổi Shop Mặc Định

Nếu muốn đổi sang shop khác:

1. Sửa file `.env`:

```env
API_DEFAULT_SHOP_ID=8  # Đổi sang shop ID khác
```

2. Clear cache:

```bash
php artisan config:cache
```

3. Test lại API!

---

## 🎯 Override Cho Từng Product

Ngay cả khi đã set default shop, bạn vẫn có thể override cho từng product:

```json
POST /api/products/create
{
  "name": "Special Product",
  "template_id": 1,
  "shop_id": 12,  // ← Override: product này vào shop 12
  "images": [...]
}
```

**Priority:** Request `shop_id` > Config default > Fallback (1)

---

## 📊 Thống Kê

Xem số lượng products theo shop:

```sql
SELECT
    s.id,
    s.shop_name,
    COUNT(p.id) as total_api_products
FROM shops s
LEFT JOIN products p ON s.id = p.shop_id AND p.created_by = 'api'
GROUP BY s.id, s.shop_name
ORDER BY total_api_products DESC;
```

---

## ⚠️ Troubleshooting

### Vấn đề 1: Config không hoạt động

**Triệu chứng:** Products vẫn vào shop ID = 1

**Giải pháp:**

```bash
# Clear tất cả caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Rebuild config cache
php artisan config:cache

# Verify
php artisan tinker
>>> config('api.default_shop_id')
```

### Vấn đề 2: Shop không tồn tại

**Triệu chứng:** Products được tạo nhưng `shop_id = NULL`

**Giải pháp:**

```sql
-- Kiểm tra shop có tồn tại không
SELECT id FROM shops WHERE id = 5;

-- Nếu không tồn tại, chọn shop ID khác hoặc tạo shop mới
```

### Vấn đề 3: Products không hiển thị

**Triệu chứng:** Products được tạo nhưng không thấy trong frontend

**Giải pháp:**

```sql
-- Kiểm tra shop status
SELECT id, shop_name, shop_status FROM shops WHERE id = 5;

-- Shop phải có shop_status = 'active'
-- Nếu không, update:
UPDATE shops SET shop_status = 'active' WHERE id = 5;
```

---

## 📝 File Cấu Hình Liên Quan

### File: `config/api.php`

```php
<?php

return [
    'default_shop_id' => env('API_DEFAULT_SHOP_ID', 1),
    // Giá trị mặc định nếu không có trong .env: 1
];
```

**Nếu muốn hardcode trong config file:**

```php
'default_shop_id' => 5,  // Bỏ env() và set trực tiếp
```

Sau đó:

```bash
php artisan config:cache
```

---

## 💡 Use Cases

### Case 1: Single Shop Platform

```env
API_DEFAULT_SHOP_ID=1
```

✅ Tất cả products vào shop chính

### Case 2: API Products Shop

```env
API_DEFAULT_SHOP_ID=5
```

✅ Tạo shop riêng cho API products, tách biệt với products thông thường

### Case 3: Testing

```env
API_DEFAULT_SHOP_ID=999
```

✅ Shop test riêng, dễ dàng filter/xóa

---

## 🔗 Tài Liệu Liên Quan

-   **Chi tiết đầy đủ:** [API_DEFAULT_SHOP_CONFIGURATION.md](API_DEFAULT_SHOP_CONFIGURATION.md)
-   **Quick Start:** [QUICK_START_API_SHOP.md](QUICK_START_API_SHOP.md)
-   **SQL Scripts:** [update_api_token_default_shop.sql](update_api_token_default_shop.sql)

---

## ✨ Hoàn Tất!

Bây giờ mọi product tạo qua API sẽ tự động vào shop mặc định bạn đã cấu hình! 🎉

**Test ngay:**

```bash
# Tạo product không cần truyền shop_id
curl -X POST http://localhost/api/products/create \
  -H "X-API-Token: YOUR_TOKEN" \
  -F "name=Auto Shop Product" \
  -F "template_id=1" \
  -F "images=@image.jpg"
```

**Happy Coding! 🚀**
