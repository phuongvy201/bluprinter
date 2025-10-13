<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductTemplate;
use App\Models\ProductVariant;
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
        $productsQuery = Product::with(['template.category', 'template.user', 'user', 'variants']);

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

        return view('admin.products.create', compact('templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:product_templates,id',
            'name' => 'required|string|max:255',
            'price_type' => 'required|in:template,override,add',
            'price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive,draft',
            'media.*' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240',
            'variants' => 'nullable|array',
            'variants.*.variant_name' => 'nullable|string',
            'variants.*.variant_key' => 'nullable|string',
            'variants.*.attributes' => 'nullable|string',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.quantity' => 'nullable|integer|min:0',
        ]);

        // Check if seller has shop (required for sellers)
        if (auth()->user()->hasRole('seller') && !auth()->user()->hasShop()) {
            return redirect()->route('seller.shop.create')
                ->with('warning', 'You need to create a shop first before adding products!');
        }

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);
        $data['user_id'] = auth()->id(); // Set product owner

        // Set shop_id if user has shop
        if (auth()->user()->hasShop()) {
            $data['shop_id'] = auth()->user()->shop->id;
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
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $filePath = Storage::disk('s3')->putFileAs('products', $file, $fileName);
                $mediaUrls[] = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/products/' . $fileName;
            }
            $data['media'] = $mediaUrls;
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

        $product->load(['template.variants', 'variants']);
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
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
            'media.*' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

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
        $user = auth()->user();

        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $productIds = $request->product_ids;
        $products = Product::with('shop')->whereIn('id', $productIds)->get();

        // Check authorization for each product
        $deletedCount = 0;
        $shopProductCounts = [];

        foreach ($products as $product) {
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
            }
        }

        // Update shop product counts
        foreach ($shopProductCounts as $shopId => $count) {
            $shop = \App\Models\Shop::find($shopId);
            if ($shop) {
                $shop->total_products = $shop->products()->count();
                $shop->save();
            }
        }

        if ($deletedCount === 0) {
            return back()->with('error', 'No products were deleted. You may not have permission to delete the selected products.');
        }

        return back()->with('success', "{$deletedCount} product(s) deleted successfully! 🗑️");
    }
}
