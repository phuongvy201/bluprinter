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
}
