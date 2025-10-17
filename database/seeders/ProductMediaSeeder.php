<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductMediaSeeder extends Seeder
{
    public function run()
    {
        $products = Product::all();
        $imageUrls = [
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=400&fit=crop',
            'https://images.unsplash.com/photo-1512389142860-9c449e58a543?w=400&h=400&fit=crop',
            'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop',
            'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=400&fit=crop',
            'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=400&h=400&fit=crop',
            'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=400&h=400&fit=crop',
            'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=400&h=400&fit=crop',
            'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=400&fit=crop'
        ];

        foreach ($products as $index => $product) {
            try {
                $product->addMediaFromUrl($imageUrls[$index % count($imageUrls)])
                    ->toMediaCollection('images');
            } catch (\Exception $e) {
                // Skip if media library is not properly configured
                continue;
            }
        }

        echo "Added media to products successfully!\n";
    }
}
