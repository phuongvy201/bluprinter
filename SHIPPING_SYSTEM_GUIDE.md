# Hệ thống Shipping - Hướng dẫn Chi tiết

## Tổng quan Hệ thống

Hệ thống shipping linh hoạt được thiết kế để:

-   Tính phí ship dựa trên **quốc gia** (country)
-   Tính phí ship dựa trên **loại sản phẩm** (product category)
-   Tính phí ship dựa trên **số lượng items** trong đơn hàng
-   **Item đầu tiên** (item có giá cao nhất) = Phí ship đầy đủ + Label fee
-   **Item tiếp theo** = Phí ship giảm (không có label fee)

## Cấu trúc Database

### 1. Bảng `shipping_zones`

```sql
- id
- name                  -- Tên vùng (VD: "USA", "Europe", "Asia")
- countries            -- JSON array chứa mã quốc gia (VD: ["US", "CA"])
- description          -- Mô tả
- is_active            -- Trạng thái kích hoạt
- sort_order           -- Thứ tự sắp xếp
- timestamps
```

### 2. Bảng `shipping_rates`

```sql
- id
- shipping_zone_id     -- Foreign key đến shipping_zones
- category_id          -- Foreign key đến categories (nullable cho rate chung)
- name                 -- Tên rate
- description          -- Mô tả
- first_item_cost      -- Phí ship cho item đầu tiên
- additional_item_cost -- Phí ship cho mỗi item tiếp theo
- label_fee            -- Phí label (chỉ tính 1 lần cho item đầu)
- min_items            -- Số lượng item tối thiểu (nullable)
- max_items            -- Số lượng item tối đa (nullable)
- min_order_value      -- Giá trị đơn hàng tối thiểu (nullable)
- max_order_value      -- Giá trị đơn hàng tối đa (nullable)
- max_weight           -- Trọng lượng tối đa (nullable)
- is_active            -- Trạng thái kích hoạt
- sort_order           -- Thứ tự ưu tiên
- timestamps
```

### 3. Cập nhật `order_items`

Thêm các trường:

```sql
- shipping_cost        -- Phí ship cho item này
- label_fee            -- Phí label (chỉ có ở item đầu)
- is_first_item        -- Đánh dấu item đầu tiên
- shipping_notes       -- Ghi chú về shipping
```

## Models đã tạo

### 1. ShippingZone Model

**File**: `app/Models/ShippingZone.php`

**Methods quan trọng:**

-   `hasCountry($countryCode)` - Kiểm tra country có trong zone không
-   `findByCountry($countryCode)` - Tìm zone theo country code
-   `activeShippingRates()` - Lấy tất cả rates active của zone

### 2. ShippingRate Model

**File**: `app/Models/ShippingRate.php`

**Methods quan trọng:**

-   `calculateCost($itemCount, $includeLabel)` - Tính phí ship
-   `isApplicable($itemCount, $orderValue)` - Kiểm tra rate có áp dụng được không
-   `forZone($zoneId)` - Scope lọc theo zone
-   `forCategory($categoryId)` - Scope lọc theo category

### 3. OrderItem Model (đã cập nhật)

Thêm các fields: `shipping_cost`, `label_fee`, `is_first_item`, `shipping_notes`

## Service - ShippingCalculator

**File**: `app/Services/ShippingCalculator.php`

### Sử dụng trong code:

```php
use App\Services\ShippingCalculator;

$calculator = new ShippingCalculator();

// Tính shipping cho giỏ hàng
$cartItems = collect([
    ['product_id' => 1, 'quantity' => 2, 'price' => 25.00],
    ['product_id' => 2, 'quantity' => 1, 'price' => 45.00],
]);

$result = $calculator->calculateShipping($cartItems, 'US');

/*
Kết quả trả về:
[
    'success' => true,
    'zone_id' => 1,
    'zone_name' => 'USA',
    'total_shipping' => 15.50,
    'items' => [
        [
            'product_id' => 2,
            'quantity' => 1,
            'shipping_cost' => 8.00,
            'label_fee' => 2.50,  // Chỉ item đắt nhất có label fee
            'total_item_shipping' => 10.50,
            'is_first_item' => true,
            ...
        ],
        [
            'product_id' => 1,
            'quantity' => 2,
            'shipping_cost' => 5.00,  // 2.50 x 2 (additional item rate)
            'label_fee' => 0,         // Không có label fee
            'total_item_shipping' => 5.00,
            'is_first_item' => false,
            ...
        ]
    ],
    'breakdown' => [...]
]
*/
```

