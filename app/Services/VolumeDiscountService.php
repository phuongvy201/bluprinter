<?php

namespace App\Services;

use App\Support\CatalogPageSettings;
use Illuminate\Support\Collection;

class VolumeDiscountService
{
    /**
     * @param  Collection<int, \App\Models\Cart>  $cartItems
     * @return array{percent: int, amount: float, total_quantity: int, tier: ?array}
     */
    public function calculate(Collection $cartItems, float $subtotal): array
    {
        $totalQuantity = (int) $cartItems->sum('quantity');
        $percent = $this->percentForQuantity($totalQuantity);
        $amount = $percent > 0 ? round($subtotal * ($percent / 100), 2) : 0.0;

        return [
            'percent' => $percent,
            'amount' => $amount,
            'total_quantity' => $totalQuantity,
            'tier' => $this->tierForQuantity($totalQuantity),
        ];
    }

    public function percentForQuantity(int $totalQuantity): int
    {
        $tier = $this->tierForQuantity($totalQuantity);

        return $tier ? (int) ($tier['discount_percent'] ?? $tier['percent'] ?? 0) : 0;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function tierForQuantity(int $totalQuantity): ?array
    {
        $tiers = CatalogPageSettings::productShow()['volume_discounts'] ?? [];
        if (! is_array($tiers) || $tiers === []) {
            return null;
        }

        usort($tiers, fn ($a, $b) => ((int) ($b['min_quantity'] ?? 0)) <=> ((int) ($a['min_quantity'] ?? 0)));

        foreach ($tiers as $tier) {
            $min = (int) ($tier['min_quantity'] ?? 0);
            if ($totalQuantity >= $min && $min > 0) {
                return $tier;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function tiers(): array
    {
        return CatalogPageSettings::productShow()['volume_discounts'] ?? [];
    }

    public function isEligible(Collection $cartItems): bool
    {
        return $this->percentForQuantity((int) $cartItems->sum('quantity')) > 0;
    }
}
