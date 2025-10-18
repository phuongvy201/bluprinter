# 🚀 BLUPRINTER API - QUICK START GUIDE

## 📌 LINKS QUAN TRỌNG

### 🌐 Web Interfaces

| Link                                                                        | Mô tả                                |
| --------------------------------------------------------------------------- | ------------------------------------ |
| **http://localhost:8000/api-token.html**                                    | 🔑 Dashboard xem API Token & Stats   |
| **http://localhost:8000/api-docs.html**                                     | 📖 Swagger Interactive Documentation |
| **http://localhost:8000/Bluprinter_API_Collection.postman_collection.json** | 📥 Download Postman Collection       |

### 📄 Documentation Files

| File                              | Mô tả                                    |
| --------------------------------- | ---------------------------------------- |
| `API_DOCUMENTATION_VIETNAMESE.md` | 📚 Hướng dẫn chi tiết bằng tiếng Việt    |
| `POSTMAN_STEP_BY_STEP.md`         | 🎯 Hướng dẫn test bằng Postman từng bước |
| `API_PRODUCT_DOCUMENTATION.md`    | 📋 Technical documentation (English)     |
| `API_QUICK_START.md`              | ⚡ File này - Quick start guide          |

---

## ⚡ QUICK START - 3 BƯỚC

### Bước 1: Lấy API Token

```bash
# Mở trình duyệt
http://localhost:8000/api-token.html

# Hoặc query database
SELECT token FROM api_tokens WHERE is_active = 1;
```

**Token mẫu:**

```
bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ
```

### Bước 2: Test với cURL

```bash
curl -X POST http://localhost:8000/api/products/create \
  -H "X-API-Token: bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ" \
  -H "Accept: application/json" \
  -F "name=My First AI Product" \
  -F "description=Testing API" \
  -F "template_id=1" \
  -F "images[]=@image1.jpg" \
  -F "images[]=@image2.jpg"
```

### Bước 3: Xem kết quả

Response sẽ trả về URL sản phẩm:

```json
{
    "data": {
        "url": "http://localhost:8000/products/my-first-ai-product"
    }
}
```

Mở URL trong trình duyệt để xem sản phẩm! ✅

---

## 🎯 TEST VỚI POSTMAN

### Cách 1: Import Collection

1. **Download collection:**

    ```
    http://localhost:8000/Bluprinter_API_Collection.postman_collection.json
    ```

2. **Import vào Postman:**

    - Postman → Import → Select file
    - Click "Import"

3. **Set Token:**

    - Click Collection → Variables
    - Set `api_token` = token của bạn
    - Save

4. **Test:**
    - Chọn request "Create Product"
    - Upload files
    - Click **Send**

### Cách 2: Manual Setup

1. **Tạo request mới:**

    - Method: `POST`
    - URL: `http://localhost:8000/api/products/create`

2. **Headers:**

    ```
    X-API-Token: bluprinter_xxxxx...
    Accept: application/json
    ```

3. **Body (form-data):**

    - `name`: AI T-Shirt
    - `description`: Beautiful design
    - `template_id`: 1
    - `images[]`: [Select files]
    - `video`: [Select file]

4. **Send** → Check response!

---

## 📋 API ENDPOINTS CHEAT SHEET

### 1. Create Product

```http
POST /api/products/create
Headers: X-API-Token, Accept: application/json
Body: multipart/form-data
Response: 201 Created
```

**Required fields:**

-   ✅ `name` (string)
-   ✅ `description` (string)
-   ✅ `template_id` (integer: 1 hoặc 2)
-   ✅ `images[]` (files, max 8)

**Optional fields:**

-   ⚪ `video` (file)
-   ⚪ `price` (decimal)
-   ⚪ `shop_id` (integer)

### 2. Get Product

```http
GET /api/products/{id}
Response: 200 OK
```

### 3. List Products

```http
GET /api/products?page=1&per_page=15
Response: 200 OK with pagination
```

---

## 🔧 TEMPLATE IDs

| ID    | Name    | Base Price | Description     |
| ----- | ------- | ---------- | --------------- |
| **1** | T-Shirt | $19.99     | Áo thun cổ tròn |
| **2** | Hoodie  | $39.99     | Áo hoodie có mũ |

