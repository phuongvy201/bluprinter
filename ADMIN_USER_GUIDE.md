# 👑 Hướng dẫn sử dụng Admin Panel - Bluprinter

## 🚀 Cách đăng nhập Admin

### **Thông tin đăng nhập Admin:**

```
Email: admin@bluprinter.com
Password: password
```

### **Các bước đăng nhập:**

1. Truy cập: `http://localhost/Bluprinter/login`
2. Nhập email và password
3. Click "Đăng nhập"
4. Sau khi đăng nhập, click vào "Dashboard" hoặc truy cập: `http://localhost/Bluprinter/admin/dashboard`

---

## 🎯 Admin Dashboard

### **Trang chủ Admin:**

-   **URL:** `http://localhost/Bluprinter/admin/dashboard`
-   **Hiển thị:**
    -   Thông tin user hiện tại
    -   Thống kê tổng quan (users, roles, permissions)
    -   Phân bố users theo role
    -   Quick actions (thao tác nhanh)

### **Thông tin hiển thị:**

-   ✅ **Welcome card:** Chào mừng + role + permissions
-   ✅ **Statistics cards:** Tổng users, roles, permissions
-   ✅ **Role breakdown:** Admin, Seller, Customer counts
-   ✅ **Quick actions:** Links đến các chức năng chính

---

## 👥 Quản lý Users

### **Truy cập:**

-   **URL:** `http://localhost/Bluprinter/admin/users`
-   **Hoặc:** Click "Users" trong navigation menu

### **Chức năng có sẵn:**

#### ✨ **Xem danh sách Users**

-   Hiển thị tất cả users với thông tin:
    -   Avatar (2 ký tự đầu tên)
    -   Tên và email
    -   Roles (badges màu sắc)
    -   Ngày tạo
    -   Buttons: Xem, Sửa, Xóa

#### ➕ **Tạo User mới**

-   **URL:** `http://localhost/Bluprinter/admin/users/create`
-   **Form fields:**
    -   Tên (required)
    -   Email (required, unique)
    -   Password (required, min 8 chars)
    -   Confirm Password (required)
    -   Roles (multiple select)

#### ✏️ **Chỉnh sửa User**

-   **URL:** `http://localhost/Bluprinter/admin/users/{id}/edit`
-   **Có thể thay đổi:**
    -   Tên
    -   Email
    -   Password (optional)
    -   Roles

#### 🗑️ **Xóa User**

-   Không cho phép xóa chính mình
-   Confirmation dialog trước khi xóa

---

## 🔐 Quản lý Roles

### **Truy cập:**

-   **URL:** `http://localhost/Bluprinter/admin/roles`
-   **Hoặc:** Click "Roles" trong navigation menu

### **Chức năng có sẵn:**

#### ✨ **Xem danh sách Roles**

-   Hiển thị các roles:
    -   Tên role
    -   Số users đang sử dụng
    -   Số permissions
    -   5 permissions đầu tiên + "more"
    -   Buttons: Xem, Sửa, Xóa

#### ➕ **Tạo Role mới**

-   **URL:** `http://localhost/Bluprinter/admin/roles/create`
-   **Form fields:**
    -   Tên role (required, unique)
    -   Permissions (grouped by category):
        -   **User Management:** view-users, create-users, edit-users, delete-users
        -   **Role Management:** view-roles, create-roles, edit-roles, delete-roles
        -   **Permission Management:** view-permissions, create-permissions, edit-permissions, delete-permissions
        -   **Product Management:** view-products, create-products, edit-products, delete-products
        -   **Order Management:** view-orders, create-orders, edit-orders, delete-orders
        -   **Dashboard & Analytics:** view-dashboard, view-analytics

#### ✏️ **Chỉnh sửa Role**

-   **URL:** `http://localhost/Bluprinter/admin/roles/{id}/edit`
-   **Có thể thay đổi:**
    -   Tên role
    -   Permissions (add/remove)

#### 🗑️ **Xóa Role**

-   Không cho phép xóa role đang có users
-   Confirmation dialog

---

## 🧪 Test API Endpoints

### **Trong giao diện:**

1. Vào `/admin/roles` hoặc `/admin/users`
2. Scroll xuống phần "API Test"
3. Click "Test Roles API" hoặc "Test Users API"
4. Xem kết quả JSON

### **Test bằng Browser:**

```bash
# Test Roles API
http://localhost/Bluprinter/admin/roles-api

# Test Users API
http://localhost/Bluprinter/admin/users-api
```

---

## 🔒 Quyền hạn Admin

