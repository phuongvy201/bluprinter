<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudioMockup extends Model
{
    protected $fillable = [
        'studio_product_id',
        'name',
        'image_url',
        'color',
        'print_area',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'print_area' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function studioProduct(): BelongsTo
    {
        return $this->belongsTo(StudioProduct::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function normalizedPrintArea(?array $fallback = null): array
    {
        return StudioProduct::normalizePrintArea($this->print_area, $fallback);
    }
}
