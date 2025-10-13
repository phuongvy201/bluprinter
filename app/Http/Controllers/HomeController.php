<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Collection;
use App\Models\ProductTemplate;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Get featured collections
        $featuredCollections = Collection::with(['products.template'])
            ->where('featured', true)
            ->where('admin_approved', true)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        // Get featured products
        $featuredProducts = Product::with(['template.category', 'shop'])
            ->where('status', 'active')
            ->inRandomOrder()
            ->limit(8)
            ->get();

        // Get popular templates
        $popularTemplates = ProductTemplate::with(['category', 'user'])
            ->inRandomOrder()
            ->limit(6)
            ->get();

        return view('home', compact('featuredCollections', 'featuredProducts', 'popularTemplates'));
    }
}
