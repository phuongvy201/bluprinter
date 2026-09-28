<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\Post;
use App\Models\Shop;
use App\Support\S3Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $shopId = $this->requireShopId($request);
        if ($shopId instanceof JsonResponse) {
            return $shopId;
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $query = Post::query()
            ->with(['category', 'tags', 'shop', 'user'])
            ->where('shop_id', $shopId)
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }
        if ($request->filled('post_category_id')) {
            $query->where('post_category_id', (int) $request->query('post_category_id'));
        }
        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->getCollection()->map(fn (Post $p) => $this->serialize($p))->values(),
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

        $post = Post::with(['category', 'tags', 'shop', 'user'])
            ->where('shop_id', $shopId)
            ->find($id);

        if (! $post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->serialize($post, true),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $shopId = $this->requireShopId($request);
        if ($shopId instanceof JsonResponse) {
            return $shopId;
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image_url' => 'nullable|url|max:2000',
            'gallery_urls' => 'nullable|array',
            'gallery_urls.*' => 'url|max:2000',
            'post_category_id' => 'nullable|exists:post_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:post_tags,id',
            'status' => 'nullable|in:published,draft,scheduled,pending',
            'published_at' => 'nullable|date',
            'type' => 'required|in:article,video,gallery,product_review',
            'template' => 'nullable|string|max:50',
            'featured' => 'nullable|boolean',
            'allow_comments' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $shop = Shop::find($shopId);
            $userId = $shop?->user_id;
            if (! $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop owner (user_id) not found for this seller token shop.',
                ], 422);
            }

            $status = $request->input('status', 'published');
            $featuredImage = null;
            if ($request->filled('featured_image_url')) {
                $featuredImage = $this->ingestImageUrl((string) $request->input('featured_image_url'), 'posts/featured')
                    ?: $request->input('featured_image_url');
            }

            $gallery = [];
            foreach ($request->input('gallery_urls', []) as $url) {
                $stored = $this->ingestImageUrl((string) $url, 'posts/gallery');
                $gallery[] = $stored ?: $url;
            }

            $post = Post::create([
                'user_id' => $userId,
                'shop_id' => $shopId,
                'post_category_id' => $request->input('post_category_id'),
                'title' => $request->input('title'),
                'slug' => Post::generateSlug((string) $request->input('title')),
                'content' => $request->input('content'),
                'excerpt' => $request->input('excerpt'),
                'featured_image' => $featuredImage,
                'gallery' => $gallery !== [] ? $gallery : null,
                'status' => $status,
                'published_at' => $request->input('published_at')
                    ?: ($status === 'published' ? now() : null),
                'type' => $request->input('type'),
                'template' => $request->input('template', 'default'),
                'featured' => (bool) $request->boolean('featured'),
                'allow_comments' => $request->has('allow_comments') ? $request->boolean('allow_comments') : true,
                'meta_title' => $request->input('meta_title'),
                'meta_description' => $request->input('meta_description'),
                'meta_keywords' => $request->input('meta_keywords'),
                'reading_time' => $this->estimateReadingTime((string) $request->input('content')),
            ]);

            if ($request->filled('tags')) {
                $post->tags()->sync($request->input('tags'));
            }

            return response()->json([
                'success' => true,
                'message' => 'Blog post created successfully',
                'post_id' => $post->id,
                'post_url' => route('blog.show', $post->slug),
                'data' => $this->serialize($post->fresh(['category', 'tags', 'shop', 'user']), true),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Seller API blog create failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create blog post: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $shopId = $this->requireShopId($request);
        if ($shopId instanceof JsonResponse) {
            return $shopId;
        }

        $post = Post::where('shop_id', $shopId)->find($id);
        if (! $post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'excerpt' => 'nullable|string',
            'featured_image_url' => 'nullable|url|max:2000',
            'gallery_urls' => 'nullable|array',
            'gallery_urls.*' => 'url|max:2000',
            'post_category_id' => 'nullable|exists:post_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:post_tags,id',
            'status' => 'nullable|in:published,draft,scheduled,pending',
            'published_at' => 'nullable|date',
            'type' => 'nullable|in:article,video,gallery,product_review',
            'template' => 'nullable|string|max:50',
            'featured' => 'nullable|boolean',
            'allow_comments' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = collect($validator->validated())->except([
            'featured_image_url', 'gallery_urls', 'tags',
        ])->all();

        if (isset($data['title']) && $data['title'] !== $post->title) {
            $data['slug'] = Post::generateSlug($data['title'], $post->id);
        }

        if ($request->filled('featured_image_url')) {
            $data['featured_image'] = $this->ingestImageUrl((string) $request->input('featured_image_url'), 'posts/featured')
                ?: $request->input('featured_image_url');
        }

        if ($request->has('gallery_urls')) {
            $gallery = [];
            foreach ($request->input('gallery_urls', []) as $url) {
                $stored = $this->ingestImageUrl((string) $url, 'posts/gallery');
                $gallery[] = $stored ?: $url;
            }
            $data['gallery'] = $gallery;
        }

        if (isset($data['content'])) {
            $data['reading_time'] = $this->estimateReadingTime((string) $data['content']);
        }

        if (($data['status'] ?? null) === 'published' && empty($data['published_at']) && ! $post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);

        if ($request->has('tags')) {
            $post->tags()->sync($request->input('tags', []));
        }

        return response()->json([
            'success' => true,
            'message' => 'Blog post updated successfully',
            'data' => $this->serialize($post->fresh(['category', 'tags', 'shop', 'user']), true),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $shopId = $this->requireShopId($request);
        if ($shopId instanceof JsonResponse) {
            return $shopId;
        }

        $post = Post::where('shop_id', $shopId)->find($id);
        if (! $post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog post deleted successfully',
        ]);
    }

    public function publish(Request $request, int $id): JsonResponse
    {
        $shopId = $this->requireShopId($request);
        if ($shopId instanceof JsonResponse) {
            return $shopId;
        }

        $post = Post::where('shop_id', $shopId)->find($id);
        if (! $post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        $post->update([
            'status' => 'published',
            'published_at' => $post->published_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Blog post published successfully',
            'data' => $this->serialize($post->fresh(['category', 'tags', 'shop', 'user']), true),
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

    protected function ingestImageUrl(string $url, string $folder): ?string
    {
        try {
            $contents = @file_get_contents($url);
            if ($contents === false || $contents === '') {
                return null;
            }
            $ext = pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';

            return S3Media::storeContents($contents, $folder, $ext);
        } catch (\Throwable $e) {
            Log::warning('Seller API blog image ingest failed', ['url' => $url, 'error' => $e->getMessage()]);

            return null;
        }
    }

    protected function estimateReadingTime(string $content): int
    {
        $words = str_word_count(strip_tags($content));

        return max(1, (int) ceil($words / 200));
    }

    /**
     * @return array<string, mixed>
     */
    protected function serialize(Post $post, bool $detailed = false): array
    {
        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'status' => $post->status,
            'type' => $post->type,
            'template' => $post->template,
            'featured' => (bool) $post->featured,
            'featured_image' => $post->featured_image,
            'post_category_id' => $post->post_category_id,
            'shop_id' => $post->shop_id,
            'user_id' => $post->user_id,
            'published_at' => optional($post->published_at)?->toIso8601String(),
            'url' => route('blog.show', $post->slug),
            'reading_time' => $post->reading_time,
            'created_at' => optional($post->created_at)?->toIso8601String(),
            'updated_at' => optional($post->updated_at)?->toIso8601String(),
        ];

        if ($detailed) {
            $data['content'] = $post->content;
            $data['gallery'] = $post->gallery;
            $data['meta_title'] = $post->meta_title;
            $data['meta_description'] = $post->meta_description;
            $data['meta_keywords'] = $post->meta_keywords;
            $data['allow_comments'] = (bool) $post->allow_comments;
            $data['category'] = $post->category;
            $data['tags'] = $post->tags;
            $data['shop'] = $post->shop ? [
                'id' => $post->shop->id,
                'name' => $post->shop->name,
            ] : null;
            $data['user'] = $post->user ? [
                'id' => $post->user->id,
                'name' => $post->user->name,
            ] : null;
        }

        return $data;
    }
}