💡 **Tip:** Luôn dùng `template_id = 1` cho T-Shirt khi test

---

## 📸 FILE SIZE LIMITS

| Type             | Max Size | Format                    |
| ---------------- | -------- | ------------------------- |
| **Images**       | 5 MB     | jpg, jpeg, png, gif, webp |
| **Video**        | 50 MB    | mp4, mov, avi             |
| **Total images** | 8 files  | Maximum per product       |

---

## ⚠️ XỬ LÝ LỖI

| Code    | Error               | Fix                             |
| ------- | ------------------- | ------------------------------- |
| **419** | CSRF Token Mismatch | ✅ Đã fix! API bypass CSRF      |
| **401** | Unauthorized        | Check header `X-API-Token`      |
| **404** | Not Found           | ✅ Đã fix! Auto-assign shop_id  |
| **400** | Validation Error    | Check required fields           |
| **500** | Server Error        | Check AWS credentials in `.env` |

---

## 🐛 DEBUGGING

### Check logs:

```bash
tail -f storage/logs/laravel.log
```

### Test connection:

```bash
curl -X GET http://localhost:8000/api/products
```

### Check database:

```sql
-- Check token
SELECT * FROM api_tokens WHERE is_active = 1;

-- Check products
SELECT id, name, shop_id, created_by FROM products ORDER BY id DESC LIMIT 5;

-- Check API usage
SELECT
    COUNT(*) as total_products,
    MAX(created_at) as last_created
FROM products
WHERE created_by = 'api';
```

---

## 💻 CODE EXAMPLES

### cURL (Bash)

```bash
curl -X POST http://localhost:8000/api/products/create \
  -H "X-API-Token: YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "name=AI Product" \
  -F "description=Testing" \
  -F "template_id=1" \
  -F "images[]=@image1.jpg"
```

### Python

```python
import requests

files = {'images[]': open('image.jpg', 'rb')}
data = {'name': 'AI Product', 'description': 'Test', 'template_id': 1}
headers = {'X-API-Token': 'YOUR_TOKEN', 'Accept': 'application/json'}

r = requests.post('http://localhost:8000/api/products/create',
                  headers=headers, files=files, data=data)
print(r.json())
```

### Node.js

```javascript
const FormData = require("form-data");
const form = new FormData();
form.append("name", "AI Product");
form.append("template_id", "1");
form.append("images[]", fs.createReadStream("image.jpg"));

axios.post("http://localhost:8000/api/products/create", form, {
    headers: { "X-API-Token": "YOUR_TOKEN", ...form.getHeaders() },
});
```

### PHP

```php
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'http://localhost:8000/api/products/create',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'name' => 'AI Product',
        'template_id' => 1,
        'images[]' => new CURLFile('image.jpg')
    ],
    CURLOPT_HTTPHEADER => ['X-API-Token: YOUR_TOKEN']
]);
$response = curl_exec($curl);
```

---

## 🎉 SUCCESS CHECKLIST

-   [ ] ✅ Đã lấy được API Token
-   [ ] ✅ Test API bằng cURL hoặc Postman thành công
-   [ ] ✅ Sản phẩm được tạo và hiển thị trên website
-   [ ] ✅ Hình ảnh upload lên S3 thành công
-   [ ] ✅ Đọc xong documentation

**Nếu tất cả đều check ✅ → Bạn đã sẵn sàng integrate vào AI system! 🚀**

---

## 📞 SUPPORT

Nếu gặp vấn đề:

1. **Check Swagger UI:** http://localhost:8000/api-docs.html
2. **Read full docs:** `API_DOCUMENTATION_VIETNAMESE.md`
3. **Check Postman guide:** `POSTMAN_STEP_BY_STEP.md`
4. **View logs:** `storage/logs/laravel.log`

---

## 🔗 USEFUL LINKS

-   🏠 **Homepage:** http://localhost:8000
-   🔑 **API Token Dashboard:** http://localhost:8000/api-token.html
-   📖 **Swagger Docs:** http://localhost:8000/api-docs.html
-   🛍️ **Shop:** http://localhost:8000/shops
-   📦 **Products:** http://localhost:8000/products

---

**Made with ❤️ by Bluprinter Team**

Last Updated: 2025-10-16


