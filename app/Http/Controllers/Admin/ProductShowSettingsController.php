<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CatalogPageSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductShowSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.product-show', [
            'settings' => CatalogPageSettings::productShow(),
            'defaults' => config('catalog.product_show', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sale_ends_date' => ['nullable', 'date'],
            'free_shipping_threshold_usd' => ['required', 'numeric', 'min:1', 'max:100000'],
            'volume_discounts' => ['nullable', 'array'],
            'volume_discounts.*.min_quantity' => ['required_with:volume_discounts', 'integer', 'min:2', 'max:999'],
            'volume_discounts.*.discount_percent' => ['required_with:volume_discounts', 'integer', 'min:1', 'max:90'],
            'volume_discounts.*.is_popular' => ['nullable', 'boolean'],
            'virtual_stats.views_base' => ['nullable', 'integer', 'min:0'],
            'virtual_stats.views_multiplier' => ['nullable', 'integer', 'min:0'],
            'virtual_stats.in_cart_base' => ['nullable', 'integer', 'min:0'],
            'virtual_stats.in_cart_multiplier' => ['nullable', 'integer', 'min:0'],
        ]);

        $tiers = collect($validated['volume_discounts'] ?? [])
            ->map(function ($tier) {
                return [
                    'min_quantity' => (int) ($tier['min_quantity'] ?? 0),
                    'discount_percent' => (int) ($tier['discount_percent'] ?? 0),
                    'is_popular' => (bool) ($tier['is_popular'] ?? false),
                ];
            })
            ->filter(fn ($tier) => $tier['min_quantity'] >= 2 && $tier['discount_percent'] > 0)
            ->sortBy('min_quantity')
            ->values()
            ->all();

        CatalogPageSettings::saveProductShow([
            'sale_ends_date' => $validated['sale_ends_date'] ?? null,
            'free_shipping_threshold_usd' => round((float) $validated['free_shipping_threshold_usd'], 2),
            'volume_discounts' => $tiers,
            'virtual_stats' => [
                'views_base' => (int) ($validated['virtual_stats']['views_base'] ?? 800),
                'views_multiplier' => (int) ($validated['virtual_stats']['views_multiplier'] ?? 37),
                'in_cart_base' => (int) ($validated['virtual_stats']['in_cart_base'] ?? 40),
                'in_cart_multiplier' => (int) ($validated['virtual_stats']['in_cart_multiplier'] ?? 1),
            ],
        ]);

        return redirect()
            ->route('admin.settings.product-show.edit')
            ->with('success', 'Product page settings saved.');
    }
}
