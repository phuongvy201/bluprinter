# Spatie Laravel Permission - Hướng dẫn sử dụng

## Đã cài đặt thành công:

✅ Package `spatie/laravel-permission` đã được cài đặt  
✅ Migrations đã được publish và chạy  
✅ User model đã được cấu hình với `HasRoles` trait  
✅ Roles và Permissions mẫu đã được tạo  
✅ Middleware đã được đăng ký  
✅ Routes demo đã được tạo

## Cách sử dụng:

### 1. Gán roles cho user:

```php
$user = User::find(1);
$user->assignRole('admin');

// Gán nhiều roles
$user->assignRole(['admin', 'moderator']);

// Gán permission trực tiếp
$user->givePermissionTo('view-dashboard');
```

### 2. Kiểm tra roles và permissions:

```php
// Kiểm tra role
if ($user->hasRole('admin')) {
    // User có role admin
}

// Kiểm tra permission
if ($user->can('view-dashboard')) {
    // User có permission view-dashboard
}

// Kiểm tra nhiều roles
if ($user->hasAnyRole(['admin', 'moderator'])) {
    // User có một trong các roles
}

// Kiểm tra tất cả roles
if ($user->hasAllRoles(['admin', 'moderator'])) {
    // User có tất cả các roles
}
```

### 3. Sử dụng middleware trong routes:

```php
// Kiểm tra role
Route::get('/admin', function () {
    return 'Admin only';
})->middleware('role:admin');

// Kiểm tra permission
Route::get('/dashboard', function () {
    return 'Dashboard';
})->middleware('permission:view-dashboard');

// Kiểm tra role hoặc permission
Route::get('/special', function () {
    return 'Special access';
})->middleware('role_or_permission:admin|view-dashboard');
```

### 4. Sử dụng trong Controller:

```php
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-products');
    }

    public function index()
    {
        // Chỉ user có permission 'view-products' mới được truy cập
    }

    public function create()
    {
        if (!auth()->user()->can('create-products')) {
            abort(403);
        }
        // Logic tạo product
    }
}
```

### 5. Sử dụng trong Blade templates:

```blade
@role('admin')
    <p>Chỉ admin mới thấy được đoạn này</p>
@endrole

@permission('view-dashboard')
    <a href="/dashboard">Dashboard</a>
@endpermission

@hasanyrole('admin|moderator')
    <p>Admin hoặc Moderator</p>
@endhasanyrole
```

## Roles và Permissions đã tạo:

### Roles:

-   `super-admin`: Toàn quyền
-   `admin`: Quản lý users, products, orders
-   `moderator`: Xem và chỉnh sửa products, orders
-   `user`: Chỉ xem dashboard

### Permissions:

-   User management: `view-users`, `create-users`, `edit-users`, `delete-users`
-   Role management: `view-roles`, `create-roles`, `edit-roles`, `delete-roles`
-   Permission management: `view-permissions`, `create-permissions`, `edit-permissions`, `delete-permissions`
-   Product management: `view-products`, `create-products`, `edit-products`, `delete-products`
-   Order management: `view-orders`, `create-orders`, `edit-orders`, `delete-orders`
-   Dashboard: `view-dashboard`, `view-analytics`

## Routes demo:

-   `/admin/dashboard` - Dashboard với permission middleware
-   `/admin/analytics` - Analytics với permission middleware
-   `/admin-only` - Chỉ admin role
-   `/super-admin-only` - Chỉ super-admin role
-   `/user-management` - Permission view-users

## Lưu ý:

-   Luôn clear cache sau khi thay đổi roles/permissions: `php artisan permission:cache-reset`
-   Sử dụng `php artisan tinker` để test nhanh
-   Có thể tạo thêm roles và permissions mới bằng cách chạy lại seeder
