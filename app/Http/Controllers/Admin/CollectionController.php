<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use App\Services\CollectionKeywordSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    public function __construct(
        protected CollectionKeywordSyncService $keywordSync
    ) {}

    /**
     * Display a listing of the resource.
     * Global collections are shared across all shops.
     */
    public function index()
    {
        $collections = Collection::with(['user', 'products'])
            ->whereNull('shop_id')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('admin.collections.index', compact('collections'));
    }

    /**
     * Show the form for creating a new resource (admin only).
     */
    public function create()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Only admins can create global collections.');
        }

        $products = Product::with(['template.category', 'shop'])->orderBy('name')->get();

        return view('admin.collections.create', compact('products'));
    }

    /**
     * Store a newly created global collection (admin only).
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Only admins can create global collections.');
        }

        \Log::info('Collection store attempt', [
            'user_id' => auth()->id(),
            'roles' => auth()->user()?->getRoleNames()?->toArray(),
            'has_file' => $request->hasFile('image'),
            'name' => $request->input('name'),
            'keywords' => $request->input('keywords'),
            'status' => $request->input('status'),
        ]);

        if ($request->isMethod('post') && count($request->all()) <= 1 && !$request->hasFile('image')) {
            \Log::warning('Collection store received nearly empty POST (possible post_max_size/upload limit)', [
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Upload may have exceeded server limits (post_max_size=' . ini_get('post_max_size') . '). Try without image or use a smaller file.');
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'keywords' => 'nullable|string|max:2000',
            'status' => 'required|in:active,inactive,draft',
            'featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        if ($validator->fails()) {
            \Log::warning('Collection store validation failed', [
                'user_id' => auth()->id(),
                'errors' => $validator->errors()->toArray(),
            ]);

            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $keywords = $this->keywordSync->normalize($validated['keywords'] ?? null);

        try {
            $data = [
                'user_id' => auth()->id(),
                'shop_id' => null, // global — shared by all shops
                'name' => $validated['name'],
                'slug' => Collection::generateSlug($validated['name']),
                'description' => $validated['description'] ?? null,
                'type' => !empty($keywords) ? 'automatic' : 'manual',
                'keywords' => $keywords ?: null,
                'status' => $validated['status'],
                'featured' => $request->boolean('featured'),
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'admin_approved' => true,
            ];

            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');
                if (!$imageFile->isValid()) {
                    return back()
                        ->withInput()
                        ->withErrors(['image' => 'Image upload failed: ' . $imageFile->getErrorMessage()]);
                }
                $data['image'] = $this->uploadCollectionImage($imageFile);
            }

            $collection = Collection::create($data);

            if (!empty($keywords)) {
                $this->keywordSync->syncCollection($collection);
            }

            if (!empty($validated['products'])) {
                $payload = [];
                foreach ($validated['products'] as $productId) {
                    $payload[(int) $productId] = ['source' => 'manual'];
                }
                $collection->products()->syncWithoutDetaching($payload);
            }

            if ($request->boolean('ai_match_products')) {
                \App\Jobs\MatchCollectionProductsWithAi::dispatch($collection->id)->afterResponse();
            }

            \Log::info('Collection created successfully', [
                'collection_id' => $collection->id,
                'slug' => $collection->slug,
                'keywords' => $keywords,
                'products_count' => $collection->products()->count(),
            ]);

            $message = 'Global collection created! Products matching keywords are linked automatically.';
            if ($request->boolean('ai_match_products')) {
                $message .= ' AI is adding more matching products in the background.';
            }

            return redirect()->route('admin.collections.index')
                ->with('success', $message.' ID #'.$collection->id);
        } catch (\Throwable $e) {
            \Log::error('Failed to create collection', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Could not create collection: ' . $e->getMessage());
        }
    }

    public function show(Collection $collection)
    {
        if (!$collection->canView()) {
            abort(403, 'You do not have permission to view this collection.');
        }

        $collection->load(['products.template', 'user']);

        return view('admin.collections.show', compact('collection'));
    }

    public function edit(Collection $collection)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Only admins can edit global collections.');
        }

        $products = Product::with(['template.category', 'shop'])->orderBy('name')->get();
        $collection->load('products');

        return view('admin.collections.edit', compact('collection', 'products'));
    }

    public function update(Request $request, Collection $collection)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Only admins can edit global collections.');
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'keywords' => 'nullable|string|max:2000',
            'status' => 'required|in:active,inactive,draft',
            'featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $keywords = $this->keywordSync->normalize($validated['keywords'] ?? null);

        try {
            $data = [
                'shop_id' => null,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'type' => !empty($keywords) ? 'automatic' : 'manual',
                'keywords' => $keywords ?: null,
                'status' => $validated['status'],
                'featured' => $request->boolean('featured'),
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'admin_approved' => true,
            ];

            if ($request->name !== $collection->name) {
                $data['slug'] = Collection::generateSlug($request->name, $collection->id);
            }

            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');
                if (!$imageFile->isValid()) {
                    return back()
                        ->withInput()
                        ->withErrors(['image' => 'Image upload failed: ' . $imageFile->getErrorMessage()]);
                }
                $data['image'] = $this->uploadCollectionImage($imageFile);
            }

            $collection->update($data);

            if (!empty($keywords)) {
                $this->keywordSync->syncCollection($collection->fresh());
            }

            if ($request->has('products')) {
                $payload = [];
                foreach ($validated['products'] ?? [] as $productId) {
                    $payload[(int) $productId] = ['source' => 'manual'];
                }
                if ($payload !== []) {
                    $collection->products()->syncWithoutDetaching($payload);
                }
            }

            if ($request->boolean('ai_match_products')) {
                \App\Jobs\MatchCollectionProductsWithAi::dispatch($collection->id)->afterResponse();
            }

            $message = 'Collection updated! Keyword matches were re-synced.';
            if ($request->boolean('ai_match_products')) {
                $message .= ' AI matching is running in the background.';
            }

            return redirect()->route('admin.collections.index')
                ->with('success', $message);
        } catch (\Throwable $e) {
            \Log::error('Failed to update collection', [
                'collection_id' => $collection->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Could not update collection: ' . $e->getMessage());
        }
    }

    public function destroy(Collection $collection)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Only admins can delete global collections.');
        }

        $collection->delete();

        return redirect()->route('admin.collections.index')
            ->with('success', 'Collection deleted successfully!');
    }

    public function toggleFeatured(Collection $collection)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $collection->update(['featured' => !$collection->featured]);
        $status = $collection->featured ? 'featured' : 'unfeatured';

        return back()->with('success', "Collection {$status} successfully!");
    }

    public function updateSortOrder(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $request->validate([
            'collections' => 'required|array',
            'collections.*.id' => 'required|exists:collections,id',
            'collections.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->collections as $item) {
            Collection::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    public function approve(Collection $collection)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $collection->update(['admin_approved' => true]);

        return back()->with('success', 'Collection approved!');
    }

    public function reject(Request $request, Collection $collection)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $collection->update([
            'admin_approved' => false,
            'admin_notes' => $request->input('admin_notes'),
        ]);

        return back()->with('success', 'Collection rejected.');
    }

    public function bulkApprove(Request $request)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $request->validate([
            'collection_ids' => 'required|array',
            'collection_ids.*' => 'exists:collections,id',
        ]);

        Collection::whereIn('id', $request->collection_ids)
            ->update(['admin_approved' => true]);

        return back()->with('success', count($request->collection_ids) . ' collections approved!');
    }

    protected function uploadCollectionImage(\Illuminate\Http\UploadedFile $imageFile): string
    {
        if (!$imageFile->isValid()) {
            throw new \RuntimeException('Uploaded image is invalid: ' . $imageFile->getErrorMessage());
        }

        $extension = $imageFile->getClientOriginalExtension() ?: $imageFile->guessExtension() ?: 'jpg';
        $imageName = time() . '_' . Str::random(10) . '.' . $extension;

        $tempPath = $imageFile->getPathname();
        if ($tempPath === '' || !is_readable($tempPath)) {
            throw new \RuntimeException(
                'Cannot read uploaded image. Check PHP upload_max_filesize/post_max_size or try a smaller file.'
            );
        }

        // Copy to a real local path — putFileAs(UploadedFile) uses getRealPath() which is empty on Windows.
        $localDir = storage_path('app/temp');
        if (!is_dir($localDir)) {
            mkdir($localDir, 0755, true);
        }
        $localCopy = $localDir . DIRECTORY_SEPARATOR . $imageName;
        if (!copy($tempPath, $localCopy)) {
            throw new \RuntimeException('Cannot copy uploaded image to temp storage.');
        }

        try {
            // Same as ProductController / CategoryController — no extra visibility option on put
            $filePath = Storage::disk('s3')->putFileAs('collections', $localCopy, $imageName);
        } finally {
            @unlink($localCopy);
        }

        if (!$filePath) {
            throw new \RuntimeException('Failed to upload collection image to S3.');
        }

        return 'https://s3.us-east-1.amazonaws.com/image.bluprinter/' . $filePath;
    }
}
