# ⚡ Video Support - Quick Reference

## 🎯 TL;DR

Video đã hoạt động! Chỉ cần upload file video (.mp4, .mov, .avi, .webm) cùng với images.

---

## ✅ What Works Now

| Feature            | Status | Notes                          |
| ------------------ | ------ | ------------------------------ |
| Video Player       | ✅     | Full HTML5 player với controls |
| Thumbnails         | ✅     | Icon play màu tím              |
| Gallery Modal      | ✅     | Fullscreen video player        |
| Mix Video + Images | ✅     | Navigate giữa cả hai           |
| Upload via Admin   | ✅     | Max 10MB                       |
| Upload via API     | ✅     | Max 100MB                      |
| Auto-detect        | ✅     | Tự động phân biệt video/image  |
| Mobile Support     | ✅     | Responsive, touch-friendly     |

---

## 🚀 Upload Video

### Admin Panel:

1. Products > Create Product
2. Upload media → Choose video file
3. Save ✅

### API:

```bash
curl -X POST http://localhost/api/products/create \
  -H "X-API-Token: YOUR_TOKEN" \
  -F "template_id=1" \
  -F "name=Product Name" \
  -F "images=@photo.jpg" \
  -F "video=@demo.mp4"
```

---

## 🎬 Supported Formats

✅ **MP4** (Best - khuyến nghị)  
✅ **WebM** (Good)  
✅ **MOV** (OK)  
⚠️ **AVI** (Limited browser support)

---

## 🎨 How It Looks

**Product Page:**

```
┌─────────────────────────┐
│  ▶️ VIDEO PLAYER       │ ← Badge "VIDEO"
│  [Play] [Volume] [⛶]   │ ← Full controls
└─────────────────────────┘

[▶️ Video] [📷 Img1] [📷 Img2]  ← Thumbnails
   Active
```

**Gallery Modal:**

```
┌────────────────────────────────┐
│     🟣 VIDEO                   │ ← Badge
│                                │
│   ▶️ FULLSCREEN VIDEO PLAYER  │
│   [Play] [Volume] [Fullscreen] │
│                                │
└────────────────────────────────┘
  [▶️] [📷] [📷] [▶️]  ← Thumb strip
```

---

## 💡 Pro Tips

1. **Video đầu tiên** → Hiển thị first trong gallery
2. **MP4 format** → Tương thích tốt nhất
3. **< 50MB** → Optimal cho mobile
4. **30-60 giây** → Perfect duration
5. **720p** → Balance quality/size

---

## 🔍 Test Ngay

1. Upload product với video qua admin hoặc API
2. Visit product page
3. Click ▶️ Play
4. Click gallery modal → Fullscreen video!

---

## 📚 Full Docs

-   📖 [VIDEO_SUPPORT_GUIDE.md](VIDEO_SUPPORT_GUIDE.md) - Chi tiết đầy đủ
-   🎬 [VIDEO_UPLOAD_EXAMPLE.md](VIDEO_UPLOAD_EXAMPLE.md) - Examples

---

**That's it! Video support is READY! 🎉**
