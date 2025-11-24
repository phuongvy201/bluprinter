<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainAnalyticsConfig extends Model
{
    protected $fillable = [
        'domain',
        'property_id',
        'credentials_file',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Lấy cấu hình GA cho domain cụ thể
     * Nếu không tìm thấy, trả về null để dùng default config
     */
    public static function getForDomain(?string $domain): ?self
    {
        if (!$domain) {
            return null;
        }

        return self::where('domain', $domain)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Lấy tất cả domain đã cấu hình
     */
    public static function getAllDomains(): array
    {
        return self::where('is_active', true)
            ->orderBy('domain')
            ->pluck('domain')
            ->toArray();
    }
}
