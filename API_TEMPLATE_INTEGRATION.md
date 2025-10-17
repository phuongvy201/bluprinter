# ✅ API TEMPLATE INTEGRATION - HOÀN THÀNH!

## 🎯 MỤC ĐÍCH

Khi tạo sản phẩm bằng AI qua API, hệ thống sẽ tự động **copy thông tin từ template** (giống như cách làm trong hmtik ProductController) nếu các thông tin đó không được cung cấp.

---

## 🔧 CÁC THAY ĐỔI ĐÃ THỰC HIỆN

### 1. ✅ Cập nhật API Controller

**File:** `app/Http/Controllers/Api/ProductController.php`

#### **Eager Loading Template với Relations:**

```php
// Get template with all relationships
$template = ProductTemplate::with(['category', 'attributes', 'variants'])
    ->findOrFail($request->template_id);
```

#### **Auto-copy thông tin từ Template:**

```php
// Prepare product data - copy thông tin từ template (giống hmtik)
$productData = [
    'name' => $request->name,
    'slug' => $slug,
    'template_id' => $request->template_id,
    'shop_id' => $shopId,
    'status' => 'active',
    'created_by' => 'api',
    'api_token_id' => $token->id,

    // Copy từ template nếu không được cung cấp
    'description' => $request->description ?? $template->description,
    'price' => $request->price ?? $template->base_price,

    // Media: Ưu tiên media mới upload, fallback về template media
    'media' => !empty($mediaUrls) ? $mediaUrls : ($template->media ?? []),

    // Quantity mặc định
    'quantity' => $request->quantity ?? 999,
];

// Copy variants from template (giống hmtik)
$createdVariants = [];
if ($template->variants && $template->variants->count() > 0) {
    foreach ($template->variants as $templateVariant) {
        $variantData = [
            'product_id' => $product->id,
            'template_id' => $template->id,
            'variant_name' => $templateVariant->variant_name,
            'attributes' => $templateVariant->attributes,
            'sku' => $templateVariant->sku . '-' . $product->id, // Make SKU unique
            'price' => $templateVariant->price ?? $template->base_price,
            'quantity' => $request->quantity ?? 999,
            'media' => $templateVariant->media ?? $template->media ?? [],
        ];

        $variant = ProductVariant::create($variantData);
        $createdVariants[] = [...];
    }
}
```

---

### 2. ✅ Cập nhật Validation

**Trước đây (Required):**

```php
'description' => 'required|string',
'video' => 'required|file|...',
'price' => 'nullable|numeric|...',
```

**Bây giờ (Optional với fallback):**

```php
'description' => 'nullable|string', // ← Sẽ lấy từ template nếu không có
'video' => 'nullable|file|...',    // ← Optional
'price' => 'nullable|numeric|...',  // ← Sẽ lấy base_price từ template
'quantity' => 'nullable|integer|...', // ← Mặc định 999
```

---

### 3. ✅ Enhanced Response Data

**Response giờ bao gồm đầy đủ thông tin:**

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

        // ⭐ Thông tin Template (NEW!)
        "template": {
            "id": 1,
            "name": "T-Shirt Template",
            "category": {
                "id": 5,
                "name": "Clothing"
            }
        },

        // ⭐ Shop info
        "shop_id": 1,

        // ⭐ Media details (NEW!)
        "media": [...],
        "uploaded_images": [
            {
                "url": "https://s3...",
                "filename": "...",
                "order": 1
            }
        ],
         "uploaded_video": "https://s3...",

         // ⭐ Variants (NEW!)
         "variants": [
             {
                 "id": 1,
                 "variant_name": "Black - S",
                 "attributes": {"color": "Black", "size": "S"},
                 "sku": "TSH-BLK-S-123",
                 "price": 19.99,
                 "quantity": 999
             },
             {
                 "id": 2,
                 "variant_name": "Black - M",
                 "attributes": {"color": "Black", "size": "M"},
                 "sku": "TSH-BLK-M-123",
                 "price": 19.99,
                 "quantity": 999
             }
         ],
         "variants_count": 2,

         // ⭐ Metadata
         "created_by": "api",
         "created_at": "2025-10-16T10:30:00Z"
     }
 }
```

---

## 📋 LOGIC TỰ ĐỘNG

### Thứ tự ưu tiên (Priority Order):

| Field           | Priority 1 (Highest)    | Priority 2       | Priority 3 (Fallback) |
| --------------- | ----------------------- | ---------------- | --------------------- |
| **description** | Request input           | -                | Template.description  |
| **price**       | Request input           | -                | Template.base_price   |
| **shop_id**     | Request input           | Template.shop_id | Default: 1            |
| **media**       | Uploaded files          | -                | Template.media        |
| **quantity**    | Request input           | -                | Default: 999          |
| **variants**    | Auto-copy from template | -                | Empty array           |

---

## 🎯 USE CASES

### Case 1: AI chỉ cần tạo với images mới

```bash
curl -X POST /api/products/create \
  -H "X-API-Token: xxx" \
  -F "name=AI Design 001" \
  -F "template_id=1" \
  -F "images[]=@design1.jpg" \
  -F "images[]=@design2.jpg"
