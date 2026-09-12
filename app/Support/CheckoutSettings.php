<?php

namespace App\Support;

class CheckoutSettings
{
    /**
     * @return array{stripe: bool, paypal: bool, lianlian: bool}
     */
    public static function paymentMethods(): array
    {
        $defaults = config('checkout.payment_methods', []);
        $stored = Settings::get('checkout.payment_methods');

        if ($stored) {
            $decoded = json_decode($stored, true);
            if (is_array($decoded)) {
                return array_merge([
                    'stripe' => true,
                    'paypal' => false,
                    'lianlian' => false,
                ], $decoded);
            }
        }

        return [
            'stripe' => (bool) ($defaults['stripe'] ?? true),
            'paypal' => (bool) ($defaults['paypal'] ?? false),
            'lianlian' => (bool) ($defaults['lianlian'] ?? false),
        ];
    }

    /**
     * @param array{stripe?: bool, paypal?: bool, lianlian?: bool} $data
     */
    public static function savePaymentMethods(array $data): void
    {
        Settings::set('checkout.payment_methods', json_encode([
            'stripe' => (bool) ($data['stripe'] ?? false),
            'paypal' => (bool) ($data['paypal'] ?? false),
            'lianlian' => (bool) ($data['lianlian'] ?? false),
        ]));
    }

    /**
     * @return list<string>
     */
    public static function allowedPaymentMethodValues(): array
    {
        $methods = self::paymentMethods();
        $allowed = [];

        if ($methods['stripe']) {
            $allowed[] = 'stripe';
        }
        if ($methods['paypal']) {
            $allowed[] = 'paypal';
        }
        if ($methods['lianlian']) {
            $allowed[] = 'lianlian_pay';
        }

        return $allowed !== [] ? $allowed : ['stripe'];
    }
}
