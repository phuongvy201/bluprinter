<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashDealRule extends Model
{
    public const TYPE_STALE_INVENTORY = 'stale_inventory';
    public const TYPE_HIGH_MARGIN = 'high_margin';
    public const TYPE_TRENDING = 'trending';

    protected $fillable = [
        'name',
        'rule_type',
        'config',
        'discount_percent',
        'max_products',
        'priority',
        'golden_hours',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'golden_hours' => 'array',
        'is_active' => 'boolean',
        'discount_percent' => 'integer',
        'max_products' => 'integer',
        'priority' => 'integer',
    ];

    public static function ruleTypes(): array
    {
        return [
            self::TYPE_STALE_INVENTORY => 'Tồn kho lâu chưa bán',
            self::TYPE_HIGH_MARGIN => 'Margin cao (ưu tiên khung giờ vàng)',
            self::TYPE_TRENDING => 'Bán chạy gần đây',
        ];
    }
}