### Methods chính:

1. **calculateShipping($cartItems, $countryCode)**

    - Tính phí ship chi tiết cho từng item
    - Tự động sort items theo giá (đắt → rẻ)
    - Item đắt nhất = first_item_cost + label_fee
    - Items còn lại = additional_item_cost

2. **estimateShipping($countryCode, $itemCount, $estimatedValue)**

    - Ước tính nhanh phí ship (dùng cho hiển thị)

3. **getAvailableZones()**

    - Lấy danh sách zones đang active

4. **getRatesForZone($zoneId)**
    - Lấy tất cả rates của một zone

## Ví dụ Thiết lập Shipping

### Bước 1: Tạo Shipping Zones

```php
use App\Models\ShippingZone;

// USA Zone
ShippingZone::create([
    'name' => 'United States',
    'countries' => ['US'],
    'description' => 'Shipping within USA',
    'is_active' => true,
    'sort_order' => 1
]);

// Europe Zone
ShippingZone::create([
    'name' => 'Europe',
    'countries' => ['GB', 'DE', 'FR', 'IT', 'ES', 'NL', 'BE'],
    'description' => 'European countries',
    'is_active' => true,
    'sort_order' => 2
]);

// Asia Zone
ShippingZone::create([
    'name' => 'Asia',
    'countries' => ['VN', 'TH', 'SG', 'MY', 'PH', 'ID'],
    'description' => 'Asian countries',
    'is_active' => true,
    'sort_order' => 3
]);
```

### Bước 2: Tạo Shipping Rates

```php
use App\Models\ShippingRate;

// Rate cho T-Shirts ở USA
ShippingRate::create([
    'shipping_zone_id' => 1, // USA
    'category_id' => 5,      // T-Shirts category
    'name' => 'Standard Shipping - T-Shirts (USA)',
    'description' => 'Standard shipping for t-shirts to USA',
    'first_item_cost' => 5.00,      // $5 cho item đầu
    'additional_item_cost' => 2.00,  // $2 cho mỗi item thêm
    'label_fee' => 1.50,             // $1.50 phí label
    'is_active' => true,
    'sort_order' => 1
]);

// Rate cho Hoodies ở USA (đắt hơn vì nặng hơn)
ShippingRate::create([
    'shipping_zone_id' => 1, // USA
    'category_id' => 3,      // Hoodies category
    'name' => 'Standard Shipping - Hoodies (USA)',
    'description' => 'Standard shipping for hoodies to USA',
    'first_item_cost' => 8.00,      // $8 cho item đầu
    'additional_item_cost' => 3.50,  // $3.50 cho mỗi item thêm
    'label_fee' => 2.00,             // $2 phí label
    'is_active' => true,
    'sort_order' => 1
]);

// Rate chung cho items khác ở USA
ShippingRate::create([
    'shipping_zone_id' => 1, // USA
    'category_id' => null,   // Áp dụng chung
    'name' => 'Standard Shipping - General (USA)',
    'description' => 'General shipping rate for other products',
    'first_item_cost' => 6.00,
    'additional_item_cost' => 2.50,
    'label_fee' => 1.75,
    'is_active' => true,
    'sort_order' => 999 // Priority thấp nhất
]);
```

### Bước 3: Ví dụ Tính toán

**Đơn hàng có:**

-   1 Hoodie ($45) - Category: Hoodies
-   2 T-Shirts ($25 each) - Category: T-Shirts

**Tính toán:**

1. Hoodie (item đắt nhất):

    - Base: $8.00 (first_item_cost)
    - Label: $2.00 (label_fee)
    - **Total: $10.00**

2. T-Shirt 1:

    - Base: $2.00 (additional_item_cost for T-Shirts)
    - Label: $0 (không có)
    - **Total: $2.00**

