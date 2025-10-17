# ✅ CATEGORY HIERARCHY - CẬP NHẬT

## 🎯 THAY ĐỔI MỚI

### Dropdown Categories giờ hiển thị phân cấp cha-con!

**TRƯỚC:**

```
Select Category:
- Apparel
- T-Shirts
- Hoodies
- Accessories
- Hats
```

**SAU:**

```
Select Category:
- General (All Categories)
- 📁 Apparel
    └─ T-Shirts
    └─ Hoodies
    └─ Sweatshirts
- 📁 Accessories
    └─ Hats
    └─ Bags
```

## 🔄 CÁC FILE ĐÃ CẬP NHẬT

### 1. Controller ✅

**File**: `app/Http/Controllers/Admin/ShippingRateController.php`

**Thêm method mới:**

```php
protected function getCategoriesHierarchy()
{
    $categories = Category::with('children')
        ->whereNull('parent_id')
        ->orderBy('name')
        ->get();

    $flatCategories = [];

    foreach ($categories as $category) {
        // Parent category
        $flatCategories[] = [
            'id' => $category->id,
            'name' => $category->name,
            'level' => 0
        ];

        // Child categories
        if ($category->children->count() > 0) {
            foreach ($category->children->sortBy('name') as $child) {
                $flatCategories[] = [
                    'id' => $child->id,
                    'name' => $child->name,
                    'level' => 1,
                    'parent' => $category->name
                ];
            }
        }
    }

    return collect($flatCategories);
}
```

**Updated methods:**

-   ✅ `index()` - Sử dụng getCategoriesHierarchy()
-   ✅ `create()` - Sử dụng getCategoriesHierarchy()
-   ✅ `edit()` - Sử dụng getCategoriesHierarchy()

### 2. Views ✅

**Files updated:**

-   `resources/views/admin/shipping-rates/index.blade.php`
-   `resources/views/admin/shipping-rates/create.blade.php`
-   `resources/views/admin/shipping-rates/edit.blade.php`

**Dropdown template:**

```html
<option value="{{ $category['id'] }}">
    @if($category['level'] == 0) 📁 {{ $category['name'] }} @else
    &nbsp;&nbsp;&nbsp;&nbsp;└─ {{ $category['name'] }} @endif
</option>
```

## 🎨 UI HIỂN THỊ

### Dropdown trông như thế nào:

```
┌─────────────────────────────────────┐
│ Category (Optional)           ▼    │
├─────────────────────────────────────┤
│ General (All Categories)            │
│ 📁 Apparel                          │
│     └─ T-Shirts                     │
│     └─ Hoodies                      │
│     └─ Sweatshirts                  │
│ 📁 Home & Living                    │
│     └─ Posters                      │
│     └─ Canvas                       │
│ 📁 Accessories                      │
│     └─ Hats                         │
│     └─ Bags                         │
└─────────────────────────────────────┘
```

### Icons

-   **📁** - Parent categories
-   **└─** - Child categories (với indent)

## 💡 LỢI ÍCH

### 1. Rõ ràng hơn

-   ✅ Dễ phân biệt parent vs child
-   ✅ Visual hierarchy với icons
-   ✅ Indent giúp dễ đọc

### 2. Dễ sử dụng

-   ✅ Admin dễ chọn đúng category
-   ✅ Không bị nhầm lẫn giữa các levels
-   ✅ Professional appearance

### 3. Linh hoạt

-   ✅ Hỗ trợ 2 levels (parent-child)
-   ✅ Dễ mở rộng thêm levels nếu cần
-   ✅ Sort by name trong mỗi level

## 🎯 VÍ DỤ SỬ DỤNG

### Tạo Rate cho Parent Category

```
Zone: United States
Category: 📁 Apparel
Name: Standard - All Apparel (USA)
First Item: $8.00
Additional: $3.00
```

→ Áp dụng cho TẤT CẢ sản phẩm trong Apparel (T-Shirts, Hoodies, etc.)

### Tạo Rate cho Child Category

```
Zone: United States
Category: └─ T-Shirts
Name: Standard - T-Shirts Only (USA)
First Item: $6.50
Additional: $2.00
```

→ Áp dụng CHỈ cho T-Shirts (overrides parent rate nếu có)

### Tạo Rate General

```
Zone: United States
Category: General (All Categories)
Name: Standard - General Products (USA)
First Item: $8.25
Additional: $2.50
```

→ Áp dụng cho tất cả products không có specific rate

## 🔍 PRIORITY LOGIC

Khi tính shipping, hệ thống ưu tiên:

1. **Child Category Rate** (highest priority)
    - VD: "T-Shirts" rate
2. **Parent Category Rate**
    - VD: "Apparel" rate (nếu không có T-Shirts rate)
3. **General Rate** (lowest priority)
    - VD: "General" rate (fallback)

## 📊 VÍ DỤ HIERARCHY

### Sample Categories Structure:

```
📁 Apparel (parent_id: null)
  └─ T-Shirts (parent_id: 1)
  └─ Hoodies (parent_id: 1)
  └─ Sweatshirts (parent_id: 1)

📁 Home & Living (parent_id: null)
  └─ Posters (parent_id: 2)
  └─ Canvas (parent_id: 2)
  └─ Wall Art (parent_id: 2)

📁 Accessories (parent_id: null)
  └─ Hats (parent_id: 3)
  └─ Bags (parent_id: 3)
```

### Rates có thể tạo:

```
✅ Apparel (parent) → Áp dụng cho tất cả trong Apparel
✅ T-Shirts (child) → Override rate riêng cho T-Shirts
✅ General → Fallback cho các category khác
```

## 🎨 VISUAL DESIGN

### Icons & Indent

-   **Level 0** (Parent): `📁 Category Name`
-   **Level 1** (Child): `&nbsp;&nbsp;&nbsp;&nbsp;└─ Category Name`

### Colors (trong index view)

-   **Parent categories**: Purple badge có thể
-   **Child categories**: Lighter purple badge
-   **General**: Gray badge

## 🚀 TEST NGAY

### 1. Truy cập Create Form

```
URL: http://localhost/admin/shipping-rates/create
```

Xem dropdown Category → Sẽ thấy hierarchy!

### 2. Test Filter trong Index

```
URL: http://localhost/admin/shipping-rates
```

Dropdown "Filter by Category" cũng có hierarchy!

### 3. Tạo Rates với Different Levels

```
Rate 1: Zone=USA, Category=📁 Apparel, First=$8, Add=$3
Rate 2: Zone=USA, Category=└─ T-Shirts, First=$6.50, Add=$2
Rate 3: Zone=USA, Category=General, First=$8.25, Add=$2.50
```

## ✨ COMPLETED!

```
✅ Controller method: getCategoriesHierarchy()
✅ Index view: Filter với hierarchy
✅ Create view: Dropdown với hierarchy
✅ Edit view: Dropdown với hierarchy
✅ Visual indicators: 📁 và └─
✅ Proper indent: 4 spaces
✅ No linter errors (chỉ CSS warnings)
```

---

**🎊 Categories giờ hiển thị đẹp và rõ ràng với phân cấp!** ✨

Dễ chọn, dễ hiểu, professional! 👍
