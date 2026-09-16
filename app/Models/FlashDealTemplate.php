<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashDealTemplate extends Model
{
    protected $fillable = [
        'shop_id',
        'name',
        'category_id',
        'discount_percent',
        'start_time',
        'end_time',
        'recurrence',
        'days_of_week',
        'max_products',
        'is_active',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'is_active' => 'boolean',
        'discount_percent' => 'integer',
        'max_products' => 'integer',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function appliesToday(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (in_array($this->recurrence, ['daily', 'hourly'], true)) {
            return true;
        }

        $today = (int) now()->dayOfWeek; // 0=Sunday

        return in_array($today, $this->days_of_week ?? [], true);
    }

    public function recurrenceLabel(): string
    {
        return match ($this->recurrence) {
            'hourly' => 'Theo khung giờ',
            'daily' => 'Theo ngày',
            'weekly' => 'Theo tuần',
            default => (string) $this->recurrence,
        };
    }
}
