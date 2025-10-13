<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Shop extends Model
{
    protected $fillable = [
        'user_id',
        'shop_name',
        'shop_slug',
        'shop_description',
        'shop_logo',
        'shop_banner',
        'shop_phone',
        'shop_email',
        'shop_address',
        'shop_city',
        'shop_country',
        'shop_status',
        'verified',
        'rating',
        'total_ratings',
        'total_products',
        'total_sales',
        'total_revenue',
        'business_license',
        'tax_code',
        'facebook_url',
        'instagram_url',
        'website_url',
        'return_policy',
        'shipping_policy',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'rating' => 'decimal:2',
        'total_ratings' => 'integer',
        'total_products' => 'integer',
        'total_sales' => 'integer',
        'total_revenue' => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_followers', 'shop_id', 'user_id')
            ->withTimestamps();
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_favorites', 'shop_id', 'user_id')
            ->withTimestamps();
    }

    // Accessors
    public function getNameAttribute(): string
    {
        return $this->shop_name;
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->shop_status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->shop_status === 'suspended';
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function getRatingStars(): float
    {
        return round($this->rating, 1);
    }

    public function getLogoUrl(): ?string
    {
        return $this->shop_logo;
    }

    public function getBannerUrl(): ?string
    {
        return $this->shop_banner;
    }

    public function incrementProducts(): void
    {
        $this->increment('total_products');
    }

    public function decrementProducts(): void
    {
        $this->decrement('total_products');
    }

    public function incrementSales(): void
    {
        $this->increment('total_sales');
    }

    public function addRevenue(float $amount): void
    {
        $this->increment('total_revenue', $amount);
    }

    public function updateRating(float $newRating): void
    {
        $totalRatings = $this->total_ratings;
        $currentRating = $this->rating;

        $newAverage = (($currentRating * $totalRatings) + $newRating) / ($totalRatings + 1);

        $this->update([
            'rating' => $newAverage,
            'total_ratings' => $totalRatings + 1,
        ]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('shop_status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('total_sales', 'desc');
    }

    public function scopeTopRated($query)
    {
        return $query->where('total_ratings', '>', 0)
            ->orderBy('rating', 'desc');
    }

    // Route Model Binding
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === null) {
            $field = 'shop_slug';
        }

        return $this->where($field, $value)->first();
    }

    // Alias for owner relationship
    public function owner()
    {
        return $this->user();
    }
}
