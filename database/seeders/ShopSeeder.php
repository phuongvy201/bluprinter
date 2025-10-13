<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test shops
        $shops = [
            [
                'user_id' => 1, // Assuming user with ID 1 exists
                'shop_name' => 'Disappearance Unmanned',
                'shop_slug' => 'disappearance-unmanned',
                'shop_description' => 'A creative shop specializing in unique designs and custom products.',
                'shop_logo' => null,
                'shop_banner' => null,
                'shop_phone' => '+1 234 567 8900',
                'shop_email' => 'contact@disappearance-unmanned.com',
                'shop_address' => '123 Creative Street',
                'shop_city' => 'New York',
                'shop_country' => 'USA',
                'shop_status' => 'active',
                'verified' => true,
                'rating' => 4.8,
                'total_ratings' => 125,
                'total_products' => 0,
                'total_sales' => 342,
                'total_revenue' => 15420.50,
            ],
            [
                'user_id' => 2, // Assuming user with ID 2 exists
                'shop_name' => 'Artisan Creations',
                'shop_slug' => 'artisan-creations',
                'shop_description' => 'Handcrafted items and personalized gifts for every occasion.',
                'shop_logo' => null,
                'shop_banner' => null,
                'shop_phone' => '+1 555 123 4567',
                'shop_email' => 'hello@artisan-creations.com',
                'shop_address' => '456 Craft Avenue',
                'shop_city' => 'Los Angeles',
                'shop_country' => 'USA',
                'shop_status' => 'active',
                'verified' => true,
                'rating' => 4.6,
                'total_ratings' => 89,
                'total_products' => 0,
                'total_sales' => 267,
                'total_revenue' => 8930.75,
            ],
            [
                'user_id' => 3, // Assuming user with ID 3 exists
                'shop_name' => 'Digital Dreams',
                'shop_slug' => 'digital-dreams',
                'shop_description' => 'Modern digital art and tech-inspired merchandise.',
                'shop_logo' => null,
                'shop_banner' => null,
                'shop_phone' => '+1 888 999 0000',
                'shop_email' => 'info@digital-dreams.com',
                'shop_address' => '789 Tech Boulevard',
                'shop_city' => 'San Francisco',
                'shop_country' => 'USA',
                'shop_status' => 'active',
                'verified' => false,
                'rating' => 4.4,
                'total_ratings' => 56,
                'total_products' => 0,
                'total_sales' => 189,
                'total_revenue' => 5670.25,
            ]
        ];

        foreach ($shops as $shopData) {
            Shop::create($shopData);
        }
    }
}
