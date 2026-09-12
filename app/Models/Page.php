<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'status',
        'published_at',
        'template',
        'sort_order',
        'show_in_menu',
        'menu_title',
        'parent_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'views',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'show_in_menu' => 'boolean',
        'views' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the user who created this page
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get parent page
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * Get child pages
     */
    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('sort_order');
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
     * Scope for published pages
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
     * Scope for menu pages
     */
    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true)
            ->orderBy('sort_order');
    }

    /**
     * Check if page is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' &&
            ($this->published_at === null || $this->published_at <= now());
    }

    /**
     * Increment views
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * Allowed frontend page templates.
     *
     * @return array<string, string>
     */
    public static function templateOptions(): array
    {
        return [
            'default' => 'Editorial — card gọn, dễ đọc',
            'fullwidth' => 'Full width — nội dung rộng, không khung',
            'sidebar' => 'Sidebar — nội dung + trang liên quan',
            'hero' => 'Hero — ảnh full-bleed, landing',
            'magazine' => 'Magazine — kiểu tạp chí, typography lớn',
            'split' => 'Split — ảnh sticky + nội dung',
        ];
    }

    /**
     * @return array<string, array{label: string, blurb: string}>
     */
    public static function templateMeta(): array
    {
        return [
            'default' => [
                'label' => 'Editorial',
                'blurb' => 'Header petrol + nội dung trong khung — phù hợp policy, FAQ.',
            ],
            'fullwidth' => [
                'label' => 'Full width',
                'blurb' => 'Ảnh ngang full + chữ rộng — tốt khi content nhiều hình.',
            ],
            'sidebar' => [
                'label' => 'Sidebar',
                'blurb' => 'Cột phải Related pages — khi có trang con.',
            ],
            'hero' => [
                'label' => 'Hero',
                'blurb' => 'Ảnh phủ nền full-bleed + title lớn — landing / About.',
            ],
            'magazine' => [
                'label' => 'Magazine',
                'blurb' => 'Typography tạp chí, ảnh nổi — story / editorial.',
            ],
            'split' => [
                'label' => 'Split',
                'blurb' => 'Ảnh sticky trái, chữ phải — brand / campaign.',
            ],
        ];
    }

    public function resolveTemplateView(): string
    {
        $template = $this->template ?: 'default';

        if (!array_key_exists($template, static::templateOptions())) {
            $template = 'default';
        }

        return "pages.templates.{$template}";
    }

    public function featuredImageUrl(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        if (str_starts_with($this->featured_image, 'http://') || str_starts_with($this->featured_image, 'https://')) {
            return $this->featured_image;
        }

        return \Illuminate\Support\Facades\Storage::url($this->featured_image);
    }
}
