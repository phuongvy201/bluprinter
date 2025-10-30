<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    protected $fillable = [
        'email',
        'status',
        'subscribed_at',
        'unsubscribed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if email is already subscribed
     */
    public static function isSubscribed(string $email): bool
    {
        return self::where('email', $email)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Subscribe email to newsletter
     */
    public static function subscribe(string $email, string $ipAddress = null, string $userAgent = null): self
    {
        return self::updateOrCreate(
            ['email' => $email],
            [
                'status' => 'active',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]
        );
    }

    /**
     * Unsubscribe email from newsletter
     */
    public function unsubscribe(): void
    {
        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);
    }
}
