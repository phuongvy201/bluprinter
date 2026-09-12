<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudioProduct extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'price',
        'size_prices',
        'colors',
        'sizes',
        'product_id',
        'mockup_url',
        'print_area',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'print_area' => 'array',
        'colors' => 'array',
        'sizes' => 'array',
        'size_prices' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function mockups(): HasMany
    {
        return $this->hasMany(StudioMockup::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function normalizePrintArea(?array $area, ?array $defaults = null): array
    {
        $defaults = $defaults ?: config('studio.default_print_area', [
            'x' => 30,
            'y' => 22,
            'width' => 40,
            'height' => 42,
        ]);
        $area = is_array($area) ? $area : [];

        $x = max(0, min(100, (float) ($area['x'] ?? $defaults['x'])));
        $y = max(0, min(100, (float) ($area['y'] ?? $defaults['y'])));
        $width = max(4, min(100 - $x, (float) ($area['width'] ?? $defaults['width'])));
        $height = max(4, min(100 - $y, (float) ($area['height'] ?? $defaults['height'])));

        return [
            'x' => round($x, 2),
            'y' => round($y, 2),
            'width' => round($width, 2),
            'height' => round($height, 2),
        ];
    }

    public function normalizedPrintArea(): array
    {
        return self::normalizePrintArea($this->print_area);
    }

    /**
     * @return array<string, float>
     */
    public function normalizedSizePrices(): array
    {
        $fallback = (float) $this->price;
        $raw = is_array($this->size_prices) ? $this->size_prices : [];
        $prices = [];
        foreach ($this->sizeList() as $size) {
            $value = $raw[$size] ?? null;
            $prices[$size] = is_numeric($value) ? round((float) $value, 2) : $fallback;
        }

        return $prices;
    }

    public function priceUsdForSize(?string $size): float
    {
        $prices = $this->normalizedSizePrices();
        if ($size && isset($prices[$size])) {
            return $prices[$size];
        }

        $values = array_values($prices);

        return $values !== [] ? min($values) : (float) $this->price;
    }

    /**
     * @return array<int, string>
     */
    public function sizeList(): array
    {
        return collect((array) $this->sizes)
            ->map(fn ($size) => is_array($size) ? trim((string) ($size['name'] ?? '')) : trim((string) $size))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
