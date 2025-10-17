<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Product Tips',
                'slug' => 'product-tips',
                'description' => 'Learn how to get the most out of your products',
                'color' => '#3B82F6',
                'icon' => '💡',
                'sort_order' => 1,
            ],
            [
                'name' => 'Design Inspiration',
                'slug' => 'design-inspiration',
                'description' => 'Creative ideas and design trends',
                'color' => '#8B5CF6',
                'icon' => '🎨',
                'sort_order' => 2,
            ],
            [
                'name' => 'News & Updates',
                'slug' => 'news-updates',
                'description' => 'Latest news and platform updates',
                'color' => '#10B981',
                'icon' => '📰',
                'sort_order' => 3,
            ],
            [
                'name' => 'Success Stories',
                'slug' => 'success-stories',
                'description' => 'Inspiring stories from our sellers',
                'color' => '#F59E0B',
                'icon' => '⭐',
                'sort_order' => 4,
            ],
            [
                'name' => 'Tutorials',
                'slug' => 'tutorials',
                'description' => 'Step-by-step guides and how-tos',
                'color' => '#EF4444',
                'icon' => '📚',
                'sort_order' => 5,
            ],
            [
                'name' => 'Community',
                'slug' => 'community',
                'description' => 'Stories and tips from our community',
                'color' => '#EC4899',
                'icon' => '👥',
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\PostCategory::create($category);
        }
    }
}
