<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainCurrencyConfig;
use App\Models\DomainAnalyticsConfig;
use App\Services\CurrencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DomainConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $currencyConfigs = DomainCurrencyConfig::orderBy('domain')->get();
        $analyticsConfigs = DomainAnalyticsConfig::orderBy('domain')->get();

        return view('admin.settings.domain-config.index', compact('currencyConfigs', 'analyticsConfigs'));
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
        ];

        return view('admin.settings.domain-config.create', compact('currencies', 'defaultRates'));
    }

    /**
     * Store currency configuration
     */
    public function storeCurrency(Request $request): RedirectResponse
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
            ->route('admin.settings.domain-config.index')
            ->with('success', 'Đã tạo cấu hình currency cho domain: ' . $validated['domain']);
    }

    /**
     * Store analytics configuration
     */
    public function storeAnalytics(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', 'unique:domain_analytics_configs,domain'],
            'property_id' => ['required', 'string', 'max:255'],
            'credentials_file' => ['required', 'file', 'mimes:json', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        // Upload file credentials
        $file = $request->file('credentials_file');
        $filePath = $file->storeAs('domain-analytics', $validated['domain'] . '-' . time() . '.json', 'local');

        // Validate JSON file
        $fileContent = Storage::get($filePath);
        $jsonData = json_decode($fileContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Storage::delete($filePath);
            return back()->withErrors([
                'credentials_file' => 'File JSON không hợp lệ. Vui lòng kiểm tra lại file.'
            ])->withInput();
        }

        $validated['credentials_file'] = $filePath;
        $validated['is_active'] = $request->has('is_active');

        DomainAnalyticsConfig::create($validated);

        return redirect()
            ->route('admin.settings.domain-config.index')
            ->with('success', 'Đã tạo cấu hình Google Analytics cho domain: ' . $validated['domain']);
    }

    /**
     * Show the form for editing currency configuration.
     */
    public function editCurrency(string $id): View
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
        ];

        return view('admin.settings.domain-config.edit-currency', compact('config', 'currencies', 'defaultRates'));
    }

    /**
     * Show the form for editing analytics configuration.
     */
    public function editAnalytics(string $id): View
    {
        $config = DomainAnalyticsConfig::findOrFail($id);

        return view('admin.settings.domain-config.edit-analytics', compact('config'));
    }

    /**
     * Update currency configuration
     */
    public function updateCurrency(Request $request, string $id): RedirectResponse
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
            ->route('admin.settings.domain-config.index')
            ->with('success', 'Đã cập nhật cấu hình currency cho domain: ' . $validated['domain']);
    }

    /**
     * Update analytics configuration
     */
    public function updateAnalytics(Request $request, string $id): RedirectResponse
    {
        $config = DomainAnalyticsConfig::findOrFail($id);

        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', 'unique:domain_analytics_configs,domain,' . $id],
            'property_id' => ['required', 'string', 'max:255'],
            'credentials_file' => ['nullable', 'file', 'mimes:json', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        // Nếu có upload file mới
        if ($request->hasFile('credentials_file')) {
            // Xóa file cũ nếu có
            if ($config->credentials_file && Storage::exists($config->credentials_file)) {
                Storage::delete($config->credentials_file);
            }

            // Upload file mới
            $file = $request->file('credentials_file');
            $filePath = $file->storeAs('domain-analytics', $validated['domain'] . '-' . time() . '.json', 'local');

            // Validate JSON file
            $fileContent = Storage::get($filePath);
            $jsonData = json_decode($fileContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Storage::delete($filePath);
                return back()->withErrors([
                    'credentials_file' => 'File JSON không hợp lệ. Vui lòng kiểm tra lại file.'
                ])->withInput();
            }

            $validated['credentials_file'] = $filePath;
        } else {
            // Giữ nguyên file cũ
            unset($validated['credentials_file']);
        }

        $validated['is_active'] = $request->has('is_active');

        $config->update($validated);

        return redirect()
            ->route('admin.settings.domain-config.index')
            ->with('success', 'Đã cập nhật cấu hình Google Analytics cho domain: ' . $validated['domain']);
    }

    /**
     * Remove currency configuration
     */
    public function destroyCurrency(string $id): RedirectResponse
    {
        $config = DomainCurrencyConfig::findOrFail($id);
        $domain = $config->domain;

        $config->delete();

        // Clear cache
        CurrencyService::clearCache($domain);

        return redirect()
            ->route('admin.settings.domain-config.index')
            ->with('success', 'Đã xóa cấu hình currency cho domain: ' . $domain);
    }

    /**
     * Remove analytics configuration
     */
    public function destroyAnalytics(string $id): RedirectResponse
    {
        $config = DomainAnalyticsConfig::findOrFail($id);
        $domain = $config->domain;

        // Xóa file credentials nếu có
        if ($config->credentials_file && Storage::exists($config->credentials_file)) {
            Storage::delete($config->credentials_file);
        }

        $config->delete();

        return redirect()
            ->route('admin.settings.domain-config.index')
            ->with('success', 'Đã xóa cấu hình Google Analytics cho domain: ' . $domain);
    }
}

