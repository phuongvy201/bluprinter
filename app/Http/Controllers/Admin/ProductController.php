<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductTemplate;
use App\Models\ProductVariant;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // Admin xem tất cả products, Seller chỉ xem products của mình
        $productsQuery = Product::with(['template.category', 'template.user', 'user', 'shop', 'variants']);

        if ($user->hasRole('admin')) {
            $products = $productsQuery->orderBy('created_at', 'desc')->paginate(12);
        } else {
            // Seller chỉ thấy products của mình
            $products = $productsQuery->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')->paginate(12);
        }

        return view('admin.products.index', compact('products'));
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

        return view('admin.products.create', compact('templates', 'shops'));
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
                'description' => 'nullable|string',
                'quantity' => 'required|integer|min:0',
                'status' => 'required|in:active,inactive,draft',
                'shop_id' => $user->hasRole('admin') ? 'nullable|exists:shops,id' : 'nullable',
                'media.*' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240',
                'variants' => 'nullable|array',
                'variants.*.variant_name' => 'nullable|string',
                'variants.*.variant_key' => 'nullable|string',
                'variants.*.attributes' => 'nullable|string',
                'variants.*.price' => 'nullable|numeric|min:0',
                'variants.*.quantity' => 'nullable|integer|min:0',
            ]);

            // Check if seller has shop (required for sellers)
            if ($user->hasRole('seller') && !$user->hasShop()) {
                return redirect()->route('seller.shop.create')
                    ->with('warning', 'You need to create a shop first before adding products!');
            }

            $data = $request->all();
            $data['slug'] = $this->generateUniqueSlug($request->name);
            $data['user_id'] = auth()->id(); // Set product owner

            // Set shop_id based on user role
            if ($user->hasRole('admin')) {
                // Admin can assign to any shop via form
                $data['shop_id'] = $request->shop_id;
            } elseif ($user->hasShop()) {
                // Seller uses their own shop
                $data['shop_id'] = $user->shop->id;
            }

            // Calculate final price based on price_type
            $template = ProductTemplate::find($request->template_id);

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

            // Handle description logic
            $customDescription = trim($request->description ?? '');
            if (!empty($customDescription)) {
                // Seller provided custom description - use it
                $data['description'] = $customDescription;
            } else {
                // No custom description - use template description
                $data['description'] = $template->description;
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
                $mediaUrls = [];
                foreach ($request->file('media') as $file) {
                    try {
                        // Validate file
                        if (!$file->isValid()) {
                            Log::error('Invalid file uploaded', ['file' => $file->getClientOriginalName()]);
                            continue;
                        }

                        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                        $filePath = Storage::disk('s3')->putFileAs('products', $file, $fileName);

                        if ($filePath) {
                            // Create the correct S3 URL format
                            $mediaUrls[] = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/' . $filePath;
                            Log::info('File uploaded successfully', ['file' => $fileName, 'path' => $filePath, 'url' => $mediaUrls[count($mediaUrls) - 1]]);
                        } else {
                            Log::error('Failed to upload file to S3', ['file' => $fileName]);
                        }
                    } catch (Exception $e) {
                        Log::error('Error uploading file', [
                            'file' => $file->getClientOriginalName(),
                            'error' => $e->getMessage()
                        ]);
                        // Continue with other files instead of failing completely
                    }
                }

                if (!empty($mediaUrls)) {
                    $data['media'] = $mediaUrls;
                }
            }

            $product = Product::create($data);

            // Increment shop products count
            if (auth()->user()->hasShop()) {
                auth()->user()->shop->incrementProducts();
            }

            // Create product variants from template variants
            if ($request->has('variants')) {
                foreach ($request->variants as $variantData) {
                    $variantName = $variantData['variant_name'] ?? '';

                    // Get attributes from form or parse from variant_name
                    $attributes = [];

                    // Get attributes from template variant first (preferred method)
                    $templateVariant = \App\Models\TemplateVariant::where('template_id', $request->template_id)
                        ->where('variant_name', $variantName)
                        ->first();

                    if ($templateVariant && !empty($templateVariant->attributes)) {
                        $attributes = $templateVariant->attributes;
                    } else {
                        // Try to get attributes from form
                        if (isset($variantData['attributes']) && !empty($variantData['attributes'])) {
                            $attributes = is_string($variantData['attributes'])
                                ? json_decode($variantData['attributes'], true)
                                : $variantData['attributes'];
                        }

                        // If still no attributes, try to parse from variant_name (fallback)
                        if (empty($attributes)) {
                            // Common size patterns
                            $sizePatterns = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Small', 'Medium', 'Large', '11oz', '12oz', '15oz'];
                            $colorPatterns = ['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow', 'Purple', 'Pink', 'Gray', 'Grey', 'Brown', 'Orange', 'Navy', 'Maroon', 'Teal'];

                            // Handle format like "Black/S" or "Black S" or "Black-S"
                            $variantName = str_replace(['/', '-'], ' ', $variantName);
                            $nameParts = array_filter(explode(' ', trim($variantName)));

                            foreach ($nameParts as $part) {
                                $part = trim($part);
                                if (in_array($part, $sizePatterns)) {
                                    $attributes['Size'] = $part;
                                } elseif (in_array($part, $colorPatterns)) {
                                    $attributes['Color'] = $part;
                                } else {
                                    // If it's not a common size/color, treat as additional attribute
                                    if (!isset($attributes['Material'])) {
                                        $attributes['Material'] = $part;
                                    } elseif (!isset($attributes['Style'])) {
                                        $attributes['Style'] = $part;
                                    }
                                }
                            }
                        }

                        // If still no attributes, create a generic one
                        if (empty($attributes)) {
                            $attributes['Variant'] = $variantName;
                        }
                    }

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'template_id' => $request->template_id,
                        'variant_name' => $variantName,
                        'attributes' => $attributes,
                        'price' => $variantData['price'] ?? null,
                        'sku' => 'SKU-' . strtoupper(Str::random(8)),
                        'quantity' => $variantData['quantity'] ?? 0,
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

        $product->load(['template.variants', 'variants']);
        return view('admin.products.edit', compact('product', 'shops'));
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
                'description' => 'nullable|string',
                'quantity' => 'required|integer|min:0',
                'status' => 'required|in:active,inactive,draft',
                'shop_id' => $user->hasRole('admin') ? 'nullable|exists:shops,id' : 'nullable',
                'media.*' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240',
            ]);

            $data = $request->all();
            $data['slug'] = $this->generateUniqueSlug($request->name);

            // Handle media upload
            if ($request->hasFile('media')) {
                $mediaUrls = [];
                foreach ($request->file('media') as $file) {
                    $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $filePath = Storage::disk('s3')->putFileAs('products', $file, $fileName);
                    $mediaUrls[] = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/products/' . $fileName;
                }
                $data['media'] = $mediaUrls;
            }

            $product->update($data);

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
     */
    private function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        // Check if slug already exists
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
