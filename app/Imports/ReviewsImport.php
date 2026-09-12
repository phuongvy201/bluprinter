<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Review;
use App\Services\ReviewRatingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ReviewsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected int $successCount = 0;

    /** @var array<int, string> */
    protected array $errors = [];

    /** @var array<int, int> */
    protected array $affectedShopIds = [];

    public function __construct(
        protected $user,
        protected ?ReviewRatingService $reviewRatingService = null
    ) {
        $this->reviewRatingService = $reviewRatingService ?? app(ReviewRatingService::class);
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;

            try {
                $productId = trim((string) ($row['product_id'] ?? ''));
                $productSlug = trim((string) ($row['product_slug'] ?? ''));
                $customerName = trim((string) ($row['customer_name'] ?? ''));

                if ($productId === '' && $productSlug === '' && $customerName === '') {
                    continue;
                }

                if ($customerName === '') {
                    $this->errors[] = "Row {$line}: customer_name is required.";
                    continue;
                }

                $productQuery = Product::query();
                if ($this->user->hasRole('seller') && $this->user->shop) {
                    $productQuery->where('shop_id', $this->user->shop->id);
                }

                if ($productId !== '') {
                    $product = $productQuery->whereKey((int) $productId)->first();
                } elseif ($productSlug !== '') {
                    $product = $productQuery->where('slug', $productSlug)->first();
                } else {
                    $this->errors[] = "Row {$line}: product_id or product_slug is required.";
                    continue;
                }

                if (!$product) {
                    $this->errors[] = "Row {$line}: product not found or not accessible.";
                    continue;
                }

                $rating = (int) ($row['rating'] ?? 0);
                if ($rating < 1 || $rating > 5) {
                    $this->errors[] = "Row {$line}: rating must be between 1 and 5.";
                    continue;
                }

                $images = [];
                foreach (['image_1', 'image_2', 'image_3', 'image_4', 'image_5'] as $imageColumn) {
                    $imageUrl = trim((string) ($row[$imageColumn] ?? ''));
                    if ($imageUrl !== '') {
                        $images[] = $imageUrl;
                    }
                }

                $reviewDate = null;
                $rawDate = trim((string) ($row['review_date'] ?? ''));
                if ($rawDate !== '') {
                    try {
                        $reviewDate = Carbon::parse($rawDate);
                    } catch (\Throwable) {
                        $this->errors[] = "Row {$line}: invalid review_date.";
                        continue;
                    }
                }

                $isVerified = $this->parseBool($row['is_verified_purchase'] ?? false);
                $isApproved = $this->parseBool($row['is_approved'] ?? true);

                Review::create([
                    'product_id' => $product->id,
                    'user_id' => null,
                    'customer_name' => $customerName,
                    'customer_email' => trim((string) ($row['customer_email'] ?? '')) ?: null,
                    'rating' => $rating,
                    'review_text' => trim((string) ($row['review_text'] ?? '')) ?: null,
                    'images' => $images,
                    'is_verified_purchase' => $isVerified,
                    'is_approved' => $isApproved,
                    'created_at' => $reviewDate ?? now(),
                    'updated_at' => $reviewDate ?? now(),
                ]);

                if ($isApproved && $product->shop_id) {
                    $this->affectedShopIds[$product->shop_id] = $product->shop_id;
                }

                $this->successCount++;
            } catch (\Throwable $e) {
                $this->errors[] = "Row {$line}: {$e->getMessage()}";
            }
        }

        foreach ($this->affectedShopIds as $shopId) {
            $shop = \App\Models\Shop::query()->find($shopId);
            if ($shop) {
                $this->reviewRatingService->syncShopRating($shop);
            }
        }
    }

    protected function parseBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 'yes', 'y'], true);
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
