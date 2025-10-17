<?php

namespace App\Services;

use App\Models\ShippingZone;
use App\Models\ShippingRate;
use App\Models\Product;
use Illuminate\Support\Collection;

class ShippingCalculator
{
    /**
     * Calculate shipping cost for a cart
     * 
     * @param Collection $cartItems Array of items with 'product_id', 'quantity', 'price'
     * @param string $countryCode ISO country code (e.g., 'US', 'VN')
     * @return array Shipping details with cost breakdown per item
     */
    public function calculateShipping(Collection $cartItems, string $countryCode): array
    {
        // Find shipping zone for the country
        $zone = ShippingZone::findByCountry($countryCode);

        if (!$zone) {
            return [
                'success' => false,
                'message' => 'Shipping not available for this country',
                'total_shipping' => 0,
                'items' => []
            ];
        }

        // Group items by category and calculate total value
        $itemsWithDetails = $this->prepareItems($cartItems);
        $totalValue = $itemsWithDetails->sum('total_price');
        $totalItems = $itemsWithDetails->sum('quantity');

        // Sort items by price (descending) to apply first-item rate to most expensive
        $sortedItems = $itemsWithDetails->sortByDesc('unit_price');

        // Calculate shipping for each item
        $shippingDetails = [];
        $totalShipping = 0;
        $isFirstItemProcessed = false;

        foreach ($sortedItems as $index => $item) {
            $product = Product::with('template.category')->find($item['product_id']);
            $categoryId = $product->template->category_id ?? null;

            // Find applicable shipping rate
            $shippingRate = $this->findApplicableRate($zone->id, $categoryId, $totalItems, $totalValue);

            if (!$shippingRate) {
                return [
                    'success' => false,
                    'message' => 'No shipping rate found for this combination',
                    'total_shipping' => 0,
                    'items' => []
                ];
            }

            // Calculate cost for this item
            $itemShipping = 0;
            $isFirstItem = false;
            $itemQuantity = $item['quantity'];

            if (!$isFirstItemProcessed) {
                // CHỈ CÓ 1 ITEM ĐẦU TIÊN được tính first_item_cost
                // Các items còn lại (kể cả cùng product) tính additional_item_cost
                $itemShipping = $shippingRate->first_item_cost; // Chỉ 1 item

                // Nếu quantity > 1, các items còn lại tính additional
                if ($itemQuantity > 1) {
                    $itemShipping += ($itemQuantity - 1) * $shippingRate->additional_item_cost;
                }

                $isFirstItem = true;
                $isFirstItemProcessed = true;
            } else {
                // Tất cả items tiếp theo đều tính additional_item_cost
                $itemShipping = $shippingRate->additional_item_cost * $itemQuantity;
            }

            $totalShipping += $itemShipping;

            $shippingDetails[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'shipping_cost' => $itemShipping,
                'total_item_shipping' => $itemShipping,
                'is_first_item' => $isFirstItem,
                'shipping_rate_id' => $shippingRate->id,
                'shipping_rate_name' => $shippingRate->name,
            ];
        }

        return [
            'success' => true,
            'zone_id' => $zone->id,
            'zone_name' => $zone->name,
            'total_shipping' => round($totalShipping, 2),
            'items' => $shippingDetails,
            'breakdown' => [
                'total_items' => $totalItems,
                'total_value' => $totalValue,
                'currency' => 'USD'
            ]
        ];
    }

    /**
     * Prepare cart items with product details
     * 
     * @param Collection $cartItems
     * @return Collection
     */
    protected function prepareItems(Collection $cartItems): Collection
    {
        return $cartItems->map(function ($item) {
            $product = Product::find($item['product_id']);

            return [
                'product_id' => $item['product_id'],
                'product_name' => $product->name ?? 'Unknown Product',
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['price'] ?? $product->price ?? 0,
                'total_price' => ($item['price'] ?? $product->price ?? 0) * ($item['quantity'] ?? 1),
            ];
        });
    }

    /**
     * Find the most applicable shipping rate
     * 
     * @param int $zoneId
     * @param int|null $categoryId
     * @param int $itemCount
     * @param float $orderValue
     * @return ShippingRate|null
     */
    protected function findApplicableRate(int $zoneId, ?int $categoryId, int $itemCount, float $orderValue): ?ShippingRate
    {
        // First, try to find category-specific rate
        if ($categoryId) {
            $rate = ShippingRate::active()
                ->forZone($zoneId)
                ->where('category_id', $categoryId)
                ->ordered()
                ->get()
                ->first(fn($r) => $r->isApplicable($itemCount, $orderValue));

            if ($rate) {
                return $rate;
            }
        }

        // Fall back to general rate (category_id is null)
        return ShippingRate::active()
            ->forZone($zoneId)
            ->whereNull('category_id')
            ->ordered()
            ->get()
            ->first(fn($r) => $r->isApplicable($itemCount, $orderValue));
    }

    /**
     * Get available shipping zones
     * 
     * @return Collection
     */
    public function getAvailableZones(): Collection
    {
        return ShippingZone::active()->ordered()->get();
    }

    /**
     * Get shipping rates for a specific zone
     * 
     * @param int $zoneId
     * @return Collection
     */
    public function getRatesForZone(int $zoneId): Collection
    {
        return ShippingRate::active()
            ->forZone($zoneId)
            ->ordered()
            ->get();
    }

    /**
     * Estimate shipping for quick display (simplified version)
     * 
     * @param string $countryCode
     * @param int $itemCount
     * @param float $estimatedValue
     * @return array
     */
    public function estimateShipping(string $countryCode, int $itemCount = 1, float $estimatedValue = 0): array
    {
        $zone = ShippingZone::findByCountry($countryCode);

        if (!$zone) {
            return [
                'available' => false,
                'message' => 'Shipping not available',
                'estimated_cost' => 0
            ];
        }

        // Get a general rate
        $rate = ShippingRate::active()
            ->forZone($zone->id)
            ->whereNull('category_id')
            ->ordered()
            ->first();

        if (!$rate || !$rate->isApplicable($itemCount, $estimatedValue)) {
            return [
                'available' => true,
                'message' => 'Rate depends on product type',
                'estimated_cost' => 0
            ];
        }

        $estimatedCost = $rate->calculateCost($itemCount);

        return [
            'available' => true,
            'zone_name' => $zone->name,
            'estimated_cost' => round($estimatedCost, 2),
            'currency' => 'USD'
        ];
    }
}
