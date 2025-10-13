<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductTemplate;
use App\Models\Category;
use App\Models\TemplateAttribute;

class ProductTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->info('No categories found. Please run CategorySeeder first.');
            return;
        }

        $templates = [
            [
                'name' => 'Classic T-Shirt Template',
                'category_id' => $categories->where('name', 'like', '%clothing%')->first()?->id ?? $categories->first()->id,
                'base_price' => 19.99,
                'description' => 'A versatile basic t-shirt template perfect for custom designs and branding.',
                'media' => [
                    'https://s3.us-east-1.amazonaws.com/image.bluprinter/templates/tshirt_front.jpg',
                    'https://s3.us-east-1.amazonaws.com/image.bluprinter/templates/tshirt_back.jpg'
                ],
                'attributes' => [
                    ['name' => 'Material', 'value' => '100% Cotton'],
                    ['name' => 'Weight', 'value' => '180 GSM'],
                    ['name' => 'Fit', 'value' => 'Regular Fit'],
                    ['name' => 'Care Instructions', 'value' => 'Machine wash cold, tumble dry low'],
                    ['name' => 'Brand', 'value' => 'Bluprinter']
                ]
            ],
            [
                'name' => 'Premium Hoodie Template',
                'category_id' => $categories->where('name', 'like', '%clothing%')->first()?->id ?? $categories->first()->id,
                'base_price' => 45.99,
                'description' => 'High-quality hoodie template with premium materials and modern design.',
                'media' => [
                    'https://s3.us-east-1.amazonaws.com/image.bluprinter/templates/hoodie_front.jpg',
                    'https://s3.us-east-1.amazonaws.com/image.bluprinter/templates/hoodie_detail.jpg'
                ],
                'attributes' => [
                    ['name' => 'Material', 'value' => '80% Cotton, 20% Polyester'],
                    ['name' => 'Weight', 'value' => '280 GSM'],
                    ['name' => 'Fit', 'value' => 'Relaxed Fit'],
                    ['name' => 'Features', 'value' => 'Kangaroo pocket, drawstring hood'],
                    ['name' => 'Care Instructions', 'value' => 'Machine wash cold, do not bleach'],
                    ['name' => 'Brand', 'value' => 'Bluprinter']
                ]
            ],
            [
                'name' => 'Coffee Mug Template',
                'category_id' => $categories->where('name', 'like', '%accessories%')->first()?->id ?? $categories->first()->id,
                'base_price' => 12.99,
                'description' => 'Ceramic coffee mug perfect for custom printing and personalization.',
                'media' => [
                    'https://s3.us-east-1.amazonaws.com/image.bluprinter/templates/mug_front.jpg',
                    'https://s3.us-east-1.amazonaws.com/image.bluprinter/templates/mug_side.jpg'
                ],
                'attributes' => [
                    ['name' => 'Material', 'value' => 'High-quality Ceramic'],
                    ['name' => 'Capacity', 'value' => '11 oz (325ml)'],
                    ['name' => 'Dimensions', 'value' => '3.5" x 4.5"'],
                    ['name' => 'Features', 'value' => 'Dishwasher safe, microwave safe'],
                    ['name' => 'Print Area', 'value' => '8" x 3.5"'],
                    ['name' => 'Brand', 'value' => 'Bluprinter']
                ]
            ],
            [
                'name' => 'Laptop Sticker Template',
                'category_id' => $categories->where('name', 'like', '%accessories%')->first()?->id ?? $categories->first()->id,
                'base_price' => 3.99,
                'description' => 'Vinyl laptop sticker template for custom designs and branding.',
                'media' => [
                    'https://s3.us-east-1.amazonaws.com/image.bluprinter/templates/sticker_design.jpg'
                ],
                'attributes' => [
                    ['name' => 'Material', 'value' => 'Premium Vinyl'],
                    ['name' => 'Size', 'value' => '3" x 3"'],
                    ['name' => 'Finish', 'value' => 'Matte'],
                    ['name' => 'Durability', 'value' => 'Weather resistant, UV protected'],
                    ['name' => 'Application', 'value' => 'Easy peel and stick'],
                    ['name' => 'Brand', 'value' => 'Bluprinter']
                ]
            ],
            [
                'name' => 'Canvas Print Template',
                'category_id' => $categories->where('name', 'like', '%art%')->first()?->id ?? $categories->first()->id,
                'base_price' => 29.99,
                'description' => 'High-quality canvas print template for artwork and photography.',
                'media' => [
                    'https://s3.us-east-1.amazonaws.com/image.bluprinter/templates/canvas_sample.jpg'
                ],
                'attributes' => [
                    ['name' => 'Material', 'value' => 'Premium Canvas'],
                    ['name' => 'Size', 'value' => '12" x 16"'],
                    ['name' => 'Finish', 'value' => 'Matte'],
                    ['name' => 'Frame', 'value' => 'Gallery wrapped'],
                    ['name' => 'Quality', 'value' => 'Museum quality print'],
                    ['name' => 'Brand', 'value' => 'Bluprinter']
                ]
            ],
            [
                'name' => 'Phone Case Template',
                'category_id' => $categories->where('name', 'like', '%accessories%')->first()?->id ?? $categories->first()->id,
                'base_price' => 15.99,
                'description' => 'Protective phone case template with custom design options.',
                'media' => [
                    'https://s3.us-east-1.amazonaws.com/image.bluprinter/templates/phone_case.jpg'
                ],
                'attributes' => [
                    ['name' => 'Material', 'value' => 'Hard Plastic'],
                    ['name' => 'Compatibility', 'value' => 'iPhone 14 Pro'],
                    ['name' => 'Protection', 'value' => 'Drop protection up to 6ft'],
                    ['name' => 'Features', 'value' => 'Wireless charging compatible'],
                    ['name' => 'Design', 'value' => 'Full wrap coverage'],
                    ['name' => 'Brand', 'value' => 'Bluprinter']
                ]
            ]
        ];

        foreach ($templates as $templateData) {
            $attributes = $templateData['attributes'];
            unset($templateData['attributes']);

            $template = ProductTemplate::create($templateData);

            // Create template attributes
            foreach ($attributes as $attribute) {
                $template->attributes()->create([
                    'attribute_name' => $attribute['name'],
                    'attribute_value' => $attribute['value']
                ]);
            }

            $this->command->info("Created template: {$template->name}");
        }

        $this->command->info('Product templates seeded successfully!');
    }
}
