# 🏪 Cấu Hình Shop Mặc Định Cho API

## 📋 Tổng Quan

Khi tạo sản phẩm qua API, hệ thống sẽ tự động gán shop cho sản phẩm theo thứ tự ưu tiên sau:

### Thứ Tự Ưu Tiên

1. **Shop ID trong request** (Ưu tiên cao nhất)
2. **Default Shop ID của API Token**
3. **Shop ID từ Template**
4. **Shop mặc định trong config**
5. **Fallback về Shop ID = 1** (Ưu tiên thấp nhất)

---

## 🚀 Cách Cấu Hình

### 1️⃣ Cấu Hình Qua Request API (Cao Nhất)

Truyền trực tiếp `shop_id` trong request:

```json
POST /api/products/create
Headers:
  X-API-Token: your_api_token_here
  Content-Type: multipart/form-data

Body:
{
  "name": "My Product",
  "template_id": 1,
  "shop_id": 5,  // ← Sản phẩm sẽ được gán cho shop ID 5
  "images": [file1, file2],
  "price": 29.99
}
```

✅ **Ưu điểm:** Linh hoạt, có thể thay đổi shop cho từng sản phẩm
❌ **Nhược điểm:** Phải truyền shop_id mỗi lần tạo sản phẩm

---

### 2️⃣ Cấu Hình Default Shop Cho API Token (Khuyến Nghị) ⭐

Mỗi API token có thể có 1 shop mặc định. Tất cả sản phẩm tạo bởi token đó sẽ tự động được gán vào shop này.

#### Bước 1: Chạy Migration

```bash
php artisan migrate
```

Migration `add_default_shop_id_to_api_tokens_table` sẽ thêm cột `default_shop_id` vào bảng `api_tokens`.

#### Bước 2: Cập Nhật Database

**Cách 1: Qua SQL**

```sql
-- Cập nhật shop mặc định cho API token cụ thể
UPDATE api_tokens
SET default_shop_id = 5
WHERE id = 1;

-- Hoặc theo tên token
UPDATE api_tokens
SET default_shop_id = 5
WHERE name = 'My Production Token';
```

**Cách 2: Qua Laravel Tinker**

```bash
php artisan tinker
```

```php
// Tìm token và set default shop
$token = App\Models\ApiToken::where('name', 'My Token')->first();
$token->default_shop_id = 5;
$token->save();

// Hoặc khi tạo token mới
App\Models\ApiToken::generateToken(
    name: 'New Token',
    description: 'Token for Shop XYZ',
    permissions: ['product:create']
)->update(['default_shop_id' => 5]);
```

#### Bước 3: Test API

```json
POST /api/products/create
Headers:
  X-API-Token: your_api_token_here

Body:
{
  "name": "My Product",
  "template_id": 1,
  "images": [file1, file2]
  // Không cần truyền shop_id - sẽ tự động lấy từ token
}
```

✅ **Ưu điểm:** Tiện lợi, không cần truyền shop_id mỗi lần
✅ **Use case:** Mỗi shop có 1 API token riêng

---

### 3️⃣ Cấu Hình Default Shop Toàn Hệ Thống

#### Cách 1: Qua File `.env`

Thêm vào file `.env`:

```env
# API Configuration
API_DEFAULT_SHOP_ID=5
```

#### Cách 2: Qua Config File

Chỉnh sửa file `config/api.php`:

```php
'default_shop_id' => env('API_DEFAULT_SHOP_ID', 5),
```

#### Clear Cache

```bash
php artisan config:cache
```

✅ **Ưu điểm:** Đơn giản, áp dụng cho tất cả API tokens
❌ **Nhược điểm:** Tất cả products sẽ vào cùng 1 shop

---

## 📊 Bảng So Sánh

| Phương Pháp       | Ưu Tiên       | Use Case                               | Linh Hoạt  |
| ----------------- | ------------- | -------------------------------------- | ---------- |
| Request `shop_id` | 1 (Cao nhất)  | Tạo products cho nhiều shops khác nhau | ⭐⭐⭐⭐⭐ |
| API Token Default | 2             | Mỗi shop có 1 token riêng              | ⭐⭐⭐⭐   |
| Template Shop     | 3             | Template thuộc về shop cụ thể          | ⭐⭐⭐     |
| Config Default    | 4             | Hệ thống đơn giản, 1 shop duy nhất     | ⭐⭐       |
| Fallback (ID=1)   | 5 (Thấp nhất) | Khi không có cấu hình nào              | ⭐         |

