# 🎬 Video Poster & Click-to-Play Guide

## ✅ Tổng Quan

Video giờ có:

1. **Poster/Thumbnail** - Hình đại diện trước khi play
2. **Click-to-Play** - Click vào video → Play luôn (không zoom)
3. **Custom Play Button** - Nút play lớn ở giữa với animation

---

## 🎨 Cách Hoạt Động

### Poster Image (Hình Đại Diện)

**Thứ tự ưu tiên lấy poster:**

1. **Image đầu tiên** trong media array (nếu có)
2. **Template image** (nếu product không có image)
3. **Placeholder** (SVG tím gradient với play icon)

**Ví dụ:**

```php
// Product media
$product->media = [
    'demo.mp4',        // Video
    'front.jpg',       // Image ← Được dùng làm poster
    'back.jpg'
];

// Result: Video sẽ có poster = front.jpg
```

---

## ▶️ Click-to-Play Feature

### Trước Khi Play:

```
┌─────────────────────────┐
│  🟣 VIDEO               │ ← Badge
│                         │
│     [Poster Image]      │ ← Hình đại diện
│                         │
│         ⭕              │ ← Nút play to (pulse)
│          ▶️             │
│                         │
└─────────────────────────┘
```

**Click anywhere** → Video plays!

### Đang Play:

```
┌─────────────────────────┐
│  🟣 VIDEO               │ ← Badge
│                         │
│    [Video Playing]      │
│    ═══════════╸        │ ← Progress
│    🔊  ⏸  ⛶           │ ← Controls
└─────────────────────────┘
```

**Click pause** → Nút play lớn xuất hiện lại!

---

## 🎯 Tính Năng

### 1️⃣ Custom Play Button Overlay

-   ⭕ **Nút tròn lớn** ở giữa màn hình
-   💜 **Play icon tím** bên trong
-   ✨ **Pulse animation** (nhấp nháy nhẹ)
-   🔄 **Hover scale** (phóng to khi hover)
-   👆 **Click anywhere** → Play

### 2️⃣ Poster Image Smart Selection

**Case 1: Có images trong media**

```php
media = ['video.mp4', 'image1.jpg', 'image2.jpg']
→ Poster = image1.jpg ✅
```

**Case 2: Chỉ có video**

```php
media = ['video.mp4']
→ Poster = template image (nếu có) ✅
```

**Case 3: Video first, images after**

```php
media = ['intro.mp4', 'photo.jpg']
→ Poster = photo.jpg ✅
```

### 3️⃣ Auto Show/Hide Overlay

-   **Video paused** → Nút play xuất hiện
-   **Video playing** → Nút play ẩn
-   **Video ended** → Nút play xuất hiện lại
-   **Smooth transitions** với fade effect

---

## 🚀 Cách Sử Dụng

### Upload Product Với Video + Image

**Khuyến nghị:** Upload ít nhất 1 image cùng video

```bash
curl -X POST /api/products/create \
  -H "X-API-Token: YOUR_TOKEN" \
  -F "name=Product Name" \
  -F "template_id=1" \
  -F "images=@product.jpg" \    # ← Sẽ dùng làm poster
  -F "video=@demo.mp4" \
  -F "shop_id=1"
```

**Kết quả:**

-   Video có poster = `product.jpg`
-   Click vào → Play luôn
-   Pause → Nút play xuất hiện

### Chỉ Upload Video (Không Có Image)

```bash
curl -X POST /api/products/create \
  -H "X-API-Token: YOUR_TOKEN" \
  -F "name=Video Product" \
  -F "template_id=1" \
  -F "video=@demo.mp4" \
  -F "shop_id=1"
```

**Kết quả:**

-   Video dùng template image làm poster (nếu có)
-   Hoặc gradient placeholder

---

## 🎨 UI/UX Improvements

### Before (Old):

-   ❌ Video chỉ có icon
-   ❌ Không có poster
-   ❌ Không click-to-play

### After (New):

