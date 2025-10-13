# 🔄 Cập nhật hệ thống Roles - Bluprinter

## ✅ Đã cập nhật thành công

### 🔐 Hệ thống Roles mới (3 roles)

#### 👑 **Admin**

-   **Quyền hạn:** Toàn quyền (tất cả permissions)
-   **Permissions:** Tất cả 20+ permissions
-   **Chức năng:** Quản lý toàn bộ hệ thống

#### 🛒 **Seller**

-   **Quyền hạn:** Quản lý sản phẩm và đơn hàng
-   **Permissions:**
    -   `view-products`, `create-products`, `edit-products`
    -   `view-orders`, `edit-orders`
    -   `view-dashboard`
-   **Chức năng:** Quản lý sản phẩm, xử lý đơn hàng

#### 👤 **Customer**

-   **Quyền hạn:** Xem dashboard cơ bản
-   **Permissions:**
    -   `view-dashboard`
-   **Chức năng:** Xem thông tin cá nhân, theo dõi đơn hàng

---

## 👥 Danh sách Users hiện tại

| Email                    | Password | Role     | Mô tả               |
| ------------------------ | -------- | -------- | ------------------- |
| admin@bluprinter.com     | password | admin    | Quản trị viên chính |
| seller@bluprinter.com    | password | seller   | Nhân viên bán hàng  |
| seller2@bluprinter.com   | 123456   | seller   | Seller mới tạo      |
| customer@bluprinter.com  | password | customer | Khách hàng mẫu      |
| customer1@bluprinter.com | password | customer | Khách hàng 1        |
| customer2@bluprinter.com | password | customer | Khách hàng 2        |
| customer3@bluprinter.com | password | customer | Khách hàng 3        |

---

## 🛠️ Commands quản lý

### Tạo Seller mới

```bash
php artisan seller:create "Tên Seller" "email@example.com" "password123"
```

### Tạo Customer mới

```bash
php artisan user:create "Tên Customer" "email@example.com" "password123" "customer"
```

### Xem danh sách users

```bash
php artisan users:list
```

---

## 🧪 Test Permissions

### Routes test theo role:

-   `/admin-only` - Chỉ admin truy cập được
-   `/seller-only` - Chỉ seller truy cập được
-   `/customer-only` - Chỉ customer truy cập được
-   `/user-management` - Cần permission `view-users` (chỉ admin)

### Dashboard test:

-   `/admin/dashboard` - Cần permission `view-dashboard` (tất cả roles)
-   `/admin/analytics` - Cần permission `view-analytics` (chỉ admin)

---

## 🎨 Giao diện cập nhật

### Navigation Bar

-   Hiển thị role badge với màu sắc khác nhau:
    -   🔴 **Admin:** Badge đỏ
    -   🔵 **Seller:** Badge xanh dương
    -   🟢 **Customer:** Badge xanh lá

### Dashboard API

-   Trả về thông tin chi tiết về roles và permissions
-   Kiểm tra quyền truy cập các chức năng

---

## 📋 Permissions chi tiết

### User Management (chỉ Admin)

-   `view-users` - Xem danh sách users
-   `create-users` - Tạo user mới
-   `edit-users` - Chỉnh sửa user
-   `delete-users` - Xóa user

### Role Management (chỉ Admin)

-   `view-roles` - Xem danh sách roles
-   `create-roles` - Tạo role mới
-   `edit-roles` - Chỉnh sửa role
-   `delete-roles` - Xóa role

### Permission Management (chỉ Admin)

-   `view-permissions` - Xem danh sách permissions
-   `create-permissions` - Tạo permission mới
-   `edit-permissions` - Chỉnh sửa permission
-   `delete-permissions` - Xóa permission

### Product Management (Admin + Seller)

-   `view-products` - Xem sản phẩm
-   `create-products` - Tạo sản phẩm (Seller)
-   `edit-products` - Chỉnh sửa sản phẩm (Seller)
-   `delete-products` - Xóa sản phẩm (chỉ Admin)

### Order Management (Admin + Seller)

-   `view-orders` - Xem đơn hàng
-   `create-orders` - Tạo đơn hàng (chỉ Admin)
-   `edit-orders` - Chỉnh sửa đơn hàng (Seller)
-   `delete-orders` - Xóa đơn hàng (chỉ Admin)

### Dashboard & Analytics

-   `view-dashboard` - Xem dashboard (tất cả roles)
-   `view-analytics` - Xem analytics (chỉ Admin)

---

## 🔄 Cách test hệ thống

### 1. Đăng nhập với từng role

```bash
# Test Admin
Email: admin@bluprinter.com
Password: password

# Test Seller
Email: seller@bluprinter.com
Password: password

# Test Customer
Email: customer@bluprinter.com
Password: password
```

### 2. Kiểm tra permissions

-   Đăng nhập với từng role
-   Truy cập các routes test
-   Xem dashboard API response
-   Kiểm tra navigation hiển thị role badge

### 3. Tạo users mới

```bash
# Tạo seller mới
php artisan seller:create "Nguyen Van C" "seller3@example.com" "123456"

# Tạo customer mới
php artisan user:create "Tran Thi D" "customer4@example.com" "123456" "customer"
```

---

## 🚀 Bước tiếp theo

1. **Tạo Product Management** cho Seller
2. **Tạo Order Management** cho Seller
3. **Tạo Customer Dashboard** cho Customer
4. **Thêm Guest Checkout** (mua hàng không cần đăng ký)
5. **Tạo Admin Panel** với Filament (khi hỗ trợ Laravel 12)
6. **Thêm Email Notifications**
7. **Tạo Reports & Analytics**

---

## 📞 Hỗ trợ

Nếu cần hỗ trợ:

1. Kiểm tra logs: `storage/logs/laravel.log`
2. Clear cache: `php artisan cache:clear`
3. Reset permissions: `php artisan permission:cache-reset`
4. Rebuild assets: `npm run build`

**Hệ thống roles đã được cập nhật thành công! 🎉**
