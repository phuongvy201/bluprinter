# 🏪 Shop Profile System - Hệ thống Quản lý Shop

## Tổng quan

Hệ thống Shop Profile cho phép sellers tạo và quản lý shop riêng của mình, tương tự như Shopee.

## Các tính năng chính

### 1. **Shop Profile**

-   ✅ Thông tin shop: Tên, logo, banner, mô tả
-   ✅ Liên hệ: Email, phone, address
-   ✅ Thống kê: Rating, số sản phẩm, doanh số
-   ✅ Verification badge
-   ✅ Social links (Facebook, Instagram, Website)
-   ✅ Policies (Return, Shipping)

### 2. **Seller Dashboard**

-   Xem thống kê shop
-   Quản lý sản phẩm của shop
-   Xem đơn hàng
-   Cập nhật thông tin shop

### 3. **Buyer Experience**

-   Xem danh sách shops
-   Tìm kiếm shops
-   Xem profile shop chi tiết
-   Xem sản phẩm của shop
-   Đánh giá shop

### 4. **Admin Management**

-   Xem tất cả shops
-   Verify/Suspend shops
-   Xem thống kê toàn hệ thống

## Cấu trúc Database

### Shops Table

```sql
- id
- user_id (unique) - 1 user = 1 shop
- shop_name
- shop_slug (unique)
- shop_description
- shop_logo (S3)
- shop_banner (S3)
- shop_phone
- shop_email
- shop_address
- shop_city
- shop_country
- shop_status (active/inactive/suspended)
- verified (boolean)
- rating (decimal 0-5)
- total_ratings
- total_products
- total_sales
- total_revenue
- business_license
- tax_code
- facebook_url
- instagram_url
- website_url
- return_policy
- shipping_policy
- timestamps
```

### Products Table (Updated)

```sql
- ... existing fields ...
- shop_id (foreign key to shops)
```

## Relationships

### User

```php
- hasOne(Shop)
```

### Shop

```php
- belongsTo(User)
- hasMany(Product)
- hasMany(Order) // future
```

### Product

```php
- belongsTo(Shop)
- belongsTo(User) // owner
```

## Routes

### Seller Routes (Auth + Role:seller|admin)

```
GET    /seller/shop/create          - Tạo shop
POST   /seller/shop                 - Lưu shop mới
GET    /seller/shop/edit            - Sửa shop
PUT    /seller/shop                 - Update shop
GET    /seller/shop/dashboard       - Dashboard shop
```

### Public Routes

```
GET    /shops                       - Danh sách shops
GET    /shops/{shop:shop_slug}      - Xem shop detail
GET    /shops/{shop}/products       - Sản phẩm của shop
```

### Admin Routes

```
GET    /admin/shops                 - Quản lý shops
POST   /admin/shops/{shop}/verify   - Verify shop
POST   /admin/shops/{shop}/suspend  - Suspend shop
```

## Middleware

### HasShop

-   Check seller đã có shop chưa
-   Redirect đến create shop nếu chưa có
-   Apply cho: product create, template create, etc.

### ShopActive

-   Check shop có active không
-   Redirect nếu shop bị suspended

## Business Logic

### Khi seller đăng ký:

1. User role = seller
2. Chưa có shop → Redirect tạo shop
3. Có shop → Truy cập dashboard

### Khi tạo product:

1. Check có shop chưa
2. Product.shop_id = seller's shop
3. Product.user_id = seller's user
4. Shop.total_products++

### Khi có đơn hàng:

1. Shop.total_sales++
2. Shop.total_revenue += order amount

### Khi buyer rating:

1. Calculate new average rating
2. Update shop.rating
3. Shop.total_ratings++

## UI Components

### Shop Card (Danh sách)

```
[Logo]  Shop Name ⭐4.5
        123 sản phẩm | 456 đánh giá
        ✓ Verified
```

### Shop Profile Page

```
[Banner]

[Logo] Shop Name ⭐4.5 (123 reviews)
       ✓ Verified Seller

📞 Contact | 📍 Location | 🌐 Website

[Tabs]
- Sản phẩm
- Thông tin shop
- Đánh giá

[Product Grid]
```

### Seller Dashboard

```
📊 Thống kê Shop
- Tổng sản phẩm: 45
- Tổng đơn hàng: 123
- Doanh thu: $12,345
- Đánh giá: ⭐4.5 (89 reviews)

[Quick Actions]
- Thêm sản phẩm
- Xem đơn hàng
- Cập nhật shop
```

## Validation Rules

### Create/Update Shop

```php
'shop_name' => 'required|string|max:255|unique:shops,shop_name,{id}'
'shop_description' => 'nullable|string|max:5000'
'shop_logo' => 'nullable|image|max:2048'
'shop_banner' => 'nullable|image|max:5120'
'shop_phone' => 'nullable|string|max:20'
'shop_email' => 'nullable|email'
'shop_address' => 'nullable|string|max:500'
```

## Permission Matrix

| Action          | Admin | Seller (Owner) | Seller (Other) | Buyer |
| --------------- | ----- | -------------- | -------------- | ----- |
| Xem shops list  | ✅    | ✅             | ✅             | ✅    |
| Xem shop detail | ✅    | ✅             | ✅             | ✅    |
| Tạo shop        | ✅    | ✅ (1 lần)     | ✅ (1 lần)     | ❌    |
| Sửa shop        | ✅    | ✅ (own)       | ❌             | ❌    |
| Xóa shop        | ✅    | ❌             | ❌             | ❌    |
| Verify shop     | ✅    | ❌             | ❌             | ❌    |
| Suspend shop    | ✅    | ❌             | ❌             | ❌    |

## Next Steps

1. ✅ Migration & Models
2. ⏳ ShopController
3. ⏳ Views
4. ⏳ Routes
5. ⏳ Middleware
6. ⏳ Update existing code
7. ⏳ Testing

---

**Status**: 🟡 In Progress
**Version**: 1.0
**Last Updated**: 2025-10-09
