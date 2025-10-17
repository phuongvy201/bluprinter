# 🔍 Video Auto-Play Debug Guide

## ✅ Vấn Đề Hiện Tại

**Trạng thái:** Video vẫn hiển thị nút play overlay khi click, chưa tự động phát

**Cần debug:** Tại sao click vào video không tự động play

---

## 🔧 Debug Steps

### 1️⃣ **Check Console Logs**

Mở browser console (F12) và click vào video, xem có logs:

```
✅ Expected logs:
- "playVideoOnClick called"
- "Video element: [object HTMLVideoElement]"
- "Overlay element: [object HTMLDivElement]"
- "Overlay hidden immediately"
- "Video playing successfully"

❌ If missing logs:
- Check if video element exists
- Check if overlay element exists
- Check if event listeners are attached
```

### 2️⃣ **Check Video Element**

```javascript
// In console, check:
const video = document.getElementById("main-video");
console.log("Video:", video);
console.log("Video src:", video?.src);
console.log("Video paused:", video?.paused);
console.log("Video ready state:", video?.readyState);
```

### 3️⃣ **Check Overlay Element**

```javascript
// In console, check:
const overlay = document.getElementById("video-play-overlay");
console.log("Overlay:", overlay);
console.log("Overlay display:", overlay?.style.display);
console.log("Overlay visibility:", overlay?.style.visibility);
```

### 4️⃣ **Test Manual Play**

```javascript
// In console, try manual play:
const video = document.getElementById("main-video");
video
    .play()
    .then(() => {
        console.log("Manual play successful");
    })
    .catch((error) => {
        console.log("Manual play failed:", error);
    });
```

---

## 🎯 Possible Issues

### Issue 1: **Video Element Not Found**

**Symptoms:**

-   Console shows "Video element: null"
-   playVideoOnClick not working

**Solution:**

```javascript
// Check if video exists on page load
document.addEventListener("DOMContentLoaded", function () {
    const video = document.getElementById("main-video");
    if (!video) {
        console.error("Video element not found!");
    }
});
```

### Issue 2: **Overlay Not Hiding**

**Symptoms:**

-   Console shows "Overlay hidden immediately" but overlay still visible
-   CSS conflicts

**Solution:**

```javascript
// Force hide overlay
const overlay = document.getElementById("video-play-overlay");
overlay.style.display = "none !important";
overlay.style.visibility = "hidden !important";
overlay.style.opacity = "0 !important";
overlay.style.pointerEvents = "none !important";
```

### Issue 3: **Event Not Firing**

**Symptoms:**

-   No console logs when clicking
-   Event listeners not attached

**Solution:**

```javascript
// Check event listeners
const video = document.getElementById("main-video");
console.log("Video click listeners:", video.onclick);
console.log("Video event listeners:", video.addEventListener);
```

### Issue 4: **Browser Autoplay Policy**

**Symptoms:**

-   Console shows "Video play failed: NotAllowedError"
-   Browser blocking autoplay

**Solution:**

```javascript
// Handle autoplay blocking
video.play().catch((error) => {
    if (error.name === "NotAllowedError") {
        console.log("Autoplay blocked by browser");
        // Show user interaction required message
    }
});
```

---

## 🚀 Quick Fixes

### Fix 1: **Force Hide Overlay**

```javascript
// Add to playVideoOnClick function
function playVideoOnClick(event) {
    const overlay = document.getElementById("video-play-overlay");
    if (overlay) {
        overlay.style.cssText =
            "display: none !important; visibility: hidden !important; opacity: 0 !important; pointer-events: none !important;";
    }
    // ... rest of function
}
```

### Fix 2: **Multiple Click Handlers**

```javascript
// Add multiple ways to trigger play
document.addEventListener("DOMContentLoaded", function () {
    const video = document.getElementById("main-video");
    const overlay = document.getElementById("video-play-overlay");

    // Method 1: Direct video click
    video?.addEventListener("click", playVideoOnClick);

    // Method 2: Overlay click
    overlay?.addEventListener("click", playVideoOnClick);

    // Method 3: Container click
    document
        .getElementById("image-container")
        ?.addEventListener("click", function (e) {
            if (video && !video.classList.contains("hidden")) {
                playVideoOnClick(e);
            }
        });
});
```

### Fix 3: **CSS Override**

```css
/* Add to CSS to force hide overlay */
#video-play-overlay.hidden {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}
```

---

## 🔍 Debug Checklist

### ✅ **Basic Checks:**

-   [ ] Video element exists (`document.getElementById('main-video')`)
-   [ ] Overlay element exists (`document.getElementById('video-play-overlay')`)
-   [ ] Event listeners attached
-   [ ] Console shows click events
-   [ ] Video src is valid
-   [ ] Video format supported (MP4/WebM)

### ✅ **Advanced Checks:**

-   [ ] Browser autoplay policy
-   [ ] CORS issues
-   [ ] Network connectivity
-   [ ] Video file size
-   [ ] CSS conflicts
-   [ ] JavaScript errors

### ✅ **Mobile Checks:**

-   [ ] Touch events working
-   [ ] iOS Safari compatibility
-   [ ] Android Chrome compatibility
-   [ ] Mobile autoplay restrictions

---

## 📱 Browser-Specific Issues

### Chrome:

-   ✅ Usually works
-   ⚠️ May block autoplay without user interaction

### Firefox:

-   ✅ Usually works
-   ⚠️ May have different event handling

### Safari:

-   ⚠️ Strict autoplay policy
-   ⚠️ Requires user interaction

### Mobile Browsers:

-   ⚠️ Limited autoplay support
-   ⚠️ Touch event differences

---

## 🎯 Testing Commands

### Test 1: **Check Elements**

```javascript
console.log("Video:", document.getElementById("main-video"));
console.log("Overlay:", document.getElementById("video-play-overlay"));
```

### Test 2: **Check Events**

```javascript
const video = document.getElementById("main-video");
video.addEventListener("click", () => console.log("Video clicked!"));
```

### Test 3: **Manual Play**

```javascript
document.getElementById("main-video").play();
```

### Test 4: **Hide Overlay**

```javascript
document.getElementById("video-play-overlay").style.display = "none";
```

---

## 🚀 Next Steps

1. **Open browser console** (F12)
2. **Click on video** and check logs
3. **Run debug commands** above
4. **Identify the issue** from symptoms
5. **Apply appropriate fix**

---

## 📞 If Still Not Working

**Provide this info:**

-   Browser console logs
-   Browser type and version
-   Video file format and size
-   Network connectivity
-   Any JavaScript errors

**Then we can:**

-   Add more specific debugging
-   Try alternative approaches
-   Check for conflicts
-   Implement workarounds

---

**Debug first, then fix! 🔍**
