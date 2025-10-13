# Hướng dẫn quản lý Users với Spatie Permission

## 🎉 Đã tạo thành công 10 users với các roles khác nhau!

### 📋 Danh sách Users đã tạo:

#### 👑 Super Admin
- **Email:** superadmin@bluprinter.com
- **Password:** password
- **Role:** super-admin (toàn quyền)

#### 👨‍💼 Admin
- **Email:** admin@bluprinter.com  
- **Password:** password
- **Role:** admin (quản lý users, products, orders)

#### 👨‍💻 Moderator
- **Email:** moderator@bluprinter.com
- **Password:** password  
- **Role:** moderator (xem và chỉnh sửa products, orders)

#### 👤 Users thường
- **Email:** user@bluprinter.com
- **Password:** password
- **Role:** user (chỉ xem dashboard)

#### 🧪 Test Users
- **Emails:** test1@bluprinter.com đến test5@bluprinter.com
- **Password:** password
- **Role:** user

#### 👨‍💼 Admin mới tạo
- **Email:** nguyenvana@example.com
- **Password:** 123456
- **Role:** admin

---

## 🛠️ Commands để quản lý Users

### 1. Xem danh sách tất cả users
```bash
php artisan users:list
```

### 2. Tạo user mới
```bash
# Cú pháp
php artisan user:create {name} {email} {password} {role}

# Ví dụ
php artisan user:create "Tên User" "email@example.com" "password123" "admin"
```

**Available roles:**
- `super-admin` - Toàn quyền
- `admin` - Quản lý users, products, orders  
- `moderator` - Xem và chỉnh sửa products, orders
- `user` - Chỉ xem dashboard

### 3. Các commands khác hữu ích

```bash
# Chạy lại seeder để tạo thêm users mẫu
php artisan db:seed --class=UserSeeder

# Clear cache permissions (khi thay đổi roles/permissions)
php artisan permission:cache-reset

# Xem danh sách routes
php artisan route:list --name=admin
```

---

## 🔐 Cách đăng nhập và test

### 1. Đăng nhập vào hệ thống
Sử dụng bất kỳ email/password nào ở trên để đăng nhập.

### 2. Test các routes với permissions
Sau khi đăng nhập, truy cập các URL sau để test:

#### ✅ Super Admin có thể truy cập tất cả:
- `/admin/dashboard` - Dashboard
- `/admin/analytics` - Analytics  
- `/admin-only` - Admin only
- `/super-admin-only` - Super admin only
- `/user-management` - User management

#### ✅ Admin có thể truy cập:
- `/admin/dashboard` - Dashboard
- `/admin/analytics` - Analytics
- `/admin-only` - Admin only
- `/user-management` - User management

#### ✅ Moderator có thể truy cập:
- `/admin/dashboard` - Dashboard
- `/user-management` - User management

#### ✅ User chỉ có thể truy cập:
- `/admin/dashboard` - Dashboard

---

## 💡 Tips sử dụng

### 1. Kiểm tra quyền trong code
```php
// Kiểm tra role
if (auth()->user()->hasRole('admin')) {
    // Logic cho admin
}

// Kiểm tra permission  
if (auth()->user()->can('view-dashboard')) {
    // Logic cho user có quyền xem dashboard
}
```

### 2. Sử dụng trong Blade templates
```blade
@role('admin')
    <p>Chỉ admin mới thấy được</p>
@endrole

@permission('view-dashboard')
    <a href="/dashboard">Dashboard</a>
@endpermission
```

### 3. Bảo vệ routes
```php
// Kiểm tra role
Route::get('/admin', function () {
    return 'Admin only';
})->middleware('role:admin');

// Kiểm tra permission
Route::get('/dashboard', function () {
    return 'Dashboard';
})->middleware('permission:view-dashboard');
```

---

## 🚨 Lưu ý quan trọng

1. **Bảo mật:** Đổi password mặc định trong production
2. **Cache:** Chạy `php artisan permission:cache-reset` sau khi thay đổi roles/permissions
3. **Backup:** Backup database trước khi chạy seeders
4. **Testing:** Test kỹ các permissions trước khi deploy

---

## 📞 Hỗ trợ

Nếu cần thêm roles/permissions mới, hãy:
1. Sửa file `RolePermissionSeeder.php`
2. Chạy lại seeder
3. Clear cache permissions
4. Test lại
