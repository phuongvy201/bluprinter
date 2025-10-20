# 🧪 POSTMAN TEST GUIDE

## 📋 Setup Postman

### 1. Create New Collection

-   Tên: `Bluprinter API Tests`
-   Description: `Test API for AI product creation`

### 2. Environment Variables

Tạo Environment với các variables:

```
base_url: http://localhost:8000
api_token: bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ
```

## 🚀 Test Cases

### Test 1: Create Product (Success)

**Method:** `POST`  
**URL:** `{{base_url}}/api/products/create`

**Headers:**

```
X-API-Token: {{api_token}}
Content-Type: multipart/form-data
```

**Body (form-data):**

```
name: AI Test T-Shirt
description: Beautiful AI-generated design for testing
template_id: 1
images[]: [Select 2-3 image files]
video: [Select 1 video file]
```

**Expected Response (201):**

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
            "https://s3.us-east-1.amazonaws.com/image.bluprinter/products/..."
        ],
        "created_at": "2025-10-16T..."
    }
}
```

### Test 2: Invalid Token

**Method:** `POST`  
**URL:** `{{base_url}}/api/products/create`

**Headers:**

```
X-API-Token: invalid_token_123
Content-Type: multipart/form-data
```

**Expected Response (401):**

```json
{
    "success": false,
    "message": "Invalid or expired API token"
}
```

### Test 3: Missing Required Fields

**Method:** `POST`  
**URL:** `{{base_url}}/api/products/create`

**Headers:**

```
X-API-Token: {{api_token}}
Content-Type: multipart/form-data
```

**Body (form-data):**

```
name: Test Product
// Missing description, template_id, images, video
```

**Expected Response (422):**

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

### Test 4: Get Product Details

**Method:** `GET`  
**URL:** `{{base_url}}/api/products/{{product_id}}`

**Headers:**

```
X-API-Token: {{api_token}}
```

**Expected Response (200):**

```json
{
  "success": true,
  "data": {
    "id": 123,
    "name": "AI Test T-Shirt",
    "slug": "ai-test-t-shirt",
    "description": "Beautiful AI-generated design for testing",
    "price": 29.99,
    "status": "active",
    "url": "http://localhost:8000/products/ai-test-t-shirt",
    "media": ["https://s3..."],
    "shop": {...},
    "template": {...},
    "created_at": "2025-10-16T..."
  }
}
```

### Test 5: List Products

**Method:** `GET`  
**URL:** `{{base_url}}/api/products/`

**Headers:**

```
X-API-Token: {{api_token}}
```

**Expected Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "name": "AI Test T-Shirt",
            "slug": "ai-test-t-shirt",
            "url": "http://localhost:8000/products/ai-test-t-shirt",
            "created_at": "2025-10-16T..."
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 20,
        "total": 1
    }
}
```

## 🔧 Pre-Test Setup

### 1. Check Database

```sql
-- Check if we have templates
SELECT id, name FROM product_templates LIMIT 5;

-- Check if we have shops
SELECT id, name FROM shops LIMIT 5;

-- Check API token
SELECT name, token, is_active FROM api_tokens;
```

### 2. Prepare Test Files

Tạo thư mục `test_files/` với:

-   `test_image1.jpg` (small image file)
-   `test_image2.jpg` (small image file)
-   `test_video.mp4` (small video file)

### 3. Start Laravel Server

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

## 🐛 Common Issues & Solutions

### Issue 1: "Invalid or expired API token"

**Solution:** Check token in database:

```sql
SELECT * FROM api_tokens WHERE token = 'your_token';
```

### Issue 2: "Template not found"

**Solution:** Use valid template_id:

```sql
SELECT id, name FROM product_templates WHERE id = 1;
```

### Issue 3: "AWS S3 upload failed"

**Solution:** Check AWS credentials in `.env`:

```env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_BUCKET=image.bluprinter
```

### Issue 4: "File too large"

**Solution:** Use smaller test files (< 10MB images, < 100MB video)

## 📊 Test Checklist

-   [ ] ✅ API token authentication works
-   [ ] ✅ Product creation with valid data
-   [ ] ✅ File upload to AWS S3
-   [ ] ✅ Validation errors for missing fields
-   [ ] ✅ Get product details
-   [ ] ✅ List products
-   [ ] ✅ Error handling for invalid requests
-   [ ] ✅ Response format matches documentation

## 🎯 Success Criteria

1. **Product Created:** Returns 201 with product URL
2. **Files Uploaded:** S3 URLs in response
3. **Database Updated:** Product exists in database
4. **Frontend Accessible:** Product page loads correctly
5. **Error Handling:** Proper error messages for invalid requests



