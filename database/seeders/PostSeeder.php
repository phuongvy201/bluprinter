<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\PostCategory;

class PostSeeder extends Seeder
{
    public function run()
    {
        // Get first user
        $user = User::first();

        // Create post categories
        $categories = [
            ['name' => 'Design Tips', 'slug' => 'design-tips'],
            ['name' => 'Printing Guide', 'slug' => 'printing-guide'],
            ['name' => 'Business', 'slug' => 'business'],
            ['name' => 'Tutorials', 'slug' => 'tutorials']
        ];

        foreach ($categories as $categoryData) {
            PostCategory::updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }

        // Create posts
        $posts = [
            [
                'title' => '10 Design Tips for Better Print Quality',
                'slug' => '10-design-tips-better-print-quality',
                'content' => 'Learn the essential design principles that will make your printed materials look professional and eye-catching. From color theory to typography, we cover everything you need to know.',
                'excerpt' => 'Essential design principles for professional printed materials.',
                'featured_image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop',
                'status' => 'published',
                'user_id' => $user->id
            ],
            [
                'title' => 'How to Choose the Right Paper for Your Project',
                'slug' => 'choose-right-paper-project',
                'content' => 'Paper selection can make or break your print project. Discover the different types of paper, their characteristics, and when to use each one for optimal results.',
                'excerpt' => 'Complete guide to paper selection for print projects.',
                'featured_image' => 'https://images.unsplash.com/photo-1586281380349-632531db7ed4?w=800&h=600&fit=crop',
                'status' => 'published',
                'user_id' => $user->id
            ],
            [
                'title' => 'Color Psychology in Marketing Materials',
                'slug' => 'color-psychology-marketing-materials',
                'content' => 'Colors have a powerful impact on human psychology and can influence purchasing decisions. Learn how to use color effectively in your marketing materials.',
                'excerpt' => 'How colors influence customer behavior and purchasing decisions.',
                'featured_image' => 'https://images.unsplash.com/photo-1557683316-973673baf926?w=800&h=600&fit=crop',
                'status' => 'published',
                'user_id' => $user->id
            ],
            [
                'title' => 'Typography Best Practices for Print',
                'slug' => 'typography-best-practices-print',
                'content' => 'Good typography is crucial for readability and visual appeal. Discover the best practices for choosing and using fonts in your print materials.',
                'excerpt' => 'Essential typography guidelines for professional print materials.',
                'featured_image' => 'https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=800&h=600&fit=crop',
                'status' => 'published',
                'user_id' => $user->id
            ],
            [
                'title' => 'Sustainable Printing: Eco-Friendly Options',
                'slug' => 'sustainable-printing-eco-friendly',
                'content' => 'Learn about environmentally friendly printing options and how to make your print projects more sustainable without compromising quality.',
                'excerpt' => 'Environmentally conscious printing practices and materials.',
                'featured_image' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=800&h=600&fit=crop',
                'status' => 'published',
                'user_id' => $user->id
            ],
            [
                'title' => 'Creating Effective Business Cards',
                'slug' => 'creating-effective-business-cards',
                'content' => 'Your business card is often the first impression you make. Learn how to design business cards that stand out and effectively represent your brand.',
                'excerpt' => 'Design tips for memorable and effective business cards.',
                'featured_image' => 'https://images.unsplash.com/photo-1607082349566-187342175e2f?w=800&h=600&fit=crop',
                'status' => 'published',
                'user_id' => $user->id
            ]
        ];

        foreach ($posts as $postData) {
            Post::updateOrCreate(
                ['slug' => $postData['slug']],
                $postData
            );
        }

        echo "Posts created successfully!\n";
    }
}
