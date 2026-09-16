<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StudioDesign;
use App\Services\StudioAiHistoryService;
use App\Services\StudioAiService;
use App\Services\StudioCatalogService;
use App\Support\S3Media;
use App\Support\StudioAiSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;

class StudioController extends Controller
{
    public function __construct(
        protected StudioCatalogService $catalog,
        protected StudioAiService $ai,
        protected StudioAiHistoryService $history,
    ) {
    }

    public function index(Request $request): View
    {
        $studio = $this->catalog->bootstrap();
        $studio['history'] = $this->history->recentForVisitor($request);

        return view('studio.index', [
            'title' => 'Create Your Own',
            'studio' => $studio,
        ]);
    }

    public function explore(Request $request): View
    {
        $tag = trim((string) $request->query('tag', ''));
        $search = trim((string) $request->query('q', ''));

        $query = StudioDesign::query()
            ->active()
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($tag !== '') {
            $query->where('tag', $tag);
        }

        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%'.$search.'%')
                    ->orWhere('tag', 'like', '%'.$search.'%');
            });
        }

        $designs = $query->paginate(24)->withQueryString();
        $designs->setCollection(
            $designs->getCollection()->map(fn (StudioDesign $design) => $this->catalog->serializeDesign($design))
        );

        $tags = StudioDesign::query()
            ->active()
            ->whereNotNull('tag')
            ->where('tag', '!=', '')
            ->distinct()
            ->orderBy('tag')
            ->pluck('tag');

        return view('studio.explore', [
            'title' => 'Explore Design',
            'designs' => $designs,
            'tags' => $tags,
            'activeTag' => $tag,
            'search' => $search,
        ]);
    }

    public function aiDesign(Request $request): View
    {
        $ai = StudioAiSettings::resolved();

        return view('studio.ai-design', [
            'title' => 'AI Design Gen',
            'aiEnabled' => $this->ai->isEnabled(),
            'imageCount' => (int) ($ai['image_count'] ?? 2),
            'promptMax' => (int) ($ai['prompt_max'] ?? 1000),
            'maxReferences' => (int) ($ai['max_references'] ?? 4),
            'inspirationPrompts' => $ai['inspiration_prompts'] ?? [],
            'history' => $this->history->recentForVisitor($request, 16),
        ]);
    }

    public function tryOn(Request $request): View
    {
        $products = Product::query()
            ->availableForDisplay()
            ->with(['shop', 'template.category'])
            ->latest()
            ->limit(12)
            ->get();

        $selectedSlug = (string) $request->query('product', '');
        if ($selectedSlug !== '' && ! $products->contains('slug', $selectedSlug)) {
            $selected = Product::query()
                ->availableForDisplay()
                ->where('slug', $selectedSlug)
                ->first();
            if ($selected) {
                $products = $products->prepend($selected)->unique('id')->take(12)->values();
            }
        }

        return view('studio.try-on', [
            'title' => 'Virtual Try-On',
            'products' => $products,
            'selectedSlug' => $selectedSlug,
            'aiEnabled' => $this->ai->isEnabled() && (bool) StudioAiSettings::resolved()['try_on_enabled'],
            'timeout' => (int) StudioAiSettings::resolved()['timeout'],
        ]);
    }

    public function tryOnGenerate(Request $request): JsonResponse
    {
        $ai = StudioAiSettings::resolved();
        if (! $this->ai->isEnabled() || empty($ai['try_on_enabled'])) {
            return response()->json(['success' => false, 'message' => 'AI try-on is currently turned off.'], 422);
        }

        set_time_limit(max(120, (int) $ai['timeout'] + 30));

        $validated = $request->validate([
            'photo_url' => 'required|string|max:2000',
            'product_image' => 'required|string|max:2000',
            'product_back' => 'nullable|string|max:2000',
            'product_name' => 'nullable|string|max:180',
            'product_type' => 'nullable|string|max:80',
            'view' => 'nullable|in:front,back',
        ]);

        try {
            $result = $this->ai->generateTryOn(
                $this->absoluteMediaUrl($validated['photo_url']),
                $this->absoluteMediaUrl($validated['product_image']),
                (string) ($validated['product_name'] ?? ''),
                $this->absoluteMediaUrl((string) ($validated['product_back'] ?? '')),
                (string) ($validated['view'] ?? 'front'),
                (string) ($validated['product_type'] ?? ''),
            );
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::warning('Studio AI try-on unreachable', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Could not reach the AI API. Check STUDIO_AI_BASE_URL and STUDIO_AI_API_KEY.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'image' => $result['url'],
        ]);
    }

    public function history(Request $request): View
    {
        return view('studio.history', [
            'title' => 'My designs',
            'generations' => $this->history->paginateForVisitor($request, 12),
        ]);
    }

    public function destroyHistory(Request $request, int $generation): JsonResponse|RedirectResponse
    {
        $row = $this->history->findOwned($request, $generation);
        if (!$row) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Design not found.'], 404);
            }

            return redirect()->route('studio.history')->with('error', 'Design not found.');
        }

        $row->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('studio.history')->with('success', 'Design removed from your history.');
    }

    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|image|max:10240|mimes:jpg,jpeg,png,webp,gif,svg',
        ]);

        $url = S3Media::store($validated['file'], 'studio/uploads');
        if (!$url) {
            return response()->json([
                'success' => false,
                'message' => 'Could not upload that file. Please try another image.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'url' => $url,
            'name' => $validated['file']->getClientOriginalName(),
        ]);
    }

    public function improvePrompt(Request $request): JsonResponse
    {
        $ai = StudioAiSettings::resolved();
        $promptMax = (int) $ai['prompt_max'];
        $maxRefs = (int) $ai['max_references'];
        set_time_limit(max(120, (int) $ai['timeout']));

        $validated = $request->validate([
            'prompt' => 'nullable|string|max:'.$promptMax,
            'references' => 'nullable|array|max:'.$maxRefs,
            'references.*' => 'nullable|string|max:2000',
        ]);

        try {
            $improved = $this->ai->improvePrompt(
                (string) ($validated['prompt'] ?? ''),
                array_values(array_filter($validated['references'] ?? [])),
            );
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::warning('Studio AI improve unreachable', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Could not reach the AI API. Check STUDIO_AI_BASE_URL and STUDIO_AI_API_KEY.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'prompt' => $improved,
        ]);
    }

    public function generate(Request $request): JsonResponse
    {
        $ai = StudioAiSettings::resolved();
        $promptMax = (int) $ai['prompt_max'];
        $maxRefs = (int) $ai['max_references'];
        set_time_limit(max(120, (int) $ai['timeout'] + 30));

        $validated = $request->validate([
            'prompt' => 'nullable|string|max:'.$promptMax,
            'references' => 'nullable|array|max:'.$maxRefs,
            'references.*' => 'nullable|string|max:2000',
            'reference_files' => 'nullable|array|max:'.$maxRefs,
            'reference_files.*' => 'nullable|file|image|max:5120',
        ]);

        $referenceUrls = array_values(array_filter($validated['references'] ?? []));
        $files = $request->file('reference_files', []);
        if (!is_array($files)) {
            $files = $files ? [$files] : [];
        }
        if ($files !== []) {
            $referenceUrls = array_merge($referenceUrls, $this->ai->storeReferenceImages($files));
        }

        try {
            $designs = $this->ai->generateDesigns(
                (string) ($validated['prompt'] ?? ''),
                $referenceUrls,
                (int) $ai['image_count'],
            );
            $generation = $this->history->record($request, $validated['prompt'], $designs, $referenceUrls);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::warning('Studio AI generate unreachable', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Could not reach the AI API. Check STUDIO_AI_BASE_URL and STUDIO_AI_API_KEY.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'designs' => $designs,
            'references' => $referenceUrls,
            'history' => $this->history->serialize($generation),
        ]);
    }

    /**
     * Redesign an existing product mockup from the PDP (AI Custom).
     */
    public function redesignProduct(Request $request): JsonResponse
    {
        if (! $this->ai->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'AI redesign is turned off right now.',
            ], 422);
        }

        $ai = StudioAiSettings::resolved();
        $promptMax = (int) $ai['prompt_max'];
        set_time_limit(max(120, (int) $ai['timeout'] + 30));

        $validated = $request->validate([
            'product_image' => 'required|string|max:2000',
            'prompt' => 'nullable|string|max:'.$promptMax,
            'photo_url' => 'nullable|string|max:2000',
            'product_name' => 'nullable|string|max:255',
            'product_id' => 'nullable|integer|exists:products,id',
            'photo' => 'nullable|file|image|max:5120|mimes:jpg,jpeg,png,webp',
        ]);

        $photoUrl = trim((string) ($validated['photo_url'] ?? ''));
        if ($request->hasFile('photo')) {
            $uploaded = S3Media::store($request->file('photo'), 'studio/uploads');
            if (! $uploaded) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not upload your photo. Please try another image.',
                ], 422);
            }
            $photoUrl = $uploaded;
        }

        try {
            $result = $this->ai->redesignProduct(
                (string) $validated['product_image'],
                (string) ($validated['prompt'] ?? ''),
                $photoUrl !== '' ? $photoUrl : null,
                (string) ($validated['product_name'] ?? ''),
            );

            $this->history->record(
                $request,
                (string) (($validated['prompt'] ?? '') !== '' ? $validated['prompt'] : 'Product redesign'),
                [$result],
                array_values(array_filter([
                    $validated['product_image'],
                    $photoUrl,
                ])),
            );
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::warning('Studio AI product redesign unreachable', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Could not reach the AI API. Check STUDIO_AI_BASE_URL and STUDIO_AI_API_KEY.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'image' => $result['url'] ?? null,
            'prompt' => $result['prompt'] ?? ($validated['prompt'] ?? ''),
            'photo_url' => $photoUrl !== '' ? $photoUrl : null,
        ]);
    }

    public function media(Request $request): Response
    {
        $url = (string) $request->query('u', '');
        if (! S3Media::isPublicUrl($url)) {
            abort(404);
        }

        $response = Http::timeout(20)->get($url);
        if (! $response->successful()) {
            abort(404);
        }

        $contentType = strtolower((string) $response->header('Content-Type', ''));
        $path = (string) (parse_url($url, PHP_URL_PATH) ?: '');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $extMap = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
        ];

        $allowed = str_starts_with($contentType, 'image/') || $contentType === 'image/svg+xml';
        if (! $allowed) {
            if (! isset($extMap[$ext])) {
                abort(404);
            }
            $contentType = $extMap[$ext];
        }

        return response($response->body(), 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    protected function absoluteMediaUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return url($url);
    }
}
