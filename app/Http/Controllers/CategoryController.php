<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show($slug, Request $request)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $parentCategory = $category->parent_id
            ? Category::find($category->parent_id)
            : null;

        if ($category->parent_id) {
            $allCategoryIds = [$category->id];
            $subcategories = collect();
        } else {
            $subcategoryIds = Category::where('parent_id', $category->id)->pluck('id')->toArray();
            $allCategoryIds = array_merge([$category->id], $subcategoryIds);
            $subcategories = Category::where('parent_id', $category->id)
                ->orderBy('name')
                ->get();
        }

        $productsQuery = Product::where(function ($query) use ($allCategoryIds) {
            $query->inCategoryIds($allCategoryIds);
        })
            ->availableForDisplay()
            ->with(['template', 'shop']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $productsQuery->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('shop', function ($shopQuery) use ($searchTerm) {
                        $shopQuery->where('shop_name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        if ($request->filled('shop')) {
            $productsQuery->where('shop_id', $request->shop);
        }

        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_low':
            case 'price_asc':
                $productsQuery->orderBy('price', 'asc');
                break;
            case 'price_high':
            case 'price_desc':
                $productsQuery->orderBy('price', 'desc');
                break;
            case 'name':
                $productsQuery->orderBy('name', 'asc');
                break;
            case 'newest':
            default:
                $productsQuery->orderBy('created_at', 'desc');
                break;
        }

        $products = $productsQuery->paginate(20)->withQueryString();

        $relatedCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->whereHas('templates.products', function ($query) {
                $query->availableForDisplay();
            })
            ->withCount(['templates as products_count' => function ($query) {
                $query->whereHas('products', function ($q) {
                    $q->availableForDisplay();
                });
            }])
            ->orderBy('products_count', 'desc')
            ->limit(6)
            ->get();

        $shops = Shop::where('shop_status', 'active')
            ->whereHas('products', function ($query) use ($allCategoryIds) {
                $query->availableForDisplay()
                    ->inCategoryIds($allCategoryIds);
            })
            ->orderBy('shop_name')
            ->get();

        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Products', 'url' => route('products.index')],
        ];

        if ($parentCategory) {
            $breadcrumbs[] = ['name' => $parentCategory->name, 'url' => route('category.show', $parentCategory->slug)];
        }

        $breadcrumbs[] = ['name' => $category->name, 'url' => null];

        return view('categories.show', compact(
            'category',
            'products',
            'subcategories',
            'relatedCategories',
            'shops',
            'breadcrumbs',
            'parentCategory'
        ));
    }
}
