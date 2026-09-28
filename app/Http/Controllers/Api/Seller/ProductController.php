<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\Product;
use App\Models\ProductTemplate;
use App\Support\S3Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $shopId = $this->requireShopId($request);
        if ($shopId instanceof JsonResponse) {
            return $shopId;
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $query = Product::query()
            ->with(['shop', 'template.category'])
            ->where('shop_id', $shopId)
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->getCollection()->map(fn (Product $p) => $this->serialize($p))->values(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $shopId = $this->requireShopId($request);
        if ($shopId instanceof JsonResponse) {
            return $shopId;
        }

        $product = Product::with(['shop', 'template.category', 'variants'])
            ->where('shop_id', $shopId)
            ->find($id);

        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->serialize($product, true),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $shopId = $this->requireShopId($request);
        if ($shopId instanceof JsonResponse) {
            return $shopId;
        }

        /** @var ApiToken|null $token */
        $token = $request->attributes->get('api_token');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'required|exists:product_templates,id',
            'price' => 'nullable|numeric|min:0',
            'list_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive,draft',
            'media_urls' => 'required|array|min:1',
            'media_urls.*' => 'required|url|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $template = ProductTemplate::with(['variants'])->findOrFail($request->integer('template_id'));

            $media = $this->ingestMediaUrls($request->input('media_urls', []));
            if ($media === []) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not download any media_urls.',
                ], 422);
            }

            $slug = $this->uniqueProductSlug((string) $request->input('name'));
            $price = $request->filled('price')
                ? (float) $request->input('price')
                : (float) ($template->base_price ?? 0);

            $product = Product::create([
                'name' => $request->input('name'),
                'slug' => $slug,
                'description' => $request->input('description') ?: $template->description,
                'price' => $price,
                'list_price' => $request->input('list_price', $template->list_price),
                'template_id' => $template->id,
                'shop_id' => $shopId,
                'quantity' => $request->input('quantity', 999),
                'status' => $request->input('status', 'active'),
                'media' => $media,
                'created_by' => 'api',
                'api_token_id' => $token?->id,
            ]);

            $this->cloneTemplateVariants($product, $template, (int) $request->input('quantity', 999));

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'product_id' => $product->id,
                'product_url' => route('products.show', $product->slug),
                'data' => $this->serialize($product->fresh(['shop', 'template.category'])),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Seller API product create failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $shopId = $this->requireShopId($request);
        if ($shopId instanceof JsonResponse) {
            return $shopId;
        }

        $product = Product::where('shop_id', $shopId)->find($id);
        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'list_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive,draft',
            'media_urls' => 'nullable|array|min:1',
            'media_urls.*' => 'required|url|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueProductSlug($data['name'], $product->id);
        }

        if (! empty($data['media_urls'])) {
            $media = $this->ingestMediaUrls($data['media_urls']);
            if ($media === []) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not download any media_urls.',
                ], 422);
            }
            $data['media'] = $media;
        }
        unset($data['media_urls']);

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $this->serialize($product->fresh(['shop', 'template.category', 'variants']), true),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $shopId = $this->requireShopId($request);
        if ($shopId instanceof JsonResponse) {
            return $shopId;
        }

        $product = Product::where('shop_id', $shopId)->find($id);
        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * @return int|JsonResponse
     */
    protected function requireShopId(Request $request)
    {
        /** @var ApiToken|null $token */
        $token = $request->attributes->get('api_token');
        $shopId = (int) ($token?->default_shop_id ?? 0);

        if ($shopId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Seller API token must have default_shop_id set (your shop).',
            ], 422);
        }

        return $shopId;
    }

    /**
     * @return array<int, string>
     */
    protected function ingestMediaUrls(array $urls): array
    {
        $out = [];
        foreach ($urls as $url) {
            if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }
            try {
                $contents = @file_get_contents($url);
                if ($contents === false || $contents === '') {
                    continue;
                }
                $ext = pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $stored = S3Media::storeContents($contents, 'products', $ext);
                if ($stored) {
                    $out[] = $stored;
                }
            } catch (\Throwable $e) {
                Log::warning('Seller API media ingest failed', ['url' => $url, 'error' => $e->getMessage()]);
            }
        }

        return $out;
    }

    protected function uniqueProductSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name) ?: 'product';
        $base = $slug;
        $i = 1;
        while (
            Product::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    protected function cloneTemplateVariants(Product $product, ProductTemplate $template, int $quantity): void
    {
        foreach ($template->variants ?? [] as $templateVariant) {
            try {
                $baseSku = Str::slug((string) $templateVariant->variant_name) ?: 'variant';
                $uniqueSku = $baseSku.'-'.$product->id;
                $counter = 1;
                while (\App\Models\ProductVariant::where('sku', $uniqueSku)->exists()) {
                    $uniqueSku = $baseSku.'-'.$product->id.'-'.$counter;
                    $counter++;
                }

                \App\Models\ProductVariant::create([
                    'product_id' => $product->id,
                    'template_id' => $template->id,
                    'variant_name' => $templateVariant->variant_name,
                    'attributes' => $templateVariant->attributes ?? [],
                    'sku' => $uniqueSku,
                    'price' => $templateVariant->price ?? $template->base_price ?? 0,
                    'list_price' => $templateVariant->list_price ?? $template->list_price,
                    'quantity' => $quantity,
                    'media' => $templateVariant->media ?? $template->media ?? [],
                ]);
            } catch (\Throwable $e) {
                Log::warning('Seller API variant clone failed', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function serialize(Product $product, bool $detailed = false): array
    {
        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price' => $product->price,
            'list_price' => $product->list_price ?? null,
            'quantity' => $product->quantity ?? null,
            'status' => $product->status,
            'template_id' => $product->template_id,
            'shop_id' => $product->shop_id,
            'url' => route('products.show', $product->slug),
            'media' => $product->media,
            'created_by' => $product->created_by ?? null,
            'created_at' => optional($product->created_at)?->toIso8601String(),
            'updated_at' => optional($product->updated_at)?->toIso8601String(),
        ];

        if ($detailed) {
            $data['shop'] = $product->shop;
            $data['template'] = $product->template;
            $data['variants'] = $product->variants;
        }

        return $data;
    }
}
