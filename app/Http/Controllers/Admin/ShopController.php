<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display all shops
     */
    public function index()
    {
        $shops = Shop::with('user')
            ->withCount('products')
            ->latest()
            ->paginate(20);

        return view('admin.shops.index', compact('shops'));
    }

    /**
     * Verify a shop
     */
    public function verify(Shop $shop)
    {
        $shop->update(['verified' => true]);

        return back()->with('success', "Shop '{$shop->shop_name}' đã được verify! ✓");
    }

    /**
     * Suspend a shop
     */
    public function suspend(Shop $shop)
    {
        $shop->update(['shop_status' => 'suspended']);

        return back()->with('success', "Shop '{$shop->shop_name}' đã bị suspend!");
    }

    /**
     * Activate a shop
     */
    public function activate(Shop $shop)
    {
        $shop->update(['shop_status' => 'active']);

        return back()->with('success', "Shop '{$shop->shop_name}' đã được kích hoạt lại!");
    }
}
