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

        // Validate request data - support multipart form data with URLs
        $validationRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'required|exists:product_templates,id',
            'price' => 'nullable|numeric|min:0',
            'shop_id' => 'nullable|exists:shops,id',
            'quantity' => 'nullable|integer|min:0',
        ];

        // Handle media_urls from form data
        $mediaUrls = [];

        // Try different ways to get media URLs
        if ($request->has('media_urls') && is_array($request->input('media_urls'))) {
            // If it's already an array
            $mediaUrls = $request->input('media_urls');
        } else {
            // Try indexed format
            $urlIndex = 0;
            while ($request->has("media_urls[$urlIndex]")) {
                $mediaUrls[] = $request->input("media_urls[$urlIndex]");
                $urlIndex++;
            }
        }


        // Validate media URLs
        if (empty($mediaUrls)) {
            return response()->json([
                'success' => false,
                'message' => 'At least one media URL is required',
                'errors' => [
                    'media_urls' => ['At least one media URL is required']
                ]
            ], 422)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
        }

        // Validate each URL
        foreach ($mediaUrls as $index => $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid URL format',
                    'errors' => [
                        "media_urls.$index" => ['The URL must be a valid URL']
                    ]
                ], 422)
                    ->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
            }
        }

        if (count($mediaUrls) > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Too many media URLs',
                'errors' => [
                    'media_urls' => ['Maximum 10 media URLs allowed']
                ]
            ], 422)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'X-API-Token, Content-Type, Accept, Authorization');
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

            // Handle media - download and upload URLs to S3
            $processedMediaUrls = [];

            foreach ($mediaUrls as $index => $url) {
                try {
                    // Download file from URL
                    $fileContent = file_get_contents($url);
                    if ($fileContent === false) {
                        \Log::warning("Failed to download file from URL: $url");
                        continue;
                    }

                    // Get file info
                    $urlInfo = parse_url($url);
                    $pathInfo = pathinfo($urlInfo['path'] ?? '');
                    $extension = $pathInfo['extension'] ?? 'jpg';

                    // Determine content type
                    $contentType = $this->getContentTypeFromExtension($extension);

                    // Generate unique filename
                    $fileName = time() . '_' . Str::random(10) . '_' . $index . '.' . $extension;

                    // Determine folder based on content type
                    $folder = strpos($contentType, 'video/') === 0 ? 'products/videos' : 'products/images';
                    $filePath = $folder . '/' . $fileName;

                    // Upload to S3
                    $uploaded = Storage::disk('s3')->put($filePath, $fileContent);

                    if ($uploaded) {
                        $imageUrl = 'https://s3.us-east-1.amazonaws.com/image.bluprinter/' . $filePath;
                        $processedMediaUrls[] = $imageUrl;
                        \Log::info("Successfully uploaded URL to S3", [
                            'original_url' => $url,
                            's3_url' => $imageUrl,
                            'file_path' => $filePath
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::warning("Failed to process URL: $url - " . $e->getMessage());
                    continue;
                }
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
                'media' => !empty($processedMediaUrls) ? $processedMediaUrls : ($template->media ?? []),

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

    /**
     * Get content type from file extension
     */
    private function getContentTypeFromExtension($extension)
    {
        $contentTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'avi' => 'video/avi',
            'mov' => 'video/quicktime',
            'webm' => 'video/webm',
        ];

        return $contentTypes[strtolower($extension)] ?? 'image/jpeg';
    }
}
