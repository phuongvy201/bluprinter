<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;

class FlexibleVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->info('No products found. Please run ProductSeeder first.');
            return;
        }

        // Clear existing variants
        ProductVariant::truncate();

        foreach ($products as $product) {
            // Example 1: Clothing with Size, Color, Material
            if (str_contains(strtolower($product->name), 'shirt') || str_contains(strtolower($product->name), 'tee')) {
                $variants = [
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'Small Black Cotton',
                        'attributes' => ['Size' => 'S', 'Color' => 'Black', 'Material' => 'Cotton'],
                        'sku' => $product->slug . '-s-black-cotton',
                        'price' => $product->base_price,
                        'quantity' => 25,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/black_small_cotton.jpg']
                    ],
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'Medium Black Cotton',
                        'attributes' => ['Size' => 'M', 'Color' => 'Black', 'Material' => 'Cotton'],
                        'sku' => $product->slug . '-m-black-cotton',
                        'price' => $product->base_price,
                        'quantity' => 30,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/black_medium_cotton.jpg']
                    ],
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'Large White Cotton',
                        'attributes' => ['Size' => 'L', 'Color' => 'White', 'Material' => 'Cotton'],
                        'sku' => $product->slug . '-l-white-cotton',
                        'price' => $product->base_price,
                        'quantity' => 20,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/white_large_cotton.jpg']
                    ],
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'Medium Red Polyester',
                        'attributes' => ['Size' => 'M', 'Color' => 'Red', 'Material' => 'Polyester'],
                        'sku' => $product->slug . '-m-red-polyester',
                        'price' => $product->base_price + 3.00,
                        'quantity' => 15,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/red_medium_polyester.jpg']
                    ]
                ];
            }
            // Example 2: Mug with Size, Color, Finish
            elseif (str_contains(strtolower($product->name), 'mug') || str_contains(strtolower($product->name), 'cup')) {
                $variants = [
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'Small White Glossy',
                        'attributes' => ['Size' => '11oz', 'Color' => 'White', 'Finish' => 'Glossy'],
                        'sku' => $product->slug . '-11oz-white-glossy',
                        'price' => $product->base_price,
                        'quantity' => 50,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/white_11oz_glossy.jpg']
                    ],
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'Large Black Matte',
                        'attributes' => ['Size' => '15oz', 'Color' => 'Black', 'Finish' => 'Matte'],
                        'sku' => $product->slug . '-15oz-black-matte',
                        'price' => $product->base_price + 2.00,
                        'quantity' => 30,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/black_15oz_matte.jpg']
                    ],
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'Medium Blue Glossy',
                        'attributes' => ['Size' => '12oz', 'Color' => 'Blue', 'Finish' => 'Glossy'],
                        'sku' => $product->slug . '-12oz-blue-glossy',
                        'price' => $product->base_price + 1.50,
                        'quantity' => 40,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/blue_12oz_glossy.jpg']
                    ]
                ];
            }
            // Example 3: Phone case with Model, Color, Protection Level
            elseif (str_contains(strtolower($product->name), 'phone') || str_contains(strtolower($product->name), 'case')) {
                $variants = [
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'iPhone 14 Black Basic',
                        'attributes' => ['Model' => 'iPhone 14', 'Color' => 'Black', 'Protection' => 'Basic'],
                        'sku' => $product->slug . '-iphone14-black-basic',
                        'price' => $product->base_price,
                        'quantity' => 25,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/iphone14_black_basic.jpg']
                    ],
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'iPhone 14 White Premium',
                        'attributes' => ['Model' => 'iPhone 14', 'Color' => 'White', 'Protection' => 'Premium'],
                        'sku' => $product->slug . '-iphone14-white-premium',
                        'price' => $product->base_price + 5.00,
                        'quantity' => 20,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/iphone14_white_premium.jpg']
                    ],
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'Samsung S23 Black Premium',
                        'attributes' => ['Model' => 'Samsung S23', 'Color' => 'Black', 'Protection' => 'Premium'],
                        'sku' => $product->slug . '-samsung-s23-black-premium',
                        'price' => $product->base_price + 5.00,
                        'quantity' => 15,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/samsung_s23_black_premium.jpg']
                    ]
                ];
            }
            // Default: Simple Size and Color
            else {
                $variants = [
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'Small Black',
                        'attributes' => ['Size' => 'S', 'Color' => 'Black'],
                        'sku' => $product->slug . '-s-black',
                        'price' => $product->base_price,
                        'quantity' => 25,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/black_small.jpg']
                    ],
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'Medium White',
                        'attributes' => ['Size' => 'M', 'Color' => 'White'],
                        'sku' => $product->slug . '-m-white',
                        'price' => $product->base_price,
                        'quantity' => 30,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/white_medium.jpg']
                    ],
                    [
                        'template_id' => $product->template_id,
                        'product_id' => $product->id,
                        'variant_name' => 'Large Red',
                        'attributes' => ['Size' => 'L', 'Color' => 'Red'],
                        'sku' => $product->slug . '-l-red',
                        'price' => $product->base_price + 2.00,
                        'quantity' => 20,
                        'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/red_large.jpg']
                    ]
                ];
            }

            foreach ($variants as $variantData) {
                ProductVariant::create($variantData);
            }

            $this->command->info("Created flexible variants for product: {$product->name}");
        }

        $this->command->info('Flexible variants seeded successfully!');
    }
}











