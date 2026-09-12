<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Services\ReviewRatingService;
use App\Support\S3Media;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewRatingService $reviewRatingService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $productsQuery = $this->authorizedProductsQuery($user);

        $reviewsQuery = Review::query()
            ->with(['product.shop', 'user'])
            ->whereIn('product_id', $productsQuery->select('id'))
            ->orderByDesc('created_at');

        if ($request->filled('product_id')) {
            $reviewsQuery->where('product_id', $request->product_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $reviewsQuery->where(function ($query) use ($search) {
                $query->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('review_text', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('approved')) {
            $reviewsQuery->where('is_approved', $request->approved === '1');
        }

        $reviews = $reviewsQuery->paginate(20)->withQueryString();
        $products = $productsQuery->orderBy('name')->limit(500)->get(['id', 'name']);

        return view('admin.reviews.index', compact('reviews', 'products'));
    }

    public function create()
    {
        $user = auth()->user();
        $products = $this->authorizedProductsQuery($user)->orderBy('name')->limit(500)->get(['id', 'name']);

        return view('admin.reviews.create', compact('products'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $this->validateReview($request);

        $product = $this->authorizedProductsQuery($user)
            ->whereKey($validated['product_id'])
            ->firstOrFail();

        $images = $this->uploadImages($request);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => null,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text'] ?? null,
            'images' => $images,
            'is_verified_purchase' => (bool) ($validated['is_verified_purchase'] ?? false),
            'is_approved' => (bool) ($validated['is_approved'] ?? true),
            'created_at' => $validated['review_date'] ?? now(),
            'updated_at' => $validated['review_date'] ?? now(),
        ]);

        if ($review->is_approved && $product->shop_id) {
            $this->reviewRatingService->syncShopRating($product->shop);
        }

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review created successfully.');
    }

    public function edit(Review $review)
    {
        $this->authorizeReview($review);

        $user = auth()->user();
        $products = $this->authorizedProductsQuery($user)->orderBy('name')->limit(500)->get(['id', 'name']);

        return view('admin.reviews.edit', compact('review', 'products'));
    }

    public function update(Request $request, Review $review)
    {
        $this->authorizeReview($review);

        $user = $request->user();
        $validated = $this->validateReview($request);

        $product = $this->authorizedProductsQuery($user)
            ->whereKey($validated['product_id'])
            ->firstOrFail();

        $existingImages = $review->images ?? [];
        $keptImages = collect($request->input('existing_images', []))
            ->filter(fn ($url) => is_string($url) && in_array($url, $existingImages, true))
            ->values()
            ->all();

        $newImages = $this->uploadImages($request);
        $images = array_values(array_unique(array_merge($keptImages, $newImages)));

        $wasApproved = $review->is_approved;
        $oldProductId = $review->product_id;

        $review->update([
            'product_id' => $product->id,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text'] ?? null,
            'images' => $images,
            'is_verified_purchase' => (bool) ($validated['is_verified_purchase'] ?? false),
            'is_approved' => (bool) ($validated['is_approved'] ?? false),
            'created_at' => $validated['review_date'] ?? $review->created_at,
        ]);

        if ($oldProductId !== $product->id) {
            $this->reviewRatingService->syncShopForProduct($oldProductId);
        }

        if ($review->is_approved || $wasApproved) {
            $this->reviewRatingService->syncShopForProduct($product->id);
        }

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review updated successfully.');
    }

    public function destroy(Review $review)
    {
        $this->authorizeReview($review);

        $productId = $review->product_id;
        $wasApproved = $review->is_approved;

        $review->delete();

        if ($wasApproved) {
            $this->reviewRatingService->syncShopForProduct($productId);
        }

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review deleted successfully.');
    }

    protected function validateReview(Request $request): array
    {
        return $request->validate([
            'product_id' => 'required|exists:products,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:5000',
            'is_verified_purchase' => 'nullable|boolean',
            'is_approved' => 'nullable|boolean',
            'review_date' => 'nullable|date',
            'images' => 'nullable|array|max:6',
            'images.*' => 'image|max:5120',
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'string|max:2048',
        ]);
    }

    protected function uploadImages(Request $request): array
    {
        if (!$request->hasFile('images')) {
            return [];
        }

        $urls = [];
        foreach ($request->file('images') as $file) {
            $url = S3Media::upload($file, 'reviews');
            if ($url) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    protected function authorizedProductsQuery($user)
    {
        $query = Product::query();

        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('seller') && $user->shop) {
            return $query->where('shop_id', $user->shop->id);
        }

        abort(403);
    }

    protected function authorizeReview(Review $review): void
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return;
        }

        if ($user->hasRole('seller') && $user->shop) {
            $ownsProduct = Product::query()
                ->whereKey($review->product_id)
                ->where('shop_id', $user->shop->id)
                ->exists();

            if ($ownsProduct) {
                return;
            }
        }

        abort(403);
    }
}
