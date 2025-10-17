# 📚 HƯỚNG DẪN SỬ DỤNG BLUPRINTER API

## 🌐 Truy cập Swagger Documentation

Mở trình duyệt và truy cập:

```
http://localhost:8000/api-docs.html
```

## 🔑 XÁC THỰC (AUTHENTICATION)

### Lấy API Token

1. **Kiểm tra token trong database:**

```sql
SELECT token, name, is_active FROM api_tokens WHERE is_active = 1;
```

2. **Hoặc chạy script PHP:**

```bash
php artisan tinker
```

```php
$token = \App\Models\ApiToken::where('is_active', true)->first();
echo $token->token;
```

### Sử dụng Token

Thêm header vào mọi request:

```
X-API-Token: bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ
Accept: application/json
```

**⚠️ LƯU Ý:**

-   Không cần prefix `Bearer`
-   Chỉ cần giá trị token thuần
-   Token có format: `bluprinter_xxxxx...`

---

## 📋 DANH SÁCH API ENDPOINTS

### 1. 🆕 TẠO SẢN PHẨM MỚI

**Endpoint:** `POST /api/products/create`

**Mô tả:** Tạo sản phẩm mới với hình ảnh và video, tự động upload lên AWS S3

**✨ Tính năng tự động:**

-   ⭐ Copy `description`, `price` từ template nếu không cung cấp
-   ⭐ Tự động copy **TẤT CẢ variants** từ template (Size, Color, etc.)
-   ⭐ Tạo SKU unique cho mỗi variant
-   ⭐ Assign shop_id tự động

#### Headers:

```http
X-API-Token: bluprinter_xxxxx...
Accept: application/json
Content-Type: multipart/form-data
```

#### Request Body (form-data):

| Field         | Type    | Required | Description                                                       |
| ------------- | ------- | -------- | ----------------------------------------------------------------- |
| `name`        | string  | ✅       | Tên sản phẩm                                                      |
| `description` | string  | ❌       | Mô tả sản phẩm (nếu không có, tự động copy từ template)           |
| `template_id` | integer | ✅       | ID của template (1=T-Shirt, 2=Hoodie, ...)                        |
| `images[]`    | file[]  | ✅       | Mảng hình ảnh (tối đa 8 ảnh, mỗi ảnh max 5MB)                     |
| `video`       | file    | ❌       | Video sản phẩm (max 50MB)                                         |
| `price`       | decimal | ❌       | Giá sản phẩm (nếu không có, tự động lấy `base_price` từ template) |
| `shop_id`     | integer | ❌       | ID shop (nếu không có, tự động lấy từ template)                   |
| `quantity`    | integer | ❌       | Số lượng (mặc định: 999)                                          |

#### Ví dụ cURL:

```bash
curl -X POST http://localhost:8000/api/products/create \
  -H "X-API-Token: bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ" \
  -H "Accept: application/json" \
  -F "name=AI Generated T-Shirt" \
  -F "description=Beautiful AI design" \
  -F "template_id=1" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg" \
  -F "video=@/path/to/video.mp4" \
  -F "price=29.99"
```

#### Response Success (201):

```json
{
    "success": true,
    "message": "Product created successfully",
    "data": {
        "product_id": 123,
        "name": "AI Generated T-Shirt",
        "slug": "ai-generated-t-shirt",
        "description": "Mô tả từ template (nếu không submit)",
        "price": 19.99,
        "quantity": 999,
        "status": "active",
        "url": "http://localhost:8000/products/ai-generated-t-shirt",
        "template": {
            "id": 1,
            "name": "T-Shirt Template",
            "category": {
                "id": 5,
                "name": "Clothing"
            }
        },
        "shop_id": 1,
        "media": [
            "https://s3.amazonaws.com/bucket/products/abc123_image1.jpg",
            "https://s3.amazonaws.com/bucket/products/abc123_image2.jpg",
            "https://s3.amazonaws.com/bucket/products/xyz789_video.mp4"
        ],
        "uploaded_images": [
            {
                "url": "https://s3.amazonaws.com/bucket/products/abc123_image1.jpg",
                "filename": "1697456789_abc123.jpg",
                "order": 1
            },
            {
                "url": "https://s3.amazonaws.com/bucket/products/abc123_image2.jpg",
                "filename": "1697456790_def456.jpg",
                "order": 2
            }
        ],
        "uploaded_video": "https://s3.amazonaws.com/bucket/products/xyz789_video.mp4",
        "variants": [
            {
                "id": 1,
                "variant_name": "Black - S",
                "attributes": { "color": "Black", "size": "S" },
                "sku": "TSH-BLK-S-123",
                "price": 19.99,
                "quantity": 999
            },
            {
                "id": 2,
                "variant_name": "Black - M",
                "attributes": { "color": "Black", "size": "M" },
                "sku": "TSH-BLK-M-123",
                "price": 19.99,
                "quantity": 999
            }
        ],
        "variants_count": 2,
        "created_by": "api",
        "created_at": "2025-10-16T10:30:00.000000Z"
    }
}
```

