<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CollectionRelatedProductsService;
use App\Services\FrequentlyBoughtTogetherService;
use App\Models\Category;
use App\Models\Review;
use App\Models\Shop;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Services\TikTokEventsService;
use App\Services\CurrencyService;
use App\Support\CatalogPageSettings;
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

        $catalogPage = config('catalog.products_index', []);

        return view('products.index', compact('products', 'categories', 'shops', 'breadcrumbs', 'catalogPage'));
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
            ->with(['shop' => function ($query) {
                $query->withCount('followers');
            }, 'activeFlashDeal', 'template.category', 'variants', 'approvedReviews' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }])
            ->firstOrFail();

        // Shop is available and active if we reach here
        $shopAvailable = true;

        $fbtService = app(FrequentlyBoughtTogetherService::class);
        $previousProductId = (int) $request->session()->get('last_viewed_product_id', 0);
        if ($previousProductId > 0 && $previousProductId !== $product->id) {
            try {
                $fbtService->recordCoView($previousProductId, $product->id);
            } catch (\Throwable) {
                // Tables may not be migrated yet.
            }
        }
        $request->session()->put('last_viewed_product_id', $product->id);

        $fbtLimit = (int) config('catalog.product_show.fbt_limit', 32);
        $fbtProducts = $fbtService->getProducts($product, $fbtLimit);

        $collectionLimit = (int) config('catalog.product_show.collection_related_limit', 32);
        $collectionProducts = app(CollectionRelatedProductsService::class)->getProducts($product, $collectionLimit);

        // Get breadcrumb data
        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Products', 'url' => route('products.index')]
        ];

        $displayCategory = $product->resolveDisplayCategory();
        if (! empty($displayCategory['category'])) {
            $breadcrumbs[] = [
                'name' => $displayCategory['name'] ?? $displayCategory['category']->name,
                'url' => route('products.index', ['category' => $displayCategory['category']->id]),
            ];
        }
        $breadcrumbs[] = ['name' => $product->name, 'url' => ''];

        // Get current domain first
        $currentDomain = CurrencyService::getCurrentDomain();

        // Get shipping zones that have rates for this product's category
        // PRIORITY: Pass domain to prioritize zones for current domain
        $categoryId = $product->resolvedCategoryId();
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

        $productShowSettings = CatalogPageSettings::productShow();

        $shopReviews = collect();
        $shopReviewsAverage = 0;
        $shopReviewsTotal = 0;

        if ($product->shop_id) {
            $shopReviewsQuery = Review::query()
                ->approved()
                ->whereHas('product', function ($query) use ($product) {
                    $query->where('shop_id', $product->shop_id)
                        ->availableForDisplay();
                });

            $shopReviewsTotal = (clone $shopReviewsQuery)->count();
            $shopReviewsAverage = (float) ((clone $shopReviewsQuery)->avg('rating') ?? 0);

            $shopReviews = $shopReviewsQuery
                ->with([
                    'product' => function ($query) {
                        $query->select('id', 'name', 'slug', 'media', 'shop_id', 'template_id')
                            ->with('template:id,media');
                    },
                    'user:id,name',
                ])
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
        }

        return view('products.show', compact(
            'product',
            'fbtProducts',
            'collectionProducts',
            'breadcrumbs',
            'shopAvailable',
            'shippingZones',
            'defaultZone',
            'availableZones',
            'currentDomain',
            'categoryId',
            'productShowSettings',
            'shopReviews',
            'shopReviewsAverage',
            'shopReviewsTotal'
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

    /**
     * Render product-card components for recently viewed items (localStorage).
     */
    public function recentlyViewedCards(Request $request)
    {
        $ids = collect($request->input('ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->take((int) config('catalog.product_show.recently_viewed_limit', 6))
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['html' => '']);
        }

        $productsById = Product::query()
            ->with(['template.category', 'shop'])
            ->withSum('orderItems as order_items_sum_quantity', 'quantity')
            ->withAvg('approvedReviews as approved_reviews_avg_rating', 'rating')
            ->withCount(['approvedReviews', 'variants'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $products = $ids
            ->map(fn (int $id) => $productsById->get($id))
            ->filter()
            ->values();

        if ($products->isEmpty()) {
            return response()->json(['html' => '']);
        }

        $html = view('partials.recently-viewed-product-cards', [
            'products' => $products,
            'variant' => $request->input('variant', 'default'),
        ])->render();

        return response()->json(['html' => $html]);
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
