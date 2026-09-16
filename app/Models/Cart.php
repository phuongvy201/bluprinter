<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'selected_variant',
        'customizations'
    ];

    protected $appends = [
        'display_name',
        'display_image',
        'is_studio_custom',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'selected_variant' => 'array',
        'customizations' => 'array'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function studioMeta(): array
    {
        $meta = $this->customizations['_studio'] ?? null;

        return is_array($meta) ? $meta : [];
    }

    public function isStudioCustom(): bool
    {
        return (bool) ($this->studioMeta()['standalone'] ?? false);
    }

    public function resolveDisplayName(): string
    {
        $title = trim((string) ($this->studioMeta()['title'] ?? ''));
        if ($title !== '') {
            return $title;
        }

        if ($this->isStudioCustom()) {
            return 'Custom product';
        }

        return trim((string) ($this->product?->name ?? 'Custom product')) ?: 'Custom product';
    }

    public function resolveDisplayImage(): string
    {
        $image = trim((string) ($this->studioMeta()['image'] ?? ''));
        if ($image !== '') {
            return $image;
        }

        $design = $this->customizations['Custom design']['image'] ?? '';
        if (is_string($design) && $design !== '') {
            return $design;
        }

        $aiRedesign = $this->customizations['AI Redesign']['image'] ?? '';
        if (is_string($aiRedesign) && $aiRedesign !== '') {
            return $aiRedesign;
        }

        $garment = $this->customizations['Garment']['image'] ?? '';
        if (is_string($garment) && $garment !== '') {
            return $garment;
        }

        if ($this->isStudioCustom()) {
            return '/images/placeholder.jpg';
        }

        $media = $this->product?->getEffectiveMedia();
        if (is_array($media) && $media !== []) {
            $first = $media[0];
            if (is_string($first) && $first !== '') {
                return $first;
            }
            if (is_array($first)) {
                return (string) ($first['url'] ?? $first['path'] ?? '');
            }
        }

        return '/images/placeholder.jpg';
    }

    public function publicProductUrl(): ?string
    {
        if ($this->isStudioCustom()) {
            return null;
        }

        $slug = $this->product?->slug;

        return $slug ? route('products.show', $slug) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function visibleCustomizations(): array
    {
        $rows = $this->customizations;
        if (!is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->reject(fn ($value, $key) => str_starts_with((string) $key, '_'))
            ->all();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->resolveDisplayName();
    }

    public function getDisplayImageAttribute(): string
    {
        return $this->resolveDisplayImage();
    }

    public function getIsStudioCustomAttribute(): bool
    {
        return $this->isStudioCustom();
    }

    public function getDisplayName(): string
    {
        return $this->resolveDisplayName();
    }

    // Helper methods
    /**
     * Unit price in storefront currency (variant base + customization fees).
     */
    public function getEffectiveUnitPrice(): float
    {
        $price = (float) $this->price;
        $customTotal = $this->getCustomizationFeesTotal();

        if ($customTotal <= 0) {
            return $price;
        }

        // Legacy rows stored variant base in `price` without customization fees.
        $variantBase = $this->resolveVariantBasePriceInStorefrontCurrency();
        if ($variantBase !== null && abs($price - $variantBase) < 0.01) {
            return round($price + $customTotal, 2);
        }

        return $price;
    }

    /**
     * Line total in storefront currency.
     */
    public function getTotalPrice(): float
    {
        return $this->getEffectiveUnitPrice() * (int) $this->quantity;
    }

    public function getTotalPriceWithCustomizations(): float
    {
        return $this->getTotalPrice();
    }

    public function getUnitPriceWithCustomizations(): float
    {
        return $this->getEffectiveUnitPrice();
    }

    protected function resolveVariantBasePriceInStorefrontCurrency(): ?float
    {
        if ($this->variant_id && $this->variant) {
            $usd = (float) $this->variant->getFinalPrice();
            if ($usd > 0) {
                return convert_currency($usd);
            }
        }

        if ($this->product) {
            $usd = (float) ($this->product->base_price ?? $this->product->price ?? 0);
            if ($usd > 0) {
                return convert_currency($usd);
            }
        }

        return null;
    }

    /**
     * Customization fee components stored on the line item (display/breakdown only).
     */
    public function getCustomizationFeesTotal(): float
    {
        $total = 0.0;

        if ($this->customizations && is_array($this->customizations)) {
            foreach ($this->customizations as $key => $customization) {
                if (str_starts_with((string) $key, '_')) {
                    continue;
                }
                if (isset($customization['price']) && is_numeric($customization['price'])) {
                    $total += (float) $customization['price'];
                }
            }
        }

        return $total;
    }

}
