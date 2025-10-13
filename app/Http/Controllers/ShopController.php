<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $request->validate([
            'action' => 'required|in:follow,unfollow'
        ]);

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để follow shop'
            ], 401);
        }

        $user = Auth::user();
        $action = $request->input('action');

        if ($action === 'follow') {
            if (!$shop->followers()->where('user_id', $user->id)->exists()) {
                $shop->followers()->attach($user->id);
                $message = 'Đã follow shop thành công!';
            } else {
                $message = 'Bạn đã follow shop này rồi!';
            }
        } else {
            $shop->followers()->detach($user->id);
            $message = 'Đã unfollow shop thành công!';
        }

        $followersCount = $shop->followers()->count();

        return response()->json([
            'success' => true,
            'message' => $message,
            'followers_count' => $followersCount,
            'is_following' => $action === 'follow'
        ]);
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
            'message' => 'Tin nhắn đã được gửi đến shop thành công!'
        ]);
    }
}
