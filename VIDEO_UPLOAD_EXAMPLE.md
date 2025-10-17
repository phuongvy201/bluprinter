# 🎬 Video Upload Examples

## 📋 Quick Examples

### 1️⃣ Upload Video Qua Admin Panel

**Steps:**

1. Login as Admin/Seller
2. Go to: **Products > Add New Product** hoặc **Import Products**
3. Select template
4. Scroll to **Product Media** section
5. Click **Choose Files**
6. Select:
    - ✅ Images: `product_front.jpg`, `product_back.jpg`
    - ✅ Video: `product_demo.mp4`
7. Click **Create Product**

✅ **Result:** Product có 2 images + 1 video!

---

### 2️⃣ Upload Video Qua API (cURL)

**Simple Video Upload:**

```bash
curl -X POST http://localhost/api/products/create \
  -H "X-API-Token: bluprinter_abc123..." \
  -F "name=Cool Baseball Jacket" \
  -F "template_id=1" \
  -F "images=@front.jpg" \
  -F "video=@demo.mp4"
```

**Multiple Images + Video:**

```bash
curl -X POST http://localhost/api/products/create \
  -H "X-API-Token: bluprinter_abc123..." \
  -F "name=Awesome Product" \
  -F "template_id=5" \
  -F "images=@image1.jpg" \
  -F "images=@image2.jpg" \
  -F "images=@image3.jpg" \
  -F "video=@tutorial.mp4" \
  -F "description=Check out our demo video!" \
  -F "price=39.99" \
  -F "shop_id=1"
```

**API Response:**

```json
{
    "success": true,
    "message": "Product created successfully",
    "data": {
        "product_id": 123,
        "name": "Awesome Product",
        "media": [
            "https://s3.us-east-1.amazonaws.com/image.bluprinter/products/image1.jpg",
            "https://s3.us-east-1.amazonaws.com/image.bluprinter/products/image2.jpg",
            "https://s3.us-east-1.amazonaws.com/image.bluprinter/products/image3.jpg",
            "https://s3.us-east-1.amazonaws.com/image.bluprinter/products/tutorial.mp4"
        ],
        "uploaded_images": [
            { "url": "https://s3.../image1.jpg", "order": 1 },
            { "url": "https://s3.../image2.jpg", "order": 2 },
            { "url": "https://s3.../image3.jpg", "order": 3 }
        ],
        "uploaded_video": "https://s3.../tutorial.mp4",
        "shop_id": 1
    }
}
```

---

### 3️⃣ Upload Video Qua Postman

**Setup:**

1. Open Postman
2. Create **New Request**
3. Method: **POST**
4. URL: `http://localhost/api/products/create`

**Headers:**

```
X-API-Token: bluprinter_abc123...
```

**Body (form-data):**

```
name: Cool Product
template_id: 1
images: [file] image1.jpg
images: [file] image2.jpg
video: [file] demo.mp4
price: 29.99
description: Watch the demo!
shop_id: 1
```

**Send!** ✅

---

### 4️⃣ Upload Video Qua JavaScript/Fetch

```javascript
async function uploadProductWithVideo() {
    const formData = new FormData();

    // Product info
    formData.append("name", "My Product");
    formData.append("template_id", "1");
    formData.append("price", "29.99");
    formData.append("shop_id", "1");

    // Images
    const image1 = document.getElementById("image1").files[0];
    const image2 = document.getElementById("image2").files[0];
    formData.append("images", image1);
    formData.append("images", image2);

    // Video
    const video = document.getElementById("video").files[0];
    formData.append("video", video);

    // Upload
    const response = await fetch("http://localhost/api/products/create", {
        method: "POST",
        headers: {
            "X-API-Token": "bluprinter_abc123...",
        },
        body: formData,
    });

    const result = await response.json();
    console.log(result);
}
```

---

### 5️⃣ Upload Video Qua PHP

```php
<?php

$apiToken = 'bluprinter_abc123...';
$apiUrl = 'http://localhost/api/products/create';

$postData = [
    'name' => 'Product with Video',
    'template_id' => 1,
    'price' => 29.99,
    'shop_id' => 1,
    'description' => 'Amazing product with demo video'
];

$files = [
    'images' => [
        new CURLFile('path/to/image1.jpg', 'image/jpeg', 'image1.jpg'),
        new CURLFile('path/to/image2.jpg', 'image/jpeg', 'image2.jpg')
    ],
    'video' => new CURLFile('path/to/demo.mp4', 'video/mp4', 'demo.mp4')
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Token: ' . $apiToken
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($postData, $files));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);
```

---

## 📊 Verify Results

### Check in Database:

```sql
-- Xem products có video
SELECT
    id,
    name,
    media,
    created_by
FROM products
WHERE JSON_SEARCH(media, 'one', '%.mp4%') IS NOT NULL
ORDER BY created_at DESC;
```

### Check in Frontend:

1. Visit: `http://localhost/products/{product-slug}`
2. Should see: **Video player** với controls
3. Click play ▶️
4. Click thumbnail → Switch media
5. Open gallery → Video in fullscreen

---

## 🎯 Complete Example

**Scenario:** Upload T-Shirt với demo video

```bash
# 1. Prepare files
# - front.jpg (300KB)
# - back.jpg (250KB)
# - demo.mp4 (5MB)

# 2. Upload via API
curl -X POST http://localhost/api/products/create \
  -H "X-API-Token: bluprinter_abc123..." \
  -F "name=Premium Cotton T-Shirt" \
  -F "template_id=3" \
  -F "images=@front.jpg" \
  -F "images=@back.jpg" \
  -F "video=@demo.mp4" \
  -F "price=24.99" \
  -F "description=Watch our demo to see the quality!" \
  -F "quantity=100" \
  -F "shop_id=1"

# 3. Response
{
  "success": true,
  "data": {
    "product_id": 456,
    "url": "http://localhost/products/premium-cotton-t-shirt",
    "media": [
      "https://s3.../front.jpg",
      "https://s3.../back.jpg",
      "https://s3.../demo.mp4"  // ← Video here!
    ]
  }
}

# 4. Visit product page
# → Video player visible!
# → Click play → Demo video plays!
```

---

## 🔧 Advanced: Video Thumbnail Generation

**Future Enhancement:** Auto-generate video thumbnail

```php
// Optional: Generate thumbnail from video first frame
use FFMpeg\FFMpeg;

$ffmpeg = FFMpeg::create();
$video = $ffmpeg->open('demo.mp4');
$frame = $video->frame(TimeCode::fromSeconds(1));
$frame->save('thumbnail.jpg');

// Use as poster
<video poster="thumbnail.jpg">
```

---

## 📚 Resources

**Video Optimization Tools:**

-   [HandBrake](https://handbrake.fr/) - Free video compressor
-   [FFmpeg](https://ffmpeg.org/) - Command-line tool
-   [CloudConvert](https://cloudconvert.com/) - Online converter

**Testing:**

-   [HTML5 Video Support](https://caniuse.com/video) - Browser compatibility
-   [Video Format Tester](https://www.videohelp.com/) - Test your videos

---

## ✅ Checklist

-   [x] Video player hiển thị trên product page
-   [x] Video controls đầy đủ (play, pause, volume, fullscreen)
-   [x] Video thumbnails với icon đặc biệt
-   [x] Gallery modal support video
-   [x] Switch giữa video và images
-   [x] Pause video khi close modal
-   [x] Responsive trên mobile
-   [x] API support video upload
-   [x] Admin panel support video upload

**All Done! 🎉**
