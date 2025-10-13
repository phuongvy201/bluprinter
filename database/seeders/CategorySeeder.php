<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo categories chính
        $clothing = Category::create([
            'name' => 'Clothing',
            'slug' => 'clothing',
            'description' => 'All types of clothing items'
        ]);

        $accessories = Category::create([
            'name' => 'Accessories',
            'slug' => 'accessories',
            'description' => 'Fashion accessories and jewelry'
        ]);

        $home = Category::create([
            'name' => 'Home & Living',
            'slug' => 'home-living',
            'description' => 'Home decoration and living items'
        ]);

        // Tạo subcategories
        Category::create([
            'name' => 'T-Shirts',
            'slug' => 't-shirts',
            'parent_id' => $clothing->id,
            'description' => 'Basic and graphic t-shirts'
        ]);

        Category::create([
            'name' => 'Hoodies',
            'slug' => 'hoodies',
            'parent_id' => $clothing->id,
            'description' => 'Comfortable hoodies and sweatshirts'
        ]);

        Category::create([
            'name' => 'Bags',
            'slug' => 'bags',
            'parent_id' => $accessories->id,
            'description' => 'Handbags, backpacks, and purses'
        ]);

        Category::create([
            'name' => 'Jewelry',
            'slug' => 'jewelry',
            'parent_id' => $accessories->id,
            'description' => 'Necklaces, rings, and bracelets'
        ]);

        Category::create([
            'name' => 'Decor',
            'slug' => 'decor',
            'parent_id' => $home->id,
            'description' => 'Home decoration items'
        ]);

        Category::create([
            'name' => 'Kitchen',
            'slug' => 'kitchen',
            'parent_id' => $home->id,
            'description' => 'Kitchen accessories and tools'
        ]);
    }
}
