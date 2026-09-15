<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Collection;
use App\Models\Review;
use App\Http\Controllers\Admin\HomeSettingsController;
use App\Services\FlashDealService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(FlashDealService $flashDealService)
    {
        $flashDeals = $flashDealService->getHomepageDeals();
        $flashDealMaxDiscount = $flashDealService->maxDiscountFromDeals($flashDeals);
        $flashSaleEndsAt = $flashDealService->earliestEndsAt($flashDeals);

        // Top Pick for You — bento 4 cột: banner dọc + 6 SP + banner ngang + 2 SP + hàng 4 SP
        $topPickBaseQuery = Product::query()
            ->with(['template.category', 'shop'])
            ->withSum('orderItems as order_items_sum_quantity', 'quantity')
            ->withAvg('approvedReviews as approved_reviews_avg_rating', 'rating')
            ->withCount('approvedReviews');

        $topPickPool = (clone $topPickBaseQuery)
            ->availableForDisplay()
            ->join('product_templates', 'products.template_id', '=', 'product_templates.id')
            ->select('products.*')
            ->orderByRaw('CASE WHEN product_templates.base_price > products.price THEN 0 ELSE 1 END')
            ->inRandomOrder()
            ->limit(14)
            ->get();

        if ($topPickPool->isEmpty()) {
            $topPickPool = (clone $topPickBaseQuery)
                ->where('products.status', 'active')
                ->inRandomOrder()
                ->limit(14)
                ->get();
        }

        if ($topPickPool->isEmpty()) {
            $topPickPool = (clone $topPickBaseQuery)
                ->inRandomOrder()
                ->limit(14)
                ->get();
        }

        $topPickBentoSmall = $topPickPool->slice(0, 6)->values();
        $topPickRowThree = $topPickPool->slice(6, 2)->values();
        $topPickMore = $topPickPool->slice(8, 4)->values();

        $topPickPromoCollection = Collection::query()
            ->global()
            ->where('status', 'active')
            ->where('admin_approved', true)
            ->where('featured', true)
            ->inRandomOrder()
            ->first();

        $topPickBannerCollection = Collection::query()
            ->global()
            ->where('status', 'active')
            ->where('admin_approved', true)
            ->when($topPickPromoCollection, fn ($query) => $query->where('id', '!=', $topPickPromoCollection->id))
            ->inRandomOrder()
            ->first()
            ?? $topPickPromoCollection;

        $topPickPromoEndsAt = $topPickPromoCollection
            ? now()->addMonths(6)->endOfMonth()
            : null;

        $newProductBaseQuery = Product::query()
            ->with(['template.category', 'shop'])
            ->withSum('orderItems as order_items_sum_quantity', 'quantity')
            ->withAvg('approvedReviews as approved_reviews_avg_rating', 'rating')
            ->withCount('approvedReviews');

        $newProducts = (clone $newProductBaseQuery)
            ->availableForDisplay()
            ->latest('products.created_at')
            ->limit(8)
            ->get();

        if ($newProducts->isEmpty()) {
            $newProducts = (clone $newProductBaseQuery)
                ->where('products.status', 'active')
                ->latest('products.created_at')
                ->limit(8)
                ->get();
        }

        $customerReviewAverage = round((float) (Review::approved()->avg('rating') ?? 4.8), 1);
        $customerReviewPages = $this->buildCustomerReviewPages();
        $customerHappyCustomersLabel = Review::approved()->count() >= 1000
            ? number_format(Review::approved()->count()) . '+'
            : '50,000+';

        $homeSettings = HomeSettingsController::resolved();
        $heroColumns = HomeSettingsController::resolvedHeroColumns();
        $leftHeroSlides = $heroColumns['left'];
        $rightHeroSlides = $heroColumns['right'];
        $heroSlides = array_values(array_merge($leftHeroSlides, $rightHeroSlides));
        $canEditHome = Auth::check() && Auth::user()->hasRole('admin');
        $homeEditMode = $canEditHome && request()->boolean('edit');

        return view('home', compact(
            'flashDeals',
            'flashDealMaxDiscount',
            'flashSaleEndsAt',
            'topPickBentoSmall',
            'topPickRowThree',
            'topPickMore',
            'topPickBannerCollection',
            'topPickPromoCollection',
            'topPickPromoEndsAt',
            'newProducts',
            'customerReviewAverage',
            'customerReviewPages',
            'customerHappyCustomersLabel',
            'homeSettings',
            'heroSlides',
            'leftHeroSlides',
            'rightHeroSlides',
            'canEditHome',
            'homeEditMode',
        ));
    }

    /**
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function buildCustomerReviewPages(): array
    {
        $reviews = Review::query()
            ->approved()
            ->with(['product', 'user'])
            ->latest()
            ->limit(9)
            ->get();

        $items = $reviews->map(function (Review $review) {
            return $this->formatCustomerReviewItem(
                name: $review->display_name,
                headline: Str::limit(trim($review->review_text), 100),
                body: strlen(trim($review->review_text)) > 100 ? trim($review->review_text) : null,
                image: $this->productImageUrl($review->product),
                url: $review->product
                    ? route('products.show', $review->product->slug)
                    : route('products.index'),
                verified: (bool) $review->is_verified_purchase,
                rating: (int) ($review->rating ?: 5),
            );
        });

        if ($items->count() < 9) {
            $products = Product::query()
                ->availableForDisplay()
                ->inRandomOrder()
                ->limit(9)
                ->get();

            $demos = [
                ['name' => 'Sarah O.', 'headline' => 'These prints look and feel like great quality — exactly what I wanted for my shop.', 'body' => 'The colors are vibrant and the fabric feels premium. Will definitely order again!', 'verified' => true],
                ['name' => 'Mike A.', 'headline' => 'Fast shipping and the custom design came out perfectly. Highly recommend Bluprinter!', 'body' => null, 'verified' => true],
                ['name' => 'Emily R.', 'headline' => 'Easy to customize and the final product exceeded my expectations.', 'body' => 'I was nervous ordering online but the print quality is outstanding and customer support was super helpful.', 'verified' => true],
                ['name' => 'James T.', 'headline' => 'Great value for money. The t-shirt fits well and the print has held up after multiple washes.', 'body' => null, 'verified' => true],
                ['name' => 'Lisa K.', 'headline' => 'Perfect gifts for my team — everyone loved their personalized designs.', 'body' => null, 'verified' => true],
                ['name' => 'David N.', 'headline' => 'The AI design tools made it so easy to create something unique.', 'body' => 'Uploaded my artwork and it looked amazing on the final product.', 'verified' => true],
                ['name' => 'Anna P.', 'headline' => 'Super happy with my order. Colors are true to the preview.', 'body' => null, 'verified' => true],
                ['name' => 'Chris W.', 'headline' => 'Third order from Bluprinter and they never disappoint.', 'body' => null, 'verified' => true],
                ['name' => 'Rachel M.', 'headline' => 'Beautiful print quality and arrived earlier than expected.', 'body' => 'Packaging was neat too. Already recommended to friends.', 'verified' => true],
            ];

            $startIndex = $items->count();
            for ($i = $startIndex; $i < 9; $i++) {
                $demo = $demos[$i % count($demos)];
                $product = $products->isNotEmpty()
                    ? $products->get($i % $products->count())
                    : null;

                $items->push($this->formatCustomerReviewItem(
                    name: $demo['name'],
                    headline: $demo['headline'],
                    body: $demo['body'],
                    image: $product ? $this->productImageUrl($product) : null,
                    url: $product
                        ? route('products.show', $product->slug)
                        : route('products.index'),
                    verified: $demo['verified'],
                    rating: 5,
                ));
            }
        }

        return $items->take(9)
            ->chunk(3)
            ->map(fn ($chunk) => $chunk->values()->all())
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCustomerReviewItem(
        string $name,
        string $headline,
        ?string $body,
        ?string $image,
        string $url,
        bool $verified,
        int $rating = 5,
    ): array {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $initials = strtoupper(
            substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1)
        );

        return [
            'name' => $name,
            'initials' => $initials ?: 'U',
            'headline' => $headline,
            'body' => $body,
            'image' => $image,
            'url' => $url,
            'verified' => $verified,
            'rating' => max(1, min(5, $rating)),
        ];
    }

    private function productImageUrl(?Product $product): ?string
    {
        if (! $product) {
            return null;
        }

        $media = $product->getEffectiveMedia();
        if (empty($media)) {
            return null;
        }

        $first = $media[0];
        if (is_string($first)) {
            return $first;
        }

        if (is_array($first)) {
            return $first['url'] ?? $first['path'] ?? reset($first) ?: null;
        }

        return null;
    }
}
