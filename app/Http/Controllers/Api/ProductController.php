<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductTemplate;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Create a new product via API
     */
    public function create(Request $request)
    {
        // Validate API token
        $token = $this->validateApiToken($request);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired API token'
            ], 401)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
        }

        // Check permissions
        if (!$token->hasPermission('product:create')) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions'
            ], 403)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
        }

        // Validate request data - support both single file and array for Swagger UI
        $validationRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'required|exists:product_templates,id',
            'video' => 'nullable|file|mimes:mp4,avi,mov,webm|max:102400',
            'price' => 'nullable|numeric|min:0',
            'shop_id' => 'nullable|exists:shops,id',
            'quantity' => 'nullable|integer|min:0',
        ];

        // Check if images is array or single file
        if ($request->hasFile('images')) {
            if (is_array($request->file('images'))) {
                $validationRules['images'] = 'required|array|min:1|max:8';
                $validationRules['images.*'] = 'required|file|mimes:jpeg,jpg,png,webp|max:10240';
            } else {
                $validationRules['images'] = 'required|file|mimes:jpeg,jpg,png,webp|max:10240';
            }
        } else {
            $validationRules['images'] = 'required';
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
        }

        try {
            // Get template with all relationships
            $template = ProductTemplate::with(['category', 'attributes', 'variants'])
                ->findOrFail($request->template_id);

            // Generate unique slug
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Upload images to AWS S3 (support both single file and array)
            $uploadedImages = [];
            $images = $request->file('images');

            // Normalize to array if single file (for Swagger UI)
            if (!is_array($images)) {
                $images = [$images];
            }

            foreach ($images as $index => $image) {
                try {
                    // Validate file
                    if (!$image->isValid()) {
                        continue;
                    }

                    $fileName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                    $filePath = Storage::disk('s3')->putFileAs('products', $image, $fileName);

                    if ($filePath) {
                        // Create the correct S3 URL format (giống Admin/ProductController)
                        $imageUrl = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/' . $filePath;
                        $uploadedImages[] = [
                            'url' => $imageUrl,
                            'filename' => $fileName,
                            'order' => $index + 1
                        ];
                    }
                } catch (\Exception $e) {
                    // Continue with other files instead of failing completely
                    continue;
                }
            }

            // Upload video to AWS S3 (tham khảo Admin/ProductController)
            $video = $request->file('video');
            $videoUrl = null;

            try {
                if ($video->isValid()) {
                    $videoFileName = time() . '_' . Str::random(10) . '.' . $video->getClientOriginalExtension();
                    $videoPath = Storage::disk('s3')->putFileAs('products', $video, $videoFileName);

                    if ($videoPath) {
                        // Create the correct S3 URL format (giống Admin/ProductController)
                        $videoUrl = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/' . $videoPath;
                    }
                }
            } catch (\Exception $e) {
                // Continue without video if upload fails
            }

            // Prepare media array (giống format trong Admin/ProductController)
            $mediaUrls = [];
            foreach ($uploadedImages as $image) {
                $mediaUrls[] = $image['url'];
            }
            if ($videoUrl) {
                $mediaUrls[] = $videoUrl;
            }

            // Determine shop_id with priority order:
            // 1. Explicit shop_id in request (highest priority)
            // 2. API token's default_shop_id
            // 3. Template's shop_id
            // 4. System default shop from config
            // 5. Fallback to shop ID 1
            $shopId = $request->shop_id
                ?? $token->default_shop_id
                ?? $template->shop_id
                ?? config('api.default_shop_id', 1);

            // Prepare product data - copy thông tin từ template (giống hmtik)
            $productData = [
                'name' => $request->name,
                'slug' => $slug,
                'template_id' => $request->template_id,
                'shop_id' => $shopId,
                'status' => 'active',
                'created_by' => 'api',
                'api_token_id' => $token->id,

                // Copy từ template nếu không được cung cấp
                'description' => $request->description ?? $template->description,
                'price' => $request->price ?? $template->base_price,

                // Media: Ưu tiên media mới upload, fallback về template media
                'media' => !empty($mediaUrls) ? $mediaUrls : ($template->media ?? []),

                // Quantity mặc định
                'quantity' => $request->quantity ?? 999,
            ];

            // Create product
            $product = Product::create($productData);

            // Copy variants from template (giống hmtik)
            $createdVariants = [];
            if ($template->variants && $template->variants->count() > 0) {
                foreach ($template->variants as $templateVariant) {
                    // Generate unique SKU: template_sku + product_id + random suffix
                    $baseSku = $templateVariant->sku;
                    $uniqueSku = $baseSku . '-' . $product->id;

                    // Ensure SKU is truly unique by checking database
                    $counter = 1;
                    while (\App\Models\ProductVariant::where('sku', $uniqueSku)->exists()) {
                        $uniqueSku = $baseSku . '-' . $product->id . '-' . $counter;
                        $counter++;
                    }

                    $variantData = [
                        'product_id' => $product->id,
                        'template_id' => $template->id,
                        'variant_name' => $templateVariant->variant_name,
                        'attributes' => $templateVariant->attributes,
                        'sku' => $uniqueSku, // Truly unique SKU
                        'price' => $templateVariant->price ?? $template->base_price,
                        'quantity' => $request->quantity ?? 999,
                        'media' => $templateVariant->media ?? $template->media ?? [],
                    ];

                    $variant = \App\Models\ProductVariant::create($variantData);
                    $createdVariants[] = [
                        'id' => $variant->id,
                        'variant_name' => $variant->variant_name,
                        'attributes' => $variant->attributes,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'quantity' => $variant->quantity,
                    ];
                }
            }

            // Update token usage
            $token->markAsUsed();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'product_url' => route('products.show', $product->slug)
            ], 201)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
        }
    }

    /**
     * Get product details
     */
    public function show($id)
    {
        $product = Product::with(['shop', 'template.category'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'status' => $product->status,
                'url' => route('products.show', $product->slug),
                'media' => $product->media,
                'shop' => $product->shop,
                'template' => $product->template,
                'created_at' => $product->created_at,
            ]
        ]);
    }

    /**
     * List products created by API
     */
    public function index(Request $request)
    {
        $token = $this->validateApiToken($request);
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired API token'
            ], 401);
        }

        $products = Product::where('api_token_id', $token->id)
            ->with(['shop', 'template.category'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    /**
     * Validate API token
     */
    private function validateApiToken(Request $request)
    {
        $tokenValue = $request->header('X-API-Token') ?? $request->input('api_token');

        if (!$tokenValue) {
            return null;
        }

        $token = ApiToken::where('token', $tokenValue)->first();

        if (!$token || !$token->isValid()) {
            return null;
        }

        return $token;
    }
}
