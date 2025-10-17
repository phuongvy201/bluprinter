<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Customization', 'slug' => 'customization', 'color' => '#3B82F6'],
            ['name' => 'Print-on-Demand', 'slug' => 'print-on-demand', 'color' => '#8B5CF6'],
            ['name' => 'E-commerce', 'slug' => 'ecommerce', 'color' => '#10B981'],
            ['name' => 'Marketing', 'slug' => 'marketing', 'color' => '#F59E0B'],
            ['name' => 'Business Tips', 'slug' => 'business-tips', 'color' => '#EF4444'],
            ['name' => 'Design', 'slug' => 'design', 'color' => '#EC4899'],
            ['name' => 'Trending', 'slug' => 'trending', 'color' => '#6366F1'],
            ['name' => 'Beginner Guide', 'slug' => 'beginner-guide', 'color' => '#14B8A6'],
            ['name' => 'Advanced', 'slug' => 'advanced', 'color' => '#F97316'],
            ['name' => 'Case Study', 'slug' => 'case-study', 'color' => '#84CC16'],
            ['name' => 'How To', 'slug' => 'how-to', 'color' => '#06B6D4'],
            ['name' => 'Best Practices', 'slug' => 'best-practices', 'color' => '#A855F7'],
            ['name' => 'Inspiration', 'slug' => 'inspiration', 'color' => '#F43F5E'],
            ['name' => 'Tools', 'slug' => 'tools', 'color' => '#0EA5E9'],
            ['name' => 'Tips & Tricks', 'slug' => 'tips-tricks', 'color' => '#22C55E'],
        ];

        foreach ($tags as $tag) {
            \App\Models\PostTag::create($tag);
        }
    }
}
