<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GmcConfig;
use App\Models\DomainCurrencyConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GmcConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $configs = GmcConfig::orderBy('domain')
            ->orderBy('target_country')
            ->get();

        return view('admin.settings.gmc-config.index', compact('configs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $countries = [
            'US' => 'United States (USD)',
            'GB' => 'United Kingdom (GBP)',
            'VN' => 'Vietnam (VND)',
            'CA' => 'Canada (CAD)',
            'AU' => 'Australia (AUD)',
            'NZ' => 'New Zealand (NZD)',
            'DE' => 'Germany (EUR)',
            'FR' => 'France (EUR)',
            'IT' => 'Italy (EUR)',
            'ES' => 'Spain (EUR)',
        ];

        return view('admin.settings.gmc-config.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255'],
            'target_country' => ['required', 'string', 'size:2'],
            'name' => ['required', 'string', 'max:255'],
            'merchant_id' => ['required', 'string', 'max:255'],
            'data_source_id' => ['nullable', 'string', 'max:255'],
            'credentials_file' => ['required', 'file', 'mimes:json', 'max:2048'],
            'content_language' => ['required', 'string', 'max:5'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Check unique constraint: domain + target_country
        $existing = GmcConfig::where('domain', $validated['domain'])
            ->where('target_country', strtoupper($validated['target_country']))
            ->first();

        if ($existing) {
            return back()->withErrors([
                'target_country' => 'Đã tồn tại cấu hình GMC cho domain này và thị trường này. Vui lòng chỉnh sửa cấu hình hiện có hoặc chọn thị trường khác.'
            ])->withInput();
        }

        // Upload credentials file
        $file = $request->file('credentials_file');
        $fileName = 'gmc-credentials/' . $validated['domain'] . '-' . strtoupper($validated['target_country']) . '-' . time() . '.json';
        $filePath = $file->storeAs('gmc-credentials', $validated['domain'] . '-' . strtoupper($validated['target_country']) . '-' . time() . '.json', 'local');

        // Validate JSON file
        $fileContent = Storage::get($filePath);
        $jsonData = json_decode($fileContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Storage::delete($filePath);
            return back()->withErrors([
                'credentials_file' => 'File JSON không hợp lệ. Vui lòng kiểm tra lại file.'
            ])->withInput();
        }

        $validated['credentials_path'] = $filePath;
        $validated['data_source_id'] = $validated['data_source_id'] ?? 'PRODUCT_FEED_API';
        $validated['target_country'] = strtoupper($validated['target_country']);
        $validated['is_active'] = $request->has('is_active');

        // Verify DomainCurrencyConfig exists
        $domainCurrency = DomainCurrencyConfig::getForDomain($validated['domain']);
        if (!$domainCurrency) {
            return back()->withErrors([
                'domain' => 'Chưa có cấu hình Domain Currency cho domain này. Vui lòng tạo cấu hình Domain Currency trước.'
            ])->withInput();
        }

        GmcConfig::create($validated);

        Log::info('GMC Config created', [
            'domain' => $validated['domain'],
            'target_country' => $validated['target_country'],
            'merchant_id' => $validated['merchant_id']
        ]);

        return redirect()
            ->route('admin.settings.gmc-config.index')
            ->with('success', 'Đã tạo cấu hình GMC: ' . $validated['name']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $config = GmcConfig::findOrFail($id);
        $countries = [
            'US' => 'United States (USD)',
            'GB' => 'United Kingdom (GBP)',
            'VN' => 'Vietnam (VND)',
            'CA' => 'Canada (CAD)',
            'AU' => 'Australia (AUD)',
            'NZ' => 'New Zealand (NZD)',
            'DE' => 'Germany (EUR)',
            'FR' => 'France (EUR)',
            'IT' => 'Italy (EUR)',
            'ES' => 'Spain (EUR)',
        ];

        return view('admin.settings.gmc-config.edit', compact('config', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $config = GmcConfig::findOrFail($id);

        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255'],
            'target_country' => ['required', 'string', 'size:2'],
            'name' => ['required', 'string', 'max:255'],
            'merchant_id' => ['required', 'string', 'max:255'],
            'data_source_id' => ['nullable', 'string', 'max:255'],
            'credentials_file' => ['nullable', 'file', 'mimes:json', 'max:2048'],
            'content_language' => ['required', 'string', 'max:5'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Check unique constraint: domain + target_country (excluding current)
        if ($config->domain != $validated['domain'] || $config->target_country != strtoupper($validated['target_country'])) {
            $existing = GmcConfig::where('domain', $validated['domain'])
                ->where('target_country', strtoupper($validated['target_country']))
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                return back()->withErrors([
                    'target_country' => 'Đã tồn tại cấu hình GMC cho domain này và thị trường này. Vui lòng chọn thị trường khác.'
                ])->withInput();
            }
        }

        // Handle credentials file upload
        if ($request->hasFile('credentials_file')) {
            // Delete old file if exists
            if ($config->credentials_path && Storage::exists($config->credentials_path)) {
                Storage::delete($config->credentials_path);
            }

            // Upload new file
            $file = $request->file('credentials_file');
            $filePath = $file->storeAs('gmc-credentials', $validated['domain'] . '-' . strtoupper($validated['target_country']) . '-' . time() . '.json', 'local');

            // Validate JSON file
            $fileContent = Storage::get($filePath);
            $jsonData = json_decode($fileContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Storage::delete($filePath);
                return back()->withErrors([
                    'credentials_file' => 'File JSON không hợp lệ. Vui lòng kiểm tra lại file.'
                ])->withInput();
            }

            $validated['credentials_path'] = $filePath;
        } else {
            // Keep existing file
            unset($validated['credentials_file']);
        }

        $validated['data_source_id'] = $validated['data_source_id'] ?? 'PRODUCT_FEED_API';
        $validated['target_country'] = strtoupper($validated['target_country']);
        $validated['is_active'] = $request->has('is_active');

        // Verify DomainCurrencyConfig exists
        $domainCurrency = DomainCurrencyConfig::getForDomain($validated['domain']);
        if (!$domainCurrency) {
            return back()->withErrors([
                'domain' => 'Chưa có cấu hình Domain Currency cho domain này. Vui lòng tạo cấu hình Domain Currency trước.'
            ])->withInput();
        }

        $config->update($validated);

        Log::info('GMC Config updated', [
            'config_id' => $id,
            'domain' => $validated['domain'],
            'target_country' => $validated['target_country']
        ]);

        return redirect()
            ->route('admin.settings.gmc-config.index')
            ->with('success', 'Đã cập nhật cấu hình GMC: ' . $validated['name']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $config = GmcConfig::findOrFail($id);
        $name = $config->name;

        // Delete credentials file if exists
        if ($config->credentials_path && Storage::exists($config->credentials_path)) {
            Storage::delete($config->credentials_path);
        }

        $config->delete();

        Log::info('GMC Config deleted', [
            'config_id' => $id,
            'name' => $name
        ]);

        return redirect()
            ->route('admin.settings.gmc-config.index')
            ->with('success', 'Đã xóa cấu hình GMC: ' . $name);
    }
}
