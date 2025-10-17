<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingZone;
use App\Models\ShippingRate;
use App\Models\Category;

class ShippingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo Shipping Zones
        $usaZone = ShippingZone::create([
            'name' => 'United States',
            'countries' => ['US'],
            'description' => 'Shipping within USA - Standard delivery 5-7 business days',
            'is_active' => true,
            'sort_order' => 1
        ]);

        $europeZone = ShippingZone::create([
            'name' => 'Europe',
            'countries' => ['GB', 'DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'AT', 'CH', 'IE', 'PT', 'SE', 'DK', 'FI', 'NO'],
            'description' => 'European countries - Standard delivery 7-14 business days',
            'is_active' => true,
            'sort_order' => 2
        ]);

        $asiaZone = ShippingZone::create([
            'name' => 'Asia Pacific',
            'countries' => ['VN', 'TH', 'SG', 'MY', 'PH', 'ID', 'JP', 'KR', 'CN', 'HK', 'TW', 'AU', 'NZ'],
            'description' => 'Asia-Pacific region - Standard delivery 10-20 business days',
            'is_active' => true,
            'sort_order' => 3
        ]);

        $canadaZone = ShippingZone::create([
            'name' => 'Canada',
            'countries' => ['CA'],
            'description' => 'Shipping to Canada - Standard delivery 7-10 business days',
            'is_active' => true,
            'sort_order' => 4
        ]);

        // Lấy categories để tạo rates (giả sử đã có categories)
        $tshirtCategory = Category::where('name', 'like', '%T-Shirt%')->orWhere('name', 'like', '%Áo%')->first();
        $hoodieCategory = Category::where('name', 'like', '%Hoodie%')->orWhere('name', 'like', '%Áo khoác%')->first();

        // === USA SHIPPING RATES ===

        // T-Shirts - USA (first_item_cost includes shipping + label fee)
        if ($tshirtCategory) {
            ShippingRate::create([
                'shipping_zone_id' => $usaZone->id,
                'category_id' => $tshirtCategory->id,
                'name' => 'Standard - T-Shirts (USA)',
                'description' => 'Lightweight apparel shipping within USA',
                'first_item_cost' => 6.50,      // $5.00 shipping + $1.50 label = $6.50
                'additional_item_cost' => 2.00,
                'is_active' => true,
                'sort_order' => 1
            ]);
        }

        // Hoodies - USA (first_item_cost includes shipping + label fee)
        if ($hoodieCategory) {
            ShippingRate::create([
                'shipping_zone_id' => $usaZone->id,
                'category_id' => $hoodieCategory->id,
                'name' => 'Standard - Hoodies (USA)',
                'description' => 'Heavy apparel shipping within USA',
                'first_item_cost' => 10.00,     // $8.00 shipping + $2.00 label = $10.00
                'additional_item_cost' => 3.50,
                'is_active' => true,
                'sort_order' => 1
            ]);
        }

        // General - USA (first_item_cost includes shipping + label fee)
        ShippingRate::create([
            'shipping_zone_id' => $usaZone->id,
            'category_id' => null,
            'name' => 'Standard - General Products (USA)',
            'description' => 'General shipping rate for other products',
            'first_item_cost' => 8.25,          // $6.50 shipping + $1.75 label = $8.25
            'additional_item_cost' => 2.50,
            'is_active' => true,
            'sort_order' => 999
        ]);

        // === EUROPE SHIPPING RATES ===

        // T-Shirts - Europe (first_item_cost includes shipping + label fee)
        if ($tshirtCategory) {
            ShippingRate::create([
                'shipping_zone_id' => $europeZone->id,
                'category_id' => $tshirtCategory->id,
                'name' => 'Standard - T-Shirts (Europe)',
                'description' => 'Lightweight apparel shipping to Europe',
                'first_item_cost' => 15.00,     // $12.00 shipping + $3.00 label = $15.00
                'additional_item_cost' => 4.00,
                'is_active' => true,
                'sort_order' => 1
            ]);
        }

        // Hoodies - Europe (first_item_cost includes shipping + label fee)
        if ($hoodieCategory) {
            ShippingRate::create([
                'shipping_zone_id' => $europeZone->id,
                'category_id' => $hoodieCategory->id,
                'name' => 'Standard - Hoodies (Europe)',
                'description' => 'Heavy apparel shipping to Europe',
                'first_item_cost' => 22.00,     // $18.00 shipping + $4.00 label = $22.00
                'additional_item_cost' => 6.00,
                'is_active' => true,
                'sort_order' => 1
            ]);
        }

        // General - Europe (first_item_cost includes shipping + label fee)
        ShippingRate::create([
            'shipping_zone_id' => $europeZone->id,
            'category_id' => null,
            'name' => 'Standard - General Products (Europe)',
            'description' => 'General shipping rate for other products',
            'first_item_cost' => 18.50,         // $15.00 shipping + $3.50 label = $18.50
            'additional_item_cost' => 5.00,
            'is_active' => true,
            'sort_order' => 999
        ]);

        // === ASIA PACIFIC SHIPPING RATES ===

        // T-Shirts - Asia (first_item_cost includes shipping + label fee)
        if ($tshirtCategory) {
            ShippingRate::create([
                'shipping_zone_id' => $asiaZone->id,
                'category_id' => $tshirtCategory->id,
                'name' => 'Standard - T-Shirts (Asia)',
                'description' => 'Lightweight apparel shipping to Asia-Pacific',
                'first_item_cost' => 12.50,     // $10.00 shipping + $2.50 label = $12.50
                'additional_item_cost' => 3.50,
                'is_active' => true,
                'sort_order' => 1
            ]);
        }

        // Hoodies - Asia (first_item_cost includes shipping + label fee)
        if ($hoodieCategory) {
            ShippingRate::create([
                'shipping_zone_id' => $asiaZone->id,
                'category_id' => $hoodieCategory->id,
                'name' => 'Standard - Hoodies (Asia)',
                'description' => 'Heavy apparel shipping to Asia-Pacific',
                'first_item_cost' => 19.50,     // $16.00 shipping + $3.50 label = $19.50
                'additional_item_cost' => 5.50,
                'is_active' => true,
                'sort_order' => 1
            ]);
        }

        // General - Asia (first_item_cost includes shipping + label fee)
        ShippingRate::create([
            'shipping_zone_id' => $asiaZone->id,
            'category_id' => null,
            'name' => 'Standard - General Products (Asia)',
            'description' => 'General shipping rate for other products',
            'first_item_cost' => 16.00,         // $13.00 shipping + $3.00 label = $16.00
            'additional_item_cost' => 4.50,
            'is_active' => true,
            'sort_order' => 999
        ]);

        // === CANADA SHIPPING RATES ===

        // T-Shirts - Canada (first_item_cost includes shipping + label fee)
        if ($tshirtCategory) {
            ShippingRate::create([
                'shipping_zone_id' => $canadaZone->id,
                'category_id' => $tshirtCategory->id,
                'name' => 'Standard - T-Shirts (Canada)',
                'description' => 'Lightweight apparel shipping to Canada',
                'first_item_cost' => 9.00,      // $7.00 shipping + $2.00 label = $9.00
                'additional_item_cost' => 2.50,
                'is_active' => true,
                'sort_order' => 1
            ]);
        }

        // Hoodies - Canada (first_item_cost includes shipping + label fee)
        if ($hoodieCategory) {
            ShippingRate::create([
                'shipping_zone_id' => $canadaZone->id,
                'category_id' => $hoodieCategory->id,
                'name' => 'Standard - Hoodies (Canada)',
                'description' => 'Heavy apparel shipping to Canada',
                'first_item_cost' => 13.50,     // $11.00 shipping + $2.50 label = $13.50
                'additional_item_cost' => 4.50,
                'is_active' => true,
                'sort_order' => 1
            ]);
        }

        // General - Canada (first_item_cost includes shipping + label fee)
        ShippingRate::create([
            'shipping_zone_id' => $canadaZone->id,
            'category_id' => null,
            'name' => 'Standard - General Products (Canada)',
            'description' => 'General shipping rate for other products',
            'first_item_cost' => 11.25,         // $9.00 shipping + $2.25 label = $11.25
            'additional_item_cost' => 3.50,
            'is_active' => true,
            'sort_order' => 999
        ]);

        $this->command->info('Shipping zones and rates seeded successfully!');
        $this->command->info('Created ' . ShippingZone::count() . ' zones and ' . ShippingRate::count() . ' rates');
    }
}
