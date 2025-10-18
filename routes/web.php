<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductTemplateController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Admin\ProductImportController;
use App\Http\Controllers\Admin\CollectionController as AdminCollectionController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Seller\SellerDashboardController;
use App\Http\Controllers\Seller\ShopController as SellerShopController;
use App\Http\Controllers\Seller\PostController as SellerPostController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Api\CartController as ApiCartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Admin\ShippingZoneController;
use App\Http\Controllers\Admin\ShippingRateController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/shops/{shop}', [App\Http\Controllers\ShopController::class, 'show'])->name('shops.show');

// Collections routes
Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
Route::get('/collections/{slug}', [CollectionController::class, 'show'])->name('collections.show');

// Search routes
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/api/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

// Category routes
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

// Checkout routes
Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/process', [App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
Route::post('/checkout/calculate-shipping', [App\Http\Controllers\CheckoutController::class, 'calculateShipping'])->name('checkout.calculate-shipping');
Route::get('/checkout/success/{orderNumber}', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');

// LianLian Pay callback routes
Route::get('/checkout/lianlian/success', [App\Http\Controllers\CheckoutController::class, 'lianlianSuccess'])->name('checkout.lianlian.success');
Route::get('/checkout/lianlian/cancel', [App\Http\Controllers\CheckoutController::class, 'lianlianCancel'])->name('checkout.lianlian.cancel');

// LianLian Pay routes
Route::prefix('payment/lianlian')->name('payment.lianlian.')->group(function () {
    Route::post('/create/{order}', [App\Http\Controllers\Payment\LianLianPayController::class, 'createPayment'])->name('create');
    Route::get('/return', [App\Http\Controllers\Payment\LianLianPayController::class, 'handleReturn'])->name('return');
    Route::get('/cancel', [App\Http\Controllers\Payment\LianLianPayController::class, 'handleCancel'])->name('cancel');
    Route::post('/webhook', [App\Http\Controllers\Payment\LianLianPayController::class, 'handleWebhook'])->name('webhook');
    Route::post('/webhook-v2', [App\Http\Controllers\Payment\LianLianPayController::class, 'handleWebhookV2'])->name('webhook-v2');
    Route::get('/query/{order}', [App\Http\Controllers\Payment\LianLianPayController::class, 'queryPayment'])->name('query');
    Route::post('/refund/{order}', [App\Http\Controllers\Payment\LianLianPayController::class, 'processRefund'])->name('refund');
    Route::get('/token', [App\Http\Controllers\Payment\LianLianPayController::class, 'getToken'])->name('token');
    Route::post('/3ds-result', [App\Http\Controllers\Payment\LianLianPayController::class, 'handle3DSResult'])->name('3ds-result');

    // New routes for separate payment page
    Route::get('/payment', [App\Http\Controllers\Payment\LianLianPayController::class, 'showPaymentPage'])->name('payment');
    Route::post('/process', [App\Http\Controllers\Payment\LianLianPayController::class, 'processPayment'])->name('process');
});


// Wishlist routes
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
Route::post('/wishlist/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/wishlist/count', [WishlistController::class, 'count'])->name('wishlist.count');
Route::post('/wishlist/check', [WishlistController::class, 'check'])->name('wishlist.check');
Route::post('/wishlist/clear', [WishlistController::class, 'clear'])->name('wishlist.clear');
Route::post('/wishlist/transfer', [WishlistController::class, 'transferSessionToUser'])->name('wishlist.transfer');

// Customer Orders routes (for logged in customers)
Route::middleware(['auth'])->prefix('my')->name('customer.')->group(function () {
    Route::get('/orders', [App\Http\Controllers\Customer\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderNumber}', [App\Http\Controllers\Customer\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{orderNumber}/cancel', [App\Http\Controllers\Customer\OrderController::class, 'cancel'])->name('orders.cancel');
});

// Order tracking (public - no login required)
Route::get('/track-order', [App\Http\Controllers\Customer\OrderController::class, 'track'])->name('orders.track');
Route::get('/checkout/paypal/success', [App\Http\Controllers\CheckoutController::class, 'paypalSuccess'])->name('checkout.paypal.success');
Route::get('/checkout/paypal/cancel', [App\Http\Controllers\CheckoutController::class, 'paypalCancel'])->name('checkout.paypal.cancel');

// Blog routes (Posts)
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{slug}', [BlogController::class, 'tag'])->name('blog.tag');

// Pages routes (must be last to avoid conflicts)
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

// Customer Profile routes (requires authentication)
Route::middleware('auth')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/profile', [App\Http\Controllers\Customer\ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [App\Http\Controllers\Customer\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Customer\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\Customer\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [App\Http\Controllers\Customer\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Test route
Route::get('/test-shop', function () {
    $shop = App\Models\Shop::first();
    if ($shop) {
        return response()->json([
            'shop_slug' => $shop->shop_slug,
            'shop_name' => $shop->shop_name,
            'exists' => true
        ]);
    }
    return response()->json(['exists' => false]);
});

// Test shop show route
Route::get('/test-shop-show/{slug}', function ($slug) {
    try {
        $shop = App\Models\Shop::where('shop_slug', $slug)->first();
        if (!$shop) {
            return response()->json(['error' => 'Shop not found'], 404);
        }

        return response()->json([
            'shop' => $shop->toArray(),
            'success' => true
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test ShopController step by step
Route::get('/test-shop-debug/{slug}', function ($slug) {
    try {
        $shop = App\Models\Shop::where('shop_slug', $slug)->first();
        if (!$shop) {
            return response()->json(['error' => 'Shop not found'], 404);
        }

        // Test 1: Basic shop data
        $result = [
            'shop_id' => $shop->id,
            'shop_name' => $shop->shop_name,
            'shop_slug' => $shop->shop_slug
        ];

        // Test 2: User relationship
        try {
            $shop->load('user');
            $result['user_loaded'] = true;
            $result['user_name'] = $shop->user ? $shop->user->name : 'No user';
        } catch (Exception $e) {
            $result['user_error'] = $e->getMessage();
        }

        // Test 3: Products count
        try {
            $productsCount = $shop->products()->count();
            $result['products_count'] = $productsCount;
        } catch (Exception $e) {
            $result['products_error'] = $e->getMessage();
        }

        // Test 4: Followers count
        try {
            $followersCount = $shop->followers()->count();
            $result['followers_count'] = $followersCount;
        } catch (Exception $e) {
            $result['followers_error'] = $e->getMessage();
        }

        // Test 5: Favorites count
        try {
            $favoritesCount = $shop->favorites()->count();
            $result['favorites_count'] = $favoritesCount;
        } catch (Exception $e) {
            $result['favorites_error'] = $e->getMessage();
        }

        // Test 6: Load products with relationships
        try {
            $shop->load(['products' => function ($query) {
                $query->where('status', 'active')
                    ->with(['template', 'variants'])
                    ->orderBy('created_at', 'desc');
            }]);
            $result['products_with_relationships'] = true;
            $result['products_loaded_count'] = $shop->products->count();
        } catch (Exception $e) {
            $result['products_relationships_error'] = $e->getMessage();
        }

        return response()->json($result);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test ShopController complete
Route::get('/test-shop-controller-complete/{slug}', function ($slug) {
    try {
        $shop = App\Models\Shop::where('shop_slug', $slug)->first();
        if (!$shop) {
            return response()->json(['error' => 'Shop not found'], 404);
        }

        $controller = new App\Http\Controllers\ShopController();

        // Test the show method step by step
        $result = [];

        // Test 1: Load relationships
        try {
            $shop->load(['user', 'products' => function ($query) {
                $query->where('status', 'active')
                    ->with(['template', 'variants'])
                    ->orderBy('created_at', 'desc');
            }]);
            $result['step1_relationships'] = 'OK';
        } catch (Exception $e) {
            $result['step1_error'] = $e->getMessage();
        }

        // Test 2: Get stats
        try {
            $stats = [
                'total_products' => $shop->products()->where('status', 'active')->count(),
                'followers' => $shop->followers()->count(),
                'favorited' => $shop->favorites()->count(),
            ];
            $result['step2_stats'] = 'OK';
            $result['stats'] = $stats;
        } catch (Exception $e) {
            $result['step2_error'] = $e->getMessage();
        }

        // Test 3: Get categories
        try {
            $categories = App\Models\Category::whereHas('templates.products', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id)->where('status', 'active');
            })->with(['templates.products' => function ($query) use ($shop) {
                $query->where('shop_id', $shop->id)->where('status', 'active')->limit(1);
            }])->get();
            $result['step3_categories'] = 'OK';
            $result['categories_count'] = $categories->count();
        } catch (Exception $e) {
            $result['step3_error'] = $e->getMessage();
        }

        // Test 4: Get hot products
        try {
            $hotProducts = $shop->products()
                ->where('status', 'active')
                ->with(['template', 'variants'])
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
            $result['step4_hot_products'] = 'OK';
            $result['hot_products_count'] = $hotProducts->count();
        } catch (Exception $e) {
            $result['step4_error'] = $e->getMessage();
        }

        // Test 5: Get all products
        try {
            $allProducts = $shop->products()
                ->where('status', 'active')
                ->with(['template', 'variants'])
                ->orderBy('created_at', 'desc')
                ->paginate(24);
            $result['step5_all_products'] = 'OK';
            $result['all_products_count'] = $allProducts->count();
        } catch (Exception $e) {
            $result['step5_error'] = $e->getMessage();
        }

        return response()->json($result);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test view rendering
Route::get('/test-shop-view/{slug}', function ($slug) {
    try {
        $shop = App\Models\Shop::where('shop_slug', $slug)->first();
        if (!$shop) {
            return response()->json(['error' => 'Shop not found'], 404);
        }

        // Load relationships
        $shop->load(['user', 'products' => function ($query) {
            $query->where('status', 'active')
                ->with(['template', 'variants'])
                ->orderBy('created_at', 'desc');
        }]);

        // Get shop statistics
        $stats = [
            'total_products' => $shop->products()->where('status', 'active')->count(),
            'followers' => $shop->followers()->count(),
            'favorited' => $shop->favorites()->count(),
        ];

        // Get product categories for this shop
        $categories = App\Models\Category::whereHas('templates.products', function ($query) use ($shop) {
            $query->where('shop_id', $shop->id)->where('status', 'active');
        })->with(['templates.products' => function ($query) use ($shop) {
            $query->where('shop_id', $shop->id)->where('status', 'active')->limit(1);
        }])->get();

        // Get hot products (most viewed/favorited)
        $hotProducts = $shop->products()
            ->where('status', 'active')
            ->with(['template', 'variants'])
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        // Get all products for the shop
        $allProducts = $shop->products()
            ->where('status', 'active')
            ->with(['template', 'variants'])
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        // Check if current user follows this shop
        $isFollowing = false;
        if (Auth::check()) {
            $isFollowing = $shop->followers()->where('user_id', Auth::id())->exists();
        }

        // Test view rendering
        try {
            $view = view('shops.show', compact(
                'shop',
                'stats',
                'categories',
                'hotProducts',
                'allProducts',
                'isFollowing'
            ));

            $html = $view->render();
            return response()->json([
                'success' => true,
                'html_length' => strlen($html)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'view_error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::post('/shops/{shop}/follow', [App\Http\Controllers\ShopController::class, 'follow'])->name('shops.follow');
Route::post('/shops/{shop}/contact', [App\Http\Controllers\ShopController::class, 'contact'])->name('shops.contact');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

// Cart API routes (with web middleware for session support)
Route::prefix('api/cart')->middleware('web')->group(function () {
    Route::post('/add', [ApiCartController::class, 'add'])->name('api.cart.add');
    Route::get('/get', [ApiCartController::class, 'get'])->name('api.cart.get');
    Route::put('/update/{id}', [ApiCartController::class, 'update'])->name('api.cart.update');
    Route::delete('/remove/{id}', [ApiCartController::class, 'remove'])->name('api.cart.remove');
    Route::delete('/clear', [ApiCartController::class, 'clear'])->name('api.cart.clear');
    Route::post('/sync', [ApiCartController::class, 'sync'])->name('api.cart.sync');
});

// Product API routes for AI integration (with CORS support)
Route::prefix('api/products')->middleware(['web'])->group(function () {
    // Add OPTIONS route for CORS preflight
    Route::options('/create', function () {
        return response('', 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
    });

    Route::post('/create', [App\Http\Controllers\Api\ProductController::class, 'create'])
        ->name('api.products.create');
    Route::get('/{id}', [App\Http\Controllers\Api\ProductController::class, 'show'])
        ->name('api.products.show');
    Route::get('/', [App\Http\Controllers\Api\ProductController::class, 'index'])
        ->name('api.products.index');
});

// Demo routes
Route::get('/demo/color-libraries', function () {
    return view('demo.color-libraries');
})->name('demo.color-libraries');

Route::get('/test-color', function () {
    return view('test-color');
})->name('test.color');

Route::get('/test-colour', function () {
    return view('test-colour');
})->name('test.colour');

Route::get('/test-complete-colors', function () {
    return view('test-complete-colors');
})->name('test.complete-colors');

Route::get('/test-all-colors-display', function () {
    return view('test-all-colors-display');
})->name('test.all-colors-display');

Route::get('/dashboard', function () {
    $user = auth()->user();

    // Redirect based on role
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->hasRole('seller')) {
        return redirect()->route('admin.seller.dashboard');
    }

    // Default dashboard for customers
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes (Admin only)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('categories', AdminCategoryController::class);
        Route::get('categories/featured/manage', [AdminCategoryController::class, 'featured'])->name('categories.featured');
        Route::put('categories/featured/update', [AdminCategoryController::class, 'updateFeatured'])->name('categories.update-featured');

        // Pages (Admin only)
        Route::post('pages/upload-image', [AdminPageController::class, 'uploadImage'])->name('pages.upload-image');
        Route::resource('pages', AdminPageController::class);

        // Orders management (Admin only)
        Route::resource('orders', App\Http\Controllers\Admin\OrderController::class);
        Route::get('orders/export', [App\Http\Controllers\Admin\OrderController::class, 'export'])->name('orders.export');

        // Shipping Management (Admin only)
        Route::resource('shipping-zones', ShippingZoneController::class);
        Route::resource('shipping-rates', ShippingRateController::class);

        // API Token (Admin only)
        Route::get('/api-token', [App\Http\Controllers\ApiDocController::class, 'tokenDashboard'])->name('api-token');
    });

    // Seller routes (Seller + Admin)
    Route::prefix('admin')->name('admin.')->middleware('role:seller|admin')->group(function () {
        Route::get('/seller/dashboard', [SellerDashboardController::class, 'index'])->name('seller.dashboard');

        // Product Templates with clone route
        Route::post('product-templates/{product_template}/clone', [ProductTemplateController::class, 'clone'])->name('product-templates.clone');
        Route::resource('product-templates', ProductTemplateController::class);

        // Products Import
        Route::get('products/import', [ProductImportController::class, 'showImportForm'])->name('products.import');
        Route::post('products/import', [ProductImportController::class, 'import'])->name('products.import.process');
        Route::get('products/import/template', [ProductImportController::class, 'downloadTemplate'])->name('products.import.template');

        // Products
        Route::resource('products', AdminProductController::class);
        Route::post('products/bulk-delete', [AdminProductController::class, 'bulkDelete'])->name('products.bulk-delete');

        // Collections
        Route::resource('collections', AdminCollectionController::class);
        Route::post('collections/{collection}/toggle-featured', [AdminCollectionController::class, 'toggleFeatured'])->name('collections.toggle-featured');
        Route::post('collections/update-sort-order', [AdminCollectionController::class, 'updateSortOrder'])->name('collections.update-sort-order');

        // Admin approval routes
        Route::post('collections/{collection}/approve', [AdminCollectionController::class, 'approve'])->name('collections.approve');
        Route::post('collections/{collection}/reject', [AdminCollectionController::class, 'reject'])->name('collections.reject');
        Route::post('collections/bulk-approve', [AdminCollectionController::class, 'bulkApprove'])->name('collections.bulk-approve');

        // Posts (Seller can manage their own posts)
        Route::resource('posts', SellerPostController::class);
        Route::post('posts/{post}/approve', [SellerPostController::class, 'approve'])->name('posts.approve');
        Route::post('posts/{post}/reject', [SellerPostController::class, 'reject'])->name('posts.reject');

        // Post Categories (Admin only)
        Route::resource('post-categories', App\Http\Controllers\Admin\PostCategoryController::class);

        // Post Tags (Admin only)
        Route::resource('post-tags', App\Http\Controllers\Admin\PostTagController::class);
    });

    // Demo routes với role middleware
    Route::get('/admin-only', function () {
        return response()->json(['message' => 'Only admin can see this']);
    })->middleware('role:admin');

    Route::get('/seller-only', function () {
        return response()->json(['message' => 'Only seller can see this']);
    })->middleware('role:seller');

    Route::get('/customer-only', function () {
        return response()->json(['message' => 'Only customer can see this']);
    })->middleware('role:customer');

    Route::get('/user-management', function () {
        return response()->json(['message' => 'User management area']);
    })->middleware('permission:view-users');

    // Seller Shop Routes
    Route::prefix('seller')->name('seller.')->middleware('role:seller|admin')->group(function () {
        Route::get('/shop/create', [SellerShopController::class, 'create'])->name('shop.create');
        Route::post('/shop', [SellerShopController::class, 'store'])->name('shop.store');

        Route::middleware(['has.shop'])->group(function () {
            Route::get('/shop/dashboard', [SellerShopController::class, 'dashboard'])->name('shop.dashboard');
            Route::get('/shop/edit', [SellerShopController::class, 'edit'])->name('shop.edit');
            Route::put('/shop', [SellerShopController::class, 'update'])->name('shop.update');

            // Seller Orders
            Route::resource('orders', App\Http\Controllers\Seller\OrderController::class);
            Route::get('orders/export', [App\Http\Controllers\Seller\OrderController::class, 'export'])->name('orders.export');
        });
    });

    // Admin Shop Management
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/shops', [AdminShopController::class, 'index'])->name('shops.index');
        Route::post('/shops/{shop}/verify', [AdminShopController::class, 'verify'])->name('shops.verify');
        Route::post('/shops/{shop}/suspend', [AdminShopController::class, 'suspend'])->name('shops.suspend');
        Route::post('/shops/{shop}/activate', [AdminShopController::class, 'activate'])->name('shops.activate');
    });
});

Route::get('/test-zoom-effect', function () {
    return view('test-zoom-effect');
});

Route::get('/test-cart', function () {
    return view('test-cart');
})->name('test.cart');

// Page routes - Must be at the end to avoid conflicts
Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show')->where('slug', '^(?!admin|api|dashboard|cart|checkout|wishlist|search|collections|products|category|shops|blog|login|register|password|email|verification|logout|seller).*$');

require __DIR__ . '/auth.php';
