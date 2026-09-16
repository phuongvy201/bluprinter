<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::created(function ($product) {
            \Illuminate\Support\Facades\Log::info("Product created event triggered", [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'template_id' => $product->template_id
            ]);

            // Auto-create variants from template when product is created
            // Load template with variants relationship
            $template = \App\Models\ProductTemplate::with('variants')->find($product->template_id);

            \Illuminate\Support\Facades\Log::info("Template loaded for product", [
                'product_id' => $product->id,
                'template_id' => $product->template_id,
                'template_exists' => $template ? true : false,
                'variants_count' => $template && $template->variants ? $template->variants->count() : 0
            ]);

            if ($template && $template->variants && $template->variants->count() > 0) {
                // Check existing variants count
                $existingVariantsCount = $product->variants()->count();
                \Illuminate\Support\Facades\Log::info("Checking existing variants", [
                    'product_id' => $product->id,
                    'existing_variants_count' => $existingVariantsCount
                ]);

                // Only create variants if product doesn't already have variants (to avoid duplicates)
                if ($existingVariantsCount === 0) {
                    \Illuminate\Support\Facades\Log::info("Creating variants for product", [
                        'product_id' => $product->id,
                        'template_variants_count' => $template->variants->count()
                    ]);

                    foreach ($template->variants as $templateVariant) {
                        $variantName = $templateVariant->variant_name;

                        // Get attributes from template variant (same as ProductController)
                        // Use getAttribute() to ensure we get the 'attributes' column, not model attributes
                        $attributes = [];

                        // Query fresh from database to get clean attributes (same as ProductController)
                        $freshTemplateVariant = \App\Models\TemplateVariant::where('template_id', $product->template_id)
                            ->where('variant_name', $variantName)
                            ->first();

                        if ($freshTemplateVariant) {
                            // IMPORTANT: Use getRawOriginal('attributes') to get the raw value from database
                            // $freshTemplateVariant->attributes returns getAttributes() (all model attributes) due to name conflict
                            // getRawOriginal() returns the raw value before casting
                            $rawAttributes = $freshTemplateVariant->getRawOriginal('attributes');

                            // Log for debugging
                            \Illuminate\Support\Facades\Log::info("DEBUG Product Model: Getting attributes from TemplateVariant", [
                                'variant_name' => $variantName,
                                'getRawOriginal_attributes' => $rawAttributes,
                                'getRawOriginal_attributes_type' => gettype($rawAttributes),
                                'getAttribute_attributes' => $freshTemplateVariant->getAttribute('attributes'),
                                'getAttribute_attributes_type' => gettype($freshTemplateVariant->getAttribute('attributes')),
                            ]);

                            if (!empty($rawAttributes)) {
                                // getRawOriginal returns raw value from database (JSON string)
                                if (is_string($rawAttributes)) {
                                    $decoded = json_decode($rawAttributes, true);
                                    $attributes = is_array($decoded) ? $decoded : [];
                                }
                                // If already an array (shouldn't happen with getRawOriginal, but just in case)
                                elseif (is_array($rawAttributes)) {
                                    $attributes = $rawAttributes;
                                } else {
                                    $attributes = [];
                                }
                            } else {
                                $attributes = [];
                            }
                        }

                        // If no attributes from template, try to parse from variant_name
                        if (empty($attributes)) {
                            $attributes = static::parseAttributesFromVariantName($variantName);
                        }

                        // If still no attributes, create a generic one
                        if (empty($attributes)) {
                            $attributes = ['Variant' => $variantName];
                        }

                        // Log for debugging
                        \Illuminate\Support\Facades\Log::info("DEBUG Product Model: Attributes before saving", [
                            'variant_name' => $variantName,
                            'attributes' => $attributes,
                            'attributes_type' => gettype($attributes),
                            'attributes_is_array' => is_array($attributes),
                        ]);

                        try {
                            // Generate unique SKU (required field)
                            $sku = 'SKU-' . strtoupper(\Illuminate\Support\Str::random(8));
                            // Ensure SKU is unique
                            while (\App\Models\ProductVariant::where('sku', $sku)->exists()) {
                                $sku = 'SKU-' . strtoupper(\Illuminate\Support\Str::random(8));
                            }

                            // Get price, quantity, media from TemplateVariant if available
                            $variantPrice = $product->price;
                            $variantListPrice = $product->list_price;
                            $variantQuantity = 100;
                            $variantMedia = null;

                            if ($product->template) {
                                $variantPrice = $product->template->base_price ?? $variantPrice;
                                $variantListPrice = $product->template->list_price ?? $variantListPrice;
                            }

                            if ($freshTemplateVariant) {
                                $variantPrice = $freshTemplateVariant->price ?? $variantPrice;
                                $variantListPrice = $freshTemplateVariant->list_price ?? $variantListPrice;
                                $variantQuantity = $freshTemplateVariant->quantity ?? 100;
                                $variantMedia = $freshTemplateVariant->media;
                            }

                            // Create variant with all fields from TemplateVariant
                            $variant = \App\Models\ProductVariant::create([
                                'product_id' => $product->id,
                                'template_id' => $product->template_id,
                                'variant_name' => $variantName,
                                'attributes' => $attributes,
                                'sku' => $sku, // SKU is required by database
                                'price' => $variantPrice, // From TemplateVariant
                                'list_price' => $variantListPrice,
                                'quantity' => $variantQuantity, // From TemplateVariant
                                'media' => $variantMedia, // From TemplateVariant
                            ]);

                            // Log what was actually saved
                            \Illuminate\Support\Facades\Log::info("DEBUG Product Model: ProductVariant created - checking what was saved", [
                                'variant_id' => $variant->id,
                                'saved_attributes' => $variant->attributes,
                                'saved_attributes_type' => gettype($variant->attributes),
                                'saved_attributes_raw' => $variant->getRawOriginal('attributes'),
                                'saved_attributes_original' => $variant->getOriginal('attributes'),
                            ]);

                            \Illuminate\Support\Facades\Log::info("Variant created successfully", [
                                'product_id' => $product->id,
                                'variant_id' => $variant->id,
                                'variant_name' => $variantName,
                                'attributes' => $attributes,
                                'saved_attributes' => $variant->attributes,
                                'saved_attributes_type' => gettype($variant->attributes),
                                'note' => 'Only attributes saved, other fields use defaults'
                            ]);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to create variant", [
                                'product_id' => $product->id,
                                'variant_name' => $variantName,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }
                } else {
                    \Illuminate\Support\Facades\Log::info("Skipping variant creation - product already has variants", [
                        'product_id' => $product->id,
                        'existing_variants_count' => $existingVariantsCount
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::info("No variants to create", [
                    'product_id' => $product->id,
                    'template_id' => $product->template_id,
                    'template_exists' => $template ? true : false,
                    'has_variants' => $template && $template->variants ? true : false,
                    'variants_count' => $template && $template->variants ? $template->variants->count() : 0
                ]);
            }

            // Increment shop products count if product has a shop
            if ($product->shop) {
                try {
                    $product->shop->incrementProducts();
                } catch (\Exception $e) {
                    // Log error but don't fail product creation
                    \Illuminate\Support\Facades\Log::warning("Failed to increment products count for shop ID {$product->shop_id}: " . $e->getMessage());
                }
            }
        });

        static::saved(function ($product) {
            $keywordChanged = $product->wasRecentlyCreated || $product->wasChanged('keywords');
            $aiChanged = $keywordChanged
                || $product->wasChanged('name')
                || $product->wasChanged('description')
                || $product->wasChanged('category_id');

            if ($keywordChanged) {
                try {
                    app(\App\Services\CollectionKeywordSyncService::class)->syncProduct($product);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to sync product to keyword collections', [
                        'product_id' => $product->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($aiChanged) {
                try {
                    $matcher = app(\App\Services\CollectionAiMatchService::class);
                    if ($product->wasRecentlyCreated) {
                        $matcher->matchProduct($product);
                    } else {
                        \App\Jobs\MatchProductToCollectionsWithAi::dispatch($product->id)->afterResponse();
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to match product to collections with AI', [
                        'product_id' => $product->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }

    /**
     * Parse attributes from variant name (fallback method)
     * 
     * @param string $variantName
     * @return array
     */
    protected static function parseAttributesFromVariantName(string $variantName): array
    {
        $attributes = [];

        // Common size patterns
        $sizePatterns = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Small', 'Medium', 'Large', '11oz', '12oz', '15oz'];
        $colorPatterns = ['Black', 'White', 'Red', 'Blue', 'Green', 'Yellow', 'Purple', 'Pink', 'Gray', 'Grey', 'Brown', 'Orange', 'Navy', 'Maroon', 'Teal'];

        // Handle format like "Black/S" or "Black S" or "Black-S" or "S/Black"
        $variantName = str_replace(['/', '-'], ' ', $variantName);
        $nameParts = array_filter(explode(' ', trim($variantName)));

        foreach ($nameParts as $part) {
            $part = trim($part);
            if (in_array($part, $sizePatterns)) {
                $attributes['Size'] = $part;
            } elseif (in_array($part, $colorPatterns)) {
                $attributes['Color'] = $part;
            } else {
                // If it's not a common size/color, treat as additional attribute
                if (!isset($attributes['Material'])) {
                    $attributes['Material'] = $part;
                } elseif (!isset($attributes['Style'])) {
                    $attributes['Style'] = $part;
                }
            }
        }

        return $attributes;
    }
    protected $fillable = [
        'template_id',
        'category_id',
        'user_id',
        'shop_id',
        'name',
        'slug',
        'sku',
        'price',
        'list_price',
        'description',
        'allow_customization',
        'customizations',
        'keywords',
        'media',
        'quantity',
        'status',
        'flash_deal_auto_enroll',
        'flash_deal_min_price',
        'created_by',
        'api_token_id',
        // Meta fields for export
        'google_product_category',
        'fb_product_category',
        'gender',
        'color',
        'age_group',
        'material',
        'pattern',
        'shipping',
        'shipping_weight',
        'quantity_to_sell_on_facebook'
    ];

    // Ensure ID is never set manually - let database auto-increment
    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'list_price' => 'decimal:2',
        'flash_deal_min_price' => 'decimal:2',
        'flash_deal_auto_enroll' => 'boolean',
        'customizations' => 'array',
        'media' => 'array',
        'keywords' => 'array',
        'quantity' => 'integer',
    ];

    protected function allowCustomization(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value === true || $value === 1 || $value === '1') {
                    return true;
                }
                if ($value === false || $value === 0 || $value === '0') {
                    return false;
                }

                return (bool) $this->template?->allow_customization;
            },
            set: fn ($value) => $value === null ? null : (int) (bool) $value,
        );
    }

    /**
     * Keywords as comma-separated string for forms.
     */
    public function getKeywordsTextAttribute(): string
    {
        return implode(', ', $this->keywords ?? []);
    }

    // Relationships
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class, 'template_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function resolvedCategoryId(): ?int
    {
        $own = $this->getAttributes()['category_id'] ?? null;
        if ($own) {
            return (int) $own;
        }

        $fromTemplate = $this->template?->category_id;

        return $fromTemplate ? (int) $fromTemplate : null;
    }

    public function scopeInCategoryIds($query, array $categoryIds)
    {
        return $query->where(function ($q) use ($categoryIds) {
            $q->whereIn('category_id', $categoryIds)
                ->orWhere(function ($inner) use ($categoryIds) {
                    $inner->whereNull('category_id')
                        ->whereHas('template', function ($templateQuery) use ($categoryIds) {
                            $templateQuery->whereIn('category_id', $categoryIds);
                        });
                });
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function activeFlashDeal(): HasOne
    {
        return $this->hasOne(FlashDeal::class)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now());
    }

    /**
     * Flash discount percent (0 if no active deal).
     */
    public function flashDiscountPercent(): int
    {
        $deal = $this->activeFlashDeal;
        if (!$deal) {
            return 0;
        }

        $pct = (int) $deal->discount_percent;
        if ($pct > 0) {
            return min(90, max(0, $pct));
        }

        $original = (float) $deal->original_price;
        $sale = (float) $deal->sale_price;
        if ($original > 0 && $sale < $original) {
            return (int) round((($original - $sale) / $original) * 100);
        }

        return 0;
    }

    /**
     * Multiplier to apply to pre-deal prices (e.g. 0.65 for 35% off).
     */
    public function flashPriceMultiplier(): ?float
    {
        $pct = $this->flashDiscountPercent();
        if ($pct <= 0) {
            return null;
        }

        return max(0.1, 1 - ($pct / 100));
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'product_collection')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->approved();
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessors
    public function getBasePriceAttribute(): float
    {
        // Price is always saved in database now
        return $this->price ?? 0;
    }

    public function getPrimaryImageAttribute()
    {
        $media = $this->getEffectiveMedia();
        return !empty($media) ? $media[0] : null;
    }

    public function getDisplayCategoryName(): ?string
    {
        return $this->resolveDisplayCategory()['name'] ?? null;
    }

    /**
     * @return array{name: ?string, category: ?Category}
     */
    public function resolveDisplayCategory(): array
    {
        $ownCategory = $this->relationLoaded('category') ? $this->getRelation('category') : $this->category;
        $templateCategory = $this->template->category ?? null;
        $baseCategory = $ownCategory ?? $templateCategory;
        $inferredName = static::inferCategoryNameFromText(strtolower((string) $this->name));
        $displayName = $inferredName ?? optional($baseCategory)->name;

        $linkCategory = $baseCategory;
        if ($inferredName !== null) {
            $matched = Category::query()->where('name', $inferredName)->first();
            if ($matched) {
                $linkCategory = $matched;
            }
        }

        return [
            'name' => $displayName,
            'category' => $linkCategory,
        ];
    }

    public function getDisplayTitle(int $maxLength = 58): string
    {
        $name = trim((string) $this->name);
        if ($name === '') {
            return '';
        }

        if (str_contains($name, ':')) {
            $short = trim(explode(':', $name, 2)[0]);
            if (mb_strlen($short) >= 12) {
                $name = $short;
            }
        }

        if (mb_strlen($name) <= $maxLength) {
            return $name;
        }

        return \Illuminate\Support\Str::limit($name, $maxLength);
    }

    protected static function inferCategoryNameFromText(string $text): ?string
    {
        $patterns = [
            'Hoodie' => ['hoodie', 'sweatshirt', 'pullover'],
            'T-Shirt' => ['t-shirt', 'tshirt', ' tee ', ' tee,', ' tee.'],
            'Tank Top' => ['tank top', 'tank-top'],
            'Long Sleeve' => ['long sleeve', 'long-sleeve'],
            'Sweater' => ['sweater', 'jumper'],
            'Mug' => [' mug', 'coffee mug'],
            'Poster' => [' poster'],
            'Canvas' => ['canvas print', ' canvas'],
            'Phone Case' => ['phone case'],
            'Tote Bag' => ['tote bag'],
            'Sticker' => [' sticker'],
            'Hat' => [' cap ', 'baseball cap', ' dad hat'],
        ];

        foreach ($patterns as $label => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return $label;
                }
            }
        }

        return null;
    }

    // Helper methods
    public function getEffectivePrice(): float
    {
        // Price is always saved in database now
        return $this->price ?? 0;
    }

    /**
     * MSRP / compare-at price shown with a strikethrough when higher than the selling price.
     */
    public function getCompareAtPrice(): float
    {
        $list = (float) ($this->list_price ?? $this->template?->list_price ?? 0);
        if ($list > 0) {
            return $list;
        }

        return (float) ($this->template?->base_price ?? 0);
    }

    public function getEffectiveDescription(): string
    {
        return $this->description ?? $this->template->description ?? '';
    }

    public function hasCustomization(): bool
    {
        $allowed = $this->getAttributes()['allow_customization'] ?? null;
        if ($allowed === null) {
            return (bool) $this->template?->hasCustomization();
        }

        return (bool) $allowed && ! empty($this->resolvedCustomizations());
    }

    /**
     * @return array<int, mixed>
     */
    public function resolvedCustomizations(): array
    {
        $stored = $this->getAttributes()['customizations'] ?? null;
        if ($stored !== null) {
            return is_array($this->customizations) ? $this->customizations : [];
        }

        return $this->template?->customizations ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getNormalizedCustomizations(): array
    {
        return ProductTemplate::normalizeCustomizationList($this->resolvedCustomizations());
    }

    public function hasRequiredCustomizations(): bool
    {
        foreach ($this->getNormalizedCustomizations() as $customization) {
            if ($customization['required'] ?? false) {
                return true;
            }
        }

        return false;
    }

    public function getEffectiveMedia(): array
    {
        // Get media from product or template
        $media = $this->media ?? ($this->template ? $this->template->media : null) ?? [];

        // Ensure it's an array
        if (is_string($media)) {
            $decoded = json_decode($media, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($media) ? $media : [];
    }

    /**
     * First non-video image URL for admin lists and pickers.
     */
    public function adminThumbnailUrl(): ?string
    {
        foreach ($this->getEffectiveMedia() as $item) {
            $raw = is_array($item) ? ($item['url'] ?? $item['path'] ?? null) : $item;
            if (! is_string($raw) || $raw === '') {
                continue;
            }
            $lower = strtolower($raw);
            if (str_contains($lower, '.mp4') || str_contains($lower, '.mov') || str_contains($lower, '.avi') || str_contains($lower, '.webm')) {
                continue;
            }
            if (! preg_match('#^https?://#i', $raw) && ! str_starts_with($raw, '//')) {
                return url($raw);
            }

            return $raw;
        }

        return null;
    }

    /**
     * Front/back product images for virtual try-on.
     *
     * @return array{image: ?string, back: ?string}
     */
    public function tryOnImages(): array
    {
        $urls = [];
        $back = null;

        foreach ($this->getEffectiveMedia() as $item) {
            $raw = is_array($item) ? ($item['url'] ?? $item['path'] ?? null) : $item;
            if (! is_string($raw) || $raw === '') {
                continue;
            }
            $lower = strtolower($raw);
            if (str_contains($lower, '.mp4') || str_contains($lower, '.mov') || str_contains($lower, '.avi') || str_contains($lower, '.webm')) {
                continue;
            }
            if (! preg_match('#^https?://#i', $raw) && ! str_starts_with($raw, '//')) {
                $raw = url($raw);
            }
            $label = strtolower(is_array($item) ? implode(' ', array_filter([
                $item['alt'] ?? null,
                $item['name'] ?? null,
                $item['view'] ?? null,
                $item['side'] ?? null,
                $item['label'] ?? null,
                $raw,
            ])) : $raw);
            if ($back === null && (str_contains($label, 'back') || str_contains($label, 'rear'))) {
                $back = $raw;

                continue;
            }
            $urls[] = $raw;
        }

        $image = $urls[0] ?? $back;

        return [
            'image' => $image,
            'back' => ($back && $back !== $image) ? $back : null,
        ];
    }

    // Check if current user can edit this product
    public function canEdit($user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }
        return $user->hasRole('admin') || $this->user_id === $user->id;
    }

    /**
     * Scope: Chỉ lấy sản phẩm đủ điều kiện hiển thị
     * - Status = active
     * - Shop tồn tại và active
     * - Có quantity > 0 HOẶC có variants với quantity > 0
     * - Có media (từ product hoặc template)
     */
    public function scopeAvailableForDisplay($query)
    {
        return $query->where('products.status', 'active')
            // Kiểm tra shop active
            ->whereHas('shop', function ($q) {
                $q->where('shop_status', 'active');
            })
            // Kiểm tra có quantity HOẶC có variants với quantity
            ->where(function ($q) {
                $q->where('products.quantity', '>', 0)
                    ->orWhereHas('variants', function ($variantQuery) {
                        $variantQuery->where('quantity', '>', 0);
                    });
            })
            // Kiểm tra có media (product media hoặc template media)
            ->where(function ($q) {
                $q->whereNotNull('products.media')
                    ->where('products.media', '!=', '[]')
                    ->where('products.media', '!=', '')
                    ->orWhereHas('template', function ($templateQuery) {
                        $templateQuery->whereNotNull('media')
                            ->where('media', '!=', '[]')
                            ->where('media', '!=', '');
                    });
            });
    }

    /**
     * Check if product has valid media
     */
    public function hasMedia(): bool
    {
        $media = $this->getEffectiveMedia();
        return !empty($media);
    }

    /**
     * Check if product has available quantity
     */
    public function hasStock(): bool
    {
        if ($this->quantity > 0) {
            return true;
        }

        // Check variants
        return $this->variants()->where('quantity', '>', 0)->exists();
    }

    /**
     * Check if product is available for display
     */
    public function isAvailableForDisplay(): bool
    {
        return $this->status === 'active'
            && $this->shop
            && $this->shop->shop_status === 'active'
            && $this->hasStock()
            && $this->hasMedia();
    }

    /**
     * Total units sold (sum of order item quantities).
     */
    public function getSoldCount(): int
    {
        if (isset($this->order_items_sum_quantity)) {
            return (int) $this->order_items_sum_quantity;
        }

        return (int) $this->orderItems()->sum('quantity');
    }

    /**
     * Get average rating for this product
     */
    public function getAverageRating(): float
    {
        if (isset($this->approved_reviews_avg_rating)) {
            return (float) $this->approved_reviews_avg_rating;
        }

        return (float) ($this->approvedReviews()->avg('rating') ?? 0);
    }

    /**
     * Get total number of reviews
     */
    public function getTotalReviews(): int
    {
        if (isset($this->approved_reviews_count)) {
            return (int) $this->approved_reviews_count;
        }

        return $this->approvedReviews()->count();
    }

    /**
     * Get rating breakdown (1-5 stars count)
     */
    public function getRatingBreakdown(): array
    {
        $breakdown = [];
        for ($i = 1; $i <= 5; $i++) {
            $breakdown[$i] = $this->approvedReviews()->where('rating', $i)->count();
        }
        return $breakdown;
    }
}
