<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudioDesign extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'image_url',
        'preview_url',
        'tag',
        'price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function previewUrl(): string
    {
        if (filled($this->preview_url)) {
            return (string) $this->preview_url;
        }

        $made = \App\Support\StudioDesignPreview::fromUrl((string) $this->image_url);
        if ($made) {
            $this->forceFill(['preview_url' => $made])->save();

            return $made;
        }

        return (string) $this->image_url;
    }
}