### **Permissions Admin có:**

-   ✅ `view-users` - Xem danh sách users
-   ✅ `create-users` - Tạo user mới
-   ✅ `edit-users` - Chỉnh sửa user
-   ✅ `delete-users` - Xóa user
-   ✅ `view-roles` - Xem danh sách roles
-   ✅ `create-roles` - Tạo role mới
-   ✅ `edit-roles` - Chỉnh sửa role
-   ✅ `delete-roles` - Xóa role
-   ✅ `view-permissions` - Xem permissions
-   ✅ `create-permissions` - Tạo permission
-   ✅ `edit-permissions` - Chỉnh sửa permission
-   ✅ `delete-permissions` - Xóa permission
-   ✅ `view-products` - Xem sản phẩm
-   ✅ `create-products` - Tạo sản phẩm
-   ✅ `edit-products` - Chỉnh sửa sản phẩm
-   ✅ `delete-products` - Xóa sản phẩm
-   ✅ `view-orders` - Xem đơn hàng
-   ✅ `create-orders` - Tạo đơn hàng
-   ✅ `edit-orders` - Chỉnh sửa đơn hàng
-   ✅ `delete-orders` - Xóa đơn hàng
-   ✅ `view-dashboard` - Xem dashboard
-   ✅ `view-analytics` - Xem analytics

---

## 🎨 Giao diện Admin

### **Navigation:**

-   **Logo:** "A" (Admin Panel)
-   **Menu:** Dashboard, Users, Roles, Website
-   **User info:** Tên + role badge + logout

### **Color Scheme:**

-   🔴 **Admin:** Red badges
-   🔵 **Seller:** Blue badges
-   🟢 **Customer:** Green badges
-   ⚫ **No Role:** Gray badges

### **Responsive Design:**

-   ✅ Mobile-friendly
-   ✅ Tablet-friendly
-   ✅ Desktop-optimized

---

## 🛠️ Commands hữu ích

### **Tạo users mới:**

```bash
# Tạo seller mới
php artisan seller:create "Tên Seller" "email@example.com" "password123"

# Tạo customer mới
php artisan user:create "Tên Customer" "email@example.com" "password123" "customer"
```

### **Xem danh sách users:**

```bash
php artisan users:list
```

### **Clear cache:**

```bash
php artisan cache:clear
php artisan permission:cache-reset
```

---

## 📱 Demo Workflow

### **1. Đăng nhập Admin:**

```
URL: http://localhost/Bluprinter/login
Email: admin@bluprinter.com
Password: password
```

### **2. Xem Dashboard:**

```
URL: http://localhost/Bluprinter/admin/dashboard
→ Xem thống kê tổng quan
→ Xem thông tin user hiện tại
```

### **3. Quản lý Users:**

```
URL: http://localhost/Bluprinter/admin/users
→ Xem danh sách users
→ Click "Tạo User mới"
→ Điền form và tạo user
```

### **4. Quản lý Roles:**

```
URL: http://localhost/Bluprinter/admin/roles
→ Xem danh sách roles
→ Click "Tạo Role mới"
→ Điền form và chọn permissions
```

### **5. Test API:**

```
URL: http://localhost/Bluprinter/admin/roles
→ Scroll xuống "API Test"
→ Click "Test Roles API"
→ Xem kết quả JSON
```

---

## 🚨 Troubleshooting

### **Lỗi 403 Forbidden:**

-   Kiểm tra đăng nhập với role admin
-   Kiểm tra có permission tương ứng

### **Lỗi 404 Not Found:**

-   Kiểm tra route đã được đăng ký
-   Kiểm tra controller method tồn tại

### **Lỗi 500 Internal Server Error:**

-   Kiểm tra logs: `storage/logs/laravel.log`
-   Clear cache: `php artisan cache:clear`

### **Không hiển thị gì:**

-   Kiểm tra assets: `npm run build`
-   Kiểm tra Vite config
-   Clear browser cache

---

## 🎯 Next Steps

1. **Tạo Product Management** cho Seller
2. **Tạo Order Management** cho Seller
3. **Tạo Customer Dashboard**
4. **Thêm Search & Filter**
5. **Thêm Export/Import**
6. **Audit Log** (lịch sử thay đổi)

**Admin Panel đã sẵn sàng sử dụng! 🎉**

---

## 📞 Support

Nếu gặp vấn đề:

1. Kiểm tra logs: `storage/logs/laravel.log`
2. Clear cache: `php artisan cache:clear`
3. Rebuild assets: `npm run build`
4. Check permissions: `php artisan users:list`













