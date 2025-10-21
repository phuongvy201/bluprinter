<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Shop;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of all active products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Product::with(['shop', 'template.category', 'variants'])
            ->availableForDisplay();

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('template', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // Filter by shop
        if ($request->filled('shop')) {
            $query->where('shop_id', $request->shop);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where(function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price)
                    ->orWhereHas('template', function ($templateQuery) use ($request) {
                        $templateQuery->where('base_price', '>=', $request->min_price)
                            ->whereNull('products.price');
                    });
            });
        }
        if ($request->filled('max_price')) {
            $query->where(function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price)
                    ->orWhereHas('template', function ($templateQuery) use ($request) {
                        $templateQuery->where('base_price', '<=', $request->max_price)
                            ->whereNull('products.price');
                    });
            });
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('shop', function ($shopQuery) use ($search) {
                        $shopQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort functionality
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(20)->withQueryString();

        // Get filter data
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $shops = Shop::where('shop_status', 'active')->get();

        // Get breadcrumb data
        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Products', 'url' => route('products.index')]
        ];

        if ($request->filled('category')) {
            $category = Category::find($request->category);
            if ($category) {
                $breadcrumbs[] = ['name' => $category->name, 'url' => route('products.index', ['category' => $category->id])];
            }
        }

        return view('products.index', compact('products', 'categories', 'shops', 'breadcrumbs'));
    }

    /**
     * Display the specified product.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($slug)
    {
        // Get product and require all display conditions
        $product = Product::where('slug', $slug)
            ->availableForDisplay()
            ->with(['shop', 'template.category', 'variants', 'approvedReviews' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }])
            ->firstOrFail();

        // Shop is available and active if we reach here
        $shopAvailable = true;

        // Get related products from the same category (chỉ lấy đủ điều kiện hiển thị)
        $relatedProducts = Product::whereHas('template', function ($q) use ($product) {
            $q->where('category_id', $product->template->category_id);
        })
            ->where('id', '!=', $product->id)
            ->availableForDisplay()
            ->with(['shop', 'template'])
            ->limit(8)
            ->get();

        // Get breadcrumb data
        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Products', 'url' => route('products.index')]
        ];

        if ($product->template->category) {
            $breadcrumbs[] = ['name' => $product->template->category->name, 'url' => route('products.index', ['category' => $product->template->category->id])];
        }
        $breadcrumbs[] = ['name' => $product->name, 'url' => ''];

        return view('products.show', compact('product', 'relatedProducts', 'breadcrumbs', 'shopAvailable'));
    }
}
