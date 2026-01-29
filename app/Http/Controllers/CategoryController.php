<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show($slug, Request $request)
    {
        $category = Category::where('slug', $slug)
            ->whereNull('parent_id')
            ->with(['templates.products' => function ($query) {
                $query->where('status', 'active');
            }])
            ->firstOrFail();

        // Get subcategory IDs
        $subcategoryIds = Category::where('parent_id', $category->id)->pluck('id')->toArray();

        // Combine category ID with subcategory IDs
        $allCategoryIds = array_merge([$category->id], $subcategoryIds);

        // Get products in this category AND its subcategories (chỉ lấy đủ điều kiện hiển thị)
        $productsQuery = Product::whereHas('template', function ($query) use ($allCategoryIds) {
            $query->whereIn('category_id', $allCategoryIds);
        })
            ->availableForDisplay()
            ->with(['template', 'shop']);

        // Handle search
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $productsQuery->where('name', 'like', '%' . $searchTerm . '%');
        }

        // Handle sort
        $sort = $request->get('sort', 'default');
        switch ($sort) {
            case 'price_asc':
                $productsQuery->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $productsQuery->orderBy('price', 'desc');
                break;
            case 'name':
                $productsQuery->orderBy('name', 'asc');
                break;
            case 'newest':
                $productsQuery->orderBy('created_at', 'desc');
                break;
            default:
                $productsQuery->orderBy('created_at', 'desc');
                break;
        }

        $products = $productsQuery->paginate(12)->withQueryString();

        // Get subcategories
        $subcategories = Category::where('parent_id', $category->id)
            ->orderBy('name')
            ->get();

        // Get related categories (other main categories)
        $relatedCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->withCount(['templates as products_count' => function ($query) {
                $query->whereHas('products', function ($q) {
                    $q->where('status', 'active');
                });
            }])
            ->orderBy('products_count', 'desc')
            ->limit(6)
            ->get();

        // Get featured products from this category AND its subcategories (chỉ lấy đủ điều kiện hiển thị)
        $featuredProducts = Product::whereHas('template', function ($query) use ($allCategoryIds) {
            $query->whereIn('category_id', $allCategoryIds);
        })
            ->availableForDisplay()
            ->with(['template', 'shop'])
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('categories.show', compact(
            'category',
            'products',
            'subcategories',
            'relatedCategories',
            'featuredProducts'
        ));
    }
}
