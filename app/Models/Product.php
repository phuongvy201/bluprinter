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
        return $user->hasRole('admin') || $this->user_id === $user->id;
    }
}