```

**Kết quả:**

-   ✅ `description` → Copy từ template
-   ✅ `price` → Copy `base_price` từ template
-   ✅ `shop_id` → Copy từ template (hoặc default 1)
-   ✅ `media` → Sử dụng 2 images vừa upload
-   ✅ `quantity` → Default 999
-   ✅ `variants` → Tự động copy TẤT CẢ variants từ template (Size, Color, etc.)

### Case 2: AI tùy chỉnh đầy đủ

```bash
curl -X POST /api/products/create \
  -H "X-API-Token: xxx" \
  -F "name=Premium AI Design" \
  -F "description=Custom description by AI" \
  -F "price=49.99" \
  -F "quantity=100" \
  -F "template_id=1" \
  -F "images[]=@design1.jpg"
```

**Kết quả:**

-   ✅ `description` → "Custom description by AI" (từ request)
-   ✅ `price` → 49.99 (từ request)
-   ✅ `quantity` → 100 (từ request)
-   ✅ Các field khác → Từ template

---

## 📚 DOCUMENTATION UPDATED

### Files đã cập nhật:

1. ✅ **API_DOCUMENTATION_VIETNAMESE.md**

    - Updated request body table
    - Updated response example
    - Added notes về auto-copy từ template

2. ✅ **public/api-docs.html** (Swagger UI)

    - Updated required fields
    - Updated field descriptions
    - Added `quantity` field

3. ✅ **API_TEMPLATE_INTEGRATION.md** (File này)
    - Complete integration guide
    - Use cases & examples

---

## 🔍 SO SÁNH VỚI HMTIK

### hmtik ProductController.php:

```php
// hmtik tạo product từ template
$data = $request->only([...]);
$data['user_id'] = $user->id;
$data['team_id'] = $team->id;
$data['is_active'] = true;
$product = Product::create($data);
```

### Bluprinter API ProductController.php:

```php
// Bluprinter API - Tương tự nhưng có auto-copy
$productData = [
    'name' => $request->name,
    // ... các field khác

    // Auto-copy từ template
    'description' => $request->description ?? $template->description,
    'price' => $request->price ?? $template->base_price,
    'media' => !empty($mediaUrls) ? $mediaUrls : ($template->media ?? []),
];
$product = Product::create($productData);
```

---

## ✅ BENEFITS

### 1. **Giảm Payload Size**

-   AI không cần gửi description nếu dùng mặc định của template
-   Giảm bandwidth, tăng tốc độ request

### 2. **Consistency**

-   Sản phẩm tự động inherit properties từ template
-   Đảm bảo consistency về giá, mô tả cho cùng loại template

### 3. **Flexibility**

-   AI có thể override bất kỳ field nào khi cần
-   Hoặc để mặc định để nhanh hơn

### 4. **Maintenance**

-   Cập nhật template → Tất cả sản phẩm mới tự động áp dụng
-   Không cần update AI logic

---

## 🧪 TESTING

### Test Case 1: Minimal Request

```bash
# Only required fields
POST /api/products/create
{
    name: "Test Product",
    template_id: 1,
    images: [file1.jpg, file2.jpg]
}

# Expected: All other fields copied from template
```

### Test Case 2: Full Override

```bash
# All fields provided
POST /api/products/create
{
    name: "Test Product",
    description: "Custom desc",
    price: 99.99,
    quantity: 50,
    template_id: 1,
    images: [file1.jpg],
    video: video.mp4
}

# Expected: Use all provided values
```

### Test Case 3: Partial Override

```bash
# Mix of provided and template values
POST /api/products/create
{
    name: "Test Product",
    price: 29.99, // Override
    template_id: 1,
    images: [file1.jpg]
}

# Expected:
# - price: 29.99 (from request)
# - description: from template
# - media: uploaded image
# - quantity: 999 (default)
```

---

## 📝 NOTES

### Important Points:

1. **Template MUST exist**

    - API sẽ return 404 nếu `template_id` không tồn tại
    - Validate trước khi gọi API

2. **Media Priority**

    - Uploaded files > Template media
    - Nếu không upload → Dùng template media
    - Nếu template cũng không có media → Empty array

3. **Price Handling**

    - Template có `base_price`
    - Product có `price` (optional)
    - Fallback: `product.price = template.base_price`

4. **Shop Assignment**

    - Priority: Request > Template > Default (1)
    - Luôn đảm bảo product có shop_id (required cho frontend)

5. **⭐ Variants Auto-Copy (NEW!)**
    - API tự động copy **TẤT CẢ** variants từ template
    - Mỗi variant có: variant_name, attributes (color, size), sku, price, quantity
    - SKU tự động unique: `{template_sku}-{product_id}`
    - Ví dụ: Template có 6 variants (3 colors × 2 sizes) → Product cũng có 6 variants
    - Không cần AI gửi thông tin variants - Hoàn toàn tự động!

---

## 🎉 HOÀN THÀNH!

API giờ đã hoạt động giống như hmtik ProductController - **tự động copy thông tin từ template** khi cần!

### Quick Test:

```bash
# Test minimal request
curl -X POST http://localhost:8000/api/products/create \
  -H "X-API-Token: bluprinter_xxxxx" \
  -H "Accept: application/json" \
  -F "name=AI Test" \
  -F "template_id=1" \
  -F "images[]=@test.jpg"
```

**Expected Response:** Product với description và price từ template!

---

**Last Updated:** 2025-10-16  
**Status:** ✅ Complete & Tested
