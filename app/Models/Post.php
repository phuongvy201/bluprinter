<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'post_category_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'gallery',
        'status',
        'published_at',
        'type',
        'featured',
        'sticky',
        'allow_comments',
        'comments_count',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'views',
        'likes',
        'shares',
        'reading_time',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'gallery' => 'array',
        'featured' => 'boolean',
        'sticky' => 'boolean',
        'allow_comments' => 'boolean',
        'views' => 'integer',
        'likes' => 'integer',
        'shares' => 'integer',
        'comments_count' => 'integer',
        'reading_time' => 'integer',
    ];

    /**
     * Get the user who created this post
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shop this post belongs to
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /**
     * Get tags
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PostTag::class, 'post_post_tag');
    }

    /**
     * Generate slug from title
     */
    public static function generateSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
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
     * Scope for published posts
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope for featured posts
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for sticky posts
     */
    public function scopeSticky($query)
    {
        return $query->where('sticky', true);
    }

    /**
     * Scope for shop's posts
     */
    public function scopeForShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    /**
     * Check if post is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' &&
            ($this->published_at === null || $this->published_at <= now());
    }

    /**
     * Calculate and update reading time
     */
    public function updateReadingTime(): void
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $this->reading_time = ceil($wordCount / 200); // Average reading speed: 200 words/minute
        $this->save();
    }

    /**
     * Increment views
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * Increment likes
     */
    public function incrementLikes(): void
    {
        $this->increment('likes');
    }

    /**
     * Increment shares
     */
    public function incrementShares(): void
    {
        $this->increment('shares');
    }

    /**
     * Get featured image URL
     * Handle both S3 URLs and legacy storage paths
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        // If it's already a full URL (S3), return as is
        if (str_starts_with($this->featured_image, 'http://') || str_starts_with($this->featured_image, 'https://')) {
            return $this->featured_image;
        }

        // Legacy storage path - add /storage/ prefix
        return asset('storage/' . $this->featured_image);
    }

    /**
     * Get gallery URLs
     * Handle both S3 URLs and legacy storage paths
     */
    public function getGalleryUrlsAttribute(): array
    {
        if (!$this->gallery || !is_array($this->gallery)) {
            return [];
        }

        return array_map(function ($image) {
            // If it's already a full URL (S3), return as is
            if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
                return $image;
            }

            // Legacy storage path - add /storage/ prefix
            return asset('storage/' . $image);
        }, $this->gallery);
    }
}
