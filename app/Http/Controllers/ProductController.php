<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Shop;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Services\TikTokEventsService;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Auth;
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
    public function show(Request $request, $slug)
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

        // Get current domain first
        $currentDomain = CurrencyService::getCurrentDomain();

        // Get shipping zones that have rates for this product's category
        // PRIORITY: Pass domain to prioritize zones for current domain
        $categoryId = $product->template->category_id ?? null;
        $shippingZones = ShippingRate::getZonesForCategory($categoryId, $currentDomain);

        // Get default zone for current domain (PRIORITY: zone matching current domain)
        $defaultZone = null;
        if ($currentDomain) {
            // First, try to find zone matching current domain from shippingZones
            $defaultZone = $shippingZones->first(function ($zone) use ($currentDomain) {
                return $zone->domain === $currentDomain;
            });

            // If not found in shippingZones, try to get any zone for this domain
            if (!$defaultZone) {
                $defaultZone = ShippingZone::active()
                    ->where('domain', $currentDomain)
                    ->ordered()
                    ->first();
            }
        }

        // If no zone found for domain, try to get first zone from shippingZones
        if (!$defaultZone && $shippingZones->isNotEmpty()) {
            $defaultZone = $shippingZones->first();
        }

        // Get all available zones for selector (zones that have rates for this category)
        // PRIORITY: Sort zones to put current domain's zones first
        $availableZones = $shippingZones;
        if ($currentDomain && $availableZones->isNotEmpty()) {
            $availableZones = $availableZones->sortBy(function ($zone) use ($currentDomain) {
                // Zones matching current domain come first (return 0), others come after (return 1)
                return $zone->domain === $currentDomain ? 0 : 1;
            })->values();
        }

        // If no zones found for category, get all active zones
        if ($availableZones->isEmpty()) {
            $allZones = ShippingZone::active()->ordered()->get();

            // PRIORITY: Sort zones to put current domain's zones first
            if ($currentDomain) {
                $allZones = $allZones->sortBy(function ($zone) use ($currentDomain) {
                    return $zone->domain === $currentDomain ? 0 : 1;
                })->values();
            }

            $availableZones = $allZones;

            // Set default zone to first available if not set
            if (!$defaultZone && $availableZones->isNotEmpty()) {
                $defaultZone = $availableZones->first();
            }
        }

        $this->trackTikTokViewContent($request, $product);

        return view('products.show', compact(
            'product',
            'relatedProducts',
            'breadcrumbs',
            'shopAvailable',
            'shippingZones',
            'defaultZone',
            'availableZones',
            'currentDomain',
            'categoryId'
        ));
    }

    /**
     * Calculate shipping cost for a product
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateShippingCost(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|integer|exists:shipping_zones,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'quantity' => 'nullable|integer|min:1',
            'product_price' => 'nullable|numeric|min:0',
        ]);

        $zoneId = $request->input('zone_id');
        $categoryId = $request->input('category_id');
        $quantity = $request->input('quantity', 1);
        $productPrice = $request->input('product_price', 0);

        // Get current domain
        $currentDomain = CurrencyService::getCurrentDomain();

        // PRIORITY: Find shipping rate matching current domain first
        $shippingRate = null;

        if ($currentDomain) {
            // First priority: Rate matching zone, domain, and category
            $shippingRate = ShippingRate::active()
                ->forZone($zoneId)
                ->forDomain($currentDomain)
                ->forCategory($categoryId)
                ->ordered()
                ->get()
                ->first(function ($rate) use ($quantity, $productPrice) {
                    return $rate->isApplicable($quantity, $productPrice);
                });
        }

        if (!$shippingRate) {
            // Second priority: Rate matching zone and category (without domain filter)
            $shippingRate = ShippingRate::active()
                ->forZone($zoneId)
                ->forCategory($categoryId)
                ->ordered()
                ->get()
                ->first(function ($rate) use ($quantity, $productPrice, $currentDomain) {
                    // If we have current domain, prioritize rates matching that domain
                    if ($currentDomain && $rate->matchesDomain($currentDomain)) {
                        return $rate->isApplicable($quantity, $productPrice);
                    }
                    return false;
                });
        }

        if (!$shippingRate) {
            // Third priority: Any rate for this zone and category
            $shippingRate = ShippingRate::active()
                ->forZone($zoneId)
                ->forCategory($categoryId)
                ->ordered()
                ->get()
                ->first(function ($rate) use ($quantity, $productPrice) {
                    return $rate->isApplicable($quantity, $productPrice);
                });
        }

        if (!$shippingRate) {
            return response()->json([
                'success' => false,
                'message' => 'No shipping rate found for this zone and category',
                'shipping_cost' => 0,
            ]);
        }

        // Calculate shipping cost
        $shippingCostUSD = $shippingRate->calculateCost($quantity);

        // Get current currency and rate
        $currentCurrency = currency();
        $currentCurrencyRate = currency_rate() ?? 1.0;

        // Convert to current currency
        $shippingCost = $currentCurrency !== 'USD'
            ? \App\Services\CurrencyService::convertFromUSDWithRate($shippingCostUSD, $currentCurrency, $currentCurrencyRate)
            : $shippingCostUSD;

        // Get zone info
        $zone = ShippingZone::find($zoneId);

        return response()->json([
            'success' => true,
            'shipping_cost' => round($shippingCost, 2),
            'shipping_cost_usd' => round($shippingCostUSD, 2),
            'currency' => $currentCurrency,
            'zone_name' => $zone->name ?? 'Unknown',
            'rate_name' => $shippingRate->name,
            'first_item_cost' => $shippingRate->first_item_cost,
            'additional_item_cost' => $shippingRate->additional_item_cost,
        ]);
    }

    private function trackTikTokViewContent(Request $request, Product $product): void
    {
        /** @var TikTokEventsService $tikTok */
        $tikTok = app(TikTokEventsService::class);

        if (!$tikTok->enabled()) {
            return;
        }

        $user = Auth::user();

        $tikTok->track(
            'ViewContent',
            [
                'value' => round($product->price ?? $product->base_price ?? 0, 2),
                'currency' => 'USD',
                'content_type' => 'product',
                'content_id' => (string) $product->id,
                'content_name' => $product->name,
                'contents' => [[
                    'content_id' => (string) $product->id,
                    'content_type' => 'product',
                    'content_name' => $product->name,
                    'price' => round($product->price ?? $product->base_price ?? 0, 2),
                    'quantity' => 1,
                ]],
                'description' => optional($product->template)->description ?? $product->description,
            ],
            $request,
            [
                'email' => $user?->email,
                'phone' => $user?->phone,
                'external_id' => $user?->id,
            ],
            [
                'page' => [
                    'url' => $request->fullUrl(),
                ],
            ]
        );
    }
}
