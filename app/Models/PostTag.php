<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class PostTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'posts_count',
    ];

    protected $casts = [
        'posts_count' => 'integer',
    ];

    /**
     * Get posts with this tag
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_post_tag');
    }

    /**
     * Get published posts with this tag
     */
    public function publishedPosts(): BelongsToMany
    {
        return $this->posts()->published()->latest('published_at');
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
     * Update posts count
     */
    public function updatePostsCount(): void
    {
        $this->posts_count = $this->posts()->published()->count();
        $this->save();
    }
}
