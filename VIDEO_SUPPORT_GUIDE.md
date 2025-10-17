# 🎬 Hướng Dẫn Video Support

## ✅ Tổng Quan

Hệ thống Bluprinter đã được cấu hình đầy đủ để hỗ trợ **video** cho products!

### 🎯 Các Tính Năng Video

1. ✅ **Video Player** trên trang product detail
2. ✅ **Thumbnails** với icon video đặc biệt
3. ✅ **Gallery Modal** với video player
4. ✅ **Upload video** qua admin panel và API
5. ✅ **Multiple videos** trong 1 product (mix với images)

---

## 📋 Định Dạng Video Hỗ Trợ

### Upload qua Admin Panel:

-   ✅ `.mp4` (Khuyến nghị)
-   ✅ `.mov`
-   ✅ `.avi`
-   ✅ `.webm` (HTML5)

### Upload qua API:

```json
{
    "video": "video_file.mp4",
    "images": ["image1.jpg", "image2.jpg"]
}
```

**Giới hạn:**

-   Admin Panel: 10MB
-   API: 100MB

---

## 🎥 Cách Video Hiển Thị

### 1️⃣ Product Detail Page (Frontend)

**Main Media Container:**

-   Video có **video player** với controls đầy đủ
-   Play/Pause, Volume, Fullscreen
-   Badge "VIDEO" màu tím ở góc trên
-   Responsive trên mọi thiết bị

**Thumbnails:**

-   Video thumbnail = Icon play màu tím
-   Badge "VIDEO" nhỏ ở dưới
-   Click vào = switch sang video player

**Gallery Modal:**

-   Video player fullscreen
-   Controls đầy đủ
-   Badge "VIDEO" ở trên
-   Navigate giữa videos và images

### 2️⃣ Admin Products List

**Thumbnail:**

-   Icon video gradient tím-hồng
-   Không có player (chỉ preview)
-   Click "View" để xem chi tiết

---

## 🚀 Cách Sử Dụng

### Upload Video Qua Admin Panel

1. Vào **Products > Create/Edit Product**
2. Section **Product Media**
3. Click **Choose Files**
4. Chọn file video (.mp4, .mov, .avi, .webm)
5. Upload cùng với images
6. **Save**

✅ **Kết quả:** Video sẽ xuất hiện trong product media gallery!

### Upload Video Qua API

```bash
curl -X POST http://localhost/api/products/create \
  -H "X-API-Token: YOUR_TOKEN" \
  -F "name=Product with Video" \
  -F "template_id=1" \
  -F "images=@image1.jpg" \
  -F "images=@image2.jpg" \
  -F "video=@product_demo.mp4"
```

**Response:**

```json
{
    "success": true,
    "data": {
        "product_id": 123,
        "media": [
            "https://s3.../image1.jpg",
            "https://s3.../image2.jpg",
            "https://s3.../product_demo.mp4" // ← Video URL
        ],
        "uploaded_video": "https://s3.../product_demo.mp4"
    }
}
```

---

## 🎨 UI/UX Features

### Video Player Features:

-   ✅ **Play/Pause** controls
-   ✅ **Volume** control
-   ✅ **Progress bar** với seek
-   ✅ **Fullscreen** mode
-   ✅ **Playback speed** control
-   ✅ **Picture-in-Picture** (trên browsers hỗ trợ)

### Visual Indicators:

-   🟣 **Badge "VIDEO"** màu tím
-   ▶️ **Play icon** trên thumbnails
-   🎬 **Gradient background** (tím-hồng) cho video thumbnails

### Responsive:

-   📱 Mobile: Video player adapt screen size
-   💻 Desktop: Fullscreen gallery modal
-   🖥️ Tablet: Touch-friendly controls

---

## 📊 Mix Video + Images

Product có thể có **cả video VÀ images**:

```php
$product->media = [
    'https://s3.../image1.jpg',      // Image
    'https://s3.../image2.jpg',      // Image
    'https://s3.../demo.mp4',        // Video ←
    'https://s3.../image3.jpg',      // Image
    'https://s3.../tutorial.mp4'     // Video ←
];
```

**Kết quả:**

-   Thumbnails hiển thị mix icons và images
-   Click thumbnail = switch media type tự động
-   Gallery modal support cả hai

---

## 🔧 Cấu Hình Video

### File Size Limits

**Admin Panel:** `config/app.php` or validation rules

