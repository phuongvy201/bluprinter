<?php

namespace App\Support;

class CatalogPageSettings
{
    public static function productShow(): array
    {
        $defaults = config('catalog.product_show', []);
        $stored = Settings::get('catalog.product_show');

        if ($stored) {
            $decoded = json_decode($stored, true);
            if (is_array($decoded)) {
                return array_replace_recursive($defaults, $decoded);
            }
        }

        return $defaults;
    }

    public static function saveProductShow(array $data): void
    {
        Settings::set('catalog.product_show', json_encode($data));
    }

    /**
     * Free shipping unlock amount in USD (after discounts).
     */
    public static function freeShippingThresholdUsd(): float
    {
        $value = static::productShow()['free_shipping_threshold_usd']
            ?? config('catalog.product_show.free_shipping_threshold_usd', 100);

        return max(0.01, round((float) $value, 2));
    }
}