3. T-Shirt 2:
    - Base: $2.00 (additional_item_cost for T-Shirts)
    - Label: $0 (không có)
    - **Total: $2.00**

**Tổng shipping: $14.00**

## Tích hợp vào Checkout

### Trong CheckoutController hoặc CartController:

```php
use App\Services\ShippingCalculator;

public function calculateShipping(Request $request)
{
    $cart = session()->get('cart', []);
    $countryCode = $request->input('country', 'US');

    $calculator = new ShippingCalculator();
    $shipping = $calculator->calculateShipping(collect($cart), $countryCode);

    if (!$shipping['success']) {
        return response()->json([
            'error' => $shipping['message']
        ], 400);
    }

    // Lưu vào session để dùng khi tạo order
    session()->put('shipping_details', $shipping);

    return response()->json($shipping);
}

public function createOrder(Request $request)
{
    // ... validation ...

    $shippingDetails = session()->get('shipping_details');

    // Tạo order
    $order = Order::create([
        // ... order data ...
        'shipping_cost' => $shippingDetails['total_shipping'],
    ]);

    // Tạo order items với shipping details
    foreach ($shippingDetails['items'] as $itemShipping) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $itemShipping['product_id'],
            // ... other fields ...
            'shipping_cost' => $itemShipping['shipping_cost'],
            'label_fee' => $itemShipping['label_fee'],
            'is_first_item' => $itemShipping['is_first_item'],
        ]);
    }
}
```

## Tiếp theo - Cần làm

### 1. Tạo Admin Controllers

-   `ShippingZoneController` - Quản lý zones
-   `ShippingRateController` - Quản lý rates

### 2. Tạo Admin Routes

```php
// routes/web.php
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('shipping-zones', ShippingZoneController::class);
    Route::resource('shipping-rates', ShippingRateController::class);
});
```

### 3. Tạo Admin Views

-   `resources/views/admin/shipping-zones/index.blade.php`
-   `resources/views/admin/shipping-zones/create.blade.php`
-   `resources/views/admin/shipping-zones/edit.blade.php`
-   `resources/views/admin/shipping-rates/index.blade.php`
-   `resources/views/admin/shipping-rates/create.blade.php`
-   `resources/views/admin/shipping-rates/edit.blade.php`

### 4. Tạo Seeder cho dữ liệu mẫu

```bash
php artisan make:seeder ShippingSeeder
```

### 5. Tích hợp vào Checkout Flow

-   Thêm country selector
-   AJAX call để calculate shipping real-time
-   Hiển thị breakdown chi tiết
-   Lưu shipping details vào order

## API Endpoints (Nếu cần)

```php
// API cho frontend
Route::post('/api/shipping/calculate', [ShippingController::class, 'calculate']);
Route::get('/api/shipping/zones', [ShippingController::class, 'zones']);
Route::get('/api/shipping/estimate', [ShippingController::class, 'estimate']);
```

## Testing

```php
// Tests/Unit/ShippingCalculatorTest.php
public function test_first_item_includes_label_fee()
{
    $calculator = new ShippingCalculator();
    // ... test logic
}

public function test_additional_items_no_label_fee()
{
    // ... test logic
}

public function test_most_expensive_item_is_first()
{
    // ... test logic
}
```

## Lưu ý quan trọng

1. **Item đầu tiên** luôn là item CÓ GIÁ CAO NHẤT, không phải item đầu tiên trong giỏ
2. **Label fee** chỉ tính 1 lần cho toàn đơn hàng (gắn vào item đắt nhất)
3. **Category-specific rates** có ưu tiên cao hơn general rates
4. **Zone matching** dựa trên country code (ISO 2 letters: US, VN, GB, etc.)
5. Hệ thống hỗ trợ các điều kiện:
    - Min/max items
    - Min/max order value
    - Max weight

## Mở rộng trong tương lai

-   [ ] Free shipping threshold
-   [ ] Express shipping options
-   [ ] Weight-based calculation
-   [ ] Dimension-based calculation
-   [ ] Real-time carrier integration (USPS, FedEx, DHL)
-   [ ] Shipping promotions/discounts
-   [ ] Multi-warehouse support
-   [ ] International customs handling
