<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductTemplate;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Collection;
use App\Models\GmcConfig;
use App\Services\GoogleMerchantCenterService;
use App\Support\S3Media;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Admin xem tất cả products, Seller chỉ xem products của mình
        $productsQuery = Product::with(['template.category', 'template.user', 'user', 'shop', 'variants', 'collections']);

        // Apply user filter
        if (!$user->hasRole('admin')) {
            $productsQuery->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->filled('category_id')) {
            $productsQuery->inCategoryIds([(int) $request->category_id]);
        }

        if ($request->filled('template_id')) {
            $productsQuery->where('template_id', $request->template_id);
        }

        if ($request->filled('shop_id')) {
            $productsQuery->where('shop_id', $request->shop_id);
        }

        if ($request->filled('collection_id')) {
            $productsQuery->whereHas('collections', function ($q) use ($request) {
                $q->where('collections.id', $request->collection_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('template', function ($templateQuery) use ($search) {
                        $templateQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Get filter options for dropdowns
        $categories = Category::orderBy('name', 'asc')->get();

        $templatesQuery = ProductTemplate::orderBy('name', 'asc');
        if (!$user->hasRole('admin')) {
            $templatesQuery->where('user_id', $user->id);
        }
        $templates = $templatesQuery->get();

        $shops = null;
        if ($user->hasRole('admin')) {
            $shops = Shop::orderBy('shop_name', 'asc')->get();
        }

        $collectionsQuery = Collection::orderBy('name', 'asc');
        if (!$user->hasRole('admin')) {
            $collectionsQuery->where('user_id', $user->id);
        }
        $collections = $collectionsQuery->get();

        // Apply default sorting
        $productsQuery->orderBy('created_at', 'desc');

        // Per-page selection
        $perPage = (int) $request->input('per_page', 12);
        $allowedPerPage = [12, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 12;
        }

        // Paginate
        $products = $productsQuery->paginate($perPage)->withQueryString();

        // Get GMC configs for current domain (to show only available markets in modal)
        $currentDomain = $request->getHost();
        $currentDomain = preg_replace('/^www\./', '', $currentDomain);
        $availableGmcConfigs = GmcConfig::where('domain', $currentDomain)
            ->where('is_active', true)
            ->orderBy('target_country')
            ->get();

        return view('admin.products.index', compact('products', 'categories', 'templates', 'shops', 'collections', 'availableGmcConfigs', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        // Check if seller has shop (required for sellers)
        if ($user->hasRole('seller') && !$user->hasShop()) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'You need to create a shop first before adding products!');
        }

        // Get templates based on role
        if ($user->hasRole('admin')) {
            $templates = ProductTemplate::with(['category', 'attributes', 'variants'])
                ->orderBy('name', 'asc')
                ->get();
        } else {
            // Seller chỉ thấy templates của mình
            $templates = ProductTemplate::with(['category', 'attributes', 'variants'])
                ->where('user_id', $user->id)
                ->orderBy('name', 'asc')
                ->get();
        }

        // Get all shops for admin to assign products
        $shops = null;
        if ($user->hasRole('admin')) {
            $shops = Shop::with('user')
                ->orderBy('shop_name', 'asc')
                ->get();
        }

        $categories = Category::with('parent')
            ->orderBy('parent_id', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.products.create', compact('templates', 'shops', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'template_id' => 'required|exists:product_templates,id',
                'name' => 'required|string|max:255',
                'price_type' => 'required|in:template,override,add',
                'price' => 'nullable|numeric',
                'list_price' => 'nullable|numeric|min:0',
                'category_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
                'keywords' => 'nullable|string|max:2000',
                'quantity' => 'required|integer|min:0',
                'status' => 'required|in:active,inactive,draft',
                'shop_id' => $user->hasRole('admin') ? 'nullable|exists:shops,id' : 'nullable',
                'media.*' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240',
                'variants' => 'nullable|array',
                'variants.*.variant_name' => 'nullable|string',
                'variants.*.variant_key' => 'nullable|string',
                'variants.*.attributes' => 'nullable|string',
                'variants.*.price' => 'required_with:variants.*.variant_name|numeric|min:0',
                'variants.*.list_price' => 'nullable|numeric|min:0',
                'variants.*.quantity' => 'nullable|integer|min:0',
            ]);

            // Check if seller has shop (required for sellers)
            if ($user->hasRole('seller') && !$user->hasShop()) {
                return redirect()->route('seller.shop.create')
                    ->with('warning', 'You need to create a shop first before adding products!');
            }

            $template = ProductTemplate::with('variants')->findOrFail($request->template_id);

            $data = $request->only([
                'template_id',
                'name',
                'quantity',
                'status',
                'shop_id',
            ]);
            $data = array_merge($template->snapshotForProduct(), $data);
            if ($request->filled('list_price')) {
                $data['list_price'] = $request->input('list_price');
            }
            $data['category_id'] = $request->filled('category_id')
                ? $request->input('category_id')
                : ($data['category_id'] ?? null);
            $data['slug'] = $this->generateUniqueSlug($request->name);
            $data['sku'] = $this->generateUniqueSKU();
            $data['user_id'] = auth()->id(); // Set product owner
            $data['keywords'] = app(\App\Services\CollectionKeywordSyncService::class)
                ->normalize($request->input('keywords'));
            if (empty($data['keywords'])) {
                $data['keywords'] = null;
            }

            // Set shop_id based on user role
            if ($user->hasRole('admin')) {
                // Admin can assign to any shop via form
                $data['shop_id'] = $request->shop_id;
            } elseif ($user->hasShop()) {
                // Seller uses their own shop
                $data['shop_id'] = $user->shop->id;
            }

            if ($request->price_type === 'template') {
                // Use template price - save the actual template price to database
                $data['price'] = $template->base_price;
            } elseif ($request->price_type === 'override') {
                // Override with custom price
                $data['price'] = $request->price;
            } elseif ($request->price_type === 'add') {
                // Add to template price
                $addAmount = floatval($request->price ?? 0);
                $data['price'] = $template->base_price + $addAmount;
            }

            $customDescription = trim($request->description ?? '');
            if ($customDescription !== '') {
                $data['description'] = $customDescription;
            }

            Log::info('Price calculation', [
                'price_type' => $request->price_type,
                'template_price' => $template->base_price,
                'input_price' => $request->price,
                'final_price' => $data['price']
            ]);

            Log::info('Description logic', [
                'custom_description' => $request->description,
                'template_description' => $template->description,
                'final_description' => $data['description']
            ]);

            // Handle media upload to S3
            if ($request->hasFile('media')) {
                $mediaFiles = $request->file('media');
                $mediaUrls = [];

                // Get custom order if provided
                $mediaOrder = $request->input('media_order');
                $orderedIndices = $mediaOrder ? explode(',', $mediaOrder) : null;

                // If order is provided, reorder files accordingly
                if ($orderedIndices && count($orderedIndices) === count($mediaFiles)) {
                    $orderedFiles = [];
                    foreach ($orderedIndices as $index) {
                        $orderedFiles[] = $mediaFiles[(int)$index];
                    }
                    $mediaFiles = $orderedFiles;
                }

                foreach ($mediaFiles as $file) {
                    if (!$file instanceof UploadedFile || !$file->isValid()) {
                        if ($file instanceof UploadedFile) {
                            Log::error('Invalid file uploaded', ['file' => $file->getClientOriginalName()]);
                        }
                        continue;
                    }

                    // Copy to a real local path first — putFileAs(UploadedFile)
                    // uses getRealPath() which is empty on Windows.
                    $url = S3Media::upload($file, 'products');
                    if ($url) {
                        $mediaUrls[] = $url;
                        Log::info('File uploaded successfully', [
                            'file' => $file->getClientOriginalName(),
                            'url' => $url,
                        ]);
                    } else {
                        Log::error('Failed to upload file to S3', [
                            'file' => $file->getClientOriginalName(),
                        ]);
                    }
                }

                if (!empty($mediaUrls)) {
                    $data['media'] = $mediaUrls;
                }
            }

            $product = Product::create($data);

            // Variants are auto-created from the template; apply the form's base price / quantity.
            if ($request->has('variants')) {
                foreach ($request->variants as $variantData) {
                    $variantName = $variantData['variant_name'] ?? '';
                    if ($variantName === '') {
                        continue;
                    }

                    $price = $variantData['price'] ?? null;
                    $listPrice = $variantData['list_price'] ?? null;
                    $quantity = $variantData['quantity'] ?? null;
                    $templateVariant = $template?->variants?->firstWhere('variant_name', $variantName);
                    $fallbackPrice = $templateVariant?->price ?? $template?->base_price;
                    $fallbackListPrice = $templateVariant?->list_price ?? $template?->list_price;

                    $existing = $product->variants()->where('variant_name', $variantName)->first();
                    if ($existing) {
                        $existing->update([
                            'price' => ($price === '' || $price === null) ? $existing->price : $price,
                            'list_price' => ($listPrice === '' || $listPrice === null) ? $existing->list_price : $listPrice,
                            'quantity' => ($quantity === '' || $quantity === null) ? $existing->quantity : $quantity,
                        ]);

                        continue;
                    }

                    $attributes = $variantData['attributes'] ?? [];
                    if (is_string($attributes)) {
                        $attributes = json_decode($attributes, true) ?: [];
                    }

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'template_id' => $request->template_id,
                        'variant_name' => $variantName,
                        'attributes' => $attributes,
                        'price' => ($price === '' || $price === null) ? $fallbackPrice : $price,
                        'list_price' => ($listPrice === '' || $listPrice === null) ? $fallbackListPrice : $listPrice,
                        'sku' => 'SKU-' . strtoupper(Str::random(8)),
                        'quantity' => ($quantity === '' || $quantity === null) ? 100 : $quantity,
                        'media' => null,
                    ]);
                }
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            // Database errors (like duplicate entry, foreign key constraints, etc.)
            $errorMessage = 'Database error occurred while creating the product.';

            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $errorMessage = 'A product with similar information already exists. Please check the product name or try again.';
            } elseif (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                $errorMessage = 'Invalid template or shop selected. Please check your selections.';
            } elseif (strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
                $errorMessage = 'Data integrity error. Please check all required fields are filled correctly.';
            }

            return back()->with('error', $errorMessage)->withInput();
        } catch (\Exception $e) {
            // Any other unexpected errors
            \Log::error('Product creation error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->except(['media']),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An unexpected error occurred while creating the product. Please try again or contact support if the problem persists.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['template', 'variants']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Duplicate a product
     */
    public function duplicate(Product $product)
    {
        try {
            $user = auth()->user();

            // Check authorization
            if (!$user->hasRole('admin') && $product->template->user_id !== $user->id) {
                abort(403, 'Unauthorized action.');
            }

            // Load product with relationships
            $product->load(['variants']);

            // Create new product data
            $newProductData = [
                'template_id' => $product->template_id,
                'category_id' => $product->category_id ?? $product->template?->category_id,
                'user_id' => $user->id,
                'shop_id' => $product->shop_id,
                'name' => $product->name . ' (Copy)',
                'slug' => $this->generateUniqueSlug($product->name . ' (Copy)'),
                'sku' => $this->generateUniqueSKU(),
                'price' => $product->price,
                'list_price' => $product->list_price,
                'description' => $product->description,
                'allow_customization' => $product->getAttributes()['allow_customization'] ?? $product->allow_customization,
                'customizations' => $product->getAttributes()['customizations'] ?? $product->customizations,
                'media' => $product->media, // Copy media array
                'quantity' => $product->quantity,
                'status' => 'draft', // Set to draft by default
            ];

            // Create the duplicated product
            $newProduct = Product::create($newProductData);

            // Duplicate variants if they exist
            if ($product->variants && $product->variants->count() > 0) {
                foreach ($product->variants as $variant) {
                    \App\Models\ProductVariant::create([
                        'template_id' => $product->template_id,
                        'product_id' => $newProduct->id,
                        'variant_name' => $variant->variant_name,
                        'attributes' => $variant->attributes,
                        'price' => $variant->price,
                        'list_price' => $variant->list_price,
                        'quantity' => $variant->quantity,
                        'sku' => 'SKU-' . strtoupper(Str::random(8)), // Generate new unique SKU
                        'media' => $variant->media,
                    ]);
                }
            }

            // Note: Shop products count is automatically incremented by Product model's created event

            Log::info('Product duplicated', [
                'original_product_id' => $product->id,
                'new_product_id' => $newProduct->id,
                'user_id' => $user->id
            ]);

            return redirect()->route('admin.products.edit', $newProduct)
                ->with('success', 'Product duplicated successfully! You can now edit the duplicated product.');
        } catch (\Exception $e) {
            Log::error('Product duplication error: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.products.index')
                ->with('error', 'Failed to duplicate product: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $user = auth()->user();

        // Check authorization
        if (!$user->hasRole('admin') && $product->template->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Get all shops for admin to assign products
        $shops = null;
        if ($user->hasRole('admin')) {
            $shops = Shop::with('user')
                ->orderBy('shop_name', 'asc')
                ->get();
        }

        $product->load(['template.variants', 'template.attributes', 'variants']);

        $categories = Category::with('parent')
            ->orderBy('parent_id', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.products.edit', compact('product', 'shops', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        try {
            $user = auth()->user();

            // Check authorization
            if (!$user->hasRole('admin') && $product->template->user_id !== $user->id) {
                abort(403, 'Unauthorized action.');
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'nullable|numeric|min:0',
                'list_price' => 'nullable|numeric|min:0',
                'category_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
                'keywords' => 'nullable|string|max:2000',
                'quantity' => 'required|integer|min:0',
                'status' => 'required|in:active,inactive,draft',
                'shop_id' => $user->hasRole('admin') ? 'nullable|exists:shops,id' : 'nullable',
                'media.*' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240',
                'current_media_order' => 'nullable|array',
                'variants' => 'nullable|array',
                'variants.*.id' => 'nullable|exists:product_variants,id',
                'variants.*.variant_name' => 'nullable|string',
                'variants.*.price' => 'required_with:variants.*.variant_name|numeric|min:0',
                'variants.*.list_price' => 'nullable|numeric|min:0',
                'variants.*.quantity' => 'nullable|integer|min:0',
            ]);

            $data = $request->only([
                'name',
                'price',
                'list_price',
                'description',
                'quantity',
                'status',
                'shop_id',
            ]);
            $data['category_id'] = $request->filled('category_id')
                ? $request->input('category_id')
                : null;

            $keywords = app(\App\Services\CollectionKeywordSyncService::class)
                ->normalize($request->input('keywords'));
            $data['keywords'] = $keywords ?: null;

            // Chỉ tạo slug mới nếu tên sản phẩm thay đổi
            if ($request->name !== $product->name) {
                $data['slug'] = $this->generateUniqueSlug($request->name, $product->id);
            }
            // Nếu tên không đổi, giữ nguyên slug cũ (không thêm vào $data)

            // Preserve and reorder current media based on submitted order
            $existingMediaOrder = $request->input('current_media_order', []);
            $orderedExistingMedia = [];

            if (is_array($existingMediaOrder) && !empty($existingMediaOrder)) {
                foreach ($existingMediaOrder as $mediaUrl) {
                    $mediaUrl = trim($mediaUrl);
                    if (!empty($mediaUrl)) {
                        $orderedExistingMedia[] = $mediaUrl;
                    }
                }
            } else {
                // Fallback to current product media if no order provided
                if (is_array($product->media)) {
                    $orderedExistingMedia = $product->media;
                } elseif (!empty($product->media)) {
                    $orderedExistingMedia = is_string($product->media)
                        ? json_decode($product->media, true) ?? []
                        : (array) $product->media;
                }
            }

            // Handle uploaded media
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    if (!$file instanceof UploadedFile || !$file->isValid()) {
                        continue;
                    }

                    $url = S3Media::upload($file, 'products');
                    if ($url) {
                        $orderedExistingMedia[] = $url;
                    }
                }
            }

            if (!empty($orderedExistingMedia)) {
                // Re-index array to ensure clean JSON encoding
                $data['media'] = array_values($orderedExistingMedia);
            } else {
                $data['media'] = [];
            }

            $product->update($data);

            // Update product variants
            if ($request->has('variants')) {
                foreach ($request->variants as $variantData) {
                    if (isset($variantData['id']) && $variantData['id']) {
                        // Update existing variant
                        $variant = \App\Models\ProductVariant::where('id', $variantData['id'])
                            ->where('product_id', $product->id)
                            ->first();

                        if ($variant) {
                            $variant->update([
                                'price' => $variantData['price'] ?? $variant->price,
                                'list_price' => $variantData['list_price'] ?? $variant->list_price,
                                'quantity' => $variantData['quantity'] ?? $variant->quantity,
                            ]);
                        }
                    }
                }
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            // Database errors
            $errorMessage = 'Database error occurred while updating the product.';

            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $errorMessage = 'A product with similar information already exists. Please check the product name or try again.';
            } elseif (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                $errorMessage = 'Invalid data selected. Please check your selections.';
            } elseif (strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
                $errorMessage = 'Data integrity error. Please check all required fields are filled correctly.';
            }

            return back()->with('error', $errorMessage)->withInput();
        } catch (\Exception $e) {
            // Any other unexpected errors
            \Log::error('Product update error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'request_data' => $request->except(['media']),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An unexpected error occurred while updating the product. Please try again or contact support if the problem persists.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $user = auth()->user();

        // Check authorization
        if (!$user->hasRole('admin') && $product->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // Decrement shop product count if product has shop
        if ($product->shop) {
            $product->shop->decrement('total_products');
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully!');
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete(Request $request)
    {
        try {
            Log::info('Bulk delete started', [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            $user = auth()->user();

            // Validate request data
            try {
                $request->validate([
                    'product_ids' => 'required|array',
                    'product_ids.*' => 'exists:products,id',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                Log::error('Bulk delete validation failed', [
                    'errors' => $e->errors()
                ]);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid product IDs provided.',
                        'errors' => $e->errors()
                    ], 422);
                }

                throw $e;
            }

            $productIds = $request->product_ids;
            Log::info('Attempting to delete products', [
                'product_ids' => $productIds,
                'count' => count($productIds)
            ]);

            $products = Product::with('shop')->whereIn('id', $productIds)->get();

            if ($products->isEmpty()) {
                Log::warning('No products found for IDs', ['product_ids' => $productIds]);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No products found with the provided IDs.'
                    ], 404);
                }

                return back()->with('error', 'No products found with the provided IDs.');
            }

            // Check authorization for each product
            $deletedCount = 0;
            $shopProductCounts = [];
            $errors = [];

            foreach ($products as $product) {
                try {
                    // Admin can delete all, Seller can only delete their own products
                    if ($user->hasRole('admin') || $product->user_id === $user->id) {
                        // Track shop product counts
                        if ($product->shop_id) {
                            if (!isset($shopProductCounts[$product->shop_id])) {
                                $shopProductCounts[$product->shop_id] = 0;
                            }
                            $shopProductCounts[$product->shop_id]++;
                        }

                        $product->delete();
                        $deletedCount++;

                        Log::info('Product deleted successfully', [
                            'product_id' => $product->id,
                            'product_name' => $product->name
                        ]);
                    } else {
                        $errors[] = "No permission to delete product: {$product->name}";
                        Log::warning('User lacks permission to delete product', [
                            'product_id' => $product->id,
                            'user_id' => $user->id,
                            'product_user_id' => $product->user_id
                        ]);
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to delete product: {$product->name} - " . $e->getMessage();
                    Log::error('Failed to delete product', [
                        'product_id' => $product->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Update shop product counts
            foreach ($shopProductCounts as $shopId => $count) {
                try {
                    $shop = \App\Models\Shop::find($shopId);
                    if ($shop) {
                        $shop->total_products = $shop->products()->count();
                        $shop->save();
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to update shop product count', [
                        'shop_id' => $shopId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $message = $deletedCount === 0
                ? 'No products were deleted. You may not have permission to delete the selected products.'
                : "{$deletedCount} product(s) deleted successfully! 🗑️";

            // Add error details to message if there were errors
            if (!empty($errors)) {
                $message .= "\nErrors: " . implode('; ', $errors);
            }

            $success = $deletedCount > 0;

            Log::info('Bulk delete completed', [
                'deleted_count' => $deletedCount,
                'total_requested' => count($productIds),
                'errors' => $errors
            ]);

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => $success,
                    'message' => $message,
                    'deleted_count' => $deletedCount,
                    'errors' => $errors
                ]);
            }

            // Return redirect for form submissions
            if ($deletedCount === 0) {
                return back()->with('error', $message);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Bulk delete failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            $message = 'An unexpected error occurred while deleting products. Please try again.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', $message);
        }
    }

    /**
     * Generate a unique slug for the product
     * 
     * @param string $name Product name
     * @param int|null $excludeProductId Product ID to exclude from uniqueness check (for updates)
     * @return string Unique slug
     */
    private function generateUniqueSlug($name, $excludeProductId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        // Check if slug already exists (excluding current product if updating)
        $query = Product::where('slug', $slug);
        if ($excludeProductId) {
            $query->where('id', '!=', $excludeProductId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;

            // Rebuild query for next check
            $query = Product::where('slug', $slug);
            if ($excludeProductId) {
                $query->where('id', '!=', $excludeProductId);
            }
        }

        return $slug;
    }

    /**
     * Generate a unique SKU for a product
     * Format: PRD-{random 8 characters}
     * 
     * @return string Unique SKU
     */
    private function generateUniqueSKU()
    {
        do {
            $sku = 'PRD-' . strtoupper(Str::random(8));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Preview product data that will be sent to GMC (for debugging)
     */
    public function previewGMCData(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            $product = Product::with(['template.category', 'shop', 'variants'])
                ->findOrFail($request->product_id);

            // Check authorization
            $user = auth()->user();
            if (!$user->hasRole('admin') && $product->user_id !== $user->id) {
                abort(403, 'Unauthorized');
            }

            // Get current domain from request
            $currentDomain = $request->getHost();
            $currentDomain = preg_replace('/^www\./', '', $currentDomain);

            // Get target country from request or use default
            $targetCountry = strtoupper($request->input('target_country', 'GB'));

            // Get GMC config for current domain and target country
            $gmcConfig = GmcConfig::getConfigForDomainAndCountry($currentDomain, $targetCountry);

            if (!$gmcConfig) {
                return response()->json([
                    'success' => false,
                    'message' => "Không tìm thấy cấu hình GMC cho domain '{$currentDomain}' và thị trường '{$targetCountry}'. Vui lòng cấu hình GMC trước."
                ], 400);
            }

            // Prepare product data
            $productData = $this->prepareProductForGMC($product, $gmcConfig, $currentDomain);

            if (!$productData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product does not have required data (name, price, image)'
                ], 400);
            }

            // Use service to prepare final API format
            try {
                $gmcService = GoogleMerchantCenterService::fromConfig($gmcConfig);
                $apiData = $gmcService->prepareProductData($productData);
                $apiEndpoint = $gmcService->getApiEndpoint();

                return response()->json([
                    'success' => true,
                    'api_endpoint' => $apiEndpoint,
                    'product_data' => $apiData,
                    'raw_product_data' => $productData,
                    'formatted_json' => json_encode($apiData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                ]);
            } catch (\Exception $e) {
                // If service not configured, still return prepared data
                return response()->json([
                    'success' => true,
                    'message' => 'GMC service not configured, showing prepared data only',
                    'product_data' => $productData,
                    'formatted_json' => json_encode($productData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'error' => $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error preparing product data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Feed selected products to Google Merchant Center
     * Uploads directly via API or generates XML feed
     */
    public function feedToGMC(Request $request)
    {
        try {
            $user = auth()->user();

            // Validate request
            $request->validate([
                'product_ids' => 'required|array',
                'product_ids.*' => 'exists:products,id',
                'method' => 'nullable|in:api,xml', // api or xml
                'target_country' => 'required|string|size:2', // US, GB, VN, etc.
            ]);

            $productIds = $request->product_ids;
            $method = $request->input('method', 'api'); // Default to API
            $targetCountry = strtoupper($request->target_country);

            // Get products with relationships first to determine domain
            $productsQuery = Product::with(['template.category', 'shop', 'variants'])
                ->whereIn('id', $productIds);

            // Check authorization
            if (!$user->hasRole('admin')) {
                $productsQuery->where('user_id', $user->id);
            }

            $products = $productsQuery->get();

            if ($products->isEmpty()) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No products found or you do not have permission to access these products.'
                    ], 404);
                }
                return back()->with('error', 'No products found or you do not have permission to access these products.');
            }

            // Get domain from current request host (where admin is accessing from)
            // This ensures we use the correct domain for the GMC config
            $currentDomain = $request->getHost();
            // Remove www. if present
            $currentDomain = preg_replace('/^www\./', '', $currentDomain);

            // Get GMC config for domain and target country
            $gmcConfig = GmcConfig::getConfigForDomainAndCountry($currentDomain, $targetCountry);

            if (!$gmcConfig) {
                $errorMessage = "Không tìm thấy cấu hình GMC cho domain '{$currentDomain}' và thị trường '{$targetCountry}'. Vui lòng cấu hình GMC trước.";

                Log::warning('GMC Config not found', [
                    'domain' => $currentDomain,
                    'target_country' => $targetCountry,
                    'request_host' => $request->getHost()
                ]);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 400);
                }
                return back()->with('error', $errorMessage);
            }

            // Log domain being used
            Log::info('GMC Feed - Domain determined', [
                'domain_used' => $currentDomain,
                'target_country' => $targetCountry,
                'request_host' => $request->getHost(),
                'gmc_config_id' => $gmcConfig->id,
                'gmc_config_name' => $gmcConfig->name,
                'product_count' => $products->count()
            ]);

            // Use API method if requested and configured
            if ($method === 'api') {
                try {
                    // Create GMC service with config from database
                    $gmcService = GoogleMerchantCenterService::fromConfig($gmcConfig);

                    // Prepare products data for API - use current domain
                    $productsData = [];
                    foreach ($products as $product) {
                        $productData = $this->prepareProductForGMC($product, $gmcConfig, $currentDomain);
                        if ($productData) {
                            $productsData[] = $productData;
                        }
                    }

                    if (empty($productsData)) {
                        throw new \Exception('No valid products to upload. Products must have name, price, and image.');
                    }

                    // Batch upload products
                    $results = $gmcService->batchInsertProducts($productsData);

                    // Log successful batch upload
                    Log::info('GMC Batch upload completed from admin panel', [
                        'user_id' => auth()->id(),
                        'user_email' => auth()->user()->email ?? 'N/A',
                        'total_products' => $results['total'],
                        'success_count' => $results['success_count'],
                        'failed_count' => $results['failed_count'],
                        'product_ids' => $request->input('product_ids', []),
                        'results' => $results
                    ]);

                    // Return JSON response for AJAX requests
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => $results['success_count'] > 0,
                            'message' => "Uploaded {$results['success_count']} of {$results['total']} products to Google Merchant Center",
                            'results' => $results
                        ]);
                    }

                    // Return redirect with results
                    $message = "Successfully uploaded {$results['success_count']} of {$results['total']} products to Google Merchant Center";
                    if ($results['failed_count'] > 0) {
                        $message .= ". {$results['failed_count']} products failed to upload.";
                    }

                    return back()->with('success', $message)->with('gmc_results', $results);
                } catch (\Exception $e) {
                    Log::error('GMC API upload failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    // Fallback to XML if API fails
                    if (str_contains($e->getMessage(), 'not configured') || str_contains($e->getMessage(), 'credentials')) {
                        $errorMessage = 'Google Merchant Center API is not configured. Please configure GMC_MERCHANT_ID and GMC_CREDENTIALS_PATH in .env file. Falling back to XML download.';
                        Log::warning($errorMessage);

                        // Fall through to XML generation
                        $method = 'xml';
                    } else {
                        throw $e;
                    }
                }
            }

            // Generate XML feed (fallback or if explicitly requested)
            if ($method === 'xml') {
                $xml = $this->generateGMCXML($products, $gmcConfig, $currentDomain);

                // Return XML response
                return response($xml, 200)
                    ->header('Content-Type', 'application/xml; charset=utf-8')
                    ->header('Content-Disposition', 'attachment; filename="gmc_feed_' . date('Y-m-d_His') . '.xml"');
            }
        } catch (\Exception $e) {
            Log::error('GMC Feed failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            $message = 'An error occurred while processing GMC feed. Please try again.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', $message);
        }
    }

    /**
     * Prepare product data for Google Merchant Center API
     */
    private function prepareProductForGMC(Product $product, GmcConfig $gmcConfig, string $currentDomain): ?array
    {
        // Skip products without required data
        if (!$product->name || !$product->price) {
            return null;
        }

        // Use current domain to build base URL
        $scheme = request()->getScheme(); // http or https
        $baseUrl = $scheme . '://' . $currentDomain;

        // Ensure baseUrl doesn't end with slash
        $baseUrl = rtrim($baseUrl, '/');

        $media = $product->getEffectiveMedia();
        $primaryImage = !empty($media) ? $media[0] : null;

        // Convert media URL to string if it's an array
        if (is_array($primaryImage)) {
            $primaryImage = $primaryImage['url'] ?? $primaryImage['path'] ?? reset($primaryImage) ?? null;
        }

        if (!$primaryImage) {
            return null; // GMC requires image
        }

        // Ensure image URL is absolute (starts with http:// or https://)
        if ($primaryImage && !preg_match('/^https?:\/\//', $primaryImage)) {
            // If relative URL, make it absolute using baseUrl
            $primaryImage = $baseUrl . '/' . ltrim($primaryImage, '/');
        }

        // Get product URL - use shop domain
        $productUrl = $baseUrl . '/products/' . ($product->slug ?? $product->id);

        // Get description
        $description = $product->getEffectiveDescription();
        $description = strip_tags($description);
        $description = Str::limit($description, 5000); // GMC limit

        // Get category
        $category = $product->template->category->name ?? 'Other';
        $googleCategory = $this->mapToGoogleCategory($category);

        // Availability
        $availability = ($product->quantity > 0 || $product->variants->where('quantity', '>', 0)->count() > 0)
            ? 'in stock'
            : 'out of stock';

        // Get SKU (use as offer_id)
        $offerId = $product->sku ?? 'PRD-' . $product->id;

        // Brand is always Bluprinter
        $brand = 'Bluprinter';

        // Additional images - ensure all are absolute URLs
        $additionalImages = [];
        if (count($media) > 1) {
            $additionalImages = array_slice($media, 1, 10); // GMC allows up to 10 additional images
            $additionalImages = array_map(function ($image) use ($baseUrl) {
                $imageUrl = is_array($image) ? ($image['url'] ?? $image['path'] ?? reset($image)) : $image;

                // Ensure absolute URL
                if ($imageUrl && !preg_match('/^https?:\/\//', $imageUrl)) {
                    $imageUrl = $baseUrl . '/' . ltrim($imageUrl, '/');
                }

                return $imageUrl;
            }, $additionalImages);
            $additionalImages = array_filter($additionalImages);
        }

        // Get country and language from GMC config
        $targetCountry = $gmcConfig->target_country;
        // Get currency from DomainCurrencyConfig (respects domain currency configuration)
        $currency = \App\Models\DomainCurrencyConfig::getCurrencyForDomain($gmcConfig->domain) ?? 'USD';
        $contentLanguage = $gmcConfig->content_language;

        // Convert product price from USD to target currency
        // Products are stored in USD, but need to be converted for different markets
        // Uses currency_rate from DomainCurrencyConfig
        $productPriceUSD = (float)($product->price ?? 0);
        $productPrice = $this->convertProductPrice($productPriceUSD, $currency, $gmcConfig);

        // Ensure price is valid and not empty
        if (empty($productPrice) || $productPrice <= 0) {
            Log::warning('GMC Feed: Product has invalid price', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'original_price' => $product->price,
                'converted_price' => $productPrice
            ]);
            return null; // Skip products without valid price
        }

        // Shipping: only send country, no price
        $shipping = [
            [
                'country' => $targetCountry
            ]
        ];

        // Base product data
        $productData = [
            'offer_id' => $offerId,
            'title' => Str::limit($product->name, 150),
            'description' => $description,
            'link' => $productUrl,
            'image_link' => $primaryImage,
            'price' => number_format($productPrice, 2, '.', ''),
            'currency' => $currency,
            'availability' => $availability,
            'condition' => 'new',
            'brand' => $brand,
            'google_product_category' => $googleCategory,
            'product_type' => $category,
            'mpn' => $offerId,
            'content_language' => $contentLanguage,
            'target_country' => $targetCountry,
            'additional_image_links' => array_values($additionalImages),
            'shipping' => $shipping,
        ];

        // Only add gender, color, and age_group for Clothing category
        $isClothing = stripos($category, 'clothing') !== false ||
            stripos($category, 'apparel') !== false ||
            stripos($category, 'Clothing') !== false ||
            stripos($category, 'Apparel') !== false;

        if ($isClothing) {
            // Get age_group, color, gender from product attributes or use defaults
            // These are required for apparel/clothing products
            $ageGroup = $product->age_group ?? $product->template->age_group ?? 'adult';
            $gender = $product->gender ?? $product->template->gender ?? 'unisex';

            // For clothing products, always use "Black" as default color
            $color = 'Black';

            // Validate age_group values (newborn, infant, toddler, kids, adult)
            $validAgeGroups = ['newborn', 'infant', 'toddler', 'kids', 'adult'];
            if (!in_array(strtolower($ageGroup), $validAgeGroups)) {
                $ageGroup = 'adult'; // Default to adult if invalid
            }

            // Validate gender values (male, female, unisex)
            $validGenders = ['male', 'female', 'unisex'];
            if (!in_array(strtolower($gender), $validGenders)) {
                $gender = 'unisex'; // Default to unisex if invalid
            }

            $productData['age_group'] = strtolower($ageGroup);
            $productData['color'] = $color;
            $productData['gender'] = strtolower($gender);
            $productData['size_system'] = 'us';
            $productData['size_type'] = 'regular';
        }

        return $productData;
    }

    /**
     * Generate XML feed in Google Merchant Center format
     */
    private function generateGMCXML($products, GmcConfig $gmcConfig, string $currentDomain)
    {
        // Use current domain to build base URL
        $scheme = request()->getScheme(); // http or https
        $baseUrl = $scheme . '://' . $currentDomain;

        // Get currency from DomainCurrencyConfig (respects domain currency configuration)
        $currency = \App\Models\DomainCurrencyConfig::getCurrencyForDomain($gmcConfig->domain) ?? 'USD';
        $targetCountry = $gmcConfig->target_country;

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n";
        $xml .= '  <channel>' . "\n";
        $xml .= '    <title>Bluprinter Products Feed</title>' . "\n";
        $xml .= '    <link>' . $baseUrl . '</link>' . "\n";
        $xml .= '    <description>Product feed for Google Merchant Center</description>' . "\n";

        foreach ($products as $product) {
            // Skip products without required data
            if (!$product->name || !$product->price) {
                continue;
            }

            $media = $product->getEffectiveMedia();
            $primaryImage = !empty($media) ? $media[0] : null;

            // Convert media URL to string if it's an array
            if (is_array($primaryImage)) {
                $primaryImage = $primaryImage['url'] ?? $primaryImage['path'] ?? reset($primaryImage) ?? null;
            }

            // Get product URL
            $productUrl = $baseUrl . '/products/' . ($product->slug ?? $product->id);

            // Get description
            $description = $product->getEffectiveDescription();
            $description = strip_tags($description);
            $description = htmlspecialchars($description, ENT_XML1, 'UTF-8');
            $description = Str::limit($description, 5000); // GMC limit

            // Get category
            $category = $product->template->category->name ?? 'Other';
            $googleCategory = $this->mapToGoogleCategory($category);

            // Availability
            $availability = ($product->quantity > 0 || $product->variants->where('quantity', '>', 0)->count() > 0)
                ? 'in stock'
                : 'out of stock';

            // Get SKU
            $sku = $product->sku ?? 'PRD-' . $product->id;

            // Brand is always Bluprinter
            $brand = 'Bluprinter';

            $xml .= '    <item>' . "\n";
            $xml .= '      <g:id>' . htmlspecialchars($sku, ENT_XML1, 'UTF-8') . '</g:id>' . "\n";
            $xml .= '      <title>' . htmlspecialchars(Str::limit($product->name, 150), ENT_XML1, 'UTF-8') . '</title>' . "\n";
            $xml .= '      <description><![CDATA[' . $description . ']]></description>' . "\n";
            $xml .= '      <link>' . htmlspecialchars($productUrl, ENT_XML1, 'UTF-8') . '</link>' . "\n";

            if ($primaryImage) {
                $xml .= '      <g:image_link>' . htmlspecialchars($primaryImage, ENT_XML1, 'UTF-8') . '</g:image_link>' . "\n";
            }

            // Convert product price from USD to target currency for XML feed
            // Uses currency_rate from DomainCurrencyConfig
            $productPriceUSD = (float)$product->price;
            $productPrice = $this->convertProductPrice($productPriceUSD, $currency, $gmcConfig);

            $xml .= '      <g:price>' . number_format($productPrice, 2, '.', '') . ' ' . $currency . '</g:price>' . "\n";
            $xml .= '      <g:availability>' . $availability . '</g:availability>' . "\n";
            $xml .= '      <g:condition>new</g:condition>' . "\n";
            $xml .= '      <g:brand>' . htmlspecialchars($brand, ENT_XML1, 'UTF-8') . '</g:brand>' . "\n";
            $xml .= '      <g:google_product_category>' . htmlspecialchars($googleCategory, ENT_XML1, 'UTF-8') . '</g:google_product_category>' . "\n";
            $xml .= '      <g:product_type>' . htmlspecialchars($category, ENT_XML1, 'UTF-8') . '</g:product_type>' . "\n";
            $xml .= '      <g:mpn>' . htmlspecialchars($sku, ENT_XML1, 'UTF-8') . '</g:mpn>' . "\n";

            // Add additional images if available
            if (count($media) > 1) {
                $additionalImages = array_slice($media, 1, 10); // GMC allows up to 10 additional images
                foreach ($additionalImages as $image) {
                    $imageUrl = is_array($image) ? ($image['url'] ?? $image['path'] ?? reset($image)) : $image;
                    if ($imageUrl && $imageUrl !== $primaryImage) {
                        $xml .= '      <g:additional_image_link>' . htmlspecialchars($imageUrl, ENT_XML1, 'UTF-8') . '</g:additional_image_link>' . "\n";
                    }
                }
            }

            // Add size_system and size_type for Clothing category
            $isClothing = stripos($category, 'clothing') !== false ||
                stripos($category, 'apparel') !== false ||
                stripos($category, 'Clothing') !== false ||
                stripos($category, 'Apparel') !== false;

            if ($isClothing) {
                $xml .= '      <g:size_system>us</g:size_system>' . "\n";
                $xml .= '      <g:size_type>regular</g:size_type>' . "\n";
            }

            $xml .= '    </item>' . "\n";
        }

        $xml .= '  </channel>' . "\n";
        $xml .= '</rss>';

        return $xml;
    }

    /**
     * Show form to delete product from GMC
     */
    public function showDeleteFromGMCForm(Request $request)
    {
        // Get all unique domains from GMC configs
        $domains = GmcConfig::where('is_active', true)
            ->distinct()
            ->orderBy('domain')
            ->pluck('domain')
            ->toArray();

        // Get all GMC configs grouped by domain
        $gmcConfigsByDomain = GmcConfig::where('is_active', true)
            ->orderBy('domain')
            ->orderBy('target_country')
            ->get()
            ->groupBy('domain');

        // Country labels
        $countryLabels = [
            'US' => 'United States (USD)',
            'GB' => 'United Kingdom (GBP)',
            'VN' => 'Vietnam (VND)',
            'CA' => 'Canada (CAD)',
            'AU' => 'Australia (AUD)',
            'DE' => 'Germany (EUR)',
            'FR' => 'France (EUR)',
            'IT' => 'Italy (EUR)',
            'ES' => 'Spain (EUR)',
        ];

        return view('admin.products.delete-from-gmc', compact('domains', 'gmcConfigsByDomain', 'countryLabels'));
    }

    /**
     * Delete a single product from Google Merchant Center by offer_id
     * Simple API endpoint for Postman testing
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProductFromGMC(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'offer_id' => 'required|string',
                'domain' => 'nullable|string', // Optional: domain to determine GMC config
                'target_country' => 'nullable|string|size:2', // Optional: target country (US, GB, VN, etc.)
            ]);

            $offerId = $request->offer_id;
            $domain = $request->input('domain');
            $targetCountry = strtoupper($request->input('target_country', 'US'));

            // Get domain from request if not provided
            if (!$domain) {
                $domain = $request->getHost();
                // Remove port if present
                $domain = preg_replace('/:\d+$/', '', $domain);
                // Remove www. if present
                $domain = preg_replace('/^www\./', '', $domain);
            }

            // Get GMC config for domain and target country
            $gmcConfig = GmcConfig::getConfigForDomainAndCountry($domain, $targetCountry);

            if (!$gmcConfig) {
                Log::warning('GMC Config not found for delete', [
                    'domain' => $domain,
                    'target_country' => $targetCountry,
                    'offer_id' => $offerId
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "Không tìm thấy cấu hình GMC cho domain '{$domain}' và thị trường '{$targetCountry}'. Vui lòng cấu hình GMC trước.",
                    'domain' => $domain,
                    'target_country' => $targetCountry,
                    'offer_id' => $offerId
                ], 400);
            }

            // Create GMC service with config from database
            $gmcService = GoogleMerchantCenterService::fromConfig($gmcConfig);

            // Delete product from GMC
            $deleteResult = $gmcService->deleteProduct($offerId);

            // Log the operation
            Log::info('GMC Delete Product via API', [
                'offer_id' => $offerId,
                'domain' => $domain,
                'target_country' => $targetCountry,
                'success' => $deleteResult['success'],
                'message' => $deleteResult['message'] ?? null,
                'error' => $deleteResult['error'] ?? null
            ]);

            if ($deleteResult['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sản phẩm đã được xóa thành công khỏi Google Merchant Center',
                    'offer_id' => $offerId,
                    'domain' => $domain,
                    'target_country' => $targetCountry,
                    'result' => $deleteResult
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa sản phẩm khỏi Google Merchant Center',
                    'offer_id' => $offerId,
                    'domain' => $domain,
                    'target_country' => $targetCountry,
                    'error' => $deleteResult['error'] ?? 'Unknown error',
                    'result' => $deleteResult
                ], 400);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('GMC Delete Product API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa sản phẩm khỏi Google Merchant Center: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete product(s) from Google Merchant Center
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deleteFromGMC(Request $request)
    {
        try {
            $user = auth()->user();

            // Validate request
            $request->validate([
                'product_ids' => 'required|array',
                'product_ids.*' => 'exists:products,id',
                'target_country' => 'required|string|size:2', // US, GB, VN, etc.
            ]);

            $productIds = $request->product_ids;
            $targetCountry = strtoupper($request->target_country);

            // Get current domain
            $currentDomain = $request->getHost();
            // Remove port if present
            $currentDomain = preg_replace('/:\d+$/', '', $currentDomain);

            // Get GMC config for current domain and target country
            $gmcConfig = GmcConfig::getConfigForDomainAndCountry($currentDomain, $targetCountry);

            if (!$gmcConfig) {
                $errorMessage = "Không tìm thấy cấu hình GMC cho domain '{$currentDomain}' và thị trường '{$targetCountry}'. Vui lòng cấu hình GMC trước.";

                Log::warning('GMC Config not found for delete', [
                    'domain' => $currentDomain,
                    'target_country' => $targetCountry,
                    'request_host' => $request->getHost()
                ]);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 400);
                }
                return back()->with('error', $errorMessage);
            }

            // Get products
            $products = Product::whereIn('id', $productIds)->get();

            if ($products->isEmpty()) {
                $errorMessage = 'Không tìm thấy sản phẩm nào để xóa.';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 404);
                }
                return back()->with('error', $errorMessage);
            }

            // Create GMC service with config from database
            $gmcService = GoogleMerchantCenterService::fromConfig($gmcConfig);

            $results = [
                'success' => [],
                'failed' => [],
                'total' => $products->count(),
                'success_count' => 0,
                'failed_count' => 0
            ];

            // Delete each product from GMC
            foreach ($products as $product) {
                // Get offer_id (SKU or PRD-{id})
                $offerId = $product->sku ?? 'PRD-' . $product->id;

                try {
                    $deleteResult = $gmcService->deleteProduct($offerId);

                    if ($deleteResult['success']) {
                        $results['success'][] = [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'offer_id' => $offerId,
                            'message' => $deleteResult['message']
                        ];
                        $results['success_count']++;
                    } else {
                        $results['failed'][] = [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'offer_id' => $offerId,
                            'error' => $deleteResult['error'] ?? 'Unknown error',
                            'message' => $deleteResult['message']
                        ];
                        $results['failed_count']++;
                    }
                } catch (\Exception $e) {
                    Log::error('GMC Delete Product Error', [
                        'product_id' => $product->id,
                        'offer_id' => $offerId,
                        'error' => $e->getMessage()
                    ]);

                    $results['failed'][] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'offer_id' => $offerId,
                        'error' => $e->getMessage(),
                        'message' => 'Failed to delete product from Google Merchant Center'
                    ];
                    $results['failed_count']++;
                }

                // Add small delay to avoid rate limiting
                usleep(100000); // 0.1 second delay
            }

            // Log batch delete summary
            Log::info('GMC Batch delete completed from admin panel', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email ?? 'N/A',
                'total_products' => $results['total'],
                'success_count' => $results['success_count'],
                'failed_count' => $results['failed_count'],
                'product_ids' => $productIds,
                'results' => $results
            ]);

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => $results['success_count'] > 0,
                    'message' => "Đã xóa {$results['success_count']} / {$results['total']} sản phẩm khỏi Google Merchant Center",
                    'results' => $results
                ]);
            }

            // Return redirect with results
            $message = "Đã xóa thành công {$results['success_count']} / {$results['total']} sản phẩm khỏi Google Merchant Center";
            if ($results['failed_count'] > 0) {
                $message .= ". {$results['failed_count']} sản phẩm không thể xóa.";
            }

            return back()->with('success', $message)->with('gmc_delete_results', $results);
        } catch (\Exception $e) {
            Log::error('GMC Delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $errorMessage = 'Lỗi khi xóa sản phẩm khỏi Google Merchant Center: ' . $e->getMessage();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Convert product price from USD to target currency
     * Products are stored in USD, but need to be converted for different markets
     * Uses currency_rate from DomainCurrencyConfig, otherwise falls back to default rates
     */
    private function convertProductPrice(float $usdPrice, string $targetCurrency, ?GmcConfig $gmcConfig = null): float
    {
        // If currency is USD, return as is
        if ($targetCurrency === 'USD') {
            return $usdPrice;
        }

        // Get conversion rate from DomainCurrencyConfig if available
        $conversionRate = null;
        if ($gmcConfig) {
            $conversionRate = \App\Models\DomainCurrencyConfig::getCurrencyRateForDomain($gmcConfig->domain);
            if ($conversionRate) {
                $conversionRate = (float)$conversionRate;
            }
        }

        // Fallback to default rates if not set in config
        if (!$conversionRate) {
            $conversionRate = $this->getDefaultCurrencyConversionRate($targetCurrency);
        }

        if ($conversionRate) {
            return $usdPrice * $conversionRate;
        }

        // Fallback: return USD price if conversion rate not found
        Log::warning('Product price currency conversion rate not found', [
            'target_currency' => $targetCurrency,
            'usd_price' => $usdPrice,
            'gmc_config_id' => $gmcConfig?->id
        ]);

        return $usdPrice;
    }

    /**
     * Get default currency conversion rate from USD (fallback)
     * Used when currency_rate is not set in DomainCurrencyConfig
     */
    private function getDefaultCurrencyConversionRate(string $targetCurrency): ?float
    {
        // Default currency conversion rates (fallback)
        $conversionRates = [
            'GBP' => 0.79,  // 1 USD = 0.79 GBP
            'VND' => 25000, // 1 USD = 25000 VND
            'EUR' => 0.92,  // 1 USD = 0.92 EUR
            'CAD' => 1.35,  // 1 USD = 1.35 CAD
            'AUD' => 1.52,  // 1 USD = 1.52 AUD
        ];

        return $conversionRates[$targetCurrency] ?? null;
    }

    /**
     * Convert shipping cost from USD to target currency
     * Uses currency_rate from DomainCurrencyConfig, otherwise falls back to default rates
     */
    private function convertShippingCurrency(float $usdAmount, string $targetCurrency, string $targetCountry, ?GmcConfig $gmcConfig = null): float
    {
        // If currency is USD, return as is
        if ($targetCurrency === 'USD') {
            return $usdAmount;
        }

        // Get conversion rate from DomainCurrencyConfig if available
        $conversionRate = null;
        if ($gmcConfig) {
            $conversionRate = \App\Models\DomainCurrencyConfig::getCurrencyRateForDomain($gmcConfig->domain);
            if ($conversionRate) {
                $conversionRate = (float)$conversionRate;
            }
        }

        // Fallback to default rates if not set in config
        if (!$conversionRate) {
            $conversionRate = $this->getDefaultCurrencyConversionRate($targetCurrency);
        }

        if ($conversionRate) {
            return $usdAmount * $conversionRate;
        }

        // Fallback: return USD amount if conversion rate not found
        Log::warning('Shipping currency conversion rate not found', [
            'target_currency' => $targetCurrency,
            'usd_amount' => $usdAmount,
            'gmc_config_id' => $gmcConfig?->id
        ]);

        return $usdAmount;
    }

    /**
     * Map category to Google Product Category
     * Returns a basic category ID - you should customize this based on your actual categories
     */
    private function mapToGoogleCategory($categoryName)
    {
        // Basic mapping - you should expand this based on your actual categories
        $mapping = [
            'Clothing' => '212',
            'Apparel' => '1604',
            'Accessories' => '166',
            'Electronics' => '172',
            'Home & Garden' => '533',
            'Sports & Outdoors' => '888',
            'Toys & Games' => '220',
            'Books' => '266',
            'Health & Beauty' => '376',
        ];

        // Try to find exact match
        foreach ($mapping as $key => $value) {
            if (stripos($categoryName, $key) !== false) {
                return $value;
            }
        }

        // Default category: Other
        return '783';
    }

    /**
     * Export products to Meta Commerce Catalog format (CSV/Excel)
     */
    public function exportToMeta(Request $request)
    {
        $products = $this->productsForCatalogExport($request);
        $baseUrl = rtrim((string) config('app.url'), '/');
        $header = [
            'id',
            'title',
            'description',
            'availability',
            'condition',
            'price',
            'link',
            'image_link',
            'brand',
            'google_product_category',
            'fb_product_category',
            'quantity_to_sell_on_facebook',
            'sale_price',
            'sale_price_effective_date',
            'item_group_id',
            'gender',
            'color',
            'size',
            'age_group',
            'material',
            'pattern',
            'shipping',
            'shipping_weight',
            'video[0].url',
            'video[0].tag[0]',
            'gtin',
            'product_tags[0]',
            'product_tags[1]',
            'style[0]',
        ];

        $csvData = [$header];
        $saleWindow = $this->catalogSaleWindow();

        foreach ($products as $product) {
            foreach ($this->metaCatalogRowsForProduct($product, $baseUrl, $saleWindow) as $row) {
                $csvData[] = $row;
            }
        }

        return $this->downloadCatalogCsv($csvData, 'meta_products_export_' . date('Y-m-d_His') . '.csv');
    }

    /**
     * Export products to TikTok Catalog CSV format.
     */
    public function exportToTikTok(Request $request)
    {
        $products = $this->productsForCatalogExport($request);
        $baseUrl = rtrim((string) config('app.url'), '/');
        $header = [
            'sku_id',
            'title',
            'description',
            'availability',
            'condition',
            'price',
            'link',
            'image_link',
            'video_link',
            'brand',
            'additional_image_link',
            'age_group',
            'color',
            'gender',
            'item_group_id',
            'google_product_category',
            'material',
            'pattern',
            'product_type',
            'sale_price',
            'sale_price_effective_date',
            'shipping',
            'shipping_weight',
            'gtin',
            'mpn',
            'size',
            'tax',
            'ios_url',
            'ios_app_store_id',
            'ios_app_name',
            'iPhone_url',
            'iPhone_app_store_id',
            'iPhone_app_name',
            'iPad_url',
            'iPad_app_store_id',
            'iPad_app_name',
            'android_url',
            'android_package',
            'android_app_name',
            'custom_label_0',
            'custom_label_1',
            'custom_label_2',
            'custom_label_3',
            'custom_label_4',
        ];

        $csvData = [$header];
        $saleWindow = $this->catalogSaleWindow();

        foreach ($products as $product) {
            foreach ($this->tiktokCatalogRowsForProduct($product, $baseUrl, $saleWindow) as $row) {
                $csvData[] = $row;
            }
        }

        return $this->downloadCatalogCsv($csvData, 'tiktok_products_export_' . date('Y-m-d_His') . '.csv');
    }

    /**
     * Export products to Pinterest Catalog CSV format.
     */
    public function exportToPinterest(Request $request)
    {
        $products = $this->productsForCatalogExport($request);
        $baseUrl = rtrim((string) config('app.url'), '/');
        $header = [
            'id',
            'item_group_id',
            'title',
            'description',
            'link',
            'image_link',
            'price',
            'availability',
            'condition',
            'google_product_category',
            'product_type',
            'additional_image_link',
            'sale_price',
            'brand',
            'gender',
            'age_group',
            'size',
            'size_type',
            'shipping',
            'custom_label_0',
            'adwords_redirect',
        ];

        $csvData = [$header];

        foreach ($products as $product) {
            foreach ($this->pinterestCatalogRowsForProduct($product, $baseUrl) as $row) {
                $csvData[] = $row;
            }
        }

        return $this->downloadCatalogCsv($csvData, 'pinterest_products_export_' . date('Y-m-d_His') . '.csv');
    }

    /**
     * @return \Illuminate\Support\Collection<int, Product>
     */
    private function productsForCatalogExport(Request $request)
    {
        $user = auth()->user();
        $productIds = $this->catalogExportProductIds($request);

        if ($productIds !== null) {
            if ($productIds === []) {
                return collect();
            }

            $productsQuery = Product::with(['template.category', 'template.user', 'user', 'shop', 'variants', 'collections', 'category'])
                ->whereIn('id', $productIds);

            if (!$user->hasRole('admin')) {
                $productsQuery->where('user_id', $user->id);
            }

            return $productsQuery->get();
        }

        $productsQuery = Product::with(['template.category', 'template.user', 'user', 'shop', 'variants', 'collections', 'category']);

        if (!$user->hasRole('admin')) {
            $productsQuery->where('user_id', $user->id);
        }

        if ($request->filled('category_id')) {
            $productsQuery->inCategoryIds([(int) $request->category_id]);
        }

        if ($request->filled('template_id')) {
            $productsQuery->where('template_id', $request->template_id);
        }

        if ($request->filled('shop_id')) {
            $productsQuery->where('shop_id', $request->shop_id);
        }

        if ($request->filled('collection_id')) {
            $productsQuery->whereHas('collections', function ($q) use ($request) {
                $q->where('collections.id', $request->collection_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('template', function ($templateQuery) use ($search) {
                        $templateQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $productsQuery->orderByDesc('id')->get();
    }

    /**
     * @return array<int, int>|null Null = export by filters / all; empty array = no matching selection.
     */
    private function catalogExportProductIds(Request $request): ?array
    {
        if (!$request->exists('product_ids') && !$request->exists('ids')) {
            return null;
        }

        $raw = $request->input('product_ids', $request->input('ids'));

        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }

        if (!is_array($raw)) {
            return [];
        }

        return collect($raw)
            ->flatten()
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function catalogSaleWindow(): string
    {
        $saleStart = now()->timezone('America/Los_Angeles')->startOfDay();
        $saleEnd = now()->timezone('America/Los_Angeles')->addDays(30)->endOfDay();

        return $saleStart->format('Y-m-d\TH:iP') . '/' . $saleEnd->format('Y-m-d\TH:iP');
    }

    /**
     * @param  array<int, array<int, string|int>>  $csvData
     */
    private function downloadCatalogCsv(array $csvData, string $filename)
    {
        $csvContent = "\xEF\xBB\xBF";

        foreach ($csvData as $row) {
            $escapedRow = array_map(function ($field) {
                $field = (string) $field;

                if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false || strpos($field, "\r") !== false) {
                    return '"' . str_replace('"', '""', $field) . '"';
                }

                return $field;
            }, $row);

            $csvContent .= implode(',', $escapedRow) . "\n";
        }

        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * @return array<int, array<int, string|int>>
     */
    private function metaCatalogRowsForProduct(Product $product, string $baseUrl, string $saleWindow): array
    {
        $media = $product->getEffectiveMedia();
        $imageLink = $this->absoluteMediaUrl($this->firstImageUrl($media), $baseUrl);
        if ($imageLink === '') {
            return [];
        }

        $videoUrl = $this->firstVideoUrl($media, $baseUrl);
        $description = strip_tags($product->getEffectiveDescription() ?: $product->name);
        $description = trim(preg_replace('/\s+/', ' ', $description) ?? '');
        $description = mb_substr($description !== '' ? $description : $product->name, 0, 9999);
        $title = mb_substr((string) $product->name, 0, 200);
        $categoryName = $product->category?->name ?? $product->template?->category?->name ?? 'T-Shirt';
        $fbCategory = $product->fb_product_category
            ?: ('clothing > unisex clothing > ' . mb_strtolower($categoryName));
        $googleCategory = $product->google_product_category ?: '212';
        $gender = $this->normalizeMetaGender($product->gender);
        $ageGroup = $this->normalizeMetaAgeGroup($product->age_group);
        $material = $product->material ?: 'Cotton';
        $pattern = $product->pattern ?: 'graphic';
        $shipping = $product->shipping ?: 'US::Standard:6.99 USD,GB::Standard:9.99 USD,CA::Standard:9.99 USD,AU::Standard:9.99 USD,DE::Standard:9.99 USD';
        $weight = $product->shipping_weight ?: '0.4 lb';
        $link = $baseUrl . '/products/' . ($product->slug ?: $product->id);
        $collectionTags = $product->collections->pluck('name')->filter()->values();
        $tag0 = mb_substr((string) ($collectionTags[0] ?? ''), 0, 110);
        $tag1 = mb_substr((string) ($collectionTags[1] ?? ''), 0, 110);
        $facebookQty = max(1, (int) ($product->quantity_to_sell_on_facebook ?? 100));

        $variants = $product->variants;
        if ($variants->isEmpty()) {
            $selling = (float) $product->getEffectivePrice();
            if ($selling <= 0) {
                return [];
            }

            return [$this->metaCatalogRow([
                'id' => (string) $product->id,
                'title' => $title,
                'description' => $description,
                'quantity' => (int) ($product->quantity ?? 0),
                'facebook_qty' => $facebookQty,
                'selling' => $selling,
                'compare' => (float) $product->getCompareAtPrice(),
                'link' => $link,
                'image' => $imageLink,
                'google' => $googleCategory,
                'fb' => $fbCategory,
                'sale_window' => $saleWindow,
                'group_id' => (string) $product->id,
                'gender' => $gender,
                'color' => $product->color ?? '',
                'size' => '',
                'age' => $ageGroup,
                'material' => $material,
                'pattern' => $pattern,
                'shipping' => $shipping,
                'weight' => $weight,
                'video' => $videoUrl,
                'video_tag' => $videoUrl !== '' ? 'product' : '',
                'gtin' => '',
                'tag0' => $tag0,
                'tag1' => $tag1,
                'style' => '',
            ])];
        }

        $rows = [];
        foreach ($variants->values() as $index => $variant) {
            $selling = (float) $variant->getFinalPrice();
            if ($selling <= 0) {
                continue;
            }
            $attrs = is_array($variant->attributes) ? $variant->attributes : [];
            $variantMedia = is_array($variant->media) ? $variant->media : [];
            $variantImage = $this->absoluteMediaUrl($this->firstImageUrl($variantMedia), $baseUrl) ?: $imageLink;

            $rows[] = $this->metaCatalogRow([
                'id' => $product->id . '-' . ($index + 1),
                'title' => $title,
                'description' => $description,
                'quantity' => (int) ($variant->quantity ?? $product->quantity ?? 0),
                'facebook_qty' => $facebookQty,
                'selling' => $selling,
                'compare' => (float) $variant->getCompareAtPrice(),
                'link' => $link,
                'image' => $variantImage,
                'google' => $googleCategory,
                'fb' => $fbCategory,
                'sale_window' => $saleWindow,
                'group_id' => (string) $product->id,
                'gender' => $gender,
                'color' => $this->variantAttribute($attrs, ['color', 'Colour']),
                'size' => $this->variantAttribute($attrs, ['size']),
                'age' => $ageGroup,
                'material' => $material,
                'pattern' => $pattern,
                'shipping' => $shipping,
                'weight' => $weight,
                'video' => $videoUrl,
                'video_tag' => $videoUrl !== '' ? 'product' : '',
                'gtin' => '',
                'tag0' => $tag0,
                'tag1' => $tag1,
                'style' => '',
            ]);
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, string|int>
     */
    private function metaCatalogRow(array $data): array
    {
        $selling = (float) $data['selling'];
        $compare = (float) $data['compare'];
        $hasSale = $compare > $selling && $selling > 0;
        $price = number_format($hasSale ? $compare : $selling, 2, '.', '') . ' USD';
        $salePrice = $hasSale ? number_format($selling, 2, '.', '') . ' USD' : '';
        $qty = (int) $data['quantity'];

        return [
            mb_substr((string) $data['id'], 0, 100),
            $data['title'],
            $data['description'],
            $qty > 0 ? 'in stock' : 'out of stock',
            'new',
            $price,
            $data['link'],
            $data['image'],
            'Bluprinter',
            $data['google'],
            $data['fb'],
            max(1, (int) $data['facebook_qty']),
            $salePrice,
            $hasSale ? $data['sale_window'] : '',
            $data['group_id'],
            $data['gender'],
            $data['color'],
            $data['size'],
            $data['age'],
            $data['material'],
            $data['pattern'],
            mb_substr((string) $data['shipping'], 0, 200),
            mb_substr((string) $data['weight'], 0, 50),
            $data['video'],
            $data['video_tag'],
            $data['gtin'],
            $data['tag0'],
            $data['tag1'],
            $data['style'],
        ];
    }

    /**
     * @return array<int, array<int, string|int>>
     */
    private function tiktokCatalogRowsForProduct(Product $product, string $baseUrl, string $saleWindow): array
    {
        $media = $product->getEffectiveMedia();
        $imageLink = $this->absoluteMediaUrl($this->firstImageUrl($media), $baseUrl);
        if ($imageLink === '') {
            return [];
        }

        $additionalImages = $this->additionalImageLinks($media, $baseUrl, $imageLink);
        $videoUrl = $this->firstVideoUrl($media, $baseUrl);
        $description = strip_tags($product->getEffectiveDescription() ?: $product->name);
        $description = trim(preg_replace('/\s+/', ' ', $description) ?? '');
        $description = mb_substr($description !== '' ? $description : $product->name, 0, 9999);
        $title = mb_substr((string) $product->name, 0, 200);
        $categoryName = $product->category?->name ?? $product->template?->category?->name ?? 'T-Shirt';
        $productType = $product->template?->name ?: $categoryName;
        $googleCategory = $product->google_product_category ?: '212';
        $gender = $this->normalizeMetaGender($product->gender);
        $ageGroup = $this->normalizeMetaAgeGroup($product->age_group);
        $material = $product->material ?: 'Cotton';
        $pattern = $product->pattern ?: 'graphic';
        $shipping = $product->shipping ?: 'US::Standard:6.99 USD';
        $weight = $product->shipping_weight ?: '0.4 lb';
        $link = $baseUrl . '/products/' . ($product->slug ?: $product->id);
        $collections = $product->collections->pluck('name')->filter()->values();

        $variants = $product->variants;
        if ($variants->isEmpty()) {
            $selling = (float) $product->getEffectivePrice();
            if ($selling <= 0) {
                return [];
            }

            return [$this->tiktokCatalogRow([
                'sku_id' => (string) ($product->sku ?: $product->id),
                'title' => $title,
                'description' => $description,
                'quantity' => (int) ($product->quantity ?? 0),
                'selling' => $selling,
                'compare' => (float) $product->getCompareAtPrice(),
                'link' => $link,
                'image' => $imageLink,
                'video' => $videoUrl,
                'additional_images' => $additionalImages,
                'age' => $ageGroup,
                'color' => $product->color ?? '',
                'gender' => $gender,
                'group_id' => (string) $product->id,
                'google' => $googleCategory,
                'material' => $material,
                'pattern' => $pattern,
                'type' => $productType,
                'sale_window' => $saleWindow,
                'shipping' => $shipping,
                'weight' => $weight,
                'gtin' => '',
                'mpn' => (string) ($product->sku ?: $product->id),
                'size' => '',
                'tax' => '',
                'label0' => (string) $product->id,
                'label1' => (string) ($collections[0] ?? ''),
                'label2' => (string) ($collections[1] ?? ''),
                'label3' => (string) ($product->template?->name ?? ''),
                'label4' => (string) $categoryName,
            ])];
        }

        $rows = [];
        foreach ($variants->values() as $index => $variant) {
            $selling = (float) $variant->getFinalPrice();
            if ($selling <= 0) {
                continue;
            }
            $attrs = is_array($variant->attributes) ? $variant->attributes : [];
            $variantMedia = is_array($variant->media) ? $variant->media : [];
            $variantImage = $this->absoluteMediaUrl($this->firstImageUrl($variantMedia), $baseUrl) ?: $imageLink;
            $variantAdditional = $variantImage !== $imageLink
                ? $this->additionalImageLinks(array_merge($variantMedia, $media), $baseUrl, $variantImage)
                : $additionalImages;
            $skuId = $variant->sku ?: ($product->id . '-' . ($index + 1));

            $rows[] = $this->tiktokCatalogRow([
                'sku_id' => (string) $skuId,
                'title' => $title,
                'description' => $description,
                'quantity' => (int) ($variant->quantity ?? $product->quantity ?? 0),
                'selling' => $selling,
                'compare' => (float) $variant->getCompareAtPrice(),
                'link' => $link,
                'image' => $variantImage,
                'video' => $videoUrl,
                'additional_images' => $variantAdditional,
                'age' => $ageGroup,
                'color' => $this->variantAttribute($attrs, ['color', 'Colour']),
                'gender' => $gender,
                'group_id' => (string) $product->id,
                'google' => $googleCategory,
                'material' => $material,
                'pattern' => $pattern,
                'type' => $productType,
                'sale_window' => $saleWindow,
                'shipping' => $shipping,
                'weight' => $weight,
                'gtin' => '',
                'mpn' => (string) $skuId,
                'size' => $this->variantAttribute($attrs, ['size']),
                'tax' => '',
                'label0' => (string) $product->id,
                'label1' => (string) ($collections[0] ?? ''),
                'label2' => (string) ($collections[1] ?? ''),
                'label3' => (string) ($product->template?->name ?? ''),
                'label4' => (string) $categoryName,
            ]);
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, string|int>
     */
    private function tiktokCatalogRow(array $data): array
    {
        $selling = (float) $data['selling'];
        $compare = (float) $data['compare'];
        $hasSale = $compare > $selling && $selling > 0;
        $price = number_format($hasSale ? $compare : $selling, 2, '.', '') . ' USD';
        $salePrice = $hasSale ? number_format($selling, 2, '.', '') . ' USD' : '';
        $qty = (int) $data['quantity'];

        return [
            mb_substr((string) $data['sku_id'], 0, 100),
            $data['title'],
            $data['description'],
            $qty > 0 ? 'in stock' : 'out of stock',
            'new',
            $price,
            $data['link'],
            $data['image'],
            $data['video'],
            'Bluprinter',
            $data['additional_images'],
            $data['age'],
            $data['color'],
            $data['gender'],
            $data['group_id'],
            $data['google'],
            $data['material'],
            $data['pattern'],
            mb_substr((string) $data['type'], 0, 100),
            $salePrice,
            $hasSale ? $data['sale_window'] : '',
            mb_substr((string) $data['shipping'], 0, 200),
            mb_substr((string) $data['weight'], 0, 50),
            $data['gtin'],
            mb_substr((string) $data['mpn'], 0, 100),
            $data['size'],
            $data['tax'],
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $data['label0'],
            mb_substr((string) $data['label1'], 0, 100),
            mb_substr((string) $data['label2'], 0, 100),
            mb_substr((string) $data['label3'], 0, 100),
            mb_substr((string) $data['label4'], 0, 100),
        ];
    }

    /**
     * @return array<int, array<int, string|int>>
     */
    private function pinterestCatalogRowsForProduct(Product $product, string $baseUrl): array
    {
        $media = $product->getEffectiveMedia();
        $imageLink = $this->absoluteMediaUrl($this->firstImageUrl($media), $baseUrl);
        if ($imageLink === '') {
            return [];
        }

        $additionalImages = $this->additionalImageLinks($media, $baseUrl, $imageLink);
        $description = strip_tags($product->getEffectiveDescription() ?: $product->name);
        $description = trim(preg_replace('/\s+/', ' ', $description) ?? '');
        $description = mb_substr($description !== '' ? $description : $product->name, 0, 9999);
        $title = mb_substr((string) $product->name, 0, 200);
        $categoryName = $product->category?->name ?? $product->template?->category?->name ?? 'T-Shirt';
        $productType = $product->template?->name ?: $categoryName;
        $googleCategory = $product->google_product_category ?: '212';
        $gender = $this->normalizeMetaGender($product->gender);
        $ageGroup = $this->normalizeMetaAgeGroup($product->age_group);
        $shipping = $product->shipping ?: 'US::Standard:6.99 USD';
        $link = $baseUrl . '/products/' . ($product->slug ?: $product->id);

        $variants = $product->variants;
        if ($variants->isEmpty()) {
            $selling = (float) $product->getEffectivePrice();
            if ($selling <= 0) {
                return [];
            }

            return [$this->pinterestCatalogRow([
                'id' => (string) $product->id,
                'group_id' => (string) $product->id,
                'title' => $title,
                'description' => $description,
                'link' => $link,
                'image' => $imageLink,
                'selling' => $selling,
                'compare' => (float) $product->getCompareAtPrice(),
                'quantity' => (int) ($product->quantity ?? 0),
                'google' => $googleCategory,
                'type' => $productType,
                'additional_images' => $additionalImages,
                'gender' => $gender,
                'age' => $ageGroup,
                'size' => '',
                'size_type' => 'regular',
                'shipping' => $shipping,
                'label0' => (string) $product->id,
                'adwords_redirect' => $link,
            ])];
        }

        $rows = [];
        foreach ($variants->values() as $index => $variant) {
            $selling = (float) $variant->getFinalPrice();
            if ($selling <= 0) {
                continue;
            }
            $attrs = is_array($variant->attributes) ? $variant->attributes : [];
            $variantMedia = is_array($variant->media) ? $variant->media : [];
            $variantImage = $this->absoluteMediaUrl($this->firstImageUrl($variantMedia), $baseUrl) ?: $imageLink;
            $variantAdditional = $variantImage !== $imageLink
                ? $this->additionalImageLinks(array_merge($variantMedia, $media), $baseUrl, $variantImage)
                : $additionalImages;

            $rows[] = $this->pinterestCatalogRow([
                'id' => $product->id . '-' . ($index + 1),
                'group_id' => (string) $product->id,
                'title' => $title,
                'description' => $description,
                'link' => $link,
                'image' => $variantImage,
                'selling' => $selling,
                'compare' => (float) $variant->getCompareAtPrice(),
                'quantity' => (int) ($variant->quantity ?? $product->quantity ?? 0),
                'google' => $googleCategory,
                'type' => $productType,
                'additional_images' => $variantAdditional,
                'gender' => $gender,
                'age' => $ageGroup,
                'size' => $this->variantAttribute($attrs, ['size']),
                'size_type' => 'regular',
                'shipping' => $shipping,
                'label0' => (string) $product->id,
                'adwords_redirect' => $link,
            ]);
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, string|int>
     */
    private function pinterestCatalogRow(array $data): array
    {
        $selling = (float) $data['selling'];
        $compare = (float) $data['compare'];
        $hasSale = $compare > $selling && $selling > 0;
        $price = number_format($hasSale ? $compare : $selling, 2, '.', '') . ' USD';
        $salePrice = $hasSale ? number_format($selling, 2, '.', '') . ' USD' : '';
        $qty = (int) $data['quantity'];

        return [
            mb_substr((string) $data['id'], 0, 100),
            $data['group_id'],
            $data['title'],
            $data['description'],
            $data['link'],
            $data['image'],
            $price,
            $qty > 0 ? 'in stock' : 'out of stock',
            'new',
            $data['google'],
            mb_substr((string) $data['type'], 0, 100),
            $data['additional_images'],
            $salePrice,
            'Bluprinter',
            $data['gender'],
            $data['age'],
            $data['size'],
            $data['size_type'],
            mb_substr((string) $data['shipping'], 0, 200),
            $data['label0'],
            $data['adwords_redirect'],
        ];
    }

    /**
     * @param  array<int, mixed>  $media
     */
    private function additionalImageLinks(array $media, string $baseUrl, string $primaryImage): string
    {
        $urls = [];
        foreach ($media as $item) {
            $url = is_string($item) ? $item : (string) ($item['url'] ?? $item['path'] ?? '');
            if ($url === '' || $this->isVideoMediaUrl($url)) {
                continue;
            }
            $absolute = $this->absoluteMediaUrl($url, $baseUrl);
            if ($absolute === '' || $absolute === $primaryImage) {
                continue;
            }
            $urls[$absolute] = $absolute;
            if (count($urls) >= 10) {
                break;
            }
        }

        return implode(',', array_values($urls));
    }

    /**
     * @param  array<int, mixed>  $media
     */
    private function firstImageUrl(array $media): string
    {
        foreach ($media as $item) {
            $url = is_string($item) ? $item : (string) ($item['url'] ?? $item['path'] ?? '');
            if ($url === '' || $this->isVideoMediaUrl($url)) {
                continue;
            }

            return $url;
        }

        return '';
    }

    /**
     * @param  array<int, mixed>  $media
     */
    private function firstVideoUrl(array $media, string $baseUrl): string
    {
        foreach ($media as $item) {
            $url = is_string($item) ? $item : (string) ($item['url'] ?? $item['path'] ?? '');
            if ($url === '' || ! $this->isVideoMediaUrl($url)) {
                continue;
            }
            if (str_contains(strtolower($url), 'youtube.com') || str_contains(strtolower($url), 'youtu.be') || str_contains(strtolower($url), 'vimeo.com')) {
                continue;
            }

            return $this->absoluteMediaUrl($url, $baseUrl);
        }

        return '';
    }

    private function isVideoMediaUrl(string $url): bool
    {
        return (bool) preg_match('/\.(3g2|3gp|3gpp|asf|avi|dat|divx|dv|f4v|flv|m2ts|m4v|mkv|mod|mov|mp4|mpe|mpeg|mpeg4|mpg|mts|nsv|ogm|ogv|qt|tod|ts|vob|wmv)(\?|$)/i', $url);
    }

    private function absoluteMediaUrl(string $url, string $baseUrl): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        if (str_starts_with($url, '/')) {
            return $baseUrl . $url;
        }

        return $baseUrl . '/storage/' . ltrim($url, '/');
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, string>  $keys
     */
    private function variantAttribute(array $attributes, array $keys): string
    {
        foreach ($attributes as $name => $value) {
            foreach ($keys as $key) {
                if (strcasecmp((string) $name, $key) === 0) {
                    return trim((string) $value);
                }
            }
        }

        return '';
    }

    private function normalizeMetaGender(?string $gender): string
    {
        $gender = strtolower(trim((string) $gender));

        return in_array($gender, ['female', 'male', 'unisex'], true) ? $gender : 'unisex';
    }

    private function normalizeMetaAgeGroup(?string $ageGroup): string
    {
        $ageGroup = strtolower(trim((string) $ageGroup));
        $valid = ['newborn', 'infant', 'toddler', 'kids', 'teen', 'adult', 'all ages'];

        return in_array($ageGroup, $valid, true) ? $ageGroup : 'adult';
    }
}
