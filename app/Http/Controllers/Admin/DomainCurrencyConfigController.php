<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainCurrencyConfig;
use App\Services\CurrencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainCurrencyConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $configs = DomainCurrencyConfig::orderBy('domain')->get();

        return view('admin.settings.domain-currency.index', compact('configs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $currencies = [
            'USD' => 'USD - US Dollar ($)',
            'GBP' => 'GBP - British Pound (£)',
            'EUR' => 'EUR - Euro (€)',
            'VND' => 'VND - Vietnamese Dong (₫)',
            'CAD' => 'CAD - Canadian Dollar (C$)',
            'AUD' => 'AUD - Australian Dollar (A$)',
            'JPY' => 'JPY - Japanese Yen (¥)',
            'CNY' => 'CNY - Chinese Yuan (¥)',
            'HKD' => 'HKD - Hong Kong Dollar (HK$)',
            'SGD' => 'SGD - Singapore Dollar (S$)',
            'MXN' => 'MXN - Mexican Peso (MX$)',
        ];

        // Default rates
        $defaultRates = [
            'USD' => 1.0,
            'GBP' => 0.79,
            'EUR' => 0.92,
            'VND' => 25000,
            'CAD' => 1.35,
            'AUD' => 1.52,
            'JPY' => 150,
            'CNY' => 7.2,
            'HKD' => 7.8,
            'SGD' => 1.34,
            'MXN' => 17.5,
        ];

        return view('admin.settings.domain-currency.create', compact('currencies', 'defaultRates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', 'unique:domain_currency_configs,domain'],
            'currency' => ['required', 'string', 'size:3'],
            'currency_rate' => ['required', 'numeric', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        // Set default rate if not provided
        if (!isset($validated['currency_rate']) || empty($validated['currency_rate'])) {
            $defaultRates = [
                'USD' => 1.0,
                'GBP' => 0.79,
                'EUR' => 0.92,
                'VND' => 25000,
            ];
            $validated['currency_rate'] = $defaultRates[$validated['currency']] ?? 1.0;
        }

        $validated['is_active'] = $request->has('is_active');

        DomainCurrencyConfig::create($validated);

        // Clear cache for this domain
        CurrencyService::clearCache($validated['domain']);

        return redirect()
            ->route('admin.settings.domain-currency.index')
            ->with('success', 'Đã tạo cấu hình currency cho domain: ' . $validated['domain']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $config = DomainCurrencyConfig::findOrFail($id);

        $currencies = [
            'USD' => 'USD - US Dollar ($)',
            'GBP' => 'GBP - British Pound (£)',
            'EUR' => 'EUR - Euro (€)',
            'VND' => 'VND - Vietnamese Dong (₫)',
            'CAD' => 'CAD - Canadian Dollar (C$)',
            'AUD' => 'AUD - Australian Dollar (A$)',
            'JPY' => 'JPY - Japanese Yen (¥)',
            'CNY' => 'CNY - Chinese Yuan (¥)',
            'HKD' => 'HKD - Hong Kong Dollar (HK$)',
            'SGD' => 'SGD - Singapore Dollar (S$)',
            'MXN' => 'MXN - Mexican Peso (MX$)',
        ];

        $defaultRates = [
            'USD' => 1.0,
            'GBP' => 0.79,
            'EUR' => 0.92,
            'VND' => 25000,
            'CAD' => 1.35,
            'AUD' => 1.52,
            'JPY' => 150,
            'CNY' => 7.2,
            'HKD' => 7.8,
            'SGD' => 1.34,
            'MXN' => 17.5,
        ];

        return view('admin.settings.domain-currency.edit', compact('config', 'currencies', 'defaultRates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $config = DomainCurrencyConfig::findOrFail($id);
        $oldDomain = $config->domain;

        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', 'unique:domain_currency_configs,domain,' . $id],
            'currency' => ['required', 'string', 'size:3'],
            'currency_rate' => ['required', 'numeric', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $config->update($validated);

        // Clear cache for both old and new domain
        CurrencyService::clearCache($oldDomain);
        if ($oldDomain !== $validated['domain']) {
            CurrencyService::clearCache($validated['domain']);
        }

        return redirect()
            ->route('admin.settings.domain-currency.index')
            ->with('success', 'Đã cập nhật cấu hình currency cho domain: ' . $validated['domain']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $config = DomainCurrencyConfig::findOrFail($id);
        $domain = $config->domain;

        $config->delete();

        // Clear cache
        CurrencyService::clearCache($domain);

        return redirect()
            ->route('admin.settings.domain-currency.index')
            ->with('success', 'Đã xóa cấu hình currency cho domain: ' . $domain);
    }
}
