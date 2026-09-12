<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StudioMockup;
use App\Models\StudioProduct;
use App\Support\S3Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudioProductController extends Controller
{
    public function index(): View
    {
        $studioProducts = StudioProduct::query()
            ->withCount('mockups')
            ->with('product')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.studio-products.index', compact('studioProducts'));
    }

    public function create(): View
    {
        return view('admin.studio-products.create', [
            'products' => $this->productOptions(),
            'studioProduct' => new StudioProduct([
                'is_active' => true,
                'sort_order' => 0,
                'category' => 'Clothing',
                'price' => 12,
                'sizes' => ['S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'],
                'size_prices' => [
                    'S' => 12,
                    'M' => 13,
                    'L' => 14,
                    'XL' => 15,
                    '2XL' => 16,
                    '3XL' => 16.5,
                    '4XL' => 17,
                    '5XL' => 17,
                ],
                'colors' => [
                    ['name' => 'White', 'hex' => '#ffffff'],
                    ['name' => 'Black', 'hex' => '#000000'],
                ],
                'print_area' => config('studio.default_print_area'),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $studioProduct = StudioProduct::create($this->payloadFromRequest($request));
        $this->syncMockups($request, $studioProduct);

        return redirect()->route('admin.studio-products.index')->with('success', 'Garment type created.');
    }

    public function edit(StudioProduct $studioProduct): View
    {
        $studioProduct->load('mockups');

        return view('admin.studio-products.edit', [
            'studioProduct' => $studioProduct,
            'products' => $this->productOptions(),
        ]);
    }

    public function update(Request $request, StudioProduct $studioProduct): RedirectResponse
    {
        $studioProduct->update($this->payloadFromRequest($request, $studioProduct));
        $this->syncMockups($request, $studioProduct);

        return redirect()->route('admin.studio-products.index')->with('success', 'Garment type updated.');
    }

    public function destroy(StudioProduct $studioProduct): RedirectResponse
    {
        $studioProduct->delete();

        return redirect()->route('admin.studio-products.index')->with('success', 'Garment type removed.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function payloadFromRequest(Request $request, ?StudioProduct $existing = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'category' => 'nullable|string|max:80',
            'price' => 'nullable|numeric|min:0|max:9999',
            'size_prices' => 'nullable|array',
            'size_prices.*' => 'nullable|numeric|min:0|max:9999',
            'product_id' => 'nullable|exists:products,id',
            'colors_text' => 'nullable|string|max:2000',
            'sizes_text' => 'nullable|string|max:255',
            'print_area_x' => 'required|numeric|min:0|max:96',
            'print_area_y' => 'required|numeric|min:0|max:96',
            'print_area_width' => 'required|numeric|min:4|max:100',
            'print_area_height' => 'required|numeric|min:4|max:100',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
            'mockups' => 'nullable|array',
            'mockups.*.id' => 'nullable|integer',
            'mockups.*.name' => 'nullable|string|max:120',
            'mockups.*.color' => 'nullable|string|max:80',
            'mockups.*.image_url' => 'nullable|string|max:500',
            'mockups.*.image_file' => 'nullable|file|image|max:8192',
            'mockups.*.print_area_x' => 'nullable|numeric|min:0|max:96',
            'mockups.*.print_area_y' => 'nullable|numeric|min:0|max:96',
            'mockups.*.print_area_width' => 'nullable|numeric|min:4|max:100',
            'mockups.*.print_area_height' => 'nullable|numeric|min:4|max:100',
            'mockups.*.sort_order' => 'nullable|integer|min:0|max:9999',
            'mockups.*.delete' => 'nullable|boolean',
        ]);

        $name = trim($validated['name']);

        $sizes = $this->parseSizes($validated['sizes_text'] ?? '');
        $fallback = (float) ($validated['price'] ?? 0);

        return [
            'name' => $name,
            'slug' => Str::slug($name) ?: 'garment',
            'category' => trim((string) ($validated['category'] ?? 'Clothing')) ?: 'Clothing',
            'price' => $fallback,
            'product_id' => $validated['product_id'] ?? null,
            'colors' => $this->parseColors($validated['colors_text'] ?? ''),
            'sizes' => $sizes,
            'size_prices' => $this->parseSizePrices($sizes, $request->input('size_prices', []), $fallback),
            'print_area' => [
                'x' => (float) $validated['print_area_x'],
                'y' => (float) $validated['print_area_y'],
                'width' => (float) $validated['print_area_width'],
                'height' => (float) $validated['print_area_height'],
            ],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'mockup_url' => $existing?->mockup_url,
        ];
    }

    protected function syncMockups(Request $request, StudioProduct $studioProduct): void
    {
        $fallback = $studioProduct->normalizedPrintArea();
        $keptIds = [];

        foreach ((array) $request->input('mockups', []) as $index => $row) {
            if (!empty($row['delete']) && !empty($row['id'])) {
                StudioMockup::query()
                    ->where('studio_product_id', $studioProduct->id)
                    ->where('id', $row['id'])
                    ->delete();
                continue;
            }

            $file = $request->file('mockups.'.$index.'.image_file');
            $image = null;
            if ($file) {
                $image = S3Media::store($file, 'studio/mockups');
            }
            if (!$image) {
                $image = trim((string) ($row['image_url'] ?? ''));
            }
            if ($image === '' && empty($row['id'])) {
                continue;
            }

            $payload = [
                'name' => trim((string) ($row['name'] ?? '')) ?: 'Mockup '.($index + 1),
                'color' => trim((string) ($row['color'] ?? '')) ?: null,
                'print_area' => [
                    'x' => (float) ($row['print_area_x'] ?? $fallback['x']),
                    'y' => (float) ($row['print_area_y'] ?? $fallback['y']),
                    'width' => (float) ($row['print_area_width'] ?? $fallback['width']),
                    'height' => (float) ($row['print_area_height'] ?? $fallback['height']),
                ],
                'sort_order' => (int) ($row['sort_order'] ?? $index),
                'is_active' => true,
            ];
            if ($image !== '') {
                $payload['image_url'] = $image;
            }

            if (!empty($row['id'])) {
                $mockup = StudioMockup::query()
                    ->where('studio_product_id', $studioProduct->id)
                    ->where('id', $row['id'])
                    ->first();
                if ($mockup) {
                    $mockup->update($payload);
                    $keptIds[] = $mockup->id;
                    continue;
                }
            }

            if (empty($payload['image_url'])) {
                continue;
            }

            $created = $studioProduct->mockups()->create($payload);
            $keptIds[] = $created->id;
        }

        if ($studioProduct->mockups()->exists()) {
            $cover = $studioProduct->mockups()->orderBy('sort_order')->orderBy('id')->first();
            if ($cover) {
                $studioProduct->update(['mockup_url' => $cover->image_url]);
            }
        }
    }

    /**
     * @return array<int, array{name: string, hex: string}>
     */
    protected function parseColors(string $text): array
    {
        $map = config('studio.color_map', []);
        $colors = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            [$name, $hex] = array_pad(array_map('trim', explode('|', $line, 2)), 2, null);
            if ($name === '') {
                continue;
            }
            $colors[] = [
                'name' => $name,
                'hex' => $hex ?: ($map[mb_strtolower($name)] ?? '#6b7280'),
            ];
        }

        return $colors;
    }

    /**
     * @return array<int, string>
     */
    protected function parseSizes(string $text): array
    {
        return collect(preg_split('/[,|\n]/', $text) ?: [])
            ->map(fn ($size) => trim((string) $size))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $sizes
     * @return array<string, float>
     */
    protected function parseSizePrices(array $sizes, mixed $raw, float $fallback): array
    {
        $raw = is_array($raw) ? $raw : [];
        $prices = [];
        foreach ($sizes as $size) {
            $value = $raw[$size] ?? null;
            $prices[$size] = is_numeric($value) ? round((float) $value, 2) : $fallback;
        }

        return $prices;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Product>
     */
    protected function productOptions()
    {
        return Product::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->limit(400)
            ->get(['id', 'name']);
    }
}
