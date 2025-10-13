<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    /**
     * Show form to create shop
     */
    public function create()
    {
        // Check if user already has a shop
        if (auth()->user()->hasShop()) {
            return redirect()->route('seller.shop.dashboard')
                ->with('info', 'Bạn đã có shop rồi!');
        }

        return view('seller.shop.create');
    }

    /**
     * Store new shop
     */
    public function store(Request $request)
    {
        // Check if user already has a shop
        if (auth()->user()->hasShop()) {
            return redirect()->route('seller.shop.dashboard')
                ->with('info', 'Bạn đã có shop rồi!');
        }

        $validated = $request->validate([
            'shop_name' => 'required|string|max:255|unique:shops,shop_name',
            'shop_description' => 'nullable|string|max:5000',
            'shop_phone' => 'nullable|string|max:20',
            'shop_email' => 'nullable|email|max:255',
            'shop_address' => 'nullable|string|max:500',
            'shop_city' => 'nullable|string|max:100',
            'shop_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'shop_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $data = $validated;
        $data['user_id'] = auth()->id();
        $data['shop_slug'] = Str::slug($validated['shop_name']);

        // Upload logo to S3
        if ($request->hasFile('shop_logo')) {
            $logoFile = $request->file('shop_logo');
            $logoName = 'shop_logo_' . time() . '_' . Str::random(10) . '.' . $logoFile->getClientOriginalExtension();
            $logoPath = 'shops/logos/' . $logoName;
            Storage::disk('s3')->put($logoPath, file_get_contents($logoFile));
            $data['shop_logo'] = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/' . $logoPath;
        }

        // Upload banner to S3
        if ($request->hasFile('shop_banner')) {
            $bannerFile = $request->file('shop_banner');
            $bannerName = 'shop_banner_' . time() . '_' . Str::random(10) . '.' . $bannerFile->getClientOriginalExtension();
            $bannerPath = 'shops/banners/' . $bannerName;
            Storage::disk('s3')->put($bannerPath, file_get_contents($bannerFile));
            $data['shop_banner'] = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/' . $bannerPath;
        }

        Shop::create($data);

        return redirect()->route('seller.shop.dashboard')
            ->with('success', 'Shop đã được tạo thành công! 🎉');
    }

    /**
     * Show shop dashboard
     */
    public function dashboard()
    {
        $shop = auth()->user()->shop;

        if (!$shop) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Bạn cần tạo shop trước!');
        }

        // Get shop statistics
        $totalProducts = $shop->total_products;
        $totalSales = $shop->total_sales;
        $totalRevenue = $shop->total_revenue;
        $rating = $shop->rating;

        // Get recent products
        $recentProducts = $shop->products()
            ->with('template')
            ->latest()
            ->take(5)
            ->get();

        return view('seller.shop.dashboard', compact('shop', 'totalProducts', 'totalSales', 'totalRevenue', 'rating', 'recentProducts'));
    }

    /**
     * Show form to edit shop
     */
    public function edit()
    {
        $shop = auth()->user()->shop;

        if (!$shop) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Bạn cần tạo shop trước!');
        }

        return view('seller.shop.edit', compact('shop'));
    }

    /**
     * Update shop
     */
    public function update(Request $request)
    {
        $shop = auth()->user()->shop;

        if (!$shop) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'Bạn cần tạo shop trước!');
        }

        $validated = $request->validate([
            'shop_name' => 'required|string|max:255|unique:shops,shop_name,' . $shop->id,
            'shop_description' => 'nullable|string|max:5000',
            'shop_phone' => 'nullable|string|max:20',
            'shop_email' => 'nullable|email|max:255',
            'shop_address' => 'nullable|string|max:500',
            'shop_city' => 'nullable|string|max:100',
            'shop_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'shop_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'return_policy' => 'nullable|string|max:2000',
            'shipping_policy' => 'nullable|string|max:2000',
        ]);

        $data = $validated;

        // Update slug if name changed
        if ($validated['shop_name'] !== $shop->shop_name) {
            $data['shop_slug'] = Str::slug($validated['shop_name']);
        }

        // Upload new logo
        if ($request->hasFile('shop_logo')) {
            // Delete old logo if exists
            if ($shop->shop_logo) {
                // Parse URL to get path and delete from S3
                // Storage::disk('s3')->delete($oldPath);
            }

            $logoFile = $request->file('shop_logo');
            $logoName = 'shop_logo_' . time() . '_' . Str::random(10) . '.' . $logoFile->getClientOriginalExtension();
            $logoPath = 'shops/logos/' . $logoName;
            Storage::disk('s3')->put($logoPath, file_get_contents($logoFile));
            $data['shop_logo'] = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/' . $logoPath;
        }

        // Upload new banner
        if ($request->hasFile('shop_banner')) {
            // Delete old banner if exists
            if ($shop->shop_banner) {
                // Storage::disk('s3')->delete($oldPath);
            }

            $bannerFile = $request->file('shop_banner');
            $bannerName = 'shop_banner_' . time() . '_' . Str::random(10) . '.' . $bannerFile->getClientOriginalExtension();
            $bannerPath = 'shops/banners/' . $bannerName;
            Storage::disk('s3')->put($bannerPath, file_get_contents($bannerFile));
            $data['shop_banner'] = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/' . $bannerPath;
        }

        $shop->update($data);

        return redirect()->route('seller.shop.dashboard')
            ->with('success', 'Cập nhật shop thành công! ✨');
    }
}