---

## 💡 Ví Dụ Thực Tế

### Scenario 1: Multi-Shop Platform

Bạn có 3 shops khác nhau:

```sql
-- Shop 1: Electronics Store (ID: 5)
UPDATE api_tokens SET default_shop_id = 5 WHERE name = 'Electronics API Token';

-- Shop 2: Fashion Store (ID: 8)
UPDATE api_tokens SET default_shop_id = 8 WHERE name = 'Fashion API Token';

-- Shop 3: Home & Garden (ID: 12)
UPDATE api_tokens SET default_shop_id = 12 WHERE name = 'Home API Token';
```

Mỗi shop sử dụng token của mình để tạo products → Tự động vào đúng shop!

### Scenario 2: Dynamic Shop Assignment

API client muốn tạo products cho nhiều shops:

```javascript
// Shop A
await createProduct({
  name: "Product A",
  template_id: 1,
  shop_id: 5,  // Override
  images: [...]
});

// Shop B
await createProduct({
  name: "Product B",
  template_id: 1,
  shop_id: 8,  // Override
  images: [...]
});
```

### Scenario 3: Single Default Shop

Hệ thống chỉ có 1 shop duy nhất:

```env
# .env
API_DEFAULT_SHOP_ID=1
```

Tất cả products tự động vào shop ID 1. ✅ Đơn giản!

---

## 🔧 Troubleshooting

### ❓ Sản phẩm không hiển thị trong shop?

**Kiểm tra:**

1. Shop có `shop_status = 'active'` không?
2. `shop_id` có tồn tại trong database không?
3. Check logs: `storage/logs/laravel.log`

```sql
-- Kiểm tra product được gán vào shop nào
SELECT id, name, shop_id FROM products ORDER BY created_at DESC LIMIT 10;

-- Kiểm tra shop có active không
SELECT id, shop_name, shop_status FROM shops WHERE id IN (1, 5, 8);
```

### ❓ Làm sao xem default_shop_id của token?

```bash
php artisan tinker
```

```php
// Xem tất cả tokens và default shops
App\Models\ApiToken::with('defaultShop')->get()->map(function($t) {
    return [
        'token_name' => $t->name,
        'default_shop_id' => $t->default_shop_id,
        'shop_name' => $t->defaultShop?->shop_name
    ];
});
```

### ❓ Sửa default_shop_id cho nhiều tokens cùng lúc?

```sql
-- Gán tất cả tokens vào shop ID 5
UPDATE api_tokens SET default_shop_id = 5 WHERE is_active = 1;

-- Gán tokens cụ thể
UPDATE api_tokens
SET default_shop_id = 5
WHERE name LIKE '%Production%';
```

---

## 📝 API Response

Khi tạo product thành công, API sẽ trả về thông tin shop:

```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "product_id": 123,
    "name": "My Product",
    "shop_id": 5,  // ← Shop được gán
    "price": 29.99,
    "url": "https://yoursite.com/products/my-product",
    ...
  }
}
```

---

## 🎯 Best Practices

### ✅ Nên

-   **Use API Token Default Shop** cho multi-shop platforms
-   Set `shop_id` trong request khi cần override
-   Đảm bảo shop có `shop_status = 'active'`
-   Kiểm tra logs khi có vấn đề

### ❌ Không Nên

-   Hardcode shop_id trong code
-   Sử dụng shop_id không tồn tại
-   Quên chạy migration trước khi dùng
-   Để tất cả products vào shop ID 1 (nếu có nhiều shops)

---

## 🔗 Xem Thêm

-   [API Documentation](API_DOCUMENTATION_VIETNAMESE.md)
-   [Product API Guide](API_PRODUCT_DOCUMENTATION.md)
-   [Template Integration](API_TEMPLATE_INTEGRATION.md)

---

## 📞 Support

Nếu có vấn đề, kiểm tra:

1. `storage/logs/laravel.log`
2. Run `php artisan config:cache` sau khi sửa config
3. Verify database: `SELECT * FROM api_tokens;`

**Happy Coding! 🚀**
