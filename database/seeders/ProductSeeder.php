<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\ProductTemplate;
use App\Models\Product;
use App\Models\Shop;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Get or create categories
        $halloweenCategory = Category::updateOrCreate(['slug' => 'halloween'], [
            'name' => 'Halloween',
            'slug' => 'halloween',
            'description' => 'Spooky Halloween products',
            'featured' => true,
            'sort_order' => 1
        ]);

        $christmasCategory = Category::updateOrCreate(['slug' => 'christmas'], [
            'name' => 'Christmas',
            'slug' => 'christmas',
            'description' => 'Festive Christmas products',
            'featured' => true,
            'sort_order' => 2
        ]);

        $holidayCategory = Category::updateOrCreate(['slug' => 'holiday'], [
            'name' => 'Holiday',
            'slug' => 'holiday',
            'description' => 'Holiday themed products',
            'featured' => true,
            'sort_order' => 3
        ]);

        // Get shop
        $shop = Shop::first();

        // Create product templates
        $templates = [
            [
                'name' => 'Halloween Hoodie',
                'category_id' => $halloweenCategory->id,
                'description' => 'Spooky Halloween hoodie design',
                'base_price' => 49.99
            ],
            [
                'name' => 'Christmas Sweater',
                'category_id' => $christmasCategory->id,
                'description' => 'Festive Christmas sweater design',
                'base_price' => 39.99
            ],
            [
                'name' => 'Holiday Mug',
                'category_id' => $holidayCategory->id,
                'description' => 'Holiday themed mug design',
                'base_price' => 19.99
            ],
            [
                'name' => 'Halloween T-Shirt',
                'category_id' => $halloweenCategory->id,
                'description' => 'Spooky Halloween t-shirt design',
                'base_price' => 24.99
            ],
            [
                'name' => 'Christmas Ornament',
                'category_id' => $christmasCategory->id,
                'description' => 'Beautiful Christmas ornament design',
                'base_price' => 14.99
            ]
        ];

        foreach ($templates as $templateData) {
            ProductTemplate::updateOrCreate(
                ['name' => $templateData['name']],
                $templateData
            );
        }

        // Create products
        $products = [
            [
                'name' => 'Spooky Halloween Hoodie',
                'slug' => 'spooky-halloween-hoodie',
                'template_id' => ProductTemplate::where('name', 'Halloween Hoodie')->first()->id,
                'shop_id' => $shop->id,
                'price' => 49.99,
                'status' => 'active'
            ],
            [
                'name' => 'Festive Christmas Sweater',
                'slug' => 'festive-christmas-sweater',
                'template_id' => ProductTemplate::where('name', 'Christmas Sweater')->first()->id,
                'shop_id' => $shop->id,
                'price' => 39.99,
                'status' => 'active'
            ],
            [
                'name' => 'Holiday Coffee Mug',
                'slug' => 'holiday-coffee-mug',
                'template_id' => ProductTemplate::where('name', 'Holiday Mug')->first()->id,
                'shop_id' => $shop->id,
                'price' => 19.99,
                'status' => 'active'
            ],
            [
                'name' => 'Halloween T-Shirt Design',
                'slug' => 'halloween-t-shirt-design',
                'template_id' => ProductTemplate::where('name', 'Halloween T-Shirt')->first()->id,
                'shop_id' => $shop->id,
                'price' => 24.99,
                'status' => 'active'
            ],
            [
                'name' => 'Christmas Tree Ornament',
                'slug' => 'christmas-tree-ornament',
                'template_id' => ProductTemplate::where('name', 'Christmas Ornament')->first()->id,
                'shop_id' => $shop->id,
                'price' => 14.99,
                'status' => 'active'
            ],
            [
                'name' => 'Spooky Ghost Hoodie',
                'slug' => 'spooky-ghost-hoodie',
                'template_id' => ProductTemplate::where('name', 'Halloween Hoodie')->first()->id,
                'shop_id' => $shop->id,
                'price' => 54.99,
                'status' => 'active'
            ],
            [
                'name' => 'Santa Claus Sweater',
                'slug' => 'santa-claus-sweater',
                'template_id' => ProductTemplate::where('name', 'Christmas Sweater')->first()->id,
                'shop_id' => $shop->id,
                'price' => 44.99,
                'status' => 'active'
            ],
            [
                'name' => 'Holiday Gift Mug',
                'slug' => 'holiday-gift-mug',
                'template_id' => ProductTemplate::where('name', 'Holiday Mug')->first()->id,
                'shop_id' => $shop->id,
                'price' => 22.99,
                'status' => 'active'
            ]
        ];

        foreach ($products as $productData) {
            Product::updateOrCreate(
                ['name' => $productData['name']],
                $productData
            );
        }

        echo "Products created successfully!\n";
    }
}
