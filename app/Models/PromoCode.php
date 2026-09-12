<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PromoCode extends Model
{
    public const AUDIENCE_WELCOME = 'welcome';
    public const AUDIENCE_THANK_YOU = 'thank_you';
    public const AUDIENCE_WIN_BACK = 'win_back';
    public const AUDIENCE_VIP = 'vip';
    public const AUDIENCE_PUBLIC = 'public';

    protected $fillable = [
        'code',
        'title',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'audience',
        'usage_limit',
        'usage_count',
        'per_customer_limit',
        'starts_at',
        'expires_at',
        'is_active',
        'show_on_promo_page',
        'is_auto_generated',
        'assigned_email',
        'assigned_user_id',
        'created_by',
        'meta',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'per_customer_limit' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'show_on_promo_page' => 'boolean',
        'is_auto_generated' => 'boolean',
        'meta' => 'array',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    public function scopePublicPage($query)
    {
        return $query->active()
            ->where('show_on_promo_page', true)
            ->where('audience', self::AUDIENCE_PUBLIC);
    }

    public static function generateUniqueCode(string $prefix = 'SAVE'): string
    {
        do {
            $code = strtoupper($prefix . '-' . Str::random(6));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isNotStarted(): bool
    {
        return $this->starts_at !== null && $this->starts_at->isFuture();
    }

    public function hasRemainingUses(): bool
    {
        return $this->usage_limit === null || $this->usage_count < $this->usage_limit;
    }

    public function isSoldOut(): bool
    {
        return ! $this->hasRemainingUses();
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $discount = $this->type === 'fixed'
            ? (float) $this->value
            : round($subtotal * ((float) $this->value / 100), 2);

        if ($this->max_discount_amount !== null) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }

        return min($discount, $subtotal);
    }

    public function customerUsageCount(?int $userId, ?string $email): int
    {
        return $this->usages()
            ->where(function ($q) use ($userId, $email) {
                if ($userId) {
                    $q->where('user_id', $userId);
                }
                if ($email) {
                    $q->orWhere('email', strtolower(trim($email)));
                }
            })
            ->count();
    }

    public function recordUsage(Order $order, float $discountAmount): PromoCodeUsage
    {
        $this->increment('usage_count');

        return $this->usages()->create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'email' => strtolower(trim($order->customer_email)),
            'discount_amount' => $discountAmount,
        ]);
    }
}
