<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Custom clothing and apparel for every occasion',
                'featured' => true,
                'sort_order' => 1,
                'image' => 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=300&h=300&fit=crop&crop=center'
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Custom accessories and gear to complete your look',
                'featured' => true,
                'sort_order' => 2,
                'image' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=300&h=300&fit=crop&crop=center'
            ],
            [
                'name' => 'Home & Living',
                'slug' => 'home-living',
                'description' => 'Home decor and living essentials for your space',
                'featured' => true,
                'sort_order' => 3,
                'image' => 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=300&h=300&fit=crop&crop=center'
            ],
            [
                'name' => 'Luggage & Bags',
                'slug' => 'luggage-bags',
                'description' => 'Travel bags and luggage for your adventures',
                'featured' => true,
                'sort_order' => 4,
                'image' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=300&h=300&fit=crop&crop=center'
            ],
            [
                'name' => 'Gen X',
                'slug' => 'gen-x',
                'description' => 'Generation X themed products and designs',
                'featured' => true,
                'sort_order' => 5,
                'image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=300&h=300&fit=crop&crop=center'
            ],
            [
                'name' => 'Ornament',
                'slug' => 'ornament',
                'description' => 'Custom ornaments and decorations for special occasions',
                'featured' => true,
                'sort_order' => 6,
                'image' => 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=300&h=300&fit=crop&crop=center'
            ]
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Categories seeded successfully!');
    }
}
