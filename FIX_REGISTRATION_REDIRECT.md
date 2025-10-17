# Fix Login & Registration Redirect Issue

## ❌ Vấn Đề

-   Sau khi **đăng ký**, khách hàng bị redirect đến `/dashboard` thay vì trang home
-   Sau khi **đăng nhập**, khách hàng thường cũng bị redirect đến `/dashboard`

## ✅ Đã Sửa

### 1. RegisteredUserController (Đăng Ký)

File: `app/Http/Controllers/Auth/RegisteredUserController.php`

```php
// ✅ Đã sửa redirect về home cho tất cả user
return redirect()->intended(route('home', absolute: false))
    ->with('success', 'Registration successful! Please check your email to verify your account.');
```

### 2. AuthenticatedSessionController (Đăng Nhập)

File: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

```php
// ✅ Redirect dựa trên role
$user = Auth::user();

// Admin and Seller go to dashboard
if ($user->hasRole('admin') || $user->hasRole('seller')) {
    return redirect()->intended(route('dashboard', absolute: false));
}

// Regular customers go to home
return redirect()->intended(route('home', absolute: false));
```

### 2. Dashboard Route Protection

File: `routes/web.php` (line 404-416)

```php
Route::get('/dashboard', function () {
    $user = auth()->user();

    // Redirect based on role
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->hasRole('seller')) {
        return redirect()->route('admin.seller.dashboard');
    }

    // Default dashboard for customers
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
```

**Chú ý**: Route dashboard yêu cầu `verified` middleware, nghĩa là user phải verify email mới vào được.

## 🔧 Cách Fix

### Bước 1: Clear All Cache

```bash
# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear application cache
php artisan cache:clear

# Clear compiled classes
php artisan clear-compiled

# Tất cả trong 1 lệnh
php artisan optimize:clear
```

### Bước 2: Restart Server

```bash
# Nếu dùng Laragon
# Stop và Start lại Apache/Nginx

# Nếu dùng php artisan serve
# Ctrl+C để stop
php artisan serve
```

### Bước 3: Clear Browser Cache

1. Mở DevTools (F12)
2. Right-click vào nút Refresh
3. Chọn "Empty Cache and Hard Reload"

**Hoặc:**

-   Chrome/Edge: `Ctrl + Shift + Delete`
-   Firefox: `Ctrl + Shift + Delete`
-   Safari: `Cmd + Option + E`

### Bước 4: Test Registration Flow

1. Logout nếu đang login
2. Clear browser cache
3. Register user mới
4. Check xem redirect về home hay dashboard

## 📝 Flow Đúng

```
1. User điền form register
   ↓
2. Submit form → RegisteredUserController@store
   ↓
3. Tạo user mới
   ↓
4. Fire Registered event (gửi email verification)
   ↓
5. Login user tự động
   ↓
6. Redirect về HOME (NOT dashboard)
   ↓
7. Hiển thị:
   - Success message
   - Orange banner yêu cầu verify email
   - Link "Verify Email" trong header
```

## 🐛 Debug

Nếu vẫn redirect đến dashboard, check:

### 1. Check Route Cache

```bash
php artisan route:list | grep register
```

Kết quả phải là:

```
POST   register ........ register › Auth\RegisteredUserController@store
```

### 2. Check Middleware

```bash
php artisan route:list | grep dashboard
```

Phải có middleware `verified`:

```
GET    dashboard ........ dashboard › Closure
                         middleware: auth, verified
```

### 3. Add Debug Log

Thêm vào `RegisteredUserController@store`:

```php
use Illuminate\Support\Facades\Log;

// Sau Auth::login($user);
Log::info('User registered', [
    'user_id' => $user->id,
    'redirect_to' => route('home')
]);

return redirect()->intended(route('home', absolute: false))
    ->with('success', 'Registration successful!');
```

Check log:

```bash
tail -f storage/logs/laravel.log
```

### 4. Check Event Listeners

```bash
php artisan event:list
```

Tìm `Illuminate\Auth\Events\Registered` và check xem có listener nào redirect không.

### 5. Check Session

Thêm vào RegisteredUserController:

```php
// Trước return
session()->forget('url.intended');

return redirect()->intended(route('home', absolute: false))
    ->with('success', 'Registration successful!');
```

## 🔍 Các Nguyên Nhân Có Thể

### 1. Cache chưa clear

**Fix**: `php artisan optimize:clear`

### 2. Browser cache

**Fix**: Hard reload (Ctrl + Shift + R)

### 3. Event listener đang override

**Fix**: Check `app/Providers/EventServiceProvider.php`

### 4. Middleware redirect

**Fix**: Check `bootstrap/app.php` và các middleware

### 5. Session intended URL

**Fix**: Forget session trước khi redirect

## ✅ Checklist

-   [ ] Clear tất cả cache (optimize:clear)
-   [ ] Restart web server
-   [ ] Clear browser cache
-   [ ] Test register với user mới
-   [ ] Check redirect đến home
-   [ ] Check banner verification hiển thị
-   [ ] Check email verification gửi đi
-   [ ] Test flow hoàn chỉnh

## 📧 Email Verification Flow

Sau khi register:

1. ✅ User được login tự động
2. ✅ Redirect về home
3. ✅ Email verification được gửi
4. ✅ Banner orange hiển thị
5. ✅ Link "Verify Email" trong header
6. ⚠️ KHÔNG thể vào dashboard (do middleware verified)

## 🎯 Expected Behavior

### User Thường (Chưa Verify Email)

```
Register → Home (với banner warning)
         ↓
    Click "Verify Email"
         ↓
    Verify Email Page
         ↓
    Click link trong email
         ↓
    Email verified!
         ↓
    Home (banner biến mất)
```

### User Thường (Đã Verify Email)

```
Login → Home
      ↓
  Browse products
  Add to cart
  Checkout
  View orders (coming soon)
```

### Admin/Seller (Có Role)

```
Login → Dashboard (tự động redirect)
      ↓
  Admin Panel / Seller Panel
```

## 💡 Tips

1. **Always clear cache** sau khi sửa routes hoặc config
2. **Test với incognito mode** để tránh browser cache
3. **Check Laravel log** khi có vấn đề redirect
4. **Dùng `route('home')` thay vì `/`** để đảm bảo đúng route
5. **Test với user mới** chứ không phải user cũ

## 🚨 Common Mistakes

❌ Dùng `redirect('/')` thay vì `redirect()->route('home')`  
❌ Quên clear cache sau khi sửa code  
❌ Test với user đã login sẵn  
❌ Không restart server sau khi sửa  
❌ Browser cache chưa clear

## 📞 Support

Nếu vẫn gặp vấn đề:

1. Check Laravel log: `storage/logs/laravel.log`
2. Check web server log
3. Enable debug mode trong `.env`:
    ```env
    APP_DEBUG=true
    ```
4. Recreate database và test lại