```php
'media.*' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240'
// max: 10240 KB = 10 MB
```

**API:** `app/Http/Controllers/Api/ProductController.php`

```php
'video' => 'nullable|file|mimes:mp4,avi,mov,webm|max:102400'
// max: 102400 KB = 100 MB
```

### S3 Storage

Video được upload vào AWS S3 giống như images:

```
Bucket: image.bluprinter
Path: products/
URL: https://s3.us-east-1.amazonaws.com/image.bluprinter/products/video_file.mp4
```

**Config:** `config/filesystems.php`

---

## 🎯 Best Practices

### ✅ Nên:

-   Dùng định dạng **MP4** (tương thích tốt nhất)
-   Video length: **30 giây - 2 phút** (optimal)
-   Resolution: **1080p** hoặc **720p**
-   Compress video trước khi upload
-   Đặt video **đầu tiên** trong media array (hiển thị đầu tiên)
-   Thêm ít nhất **1 image** kèm theo video

### ❌ Không Nên:

-   Upload video > 100MB (API) hoặc > 10MB (Admin)
-   Dùng video quality quá cao (4K không cần thiết)
-   Video quá dài (> 5 phút) - tốn bandwidth
-   Chỉ có video không có images

---

## 📱 Tương Thích

### Browsers:

-   ✅ Chrome/Edge (Chromium)
-   ✅ Firefox
-   ✅ Safari (iOS & macOS)
-   ✅ Opera
-   ⚠️ Internet Explorer (limited)

### Mobile:

-   ✅ iOS Safari
-   ✅ Android Chrome
-   ✅ Mobile browsers (webkit-based)

### Video Formats:

| Format      | Desktop | Mobile | Recommended |
| ----------- | ------- | ------ | ----------- |
| MP4 (H.264) | ✅      | ✅     | ⭐⭐⭐⭐⭐  |
| WebM        | ✅      | ✅     | ⭐⭐⭐⭐    |
| MOV         | ✅      | ⚠️     | ⭐⭐⭐      |
| AVI         | ⚠️      | ❌     | ⭐⭐        |

---

## 🧪 Testing

### Test Video Upload:

1. **Create Product with Video:**

```bash
curl -X POST http://localhost/api/products/create \
  -H "X-API-Token: YOUR_TOKEN" \
  -F "name=Test Video Product" \
  -F "template_id=1" \
  -F "images=@test.jpg" \
  -F "video=@demo.mp4"
```

2. **Verify in Database:**

```sql
SELECT id, name, media FROM products WHERE id = 123;
-- media should contain .mp4 URL
```

3. **Check Frontend:**

-   Visit product page
-   Video player should be visible
-   Click play ▶️
-   Check fullscreen works

---

## 🎬 Example Product Media

```json
{
    "product_id": 123,
    "name": "Cool T-Shirt",
    "media": [
        "https://s3.../demo_video.mp4", // Video ← First
        "https://s3.../front_view.jpg", // Image
        "https://s3.../back_view.jpg", // Image
        "https://s3.../wearing_tutorial.mp4", // Video ←
        "https://s3.../close_up.jpg" // Image
    ]
}
```

**Frontend Display:**

-   Thumbnail 1: ▶️ Video player
-   Thumbnail 2: 🖼️ Image
-   Thumbnail 3: 🖼️ Image
-   Thumbnail 4: ▶️ Video player
-   Thumbnail 5: 🖼️ Image

---

## 💡 Use Cases

### Case 1: Product Demo

```
Videos: Product demonstration, how to use
Images: Product angles, details
```

### Case 2: Before/After

```
Video: Transformation process
Images: Before/after shots
```

### Case 3: Tutorial

```
Video: Step-by-step tutorial
Images: Reference images
```

---

## 🔍 Troubleshooting

### ❓ Video không play?

**Kiểm tra:**

1. Video URL có đúng không?
2. File có tồn tại trên S3 không?
3. Browser console có lỗi không?
4. Video format có được hỗ trợ không?

```javascript
// Check in browser console
console.log("Video URL:", document.getElementById("main-video").src);
console.log(
    "Video can play:",
    document.getElementById("main-video").canPlayType("video/mp4")
);
```

### ❓ Video hiển thị icon thay vì player?

**Nguyên nhân:** Code cũ chỉ show icon

**Giải pháp:** ✅ Đã fix! Video player được hiển thị đầy đủ

