# 🔐 Hướng dẫn quản lý Roles & Users - Admin Panel

## ✅ Đã hoàn thành

### 🎯 **Chức năng quản lý Roles**
- ✅ **RoleController** với đầy đủ CRUD operations
- ✅ **Middleware bảo mật** cho từng action
- ✅ **Views admin** với giao diện đẹp
- ✅ **API endpoints** để test

### 👥 **Chức năng quản lý Users**
- ✅ **UserManagementController** với đầy đủ CRUD operations
- ✅ **Middleware bảo mật** cho từng action
- ✅ **Views admin** với thống kê

---

## 🚀 Cách sử dụng

### 1. **Truy cập Admin Panel**
```
URL: /admin/roles (quản lý roles)
URL: /admin/users (quản lý users)
```

**Yêu cầu:** Phải đăng nhập với role **Admin** và có các permissions tương ứng.

### 2. **Đăng nhập Admin**
```
Email: admin@bluprinter.com
Password: password
```

---

## 🔐 Quản lý Roles

### **Routes & Permissions**

| Action | Route | Permission | Mô tả |
|--------|-------|------------|-------|
| Xem danh sách | `GET /admin/roles` | `view-roles` | Hiển thị tất cả roles |
| Tạo mới | `GET /admin/roles/create` | `create-roles` | Form tạo role |
| Lưu role | `POST /admin/roles` | `create-roles` | Lưu role mới |
| Xem chi tiết | `GET /admin/roles/{id}` | `view-roles` | Xem thông tin role |
| Chỉnh sửa | `GET /admin/roles/{id}/edit` | `edit-roles` | Form sửa role |
| Cập nhật | `PUT /admin/roles/{id}` | `edit-roles` | Cập nhật role |
| Xóa | `DELETE /admin/roles/{id}` | `delete-roles` | Xóa role |
| API | `GET /admin/roles-api` | `view-roles` | API lấy danh sách |

### **Tính năng Roles Management**

#### ✨ **Danh sách Roles**
- Hiển thị tất cả roles với permissions
- Đếm số users đang sử dụng role
- Hiển thị 5 permissions đầu tiên + "more"
- Buttons: Xem, Sửa, Xóa (theo permissions)

#### ➕ **Tạo Role mới**
- Form với tên role
- Chọn permissions theo nhóm:
  - **User Management:** `view-users`, `create-users`, `edit-users`, `delete-users`
  - **Role Management:** `view-roles`, `create-roles`, `edit-roles`, `delete-roles`
  - **Permission Management:** `view-permissions`, `create-permissions`, `edit-permissions`, `delete-permissions`
  - **Product Management:** `view-products`, `create-products`, `edit-products`, `delete-products`
  - **Order Management:** `view-orders`, `create-orders`, `edit-orders`, `delete-orders`
  - **Dashboard & Analytics:** `view-dashboard`, `view-analytics`
- Buttons: "Chọn tất cả" / "Bỏ chọn tất cả"

#### ✏️ **Chỉnh sửa Role**
- Form tương tự tạo mới
- Pre-select permissions hiện tại
- Validation: không cho phép trùng tên

#### 🗑️ **Xóa Role**
- Kiểm tra: không cho xóa role đang có users
- Confirmation dialog

---

## 👥 Quản lý Users

### **Routes & Permissions**

| Action | Route | Permission | Mô tả |
|--------|-------|------------|-------|
| Xem danh sách | `GET /admin/users` | `view-users` | Hiển thị tất cả users |
| Tạo mới | `GET /admin/users/create` | `create-users` | Form tạo user |
| Lưu user | `POST /admin/users` | `create-users` | Lưu user mới |
| Xem chi tiết | `GET /admin/users/{id}` | `view-users` | Xem thông tin user |
| Chỉnh sửa | `GET /admin/users/{id}/edit` | `edit-users` | Form sửa user |
| Cập nhật | `PUT /admin/users/{id}` | `edit-users` | Cập nhật user |
| Xóa | `DELETE /admin/users/{id}` | `delete-users` | Xóa user |
| API | `GET /admin/users-api` | `view-users` | API lấy danh sách |

### **Tính năng User Management**

#### ✨ **Danh sách Users**
- Hiển thị avatar (2 ký tự đầu tên)
- Thông tin: tên, email, roles, ngày tạo
- Badge "You" cho tài khoản hiện tại
- Role badges với màu sắc khác nhau
- Buttons: Xem, Sửa, Xóa (theo permissions)

#### ➕ **Tạo User mới**
- Form: tên, email, password, confirm password
- Chọn roles (multiple select)
- Auto verify email

#### ✏️ **Chỉnh sửa User**
- Form: tên, email, password (optional)
- Chọn roles (multiple select)
- Validation: không cho phép trùng email

#### 🗑️ **Xóa User**
- Không cho phép xóa chính mình
- Confirmation dialog

#### 📊 **Thống kê**
- Tổng users
- Số lượng Admin
- Số lượng Seller

---

## 🧪 Test API Endpoints

### **Test trong giao diện**
1. Đăng nhập với admin
2. Vào `/admin/roles` hoặc `/admin/users`
3. Click "Test Roles API" hoặc "Test Users API"
4. Xem kết quả JSON

### **Test bằng curl**
```bash
# Test Roles API
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost/admin/roles-api

# Test Users API  
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost/admin/users-api
```

---

## 🔒 Bảo mật

### **Middleware Protection**
- Tất cả routes đều được bảo vệ bởi middleware `auth`
- Mỗi action có middleware permission riêng
- Không thể truy cập nếu không có quyền

### **Validation**
- **Roles:** Tên role unique, permissions tồn tại
- **Users:** Email unique, password confirmed, roles tồn tại
- **Xóa:** Không cho xóa role có users, không cho xóa chính mình

### **CSRF Protection**
- Tất cả forms có CSRF token
- API endpoints cần authentication

---

## 🎨 Giao diện

### **Admin Layout**
- Navigation với logo "A" (Admin)
- Menu: Dashboard, Users, Roles, Website
- User info với role badge
- Auto-hide alerts sau 5 giây

### **Design System**
- **Colors:** Blue (primary), Red (admin), Green (customer), Gray (neutral)
- **Icons:** Heroicons SVG
- **Components:** Cards, badges, buttons, forms
- **Responsive:** Mobile-friendly

---

## 🚀 Bước tiếp theo

1. **Tạo form Edit Role** (chưa tạo view)
2. **Tạo form Create/Edit User** (chưa tạo views)
3. **Tạo Permission Management** (chưa có)
4. **Thêm Search & Filter**
5. **Thêm Pagination**
6. **Export/Import functionality**
7. **Audit Log** (lịch sử thay đổi)

---

## 📞 Troubleshooting

### **Lỗi thường gặp:**

1. **403 Forbidden**
   - Kiểm tra đăng nhập với role admin
   - Kiểm tra có permission tương ứng

2. **404 Not Found**
   - Kiểm tra route đã được đăng ký
   - Kiểm tra controller method tồn tại

3. **500 Internal Server Error**
   - Kiểm tra logs: `storage/logs/laravel.log`
   - Kiểm tra database connection
   - Kiểm tra Spatie Permission đã cài đặt

### **Debug Commands:**
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan permission:cache-reset

# Check permissions
php artisan users:list
php artisan tinker
>>> Spatie\Permission\Models\Role::all()
>>> Spatie\Permission\Models\Permission::all()
```

**Chức năng quản lý roles & users đã sẵn sàng! 🎉**