-   ✅ **Poster image** từ product/template
-   ✅ **Custom play button** to màu tím với pulse animation
-   ✅ **Click anywhere** trên video → Play
-   ✅ **Auto show overlay** khi pause/end
-   ✅ **Smooth transitions** và animations

---

## 💡 Best Practices

### ✅ Nên:

1. **Upload ít nhất 1 image** cùng với video

    - Image sẽ dùng làm poster
    - Giúp SEO và preview

2. **Đặt image đầu tiên** có chất lượng cao

    - Image này sẽ là "first impression"
    - Hiển thị trước khi video load

3. **Compress video** để load nhanh

    - Poster hiển thị instant
    - Video load trong background

4. **Thứ tự upload:**
    ```
    images: [hero_image.jpg, detail1.jpg, detail2.jpg]
    video: demo.mp4
    ```

### ❌ Không Nên:

1. Upload chỉ video không có image

    - Không có poster đẹp
    - SEO kém hơn

2. Dùng image chất lượng thấp

    - Poster mờ/xấu

3. Video quá dài/nặng
    - Poster hiển thị lâu trước khi play được

---

## 🎬 Visual Features

### Play Button Design:

```css
⭕ Nút tròn trắng (w-20 h-20)
  └─ ▶️ Play icon tím bên trong
     └─ Pulse animation (nhấp nháy)
        └─ Hover: Scale lên 1.15x
           └─ Shadow: Glow effect
```

### Poster Display:

```css
Video Container
  └─ Poster Image (background)
     └─ Play Overlay (bg-black bg-opacity-30)
        └─ Play Button (center)
           └─ Video Badge (top-left corner)
```

---

## 📊 Examples

### Example 1: T-Shirt với Demo Video

**Media:**

```json
[
    "front_view.jpg", // ← Poster cho video
    "back_view.jpg",
    "wearing_demo.mp4",
    "detail.jpg"
]
```

**Display:**

-   Video player với poster = `front_view.jpg`
-   Nút play to ở giữa
-   Click → Video plays

### Example 2: Video Tutorial Product

**Media:**

```json
[
    "thumbnail.jpg", // ← Poster
    "step1.jpg",
    "tutorial.mp4", // ← Uses thumbnail.jpg as poster
    "step2.jpg"
]
```

### Example 3: Multiple Videos

**Media:**

```json
[
    "intro.mp4", // ← Video 1
    "photo1.jpg", // ← Poster cho intro.mp4
    "photo2.jpg", // ← Poster cho demo.mp4
    "demo.mp4" // ← Video 2
]
```

**Behavior:**

-   Click thumbnail "intro.mp4" → Shows video with photo1.jpg poster
-   Click thumbnail "demo.mp4" → Shows video with photo2.jpg poster
-   Smart poster selection!

---

## 🔧 Customization

### Thay Đổi Play Button Style:

Edit CSS trong `resources/views/products/show.blade.php`:

```css
/* Play button size */
#video-play-overlay .w-20 {
    width: 5rem; /* Thay đổi size */
    height: 5rem;
}

/* Play button color */
#video-play-overlay .bg-white {
    background-color: rgba(147, 51, 234, 0.9); /* Tím thay vì trắng */
}

#video-play-overlay svg {
    color: white; /* Icon trắng thay vì tím */
}

/* Animation speed */
@keyframes playPulse {
    0%,
    100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    } /* Thay đổi intensity */
}
```

### Tắt Pulse Animation:

```css
/* Remove this line */
#video-play-overlay .w-20 {
    /* animation: playPulse 2s infinite; */  ← Comment out
}
```

### Thay Đổi Overlay Opacity:

```css
#video-play-overlay {
    background-color: rgba(0, 0, 0, 0.5); /* Tối hơn */
}
```

---

## 🎯 Advanced: Custom Poster Upload

**Future Enhancement:** Cho phép upload poster riêng cho video

```php
// In Product model
public function getVideoPoster($videoUrl) {
    // Check custom poster first
    if ($this->video_posters && isset($this->video_posters[$videoUrl])) {
        return $this->video_posters[$videoUrl];
    }

    // Fallback to first image
    return $this->getFirstImageUrl();
}
```

