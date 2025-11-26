<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy tất cả sản phẩm
        $products = Product::all();

        // Mẫu review texts bằng tiếng Anh
        $reviewTexts = [
            'Great product! Exactly as described. Fast shipping and excellent quality.',
            'Love it! The quality is amazing and it arrived quickly.',
            'Perfect! The design is exactly what I wanted.',
            'Excellent quality, fast delivery. Highly recommend!',
            'Very satisfied with this purchase. Will definitely order again.',
            'Great product, excellent customer service. Thank you!',
            'Exactly as pictured. Very happy with this purchase.',
            'Amazing quality and super fast shipping. Love it!',
            'Perfect fit and great quality. Highly recommend this seller.',
            'Beautiful product, exactly as described. Very pleased!',
            'Outstanding quality and service. Will definitely buy again!',
            'Great item, fast shipping, and excellent communication.',
            'Love it! Great quality and arrived exactly as described.',
            'Perfect! High quality product and excellent service.',
            'Amazing product! Fast delivery and great communication.',
            'Excellent quality, exactly as described. Highly satisfied!',
            'Great product and service. Will definitely order again.',
            'Love it! Perfect quality and fast shipping.',
            'Outstanding product and service. Highly recommend!',
            'Excellent purchase! Great quality and fast delivery.',
            'Perfect item! Exactly as described and fast shipping.',
            'Love this product! Great quality and excellent service.',
            'Amazing quality and fast delivery. Very satisfied!',
            'Great purchase! Excellent product and service.',
            'Perfect! Great quality and exactly as described.',
        ];

        // Mẫu tên khách hàng
        $customerNames = [
            'Alex Johnson',
            'Sarah Williams',
            'Michael Brown',
            'Emily Davis',
            'James Wilson',
            'Jessica Miller',
            'David Garcia',
            'Ashley Rodriguez',
            'Christopher Martinez',
            'Amanda Anderson',
            'Matthew Taylor',
            'Jennifer Thomas',
            'Daniel Jackson',
            'Michelle White',
            'Joshua Harris',
            'Kimberly Martin',
            'Andrew Thompson',
            'Stephanie Garcia',
            'Kevin Martinez',
            'Nicole Robinson',
            'Ryan Clark',
            'Heather Rodriguez',
            'Jacob Lewis',
            'Samantha Lee',
            'Brandon Walker',
            'Rebecca Hall',
            'Tyler Allen',
            'Megan Young',
            'Jonathan King',
            'Lauren Wright',
            'Samuel Lopez',
            'Brittany Hill',
            'Zachary Scott',
            'Rachel Green',
            'Nathan Adams',
            'Kayla Baker'
        ];

        $this->command->info('Creating fake reviews for all products...');

        foreach ($products as $product) {
            // Tạo từ 3-8 reviews cho mỗi sản phẩm
            $reviewCount = rand(3, 8);

            for ($i = 0; $i < $reviewCount; $i++) {
                // Rating từ 3-5 (chủ yếu là positive reviews)
                // 80% chance rating 4-5, 20% chance rating 3
                $ratingChance = rand(1, 100);
                if ($ratingChance <= 80) {
                    $rating = rand(4, 5);
                } else {
                    $rating = 3;
                }

                // 70% chance có review text, 30% chỉ có rating
                $hasReviewText = rand(1, 100) <= 70;

                // Random customer name
                $customerName = $customerNames[array_rand($customerNames)];

                // Generate random email
                $emailPrefix = strtolower(str_replace(' ', '.', $customerName)) . rand(100, 999);
                $customerEmail = $emailPrefix . '@example.com';

                // 75% verified purchase
                $isVerifiedPurchase = rand(1, 100) <= 75;

                // Random date trong 6 tháng qua
                $daysAgo = rand(0, 180);
                $createdAt = Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59));

                Review::create([
                    'product_id' => $product->id,
                    'user_id' => null, // Anonymous reviews
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'rating' => $rating,
                    'review_text' => $hasReviewText ? $reviewTexts[array_rand($reviewTexts)] : null,
                    'is_verified_purchase' => $isVerifiedPurchase,
                    'is_approved' => true,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $this->command->info("Created {$reviewCount} reviews for product: {$product->name}");
        }

        $this->command->info('Review seeding completed successfully!');
    }
}
