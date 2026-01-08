<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainAnalyticsConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DomainAnalyticsConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $configs = DomainAnalyticsConfig::orderBy('domain')->get();

        return view('admin.settings.domain-analytics.index', compact(
            'configs'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.settings.domain-analytics.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
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
        $fileName = 'domain-analytics/' . $validated['domain'] . '-' . time() . '.json';
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
            ->route('admin.settings.domain-analytics.index')
            ->with('success', 'Đã tạo cấu hình Google Analytics cho domain: ' . $validated['domain']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $config = DomainAnalyticsConfig::findOrFail($id);

        return view('admin.settings.domain-analytics.edit', compact(
            'config'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $config = DomainAnalyticsConfig::findOrFail($id);
        $oldDomain = $config->domain;

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
            ->route('admin.settings.domain-analytics.index')
            ->with('success', 'Đã cập nhật cấu hình Google Analytics cho domain: ' . $validated['domain']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $config = DomainAnalyticsConfig::findOrFail($id);
        $domain = $config->domain;

        // Xóa file credentials nếu có
        if ($config->credentials_file && Storage::exists($config->credentials_file)) {
            Storage::delete($config->credentials_file);
        }

        $config->delete();

        return redirect()
            ->route('admin.settings.domain-analytics.index')
            ->with('success', 'Đã xóa cấu hình Google Analytics cho domain: ' . $domain);
    }
}
