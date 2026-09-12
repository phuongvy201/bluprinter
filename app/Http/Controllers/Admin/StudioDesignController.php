<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudioDesign;
use App\Support\S3Media;
use App\Support\StudioDesignPreview;
use App\Support\StudioPricingSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudioDesignController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $designs = StudioDesign::query()
            ->when(! $user->hasRole('admin'), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(24);

        return view('admin.studio-designs.index', compact('designs'));
    }

    public function create(): View
    {
        return view('admin.studio-designs.create', [
            'design' => new StudioDesign([
                'is_active' => true,
                'price' => StudioPricingSettings::resolved()['library_default_price'],
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDesign($request, true);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['image_url'] = $this->resolveImageUrl($request, null);
        $validated['preview_url'] = StudioDesignPreview::fromUrl($validated['image_url']);
        unset($validated['image_file']);
        $validated['user_id'] = $request->user()->id;

        StudioDesign::create($validated);

        return redirect()->route('admin.studio-designs.index')->with('success', 'Design created.');
    }

    public function edit(StudioDesign $studioDesign): View
    {
        $this->authorizeDesign($studioDesign);

        return view('admin.studio-designs.edit', [
            'design' => $studioDesign,
        ]);
    }

    public function update(Request $request, StudioDesign $studioDesign): RedirectResponse
    {
        $this->authorizeDesign($studioDesign);
        $validated = $this->validateDesign($request, false);
        $validated['is_active'] = $request->boolean('is_active');
        $image = $this->resolveImageUrl($request, $studioDesign->image_url);
        if ($image) {
            $validated['image_url'] = $image;
            if ($image !== $studioDesign->image_url) {
                $validated['preview_url'] = StudioDesignPreview::fromUrl($image);
            }
        }
        unset($validated['image_file']);

        $studioDesign->update($validated);

        return redirect()->route('admin.studio-designs.index')->with('success', 'Design updated.');
    }

    public function destroy(StudioDesign $studioDesign): RedirectResponse
    {
        $this->authorizeDesign($studioDesign);
        $studioDesign->delete();

        return redirect()->route('admin.studio-designs.index')->with('success', 'Design deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateDesign(Request $request, bool $creating): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'tag' => 'nullable|string|max:80',
            'price' => 'required|numeric|min:0|max:999',
            'image_url' => ($creating ? 'nullable' : 'nullable') . '|string|max:500',
            'image_file' => ($creating ? 'required_without:image_url' : 'nullable') . '|file|image|max:102400',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ]);
    }

    protected function resolveImageUrl(Request $request, ?string $fallback): ?string
    {
        if ($request->hasFile('image_file')) {
            $stored = S3Media::store($request->file('image_file'), 'studio/designs');
            if ($stored) {
                return $stored;
            }
        }

        $url = trim((string) $request->input('image_url', $fallback ?? ''));

        return $url !== '' ? $url : $fallback;
    }

    protected function authorizeDesign(StudioDesign $design): void
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            return;
        }

        if ((int) $design->user_id !== (int) $user->id) {
            abort(403, 'You can only manage your own designs.');
        }
    }
}
