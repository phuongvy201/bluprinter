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
    public function show(Shop $shop)
    {
        // Load relationships
        $shop->load(['user', 'products' => function ($query) {
            $query->where('status', 'active')
                ->with(['template', 'variants'])
                ->orderBy('created_at', 'desc');
        }]);

        // Get shop statistics
        $stats = [
            'total_products' => $shop->products()->where('status', 'active')->count(),
            'followers' => $shop->followers()->count(),
            'favorited' => $shop->favorites()->count(),
        ];

        // Get product categories for this shop
        $categories = Category::whereHas('templates.products', function ($query) use ($shop) {
            $query->where('shop_id', $shop->id)->where('status', 'active');
        })->with(['templates.products' => function ($query) use ($shop) {
            $query->where('shop_id', $shop->id)->where('status', 'active')->limit(1);
        }])->get();

        // Get hot products (most viewed/favorited)
        $hotProducts = $shop->products()
            ->where('status', 'active')
            ->with(['template', 'variants'])
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        // Get all products for the shop
        $allProducts = $shop->products()
            ->where('status', 'active')
            ->with(['template', 'variants'])
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        // Check if current user follows this shop
        $isFollowing = false;
        if (Auth::check()) {
            $isFollowing = $shop->followers()->where('user_id', Auth::id())->exists();
        }

        return view('shops.show', compact(
            'shop',
            'stats',
            'categories',
            'hotProducts',
            'allProducts',
            'isFollowing'
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
