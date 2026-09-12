<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    public function show(Request $request, Shop $shop)
    {
        $shop->load('user');

        $stats = [
            'total_products' => $shop->products()->availableForDisplay()->count(),
            'followers' => $shop->followers()->count(),
            'favorited' => $shop->favorites()->count(),
        ];

        $categories = Category::whereHas('templates.products', function ($query) use ($shop) {
            $query->where('shop_id', $shop->id)->availableForDisplay();
        })->orderBy('sort_order')->orderBy('name')->get();

        $categoryProductCounts = Product::query()
            ->where('shop_id', $shop->id)
            ->availableForDisplay()
            ->join('product_templates', 'products.template_id', '=', 'product_templates.id')
            ->selectRaw('product_templates.category_id, COUNT(*) as total')
            ->groupBy('product_templates.category_id')
            ->pluck('total', 'category_id');

        $categories->each(function ($category) use ($categoryProductCounts) {
            $category->shop_products_count = (int) ($categoryProductCounts[$category->id] ?? 0);
        });

        $currentSort = $request->get('sort', 'newest');
        $categorySlug = $request->get('category');

        $productsQuery = $shop->products()
            ->availableForDisplay()
            ->with(['template', 'shop', 'variants']);

        if (filled($categorySlug)) {
            $productsQuery->whereHas('template.category', function ($query) use ($categorySlug) {
                $query->where('slug', $categorySlug);
            });
        }

        match ($currentSort) {
            'price_low' => $productsQuery->orderBy('base_price'),
            'price_high' => $productsQuery->orderByDesc('base_price'),
            'name' => $productsQuery->orderBy('name'),
            default => $productsQuery->latest(),
        };

        $allProducts = $productsQuery->paginate(24)->withQueryString();

        $isFollowing = false;
        if (Auth::check()) {
            $isFollowing = $shop->followers()->where('user_id', Auth::id())->exists();
        }

        return view('shops.show', compact(
            'shop',
            'stats',
            'categories',
            'allProducts',
            'isFollowing',
            'currentSort',
            'categorySlug'
        ));
    }

    public function follow(Request $request, Shop $shop)
    {
        try {
            // Check if shop exists
            if (!$shop || !$shop->exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop not found'
                ], 404);
            }

            $request->validate([
                'action' => 'required|in:follow,unfollow'
            ]);

            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You need to login to follow this shop'
                ], 401);
            }

            $user = Auth::user();
            $action = $request->input('action');

            if ($action === 'follow') {
                if (!$shop->followers()->where('user_id', $user->id)->exists()) {
                    $shop->followers()->attach($user->id);
                    $message = 'Successfully followed this shop!';
                } else {
                    $message = 'You are already following this shop!';
                }
            } else {
                $shop->followers()->detach($user->id);
                $message = 'Successfully unfollowed this shop!';
            }

            $followersCount = $shop->followers()->count();

            return response()->json([
                'success' => true,
                'message' => $message,
                'followers_count' => $followersCount,
                'is_following' => $action === 'follow'
            ]);
        } catch (\Exception $e) {
            Log::error('Follow shop error: ' . $e->getMessage(), [
                'shop_id' => $shop->id ?? null,
                'user_id' => Auth::id(),
                'action' => $request->input('action')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }

    public function contact(Request $request, Shop $shop)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'subject' => 'required|string|max:255'
        ]);

        // Here you would typically send an email to the shop owner
        // For now, we'll just return a success message

        return response()->json([
            'success' => true,
            'message' => 'Message has been sent to the shop successfully!'
        ]);
    }
}
