<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantSeeder extends Seeder
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

        foreach ($products as $product) {
            // Create variants for each product
            $variants = [
                // Size S variants
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
                    'variant_name' => 'Small White',
                    'attributes' => ['Size' => 'S', 'Color' => 'White'],
                    'sku' => $product->slug . '-s-white',
                    'price' => $product->base_price,
                    'quantity' => 15,
                    'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/white_small.jpg']
                ],
                [
                    'template_id' => $product->template_id,
                    'product_id' => $product->id,
                    'variant_name' => 'Small Red',
                    'attributes' => ['Size' => 'S', 'Color' => 'Red'],
                    'sku' => $product->slug . '-s-red',
                    'price' => $product->base_price + 2.00,
                    'quantity' => 10,
                    'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/red_small.jpg']
                ],
                // Size M variants
                [
                    'template_id' => $product->template_id,
                    'product_id' => $product->id,
                    'variant_name' => 'Medium Black',
                    'attributes' => ['Size' => 'M', 'Color' => 'Black'],
                    'sku' => $product->slug . '-m-black',
                    'price' => $product->base_price,
                    'quantity' => 30,
                    'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/black_medium.jpg']
                ],
                [
                    'template_id' => $product->template_id,
                    'product_id' => $product->id,
                    'variant_name' => 'Medium White',
                    'attributes' => ['Size' => 'M', 'Color' => 'White'],
                    'sku' => $product->slug . '-m-white',
                    'price' => $product->base_price,
                    'quantity' => 22,
                    'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/white_medium.jpg']
                ],
                [
                    'template_id' => $product->template_id,
                    'product_id' => $product->id,
                    'variant_name' => 'Medium Red',
                    'attributes' => ['Size' => 'M', 'Color' => 'Red'],
                    'sku' => $product->slug . '-m-red',
                    'price' => $product->base_price + 2.00,
                    'quantity' => 12,
                    'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/red_medium.jpg']
                ],
                // Size L variants
                [
                    'template_id' => $product->template_id,
                    'product_id' => $product->id,
                    'variant_name' => 'Large Black',
                    'attributes' => ['Size' => 'L', 'Color' => 'Black'],
                    'sku' => $product->slug . '-l-black',
                    'price' => $product->base_price,
                    'quantity' => 20,
                    'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/black_large.jpg']
                ],
                [
                    'template_id' => $product->template_id,
                    'product_id' => $product->id,
                    'variant_name' => 'Large White',
                    'attributes' => ['Size' => 'L', 'Color' => 'White'],
                    'sku' => $product->slug . '-l-white',
                    'price' => $product->base_price,
                    'quantity' => 18,
                    'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/white_large.jpg']
                ],
                [
                    'template_id' => $product->template_id,
                    'product_id' => $product->id,
                    'variant_name' => 'Large Blue',
                    'attributes' => ['Size' => 'L', 'Color' => 'Blue'],
                    'sku' => $product->slug . '-l-blue',
                    'price' => $product->base_price + 1.50,
                    'quantity' => 8,
                    'media' => ['https://s3.us-east-1.amazonaws.com/image.bluprinter/variants/blue_large.jpg']
                ]
            ];

            foreach ($variants as $variantData) {
                ProductVariant::create($variantData);
            }

            $this->command->info("Created variants for product: {$product->name}");
        }

        $this->command->info('Product variants seeded successfully!');
    }
}