#### Response Error (400 - Validation):

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "name": ["The name field is required."],
        "template_id": ["The template_id field is required."],
        "images": ["At least one image is required."]
    }
}
```

#### Response Error (401 - Authentication):

```json
{
    "success": false,
    "message": "Invalid API token"
}
```

#### Response Error (404 - Template Not Found):

```json
{
    "success": false,
    "message": "Template not found"
}
```

#### Response Error (500 - Server Error):

```json
{
    "success": false,
    "message": "Failed to upload image to S3",
    "error": "S3 connection timeout"
}
```

---

### 2. 📖 XEM CHI TIẾT SẢN PHẨM

**Endpoint:** `GET /api/products/{id}`

**Mô tả:** Lấy thông tin chi tiết của một sản phẩm

#### Parameters:

-   `id` (path, required): ID của sản phẩm

#### Ví dụ:

```bash
curl -X GET http://localhost:8000/api/products/123
```

#### Response Success (200):

```json
{
    "success": true,
    "data": {
        "id": 123,
        "name": "AI Generated T-Shirt",
        "slug": "ai-generated-t-shirt",
        "description": "Beautiful AI design",
        "price": 29.99,
        "template_id": 1,
        "shop_id": 1,
        "status": "active",
        "media": [
            "https://s3.amazonaws.com/bucket/products/image1.jpg",
            "https://s3.amazonaws.com/bucket/products/video.mp4"
        ],
        "created_by": "api",
        "created_at": "2025-10-16T10:30:00.000000Z",
        "updated_at": "2025-10-16T10:30:00.000000Z"
    }
}
```

---

### 3. 📝 DANH SÁCH SẢN PHẨM

**Endpoint:** `GET /api/products`

**Mô tả:** Lấy danh sách tất cả sản phẩm (có phân trang)

#### Query Parameters:

-   `page` (optional): Số trang (default: 1)
-   `per_page` (optional): Số item mỗi trang (default: 15)

#### Ví dụ:

```bash
curl -X GET "http://localhost:8000/api/products?page=1&per_page=20"
```

#### Response Success (200):

```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "name": "Product 1",
            "slug": "product-1",
            "price": 29.99,
            "status": "active"
        },
        {
            "id": 124,
            "name": "Product 2",
            "slug": "product-2",
            "price": 34.99,
            "status": "active"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total": 50,
        "per_page": 15,
        "last_page": 4
    }
}
```

---

## 🔧 DANH SÁCH TEMPLATE IDs

Các template có sẵn trong hệ thống:

| ID  | Tên Template | Base Price | Mô tả           |
| --- | ------------ | ---------- | --------------- |
| 1   | T-Shirt      | $19.99     | Áo thun cổ tròn |
| 2   | Hoodie       | $39.99     | Áo hoodie có mũ |

**💡 Tip:** Để xem danh sách template đầy đủ:

```sql
SELECT id, name, base_price FROM product_templates WHERE status = 'active';
```

---

## 📤 HƯỚNG DẪN TEST VỚI POSTMAN

### Bước 1: Import Collection

1. Download file: `Bluprinter_API_Collection.postman_collection.json`
2. Mở Postman → Import → Select file
3. Collection sẽ hiện trong sidebar

### Bước 2: Cấu hình Environment

1. Click vào Collection → Variables
2. Set giá trị cho `api_token`:
    ```
    bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ
    ```
3. Save

### Bước 3: Test API

1. Chọn request "Create Product"
2. Tab Body → form-data
3. Upload file cho `images[]` và `video`
4. Click **Send**

### Bước 4: Kiểm tra kết quả

1. Check response JSON
2. Copy URL từ `data.url`
3. Mở trình duyệt và truy cập URL để xem sản phẩm

---

## 🐛 XỬ LÝ LỖI THƯỜNG GẶP

### Lỗi 419 - CSRF Token Mismatch

**Nguyên nhân:** Laravel bảo vệ POST request bằng CSRF token

**Giải pháp:** ✅ Đã fix! API routes được exclude khỏi CSRF protection

### Lỗi 401 - Unauthorized

**Nguyên nhân:** Token không hợp lệ hoặc thiếu

**Giải pháp:**

-   Kiểm tra header `X-API-Token`
-   Đảm bảo token đúng format: `bluprinter_xxxxx...`
-   Không thêm prefix `Bearer`

### Lỗi 404 - Product Not Found

**Nguyên nhân:** Sản phẩm không có `shop_id`

**Giải pháp:** ✅ Đã fix! API tự động gán shop_id khi tạo sản phẩm

### Lỗi 500 - S3 Upload Failed

**Nguyên nhân:** AWS credentials không đúng

**Giải pháp:**

1. Kiểm tra file `.env`:

```env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket
```

2. Restart server: `php artisan serve`

### Lỗi: File quá lớn

**Nguyên nhân:** File vượt quá giới hạn

**Giải pháp:**

-   Images: Max 5MB mỗi file
-   Video: Max 50MB
-   Nén file trước khi upload

---

## 💻 CODE EXAMPLES

### Python Example:

```python
import requests

