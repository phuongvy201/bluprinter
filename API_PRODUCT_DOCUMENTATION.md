# API Product Creation Documentation

## 🔑 API Token

**Token:** `bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ`

**Permissions:** `product:create`, `product:read`

## 📡 API Endpoints

### 1. Create Product
**POST** `/api/products/create`

#### Headers
```
X-API-Token: bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ
Content-Type: multipart/form-data
```

#### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | ✅ | Product name |
| `description` | string | ✅ | Product description |
| `template_id` | integer | ✅ | Product template ID |
| `images[]` | file[] | ✅ | 1-8 image files (jpeg, jpg, png, webp, max 10MB each) |
| `video` | file | ✅ | 1 video file (mp4, avi, mov, webm, max 100MB) |
| `price` | float | ❌ | Custom price (optional, uses template price if not provided) |
| `shop_id` | integer | ❌ | Shop ID (optional, uses template shop if not provided) |

#### Example Request (cURL)
```bash
curl -X POST "https://your-domain.com/api/products/create" \
  -H "X-API-Token: bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ" \
  -F "name=AI Generated T-Shirt" \
  -F "description=Beautiful AI-generated design on premium cotton" \
  -F "template_id=1" \
  -F "images[]=@image1.jpg" \
  -F "images[]=@image2.jpg" \
  -F "images[]=@image3.jpg" \
  -F "video=@product_video.mp4"
```

#### Success Response (201)
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "product_id": 123,
    "name": "AI Generated T-Shirt",
    "slug": "ai-generated-t-shirt",
    "url": "https://your-domain.com/products/ai-generated-t-shirt",
    "media": [
      "https://s3.us-east-1.amazonaws.com/image.bluprinter/products/1234567890_abc123.jpg",
      "https://s3.us-east-1.amazonaws.com/image.bluprinter/products/1234567891_def456.jpg",
      "https://s3.us-east-1.amazonaws.com/image.bluprinter/products/1234567892_ghi789.mp4"
    ],
    "images": [
      {
        "url": "https://s3.us-east-1.amazonaws.com/image.bluprinter/products/1234567890_abc123.jpg",
        "filename": "1234567890_abc123.jpg",
        "order": 1
      }
    ],
    "video": "https://s3.us-east-1.amazonaws.com/image.bluprinter/products/1234567892_ghi789.mp4",
    "created_at": "2025-10-16T08:49:27.000000Z"
  }
}
```

#### Error Response (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "template_id": ["The template id field is required."]
  }
}
```

### 2. Get Product Details
**GET** `/api/products/{id}`

#### Headers
```
X-API-Token: bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ
```

#### Success Response (200)
```json
{
  "success": true,
  "data": {
    "id": 123,
    "name": "AI Generated T-Shirt",
    "slug": "ai-generated-t-shirt",
    "description": "Beautiful AI-generated design on premium cotton",
    "price": 29.99,
    "status": "active",
    "url": "https://your-domain.com/products/ai-generated-t-shirt",
    "media": ["https://s3.us-east-1.amazonaws.com/image.bluprinter/products/..."],
    "shop": {
      "id": 1,
      "name": "AI Shop"
    },
    "template": {
      "id": 1,
      "name": "Basic T-Shirt",
      "category": {
        "id": 1,
        "name": "T-Shirts"
      }
    },
    "created_at": "2025-10-16T08:49:27.000000Z"
  }
}
```

### 3. List Products
**GET** `/api/products/`

#### Headers
```
X-API-Token: bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ
```

#### Success Response (200)
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "name": "AI Generated T-Shirt",
      "slug": "ai-generated-t-shirt",
      "url": "https://your-domain.com/products/ai-generated-t-shirt",
      "created_at": "2025-10-16T08:49:27.000000Z"
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

## 🔧 Setup Requirements

### 1. AWS S3 Configuration
Add to your `.env` file:
```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=image.bluprinter
```

### 2. File Upload Limits
- **Images:** Max 10MB each, formats: jpeg, jpg, png, webp
- **Video:** Max 100MB, formats: mp4, avi, mov, webm
- **Total files:** 1-8 images + 1 video

### 3. Template Requirements
- Product template must exist in database
- Template must have valid `shop_id` and `category_id`

## 🚀 Usage Examples

### Python Example
```python
import requests

# API endpoint
url = "https://your-domain.com/api/products/create"

# Headers
headers = {
    "X-API-Token": "bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ"
}

# Data
data = {
    "name": "AI Generated Hoodie",
    "description": "Premium hoodie with AI-generated design",
    "template_id": 2
}

# Files
files = {
    "images[]": [
        ("image1.jpg", open("image1.jpg", "rb"), "image/jpeg"),
        ("image2.jpg", open("image2.jpg", "rb"), "image/jpeg")
    ],
    "video": ("video.mp4", open("video.mp4", "rb"), "video/mp4")
}

# Make request
response = requests.post(url, headers=headers, data=data, files=files)
result = response.json()

print(f"Product created: {result['data']['url']}")
```

### JavaScript Example
```javascript
const formData = new FormData();
formData.append('name', 'AI Generated Mug');
formData.append('description', 'Custom mug with AI design');
formData.append('template_id', '3');

// Add images
for (let i = 0; i < imageFiles.length; i++) {
    formData.append('images[]', imageFiles[i]);
}

// Add video
formData.append('video', videoFile);

fetch('/api/products/create', {
    method: 'POST',
    headers: {
        'X-API-Token': 'bluprinter_nW3Mw878gXQdMFt4ArO64uX7FdfjOyPCRsOBT3mwBGkQjGdcjmIpoK6nE4sZ'
    },
    body: formData
})
.then(response => response.json())
.then(data => {
    console.log('Product created:', data.data.url);
});
```

## 🔒 Security Notes

- API token is required for all requests
- Token has expiration and usage tracking
- File uploads are validated for type and size
- All uploads go to AWS S3 with public access
- Products are created with `status: active` by default

## 📊 Response Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 401 | Unauthorized (invalid token) |
| 403 | Forbidden (insufficient permissions) |
| 422 | Validation Error |
| 500 | Server Error |










