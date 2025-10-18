<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    /**
     * Display a listing of collections
     */
    public function index(Request $request)
    {
        $query = Collection::with(['shop', 'products'])
            ->active()
            ->approved();

        // Filter by type
        if ($request->has('type') && in_array($request->type, ['manual', 'automatic'])) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'featured');
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'newest':
                $query->latest();
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'products':
                $query->withCount('products')->orderBy('products_count', 'desc');
                break;
            case 'featured':
            default:
                $query->orderBy('featured', 'desc')
                    ->orderBy('sort_order')
                    ->latest();
                break;
        }

        $collections = $query->paginate(12)->withQueryString();

        // Get featured collections for sidebar
        $featuredCollections = Collection::with(['shop'])
            ->active()
            ->approved()
            ->featured()
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return view('collections.index', compact('collections', 'featuredCollections'));
    }

    /**
     * Display the specified collection
     */
    public function show(Request $request, string $slug)
    {
        $collection = Collection::with(['shop', 'products.template.category'])
            ->where('slug', $slug)
            ->active()
            ->approved()
            ->firstOrFail();

        // Get products in this collection (chỉ lấy đủ điều kiện hiển thị)
        $query = $collection->activeProducts()
            ->availableForDisplay()
            ->with(['template.category', 'shop']);

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('template.category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'default');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->join('product_templates', 'products.template_id', '=', 'product_templates.id')
                    ->orderBy('product_templates.name');
                break;
            case 'newest':
                $query->latest();
                break;
            case 'default':
            default:
                // Order by pivot sort_order if available
                $query->orderBy('product_collection.sort_order');
                break;
        }

        $products = $query->paginate(12)->withQueryString();

        // Get related collections
        $relatedCollections = Collection::with(['shop'])
            ->where('id', '!=', $collection->id)
            ->active()
            ->approved()
            ->when($collection->shop_id, function ($q) use ($collection) {
                // If collection belongs to a shop, show collections from same shop
                $q->where('shop_id', $collection->shop_id);
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('collections.show', compact('collection', 'products', 'relatedCollections'));
    }
}
