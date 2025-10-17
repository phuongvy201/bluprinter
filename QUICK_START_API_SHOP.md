# ⚡ Quick Start: Cấu Hình Shop Mặc Định Cho API

## 🎯 3 Cách Đơn Giản

### 1️⃣ Gán Shop Cho API Token (Khuyến Nghị) ⭐

```sql
-- Xem danh sách shops
SELECT id, shop_name, shop_status FROM shops;

-- Xem danh sách tokens
SELECT id, name, default_shop_id FROM api_tokens;

-- Gán shop cho token
UPDATE api_tokens SET default_shop_id = 5 WHERE id = 1;
```

✅ **Kết quả:** Tất cả products tạo bởi token này sẽ tự động vào shop ID 5!

---

### 2️⃣ Truyền Shop Trong API Request

```bash
curl -X POST https://yoursite.com/api/products/create \
  -H "X-API-Token: your_token_here" \
  -F "name=Product Name" \
  -F "template_id=1" \
  -F "shop_id=5" \
  -F "images=@image1.jpg"
```

✅ **Kết quả:** Product này sẽ vào shop ID 5!

---

### 3️⃣ Set Default Trong Config

Thêm vào file `.env`:

```env
API_DEFAULT_SHOP_ID=5
```

Sau đó:

```bash
php artisan config:cache
```

✅ **Kết quả:** Tất cả products (nếu không có cấu hình khác) sẽ vào shop ID 5!

---

## 🔍 Kiểm Tra Kết Quả

```sql
-- Xem products đã tạo qua API
SELECT
    p.id,
    p.name,
    p.shop_id,
    s.shop_name,
    t.name as token_name
FROM products p
LEFT JOIN shops s ON p.shop_id = s.id
LEFT JOIN api_tokens t ON p.api_token_id = t.id
WHERE p.created_by = 'api'
ORDER BY p.created_at DESC
LIMIT 10;
```

---

## 📚 Tài Liệu Chi Tiết

-   **Full Guide:** [API_DEFAULT_SHOP_CONFIGURATION.md](API_DEFAULT_SHOP_CONFIGURATION.md)
-   **SQL Scripts:** [update_api_token_default_shop.sql](update_api_token_default_shop.sql)
-   **API Docs:** [API_DOCUMENTATION_VIETNAMESE.md](API_DOCUMENTATION_VIETNAMESE.md)

---

## ⚠️ Lưu Ý

-   Migration đã chạy ✅
-   Shop phải có `shop_status = 'active'`
-   Thứ tự ưu tiên: **Request > Token > Template > Config > Fallback (1)**

**Done! 🎉**