url = "http://localhost:8000/api/products/create"
headers = {
    "X-API-Token": "bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ",
    "Accept": "application/json"
}

files = {
    'images[]': [
        open('image1.jpg', 'rb'),
        open('image2.jpg', 'rb')
    ],
    'video': open('video.mp4', 'rb')
}

data = {
    'name': 'AI Generated T-Shirt',
    'description': 'Beautiful AI design',
    'template_id': 1,
    'price': 29.99
}

response = requests.post(url, headers=headers, files=files, data=data)
print(response.json())
```

### Node.js Example:

```javascript
const FormData = require("form-data");
const fs = require("fs");
const axios = require("axios");

const form = new FormData();
form.append("name", "AI Generated T-Shirt");
form.append("description", "Beautiful AI design");
form.append("template_id", "1");
form.append("price", "29.99");
form.append("images[]", fs.createReadStream("image1.jpg"));
form.append("images[]", fs.createReadStream("image2.jpg"));
form.append("video", fs.createReadStream("video.mp4"));

axios
    .post("http://localhost:8000/api/products/create", form, {
        headers: {
            "X-API-Token":
                "bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ",
            Accept: "application/json",
            ...form.getHeaders(),
        },
    })
    .then((response) => console.log(response.data))
    .catch((error) => console.error(error.response.data));
```

### PHP Example:

```php
<?php

$curl = curl_init();

$url = 'http://localhost:8000/api/products/create';
$token = 'bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ';

$postData = [
    'name' => 'AI Generated T-Shirt',
    'description' => 'Beautiful AI design',
    'template_id' => 1,
    'price' => 29.99,
    'images[]' => [
        new CURLFile('image1.jpg', 'image/jpeg', 'image1.jpg'),
        new CURLFile('image2.jpg', 'image/jpeg', 'image2.jpg')
    ],
    'video' => new CURLFile('video.mp4', 'video/mp4', 'video.mp4')
];

curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_HTTPHEADER => [
        "X-API-Token: $token",
        "Accept: application/json"
    ],
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

curl_close($curl);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

$data = json_decode($response, true);
if ($data['success']) {
    echo "\n✅ Product created successfully!\n";
    echo "Product URL: {$data['data']['url']}\n";
}
?>
```

---

## 📊 RATE LIMITING

Hiện tại API **KHÔNG** có giới hạn request. Trong production nên thêm rate limiting:

```php
// Trong routes/web.php
Route::middleware('throttle:60,1')->group(function () {
    // 60 requests per minute
});
```

---

## 🔒 BẢO MẬT

### Best Practices:

1. **Không commit token vào Git:**

    ```bash
    # Add to .gitignore
    .env
    *.token
    ```

2. **Rotate token định kỳ:**

    ```php
    $token->update(['token' => ApiToken::generateToken()]);
    ```

3. **Vô hiệu hóa token khi không dùng:**

    ```php
    $token->update(['is_active' => false]);
    ```

4. **Kiểm tra token expiry:**
    ```php
    if ($token->expires_at && $token->expires_at < now()) {
        return response()->json(['error' => 'Token expired'], 401);
    }
    ```

---

## 📞 HỖ TRỢ

Nếu gặp vấn đề, hãy kiểm tra:

1. **Log Laravel:**

    ```bash
    tail -f storage/logs/laravel.log
    ```

2. **Database log:**

    ```sql
    SELECT * FROM api_tokens WHERE is_active = 1;
    ```

3. **Test connection:**
    ```bash
    curl -X GET http://localhost:8000/api/products
    ```

---

## 🎉 HOÀN THÀNH!

API đã sẵn sàng sử dụng. Chúc bạn develop thành công! 🚀

**Quick Links:**

-   📄 Swagger UI: http://localhost:8000/api-docs.html
-   📦 Postman Collection: `Bluprinter_API_Collection.postman_collection.json`
-   📚 Full Documentation: `API_PRODUCT_DOCUMENTATION.md`
