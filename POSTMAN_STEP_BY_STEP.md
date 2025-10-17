# 🧪 POSTMAN TEST - STEP BY STEP

## 📋 THÔNG TIN TEST

**Base URL:** `http://localhost:8000`  
**API Token:** `bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ`  
**Template ID:** `1` (T-shirt UK NEW ALL)  
**Test Files:** `test_files/` folder

## 🚀 BƯỚC 1: IMPORT COLLECTION

1. Mở Postman
2. Click **Import**
3. Chọn file `Bluprinter_API_Collection.postman_collection.json`
4. Collection sẽ được import với 5 requests

## 🧪 BƯỚC 2: TEST CREATE PRODUCT (SUCCESS)

### Request: "1. Create Product (Success)"

1. **Method:** `POST`
2. **URL:** `http://localhost:8000/api/products/create`
3. **Headers:**
    ```
    X-API-Token: bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ
    Accept: application/json
    ```
4. **Body (form-data):**

    ```
    name: AI Test T-Shirt
    description: Beautiful AI-generated design for testing
    template_id: 1
    images[]: [Chọn file test_files/test_image1.png]
    images[]: [Chọn file test_files/test_image2.png]
    video: [Chọn file test_files/test_video.mp4]
    ```

5. **Click Send**

### ✅ Expected Response (201):

```json
{
    "success": true,
    "message": "Product created successfully",
    "data": {
        "product_id": 123,
        "name": "AI Test T-Shirt",
        "slug": "ai-test-t-shirt",
        "url": "http://localhost:8000/products/ai-test-t-shirt",
        "media": [
            "https://s3.us-east-1.amazonaws.com/image.bluprinter/products/...",
            "https://s3.us-east-1.amazonaws.com/image.bluprinter/products/..."
        ],
        "created_at": "2025-10-16T..."
    }
}
```

## ❌ BƯỚC 3: TEST ERROR CASES

### Test 2: Invalid Token

-   **Request:** "2. Create Product (Invalid Token)"
-   **Expected:** 401 Unauthorized
-   **Response:**

```json
{
    "success": false,
    "message": "Invalid or expired API token"
}
```

### Test 3: Missing Fields

-   **Request:** "3. Create Product (Missing Fields)"
-   **Expected:** 422 Validation Error
-   **Response:**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "description": ["The description field is required."],
        "template_id": ["The template id field is required."],
        "images": ["The images field is required."],
        "video": ["The video field is required."]
    }
}
```

## 📊 BƯỚC 4: TEST GET REQUESTS

### Test 4: Get Product Details

-   **Request:** "4. Get Product Details"
-   **URL:** `http://localhost:8000/api/products/1`
-   **Expected:** 200 OK với product details

### Test 5: List Products

-   **Request:** "5. List Products"
-   **URL:** `http://localhost:8000/api/products/`
-   **Expected:** 200 OK với danh sách products

## 🔧 BƯỚC 5: KIỂM TRA KẾT QUẢ

### 1. Database Check

```sql
SELECT id, name, slug, created_by FROM products WHERE created_by = 'api';
```

### 2. Frontend Check

-   Mở browser: `http://localhost:8000/products/ai-test-t-shirt`
-   Kiểm tra product hiển thị đúng

### 3. AWS S3 Check

-   Kiểm tra files đã upload lên S3
-   URLs trong response có accessible

## 🐛 TROUBLESHOOTING

### Lỗi 1: "Connection refused"

**Solution:** Start Laravel server

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Lỗi 2: "Invalid or expired API token"

**Solution:** Check token in database

```sql
SELECT * FROM api_tokens WHERE token = 'bluprinter_...';
```

### Lỗi 3: "Template not found"

**Solution:** Use valid template_id

```sql
SELECT id, name FROM product_templates;
```

### Lỗi 4: "AWS S3 upload failed"

**Solution:** Check AWS credentials in `.env`

```env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_BUCKET=image.bluprinter
```

## ✅ CHECKLIST

-   [ ] ✅ Collection imported successfully
-   [ ] ✅ Test files created in `test_files/` folder
-   [ ] ✅ Laravel server running on port 8000
-   [ ] ✅ Create Product (Success) returns 201
-   [ ] ✅ Invalid Token returns 401
-   [ ] ✅ Missing Fields returns 422
-   [ ] ✅ Get Product Details returns 200
-   [ ] ✅ List Products returns 200
-   [ ] ✅ Product appears in frontend
-   [ ] ✅ Files uploaded to AWS S3

## 🎯 SUCCESS CRITERIA

1. **API Working:** All endpoints respond correctly
2. **File Upload:** Images and video uploaded to S3
3. **Database Updated:** Product created in database
4. **Frontend Accessible:** Product page loads correctly
5. **Error Handling:** Proper error messages for invalid requests

## 📞 SUPPORT

Nếu gặp lỗi, check:

1. Laravel server đang chạy
2. Database connection OK
3. AWS S3 credentials đúng
4. API token valid
5. Template ID exists
