<?php

namespace App\Support;

class StudioPricingSettings
{
    /**
     * @return array{ai_design_price: float, upload_design_price: float, library_default_price: float}
     */
    public static function resolved(): array
    {
        $defaults = self::defaults();
        $stored = Settings::get('studio.pricing');
        if (!$stored) {
            return $defaults;
        }

        $decoded = is_array($stored) ? $stored : json_decode((string) $stored, true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        return [
            'ai_design_price' => 0,
            'upload_design_price' => 0,
            'library_default_price' => self::money($decoded['library_default_price'] ?? $defaults['library_default_price']),
        ];
    }

    public static function save(array $data): void
    {
        Settings::set('studio.pricing', json_encode([
            'ai_design_price' => 0,
            'upload_design_price' => 0,
            'library_default_price' => self::money($data['library_default_price'] ?? 0),
        ]));
    }

    /**
     * @return array{ai_design_price: float, upload_design_price: float, library_default_price: float}
     */
    public static function defaults(): array
    {
        return [
            'ai_design_price' => 0,
            'upload_design_price' => 0,
            'library_default_price' => (float) config('studio.default_design_price', 3),
        ];
    }

    protected static function money(mixed $value): float
    {
        return max(0, round((float) $value, 2));
    }
}