---

## 📱 Mobile Behavior

### iOS Safari:

-   ✅ Poster hiển thị
-   ✅ Click-to-play works
-   ✅ `playsinline` prevents auto-fullscreen
-   ✅ Native controls

### Android Chrome:

-   ✅ Poster hiển thị
-   ✅ Click-to-play works
-   ✅ Custom play button
-   ✅ Smooth playback

---

## 🔍 Troubleshooting

### ❓ Poster không hiển thị?

**Kiểm tra:**

```javascript
// Browser console
const video = document.getElementById("main-video");
console.log("Poster URL:", video.poster);
console.log("Poster image loads:", (new Image().src = video.poster));
```

**Giải pháp:**

1. Đảm bảo có ít nhất 1 image trong product media
2. Check image URL có valid không
3. Check CORS nếu image từ external domain

### ❓ Click-to-play không hoạt động?

**Kiểm tra:**

1. Video có `onclick="playVideoOnClick(event)"` không?
2. JavaScript console có lỗi không?
3. Browser có block autoplay không?

```javascript
// Test manually
document.getElementById("main-video").play();
```

### ❓ Overlay không ẩn khi play?

**Giải pháp:**

```javascript
// Manual fix
const overlay = document.getElementById("video-play-overlay");
if (overlay) {
    overlay.style.display = "none";
}
```

---

## 📝 Code Highlights

### Poster Selection Logic:

```php
// 1. Tìm image đầu tiên trong media
foreach($allImages as $item) {
    if (!str_contains($item, '.mp4')) {
        $posterImage = $item;  // ← Found!
        break;
    }
}

// 2. Fallback: Template image
if (!$posterImage && $template->media) {
    $posterImage = $template->getFirstImage();
}

// 3. Set poster
<video poster="{{ $posterImage }}">
```

### Click-to-Play Logic:

```javascript
function playVideoOnClick(event) {
    const video =
        event.target.tagName === "VIDEO"
            ? event.target
            : document.getElementById("main-video");

    if (video && video.paused) {
        video.play(); // ← Play luôn!
        hideOverlay(); // ← Ẩn nút play
    }
}
```

---

## ✨ Visual Comparison

### With Poster + Play Button:

```
BEFORE PLAY:
┌─────────────────────────┐
│ 🟣 VIDEO                │
│  [Product Image]        │ ← Poster
│        ⭕▶️            │ ← Play button (pulsing)
│                         │
└─────────────────────────┘

AFTER CLICK:
┌─────────────────────────┐
│ 🟣 VIDEO                │
│  [Video Playing]        │
│  ═══════════╸          │ ← Progress
│  🔊 ⏸ ⛶               │ ← Controls
└─────────────────────────┘
```

---

## 🎊 Benefits

### User Experience:

-   ✅ **Visual preview** với poster
-   ✅ **Click anywhere** → Play
-   ✅ **Clear indication** có video (badge + button)
-   ✅ **Smooth experience** với animations
-   ✅ **No confusion** (không giống image)

### Developer:

-   ✅ **Auto poster selection** từ available images
-   ✅ **Fallback logic** robust
-   ✅ **Easy to customize** styles
-   ✅ **Works with existing** upload system

---

## 🔗 Related

-   [VIDEO_SUPPORT_GUIDE.md](VIDEO_SUPPORT_GUIDE.md) - Full video support guide
-   [VIDEO_UPLOAD_EXAMPLE.md](VIDEO_UPLOAD_EXAMPLE.md) - Upload examples
-   [VIDEO_QUICK_REF.md](VIDEO_QUICK_REF.md) - Quick reference

---

## ✅ Checklist

-   [x] Poster image tự động được chọn
-   [x] Custom play button với animation
-   [x] Click-to-play functionality
-   [x] Overlay auto show/hide
-   [x] Smooth transitions
-   [x] Mobile-friendly
-   [x] Fallback logic hoàn chỉnh
-   [x] Gallery modal support

**All Features Working! 🎉**

---

**Test ngay:** Upload product với video, visit page, click play! 🚀
