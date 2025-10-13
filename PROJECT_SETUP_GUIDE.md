# 🎉 Hướng dẫn dự án Bluprinter

## ✅ Đã hoàn thành

### 🔐 Spatie Laravel Permission

-   ✅ Cài đặt package `spatie/laravel-permission`
-   ✅ Cấu hình User model với `HasRoles` trait
-   ✅ Tạo migrations cho roles và permissions
-   ✅ Tạo seeder với 4 roles và 20+ permissions
-   ✅ Đăng ký middleware trong Laravel 11
-   ✅ Tạo 10 users mẫu với các roles khác nhau
-   ✅ Tạo commands để quản lý users

### 🎨 Giao diện khách hàng với Tailwind CSS

-   ✅ Cài đặt và cấu hình Tailwind CSS
-   ✅ Tạo layout responsive với navigation
-   ✅ Thiết kế trang chủ với hero section, features, products preview
-   ✅ Tạo trang sản phẩm với 6 dịch vụ in ấn
-   ✅ Thiết kế trang giới thiệu với team, mission, values
-   ✅ Tạo trang liên hệ với form và thông tin contact
-   ✅ Build assets với Vite

### 🛠️ Commands hữu ích

```bash
# Quản lý users
php artisan users:list
php artisan user:create "Tên User" "email@example.com" "password123" "admin"

# Build assets
npm run build
npm run dev

# Database
php artisan migrate
php artisan db:seed
```

---

## 🚀 Cách sử dụng

### 1. Truy cập website

-   **Trang chủ:** `/`
-   **Sản phẩm:** `/products`
-   **Giới thiệu:** `/about`
-   **Liên hệ:** `/contact`

### 2. Đăng nhập với users mẫu

| Email                     | Password | Role        |
| ------------------------- | -------- | ----------- |
| superadmin@bluprinter.com | password | super-admin |
| admin@bluprinter.com      | password | admin       |
| moderator@bluprinter.com  | password | moderator   |
| user@bluprinter.com       | password | user        |

### 3. Test permissions

Sau khi đăng nhập, truy cập:

-   `/admin/dashboard` - Cần permission: `view-dashboard`
-   `/admin/analytics` - Cần permission: `view-analytics`
-   `/admin-only` - Cần role: `admin`
-   `/super-admin-only` - Cần role: `super-admin`
-   `/user-management` - Cần permission: `view-users`

---

## 🎨 Tính năng giao diện

### ✨ Trang chủ

-   Hero section với gradient background
-   4 features chính với icons
-   Products preview với hover effects
-   CTA section với buttons

### 📦 Trang sản phẩm

-   Grid layout responsive
-   6 dịch vụ in ấn với màu sắc khác nhau
-   Cards với hover animations
-   Pricing information

### 👥 Trang giới thiệu

-   Company story và mission
-   Team members với avatars
-   Statistics và achievements
-   Core values với visual elements

### 📞 Trang liên hệ

-   Contact form với validation
-   Contact information với icons
-   FAQ section
-   Map placeholder

---

## 🛡️ Bảo mật & Permissions

### Roles

-   **super-admin:** Toàn quyền (tất cả permissions)
-   **admin:** Quản lý users, products, orders
-   **moderator:** Xem và chỉnh sửa products, orders
-   **user:** Chỉ xem dashboard

### Permissions

-   User management: `view-users`, `create-users`, `edit-users`, `delete-users`
-   Role management: `view-roles`, `create-roles`, `edit-roles`, `delete-roles`
-   Permission management: `view-permissions`, `create-permissions`, `edit-permissions`, `delete-permissions`
-   Product management: `view-products`, `create-products`, `edit-products`, `delete-products`
-   Order management: `view-orders`, `create-orders`, `edit-orders`, `delete-orders`
-   Dashboard: `view-dashboard`, `view-analytics`

---

## 🎯 Filament Admin Panel

**Lưu ý:** Filament chưa được cài đặt hoàn toàn do conflict với Laravel 12. Có thể:

1. Chờ Filament hỗ trợ Laravel 12
2. Sử dụng Nova thay thế
3. Tạo admin panel tùy chỉnh với Tailwind CSS

---

## 📁 Cấu trúc file quan trọng

```
resources/views/
├── layouts/app.blade.php          # Layout chính
├── home.blade.php                 # Trang chủ
├── products/index.blade.php       # Trang sản phẩm
├── about.blade.php                # Trang giới thiệu
└── contact.blade.php              # Trang liên hệ

app/Http/Controllers/
├── HomeController.php
├── ProductController.php
├── AboutController.php
└── ContactController.php

database/seeders/
├── RolePermissionSeeder.php       # Tạo roles & permissions
└── UserSeeder.php                 # Tạo users mẫu

app/Console/Commands/
├── ListUsers.php                  # Hiển thị danh sách users
└── CreateUser.php                 # Tạo user mới
```

---

## 🔧 Cấu hình

### Tailwind CSS

-   File: `tailwind.config.js`
-   Content paths đã được cấu hình cho Laravel và Filament
-   Plugins: forms

### Vite

-   File: `vite.config.js`
-   CSS: `resources/css/app.css`
-   JS: `resources/js/app.js`

### Laravel 11

-   Bootstrap: `bootstrap/app.php`
-   Middleware: Đã đăng ký Spatie Permission middleware

---

## 🚀 Bước tiếp theo

1. **Cài đặt Filament** khi hỗ trợ Laravel 12
2. **Tạo models** cho Products, Orders, Contacts
3. **Thêm authentication** cho customer
4. **Tích hợp payment** gateway
5. **Thêm file upload** cho thiết kế
6. **SEO optimization**
7. **Email notifications**
8. **Admin dashboard** với statistics

---

## 📞 Hỗ trợ

Nếu cần hỗ trợ thêm:

1. Kiểm tra logs: `storage/logs/laravel.log`
2. Clear cache: `php artisan cache:clear`
3. Reset permissions: `php artisan permission:cache-reset`
4. Rebuild assets: `npm run build`

**Chúc bạn phát triển dự án thành công! 🎉**
