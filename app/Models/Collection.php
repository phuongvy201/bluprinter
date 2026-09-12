<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'name',
        'slug',
        'description',
        'image',
        'type',
        'auto_rules',
        'keywords',
        'status',
        'sort_order',
        'featured',
        'admin_approved',
        'admin_notes',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'auto_rules' => 'array',
        'keywords' => 'array',
        'featured' => 'boolean',
        'admin_approved' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Keywords as comma-separated string for forms.
     */
    public function getKeywordsTextAttribute(): string
    {
        return implode(', ', $this->keywords ?? []);
    }

    /**
     * Get the user who created this collection
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shop this collection belongs to
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get products in this collection
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_collection')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Get active products in this collection
     */
    public function activeProducts(): BelongsToMany
    {
        return $this->products()->where('status', 'active');
    }

    /**
     * Get products count
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * Get active products count
     */
    public function getActiveProductsCountAttribute(): int
    {
        return $this->activeProducts()->count();
    }

    /**
     * Products eligible for storefront display (active, in stock, has media).
     */
    public function displayableProducts(): BelongsToMany
    {
        return $this->activeProducts()->availableForDisplay();
    }

    public function scopeWithDisplayableProductsCount($query)
    {
        return $query->withCount(['products as displayable_products_count' => function ($productQuery) {
            $productQuery->where('status', 'active')->availableForDisplay();
        }]);
    }

    public function scopeHasDisplayableProducts($query)
    {
        return $query->whereHas('products', function ($productQuery) {
            $productQuery->where('status', 'active')->availableForDisplay();
        });
    }

    /**
     * Generate slug from name
     */
    public static function generateSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->when($excludeId, function ($query, $id) {
            return $query->where('id', '!=', $id);
        })->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Scope for active collections
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for featured collections
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for manual collections
     */
    public function scopeManual($query)
    {
        return $query->where('type', 'manual');
    }

    /**
     * Scope for automatic collections
     */
    public function scopeAutomatic($query)
    {
        return $query->where('type', 'automatic');
    }

    /**
     * Scope for user's collections
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for shop's collections
     */
    public function scopeForShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    /**
     * Scope for admin approved collections
     */
    public function scopeApproved($query)
    {
        return $query->where('admin_approved', true);
    }

    /**
     * Scope for pending approval collections
     */
    public function scopePending($query)
    {
        return $query->where('admin_approved', false);
    }

    /**
     * Global collections are admin-managed and shared by all shops.
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('shop_id');
    }

    /**
     * Check if collection can be edited by user (global collections: admin only)
     */
    public function canEdit($user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        // Legacy shop-owned collections
        return $this->shop_id !== null && $this->user_id === $user->id;
    }

    /**
     * Sellers and admins can view global collections in admin UI.
     */
    public function canView($user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->hasRole('admin') || $user->hasRole('seller')) {
            return true;
        }

        return $this->canEdit($user);
    }

    /**
     * Check if collection is automatic
     */
    public function isAutomatic(): bool
    {
        return $this->type === 'automatic';
    }

    /**
     * Check if collection is manual
     */
    public function isManual(): bool
    {
        return $this->type === 'manual';
    }

    /**
     * Check if collection is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if collection is featured
     */
    public function isFeatured(): bool
    {
        return $this->featured === true;
    }

    /**
     * Check if collection is admin approved
     */
    public function isApproved(): bool
    {
        return $this->admin_approved === true;
    }

    /**
     * Check if collection is pending approval
     */
    public function isPending(): bool
    {
        return $this->admin_approved === false;
    }
}
