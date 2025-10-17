<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class WishlistController extends Controller
{
    /**
     * Display the wishlist page.
     */
    public function index()
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        $wishlistItems = Wishlist::getWishlistItems($userId, $sessionId, 12);

        return view('wishlist.index', compact('wishlistItems'));
    }

    /**
     * Add product to wishlist (AJAX).
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $request->product_id;
        $userId = Auth::id();
        $sessionId = session()->getId();

        // Check if product exists and is active
        $product = Product::where('id', $productId)
            ->where('status', 'active')
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or not available.',
            ], 404);
        }

        // Check if already in wishlist
        if (Wishlist::isInWishlist($productId, $userId, $sessionId)) {
            return response()->json([
                'success' => false,
                'message' => 'Product is already in your wishlist.',
            ], 400);
        }

        // Add to wishlist
        $wishlist = Wishlist::addToWishlist($productId, $userId, $sessionId);

        if ($wishlist) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist successfully.',
                'wishlist_count' => $this->getWishlistCount($userId, $sessionId),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to add product to wishlist.',
        ], 500);
    }

    /**
     * Remove product from wishlist (AJAX).
     */
    public function remove(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $request->product_id;
        $userId = Auth::id();
        $sessionId = session()->getId();

        $removed = Wishlist::removeFromWishlist($productId, $userId, $sessionId);

        if ($removed) {
            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist successfully.',
                'wishlist_count' => $this->getWishlistCount($userId, $sessionId),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found in wishlist.',
        ], 404);
    }

    /**
     * Toggle product in wishlist (AJAX).
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $request->product_id;
        $userId = Auth::id();
        $sessionId = session()->getId();

        // Check if product exists and is active
        $product = Product::where('id', $productId)
            ->where('status', 'active')
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or not available.',
            ], 404);
        }

        $isInWishlist = Wishlist::isInWishlist($productId, $userId, $sessionId);

        if ($isInWishlist) {
            // Remove from wishlist
            $removed = Wishlist::removeFromWishlist($productId, $userId, $sessionId);
            $action = 'removed';
            $message = 'Product removed from wishlist successfully.';
        } else {
            // Add to wishlist
            $added = Wishlist::addToWishlist($productId, $userId, $sessionId);
            $action = 'added';
            $message = 'Product added to wishlist successfully.';
        }

        return response()->json([
            'success' => true,
            'action' => $action,
            'message' => $message,
            'wishlist_count' => $this->getWishlistCount($userId, $sessionId),
        ]);
    }

    /**
     * Get wishlist count (AJAX).
     */
    public function count(): JsonResponse
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        return response()->json([
            'success' => true,
            'count' => $this->getWishlistCount($userId, $sessionId),
        ]);
    }

    /**
     * Check if products are in wishlist (AJAX).
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $userId = Auth::id();
        $sessionId = session()->getId();
        $productIds = $request->product_ids;

        $query = Wishlist::whereIn('product_id', $productIds);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $wishlistItems = $query->pluck('product_id')->toArray();

        return response()->json([
            'success' => true,
            'wishlist_items' => $wishlistItems,
        ]);
    }

    /**
     * Clear entire wishlist.
     */
    public function clear(): JsonResponse
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        $query = Wishlist::query();

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $deleted = $query->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared successfully.',
            'wishlist_count' => 0,
        ]);
    }

    /**
     * Get wishlist count for user or session.
     */
    private function getWishlistCount($userId = null, $sessionId = null): int
    {
        $query = Wishlist::query();

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        return $query->count();
    }

    /**
     * Transfer session wishlist to user when they log in.
     */
    public function transferSessionToUser()
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $sessionId = session()->getId();

            Wishlist::transferSessionToUser($sessionId, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Wishlist transferred successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not authenticated.',
        ], 401);
    }
}
