<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Collection;
use App\Models\Shop;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Display search results
     */
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, products, collections, shops

        // Ensure type is set to 'all' if not provided or empty
        if (empty($type)) {
            $type = 'all';
        }

        // Initialize empty results
        $products = collect();
        $collections = collect();
        $shops = collect();
        $totalResults = 0;
        $counts = [
            'products' => 0,
            'collections' => 0,
            'shops' => 0,
        ];

        if (strlen($query) >= 2) {
            // Search Products
            if ($type === 'all' || $type === 'products') {
                $products = Product::with(['template.category', 'shop'])
                    ->where('status', 'active')
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%")
                            ->orWhereHas('template', function ($q) use ($query) {
                                $q->where('name', 'like', "%{$query}%")
                                    ->orWhere('description', 'like', "%{$query}%");
                            })
                            ->orWhereHas('template.category', function ($q) use ($query) {
                                $q->where('name', 'like', "%{$query}%");
                            });
                    })
                    ->when($type === 'products', function ($q) {
                        return $q->paginate(12);
                    }, function ($q) {
                        return $q->limit(6)->get();
                    });

                $totalResults += $type === 'products' ? $products->total() : $products->count();
            }

            // Search Collections
            if ($type === 'all' || $type === 'collections') {
                $collections = Collection::with(['shop'])
                    ->active()
                    ->approved()
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                    })
                    ->when($type === 'collections', function ($q) {
                        return $q->paginate(12);
                    }, function ($q) {
                        return $q->limit(6)->get();
                    });

                $totalResults += $type === 'collections' ? $collections->total() : $collections->count();
            }

            // Search Shops
            if ($type === 'all' || $type === 'shops') {
                $shops = Shop::where('shop_status', 'active')
                    ->where(function ($q) use ($query) {
                        $q->where('shop_name', 'like', "%{$query}%")
                            ->orWhere('shop_description', 'like', "%{$query}%");
                    })
                    ->when($type === 'shops', function ($q) {
                        return $q->paginate(12);
                    }, function ($q) {
                        return $q->limit(6)->get();
                    });

                $totalResults += $type === 'shops' ? $shops->total() : $shops->count();
            }

            // Count results for each type
            // When type is 'all', need to query actual counts, not just limited results
            $counts = [
                'products' => $type === 'products' ? $products->total() : ($type === 'all' ? Product::where('status', 'active')
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%")
                            ->orWhereHas('template', function ($q) use ($query) {
                                $q->where('name', 'like', "%{$query}%")
                                    ->orWhere('description', 'like', "%{$query}%");
                            })
                            ->orWhereHas('template.category', function ($q) use ($query) {
                                $q->where('name', 'like', "%{$query}%");
                            });
                    })->count() : 0),
                'collections' => $type === 'collections' ? $collections->total() : ($type === 'all' ? Collection::active()
                    ->approved()
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                    })->count() : 0),
                'shops' => $type === 'shops' ? $shops->total() : ($type === 'all' ? Shop::where('shop_status', 'active')
                    ->where(function ($q) use ($query) {
                        $q->where('shop_name', 'like', "%{$query}%")
                            ->orWhere('shop_description', 'like', "%{$query}%");
                    })->count() : 0),
            ];

            // Recalculate total results with actual counts when type is 'all'
            if ($type === 'all') {
                $totalResults = $counts['products'] + $counts['collections'] + $counts['shops'];
            }
        }

        return view('search.index', compact(
            'query',
            'type',
            'products',
            'collections',
            'shops',
            'totalResults',
            'counts'
        ));
    }

    /**
     * API endpoint for search suggestions (autocomplete)
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Get top 5 products
        $products = Product::with('template')
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhereHas('template', function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%");
                    });
            })
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'type' => 'product',
                    'name' => $product->name,
                    'image' => $product->media[0] ?? null,
                    'price' => $product->price,
                    'url' => route('products.show', $product->slug)
                ];
            });

        // Get top 3 collections
        $collections = Collection::active()
            ->approved()
            ->where('name', 'like', "%{$query}%")
            ->limit(3)
            ->get()
            ->map(function ($collection) {
                return [
                    'type' => 'collection',
                    'name' => $collection->name,
                    'image' => $collection->image,
                    'products_count' => $collection->active_products_count,
                    'url' => route('collections.show', $collection->slug)
                ];
            });

        // Get top 2 shops
        $shops = Shop::where('shop_status', 'active')
            ->where('shop_name', 'like', "%{$query}%")
            ->limit(2)
            ->get()
            ->map(function ($shop) {
                return [
                    'type' => 'shop',
                    'name' => $shop->shop_name,
                    'image' => $shop->shop_logo,
                    'url' => route('shops.show', $shop->shop_slug)
                ];
            });

        $suggestions = $products->concat($collections)->concat($shops);

        return response()->json($suggestions);
    }
}
