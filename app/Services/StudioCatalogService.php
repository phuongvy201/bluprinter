<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\StudioDesign;
use App\Models\StudioMockup;
use App\Models\StudioProduct;
use App\Support\S3Media;
use App\Support\StudioAiSettings;
use App\Support\StudioPricingSettings;

class StudioCatalogService
{
    /**
     * @return array<string, mixed>
     */
    public function bootstrap(): array
    {
        $products = StudioProduct::query()
            ->active()
            ->with(['mockups' => fn ($query) => $query->active(), 'product.variants'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (StudioProduct $item) => $item->mockups->isNotEmpty() || filled($item->mockup_url))
            ->values();

        $designs = StudioDesign::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $serialized = $products->map(fn (StudioProduct $item) => $this->serializeStudioProduct($item))->all();
        $pricing = StudioPricingSettings::resolved();

        return [
            'products' => $serialized,
            'designs' => $designs->map(fn (StudioDesign $design) => $this->serializeDesign($design))->all(),
            'categories' => $this->buildCategories($serialized),
            'tags' => $designs->pluck('tag')->filter()->unique()->values()->all(),
            'tag_colors' => config('studio.tag_colors', []),
            'inspiration_prompts' => StudioAiSettings::resolved()['inspiration_prompts'],
            'ai_enabled' => app(StudioAiService::class)->isEnabled(),
            'ai_timeout' => (int) StudioAiSettings::resolved()['timeout'],
            'prompt_max' => (int) StudioAiSettings::resolved()['prompt_max'],
            'max_references' => (int) StudioAiSettings::resolved()['max_references'],
            'pricing' => [
                'ai_design' => convert_currency($pricing['ai_design_price']),
                'ai_design_usd' => $pricing['ai_design_price'],
                'upload_design' => convert_currency($pricing['upload_design_price']),
                'upload_design_usd' => $pricing['upload_design_price'],
                'library_default' => convert_currency($pricing['library_default_price']),
            ],
            'history' => [],
            'history_url' => route('studio.history'),
            'support_url' => route('support.ticket.create'),
            'cart_url' => route('cart.index'),
            'currency_symbol' => currency_symbol(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeStudioProduct(StudioProduct $studioProduct): array
    {
        $product = $studioProduct->product;
        $defaultArea = $studioProduct->normalizedPrintArea();
        $mockups = $studioProduct->mockups
            ->map(fn (StudioMockup $mockup) => $this->serializeMockup($mockup, $defaultArea))
            ->values()
            ->all();

        if ($mockups === [] && $studioProduct->mockup_url) {
            $mockups[] = [
                'id' => 0,
                'name' => 'Front',
                'image' => $this->resolveMediaUrl($studioProduct->mockup_url),
                'color' => null,
                'print_area' => $defaultArea,
            ];
        }

        $cover = $mockups[0]['image'] ?? $this->resolveMediaUrl($studioProduct->mockup_url);
        $sizePricesUsd = $studioProduct->normalizedSizePrices();
        $sizePrices = [];
        foreach ($sizePricesUsd as $size => $usd) {
            $sizePrices[$size] = convert_currency($usd);
        }
        $fromUsd = $sizePricesUsd !== [] ? min($sizePricesUsd) : (float) ($studioProduct->price ?: 0);

        return [
            'id' => $studioProduct->id,
            'product_id' => $product?->id,
            'slug' => $studioProduct->slug,
            'name' => $studioProduct->name,
            'title' => $studioProduct->name,
            'category' => $studioProduct->category ?: 'Clothing',
            'price' => convert_currency($fromUsd),
            'price_usd' => $fromUsd,
            'size_prices' => $sizePrices,
            'size_prices_usd' => $sizePricesUsd,
            'image' => $cover,
            'print_area' => $defaultArea,
            'product_url' => $product?->slug ? route('products.show', $product->slug) : null,
            'colors' => $this->resolveColors($studioProduct, $mockups),
            'sizes' => $this->resolveSizes($studioProduct),
            'variants' => $this->serializeVariants($studioProduct, $cover),
            'mockups' => $mockups,
        ];
    }

    /**
     * @param  array{x: float, y: float, width: float, height: float}  $fallback
     * @return array<string, mixed>
     */
    public function serializeMockup(StudioMockup $mockup, array $fallback): array
    {
        return [
            'id' => $mockup->id,
            'name' => $mockup->name ?: 'Mockup',
            'image' => $this->resolveMediaUrl($mockup->image_url),
            'color' => $mockup->color,
            'print_area' => $mockup->normalizedPrintArea($fallback),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeDesign(StudioDesign $design): array
    {
        $usd = (float) $design->price;

        return [
            'id' => $design->id,
            'name' => $design->name,
            'image' => $design->previewUrl(),
            'tag' => $design->tag,
            'price' => convert_currency($usd),
            'price_usd' => $usd,
        ];
    }

    public function resolveMediaUrl(mixed $media): ?string
    {
        if (is_string($media) && $media !== '') {
            if (str_starts_with($media, 'http://') || str_starts_with($media, 'https://') || str_starts_with($media, '/')) {
                return $media;
            }

            return S3Media::url($media);
        }

        if (is_array($media)) {
            $url = $media['url'] ?? $media['path'] ?? reset($media);

            return is_string($url) && $url !== '' ? $this->resolveMediaUrl($url) : null;
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $products
     * @return array<int, array{id: string, label: string}>
     */
    protected function buildCategories(array $products): array
    {
        $items = [['id' => 'hot', 'label' => 'Hot']];
        $groups = collect($products)->pluck('category')->filter()->unique()->sort()->values();
        foreach ($groups as $name) {
            $items[] = ['id' => 'cat:'.$name, 'label' => $name];
        }
        $types = collect($products)->pluck('name')->filter()->unique()->values();
        foreach ($types as $name) {
            $items[] = ['id' => 'type:'.$name, 'label' => $name];
        }

        return $items;
    }

    /**
     * @param  array<int, array<string, mixed>>  $mockups
     * @return array<int, array{name: string, hex: string}>
     */
    protected function resolveColors(StudioProduct $studioProduct, array $mockups): array
    {
        $colors = [];
        foreach ((array) $studioProduct->colors as $color) {
            if (is_string($color) && $color !== '') {
                $colors[mb_strtolower($color)] = ['name' => $color, 'hex' => $this->colorHex($color)];
                continue;
            }
            if (is_array($color) && !empty($color['name'])) {
                $colors[mb_strtolower((string) $color['name'])] = [
                    'name' => (string) $color['name'],
                    'hex' => $color['hex'] ?? $this->colorHex((string) $color['name']),
                ];
            }
        }

        foreach ($mockups as $mockup) {
            $name = trim((string) ($mockup['color'] ?? ''));
            if ($name !== '' && !isset($colors[mb_strtolower($name)])) {
                $colors[mb_strtolower($name)] = ['name' => $name, 'hex' => $this->colorHex($name)];
            }
        }

        if ($colors === [] && $studioProduct->product) {
            foreach ($studioProduct->product->variants as $variant) {
                $attributes = $this->variantAttributes($variant);
                $color = $this->attributeValue($attributes, ['Color', 'Colour']);
                if ($color) {
                    $colors[mb_strtolower($color)] = ['name' => $color, 'hex' => $this->colorHex($color)];
                }
            }
        }

        return array_values($colors);
    }

    /**
     * @return array<int, string>
     */
    protected function resolveSizes(StudioProduct $studioProduct): array
    {
        $sizes = collect((array) $studioProduct->sizes)
            ->map(fn ($size) => is_array($size) ? (string) ($size['name'] ?? '') : trim((string) $size))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($sizes === [] && $studioProduct->product) {
            foreach ($studioProduct->product->variants as $variant) {
                $size = $this->attributeValue($this->variantAttributes($variant), ['Size']);
                if ($size && !in_array($size, $sizes, true)) {
                    $sizes[] = $size;
                }
            }
        }

        return $this->sortSizes($sizes);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function serializeVariants(StudioProduct $studioProduct, ?string $fallbackImage): array
    {
        $product = $studioProduct->product;
        if (!$product) {
            return [];
        }

        $payload = [];
        foreach ($product->variants as $variant) {
            $attributes = $this->variantAttributes($variant);
            $color = $this->attributeValue($attributes, ['Color', 'Colour']);
            $size = $this->attributeValue($attributes, ['Size']);
            $usd = (float) ($variant->price ?: $product->price ?: $studioProduct->price ?: 0);
            $payload[] = [
                'id' => $variant->id,
                'name' => $variant->variant_name,
                'color' => $color,
                'size' => $size,
                'attributes' => $attributes,
                'price' => convert_currency($usd),
                'price_usd' => $usd,
                'image' => $this->resolveMediaUrl($variant->media[0] ?? null) ?: $fallbackImage,
                'quantity' => (int) ($variant->quantity ?? 0),
            ];
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    protected function variantAttributes(ProductVariant $variant): array
    {
        $raw = $variant->getRawOriginal('attributes');
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $attrs = $variant->getAttribute('attributes');

        return is_array($attrs) ? $attrs : [];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, string>  $keys
     */
    protected function attributeValue(array $attributes, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($attributes[$key]) && $attributes[$key] !== '' && $attributes[$key] !== null) {
                return (string) $attributes[$key];
            }
        }

        foreach ($attributes as $name => $value) {
            foreach ($keys as $key) {
                if (strcasecmp((string) $name, $key) === 0 && $value !== '' && $value !== null) {
                    return (string) $value;
                }
            }
        }

        return null;
    }

    protected function colorHex(string $name): string
    {
        $map = config('studio.color_map', []);
        $key = mb_strtolower(trim($name));

        return $map[$key] ?? '#6b7280';
    }

    /**
     * @param  array<int, string>  $sizes
     * @return array<int, string>
     */
    protected function sortSizes(array $sizes): array
    {
        $order = ['XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5, '2XL' => 6, 'XXL' => 6, '3XL' => 7, 'XXXL' => 7, '4XL' => 8, '5XL' => 9];
        usort($sizes, function ($a, $b) use ($order) {
            $ia = $order[strtoupper((string) $a)] ?? 50;
            $ib = $order[strtoupper((string) $b)] ?? 50;
            if ($ia === $ib) {
                return strnatcasecmp((string) $a, (string) $b);
            }

            return $ia <=> $ib;
        });

        return $sizes;
    }
}
