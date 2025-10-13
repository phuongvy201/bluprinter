<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\ProductTemplate;
use App\Models\Product;
use Illuminate\Http\Request;

class SellerDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Statistics for seller's own data
        $totalTemplates = ProductTemplate::where('user_id', $user->id)->count();
        $totalProducts = Product::whereHas('template', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        // Recent templates
        $recentTemplates = ProductTemplate::where('user_id', $user->id)
            ->with(['category', 'attributes'])
            ->withCount('products')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Recent products
        $recentProducts = Product::whereHas('template', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['template'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('seller.dashboard', compact(
            'totalTemplates',
            'totalProducts',
            'recentTemplates',
            'recentProducts'
        ));
    }
}