### ❓ Video bị lag/chậm?

**Giải pháp:**

1. Compress video trước khi upload
2. Sử dụng resolution thấp hơn (720p thay vì 1080p)
3. Reduce bitrate
4. Dùng tools: HandBrake, FFmpeg

```bash
# Compress video với FFmpeg
ffmpeg -i input.mp4 -vcodec h264 -acodec aac -b:v 1M -b:a 128k output.mp4
```

### ❓ Video không load trên mobile?

**Kiểm tra:**

1. Format có phải MP4 không? (Tốt nhất cho mobile)
2. File size có quá lớn không? (< 50MB khuyến nghị cho mobile)
3. Internet speed của user

---

## 📝 Video Attributes

### HTML5 Video Attributes Used:

```html
<video controls ← Show play/pause, volume, etc. playsinline ← Play inline trên
iOS (không fullscreen tự động) controlsList="nodownload" ← Ẩn nút download
(modal only) >
```

### Optional Attributes (có thể thêm):

```html
autoplay ← Tự động play (⚠️ User experience: not recommended) muted ← Mute mặc
định loop ← Loop video preload="metadata" ← Load metadata trước (faster)
poster="image.jpg" ← Thumbnail trước khi play
```

---

## 🎨 Customization

### Thay đổi Video Player Style:

Edit CSS trong `resources/views/products/show.blade.php`:

```css
/* Video Player Styles */
video#main-video {
    background-color: #000; /* Background màu đen */
    border-radius: 12px; /* Bo góc */
}

/* Controls panel */
video::-webkit-media-controls-panel {
    background-color: rgba(0, 0, 0, 0.8); /* Controls tối màu */
}
```

### Thay đổi Video Badge:

```php
<!-- Video Badge -->
<div class="... bg-purple-600 ...">  <!-- Đổi màu ở đây -->
    <svg>...</svg>
    <span>VIDEO</span>
</div>
```

---

## 📊 Storage & Performance

### Video File Sizes:

| Quality | Resolution | Bitrate  | Size (1 min) |
| ------- | ---------- | -------- | ------------ |
| Low     | 480p       | 500 kbps | ~4 MB        |
| Medium  | 720p       | 1 Mbps   | ~7.5 MB      |
| High    | 1080p      | 2 Mbps   | ~15 MB       |
| Ultra   | 4K         | 8 Mbps   | ~60 MB       |

**Khuyến nghị:** 720p, 1 Mbps cho balance quality/size tốt nhất

### S3 Costs:

-   Storage: ~$0.023/GB/month
-   Transfer: ~$0.09/GB
-   Video 10MB = $0.00023/month storage
-   1000 views = ~$0.90 transfer

---

## 🔗 Related Docs

-   [AWS S3 Setup](AWS_S3_SETUP.md)
-   [API Product Documentation](API_PRODUCT_DOCUMENTATION.md)
-   [Admin User Guide](ADMIN_USER_GUIDE.md)

---

## ✨ What's New

### Changes Made:

1. ✅ **Frontend Video Player**

    - Full HTML5 video player
    - Controls: play, pause, volume, fullscreen
    - Responsive design

2. ✅ **Gallery Modal Video Support**

    - Video player in fullscreen modal
    - Navigate between videos and images
    - Video badge indicator

3. ✅ **Thumbnail Improvements**

    - Video thumbnails với play icon
    - Visual distinction từ images
    - "VIDEO" badge

4. ✅ **JavaScript Enhancements**
    - Auto-detect video vs image
    - Switch between media types
    - Pause video when switching

---

## 🎯 Example: Complete Product with Video

```json
POST /api/products/create
{
  "name": "Premium T-Shirt with Demo",
  "template_id": 5,
  "images": [
    "front_view.jpg",
    "back_view.jpg",
    "detail_view.jpg"
  ],
  "video": "wearing_demo.mp4",
  "description": "Watch our video to see the quality!",
  "price": 29.99
}
```

**Frontend Result:**

```
[▶️ Video] [Image 1] [Image 2] [Image 3]
   ↑
Click to play demo video!
```

---

## 🎊 Enjoy!

Video support giờ hoạt động hoàn hảo! Customers có thể:

-   ✅ Xem product demo videos
-   ✅ Navigate giữa videos và images
-   ✅ Fullscreen gallery experience
-   ✅ Mobile-friendly playback

**Happy Selling! 🚀**
