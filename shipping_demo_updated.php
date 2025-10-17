<?php

/**
 * UPDATED DEMO: Shipping Calculator (Simplified - No Label Fee)
 * 
 * Chạy file này để test hệ thống shipping đã cập nhật:
 * php shipping_demo_updated.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ShippingCalculator;
use App\Models\Product;

$calculator = new ShippingCalculator();

echo "\n========================================\n";
echo "   SHIPPING CALCULATOR DEMO (UPDATED)\n";
echo "   ✨ Simplified - No Label Fee Field\n";
echo "========================================\n\n";

// Test Case 1: Đơn hàng với 1 Hoodie và 2 T-Shirts gửi đến USA
echo "📦 TEST CASE 1: Mixed Order to USA\n";
echo "-----------------------------------\n";
echo "Order Items:\n";
echo "  - 1x Hoodie ($45.00)\n";
echo "  - 2x T-Shirt ($25.00 each)\n";
echo "  - Country: United States (US)\n\n";

$cartItems = collect([
    ['product_id' => 1, 'quantity' => 1, 'price' => 45.00],
    ['product_id' => 2, 'quantity' => 2, 'price' => 25.00],
]);

$result = $calculator->calculateShipping($cartItems, 'US');

if ($result['success']) {
    echo "✅ Shipping Calculated Successfully!\n\n";
    echo "Zone: {$result['zone_name']}\n";
    echo "Total Shipping Cost: \${$result['total_shipping']}\n\n";

    echo "📋 Item Breakdown:\n";
    foreach ($result['items'] as $item) {
        echo "  • Product ID: {$item['product_id']}\n";
        echo "    Quantity: {$item['quantity']}\n";
        echo "    Shipping Cost: \${$item['shipping_cost']}";
        if ($item['is_first_item']) {
            echo " (Includes all fees: shipping + label)";
        }
        echo "\n";
        echo "    First Item: " . ($item['is_first_item'] ? '✓ Yes' : 'No') . "\n";
        echo "    Rate: {$item['shipping_rate_name']}\n\n";
    }
} else {
    echo "❌ Error: {$result['message']}\n\n";
}

// Test Case 2: Đơn hàng nhiều items cùng loại
echo "\n========================================\n";
echo "📦 TEST CASE 2: Multiple Same Items to Europe\n";
echo "-----------------------------------\n";
echo "Order Items:\n";
echo "  - 5x T-Shirt ($25.00 each)\n";
echo "  - Country: Germany (DE)\n\n";

$cartItems2 = collect([
    ['product_id' => 2, 'quantity' => 5, 'price' => 25.00],
]);

$result2 = $calculator->calculateShipping($cartItems2, 'DE');

if ($result2['success']) {
    echo "✅ Shipping Calculated Successfully!\n\n";
    echo "Zone: {$result2['zone_name']}\n";
    echo "Total Shipping Cost: \${$result2['total_shipping']}\n\n";

    echo "📋 Breakdown:\n";
    echo "  Item 1: \${$result2['items'][0]['shipping_cost']} (First item - all fees included)\n";
    echo "  Items 2-5: \$" . ($result2['items'][0]['shipping_cost'] - $result2['total_shipping']) * -1 . " total for 4 additional items\n";
    echo "  \n";
    echo "  Calculation:\n";
    echo "  - First item: Charged at first_item_cost\n";
    echo "  - Each additional: Charged at additional_item_cost\n";
    echo "  - No separate label fee! ✨\n\n";
} else {
    echo "❌ Error: {$result2['message']}\n\n";
}

// Test Case 3: Single item
echo "\n========================================\n";
echo "📦 TEST CASE 3: Single Item Order\n";
echo "-----------------------------------\n";
echo "Order Items:\n";
echo "  - 1x Premium Hoodie ($65.00)\n";
echo "  - Country: Canada (CA)\n\n";

$cartItems3 = collect([
    ['product_id' => 3, 'quantity' => 1, 'price' => 65.00],
]);

$result3 = $calculator->calculateShipping($cartItems3, 'CA');

if ($result3['success']) {
    echo "✅ Shipping Calculated Successfully!\n\n";
    echo "Zone: {$result3['zone_name']}\n";
    echo "Total Shipping Cost: \${$result3['total_shipping']}\n\n";

    echo "📋 Note:\n";
    echo "  Single item orders pay full first_item_cost\n";
    echo "  This includes ALL fees (shipping + label + handling)\n";
    echo "  Simple and transparent! ✨\n\n";
} else {
    echo "❌ Error: {$result3['message']}\n\n";
}

// Show pricing comparison
echo "\n========================================\n";
echo "💰 PRICING STRUCTURE (SIMPLIFIED)\n";
echo "========================================\n\n";

echo "USA Rates:\n";
echo "┌──────────────┬─────────────┬──────────────┐\n";
echo "│ Product Type │ 1st Item    │ Additional   │\n";
echo "├──────────────┼─────────────┼──────────────┤\n";
echo "│ T-Shirts     │ \$6.50       │ \$2.00        │\n";
echo "│ Hoodies      │ \$10.00      │ \$3.50        │\n";
echo "│ General      │ \$8.25       │ \$2.50        │\n";
echo "└──────────────┴─────────────┴──────────────┘\n";
echo "* First item cost includes shipping + label\n\n";

echo "Europe Rates:\n";
echo "┌──────────────┬─────────────┬──────────────┐\n";
echo "│ Product Type │ 1st Item    │ Additional   │\n";
echo "├──────────────┼─────────────┼──────────────┤\n";
echo "│ T-Shirts     │ \$15.00      │ \$4.00        │\n";
echo "│ Hoodies      │ \$22.00      │ \$6.00        │\n";
echo "│ General      │ \$18.50      │ \$5.00        │\n";
echo "└──────────────┴─────────────┴──────────────┘\n";
echo "* First item cost includes shipping + label\n\n";

// Show calculation example
echo "\n========================================\n";
echo "📊 CALCULATION EXAMPLE\n";
echo "========================================\n\n";

echo "Example: 3 T-Shirts to USA\n\n";
echo "Calculation:\n";
echo "  Item 1 (most expensive): \$6.50  (first_item_cost)\n";
echo "  Item 2:                   \$2.00  (additional_item_cost)\n";
echo "  Item 3:                   \$2.00  (additional_item_cost)\n";
echo "  ─────────────────────────────────\n";
echo "  Total Shipping:          \$10.50\n\n";

echo "✨ Benefits:\n";
echo "  ✓ Simpler to understand\n";
echo "  ✓ Easier to manage in admin\n";
echo "  ✓ Clearer for customers\n";
echo "  ✓ Less fields to maintain\n";
echo "  ✓ One price includes everything\n\n";

echo "========================================\n";
echo "✨ UPDATED SYSTEM READY!\n";
echo "========================================\n\n";

echo "💡 Key Changes:\n";
echo "  - ❌ Removed: Separate label_fee field\n";
echo "  - ✅ Updated: first_item_cost now all-inclusive\n";
echo "  - ✅ Simpler: Only 2 pricing fields needed\n";
echo "  - ✅ Clearer: Easier to understand and use\n\n";

echo "🎯 What's Next:\n";
echo "  1. Test in admin panel: /admin/shipping-rates\n";
echo "  2. Create new rates with simplified pricing\n";
echo "  3. Integrate into checkout process\n";
echo "  4. Monitor and adjust rates as needed\n\n";
