<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudioAiGeneration extends Model
{
    protected $fillable = [
        'user_id',
        'visitor_token',
        'prompt',
        'image_urls',
        'reference_urls',
        'image_model',
        'ip_address',
        'hidden_from_customer',
    ];

    protected $casts = [
        'image_urls' => 'array',
        'reference_urls' => 'array',
        'hidden_from_customer' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisibleToCustomer(Builder $query): Builder
    {
        return $query->where('hidden_from_customer', false);
    }

    /**
     * @return array<int, string>
     */
    public function images(): array
    {
        return array_values(array_filter(
            array_map('strval', $this->image_urls ?? []),
            fn (string $url) => $url !== '',
        ));
    }
}
