<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->whereNull('parent_id')
            ->with(['templates.products' => function ($query) {
                $query->where('status', 'active');
            }])
            ->firstOrFail();

        // Get products in this category (chỉ lấy đủ điều kiện hiển thị)
        $products = Product::whereHas('template', function ($query) use ($category) {
            $query->where('category_id', $category->id);
        })
            ->availableForDisplay()
            ->with(['template', 'shop'])
            ->paginate(12);

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

        // Get featured products from this category (chỉ lấy đủ điều kiện hiển thị)
        $featuredProducts = Product::whereHas('template', function ($query) use ($category) {
            $query->where('category_id', $category->id);
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
