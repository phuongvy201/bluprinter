<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'template_id',
        'user_id',
        'shop_id',
        'name',
        'slug',
        'price',
        'description',
        'media',
        'quantity',
        'status',
        'created_by',
        'api_token_id'
    ];

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
}
