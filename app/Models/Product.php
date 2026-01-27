<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
                        
                        // Get attributes from template variant (preferred)
                        $attributes = $templateVariant->attributes ?? [];
                        
                        // If no attributes from template, try to parse from variant_name
                        if (empty($attributes)) {
                            $attributes = static::parseAttributesFromVariantName($variantName);
                        }

                        // If still no attributes, create a generic one
                        if (empty($attributes)) {
                            $attributes = ['Variant' => $variantName];
                        }

                        try {
                            $variant = \App\Models\ProductVariant::create([
                                'product_id' => $product->id,
                                'template_id' => $product->template_id,
                                'variant_name' => $variantName,
                                'attributes' => $attributes,
                                'price' => $templateVariant->price ?? null,
                                'sku' => 'SKU-' . strtoupper(\Illuminate\Support\Str::random(8)),
                                'quantity' => $templateVariant->quantity ?? 0,
                                'media' => null,
                            ]);

                            \Illuminate\Support\Facades\Log::info("Variant created successfully", [
                                'product_id' => $product->id,
                                'variant_id' => $variant->id,
                                'variant_name' => $variantName,
                                'attributes' => $attributes
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
        'user_id',
        'shop_id',
        'name',
        'slug',
        'sku',
        'price',
        'description',
        'media',
        'quantity',
        'status',
        'created_by',
        'api_token_id'
    ];

    // Ensure ID is never set manually - let database auto-increment
    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'media' => 'array',
        'quantity' => 'integer',
    ];

    // Relationships
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class, 'template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
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

    public function getCategoryAttribute()
    {
        return $this->template->category ?? null;
    }

    // Helper methods
    public function getEffectivePrice(): float
    {
        // Price is always saved in database now
        return $this->price ?? 0;
    }

    public function getEffectiveDescription(): string
    {
        return $this->description ?? $this->template->description;
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
     * Get average rating for this product
     */
    public function getAverageRating(): float
    {
        return $this->approvedReviews()->avg('rating') ?? 0;
    }

    /**
     * Get total number of reviews
     */
    public function getTotalReviews(): int
    {
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
