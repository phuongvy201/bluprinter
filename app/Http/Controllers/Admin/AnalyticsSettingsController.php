<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsSettingsController extends Controller
{
    public function edit(): View
    {
        $defaults = [
            'meta_pixel_id' => config('services.meta.pixel_id'),
            'tiktok_pixel_id' => config('services.tiktok.pixel_id'),
            'tiktok_test_event_code' => config('services.tiktok.test_event_code'),
            'google_tag_manager_id' => config('services.google.tag_manager_id'),
            'google_ads_id' => config('services.google.ads_id'),
        ];

        // Get multiple GTM IDs
        $gtmIdsSetting = Settings::get('analytics.google_tag_manager_ids', null);
        $gtmIdsArray = [];
        if ($gtmIdsSetting) {
            $decoded = json_decode($gtmIdsSetting, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $gtmIdsArray = $decoded;
            } else {
                $gtmIdsArray = array_filter(array_map('trim', preg_split('/[,\n\r]+/', $gtmIdsSetting)));
            }
        }
        // Fallback to single GTM ID
        if (empty($gtmIdsArray)) {
            $singleGtmId = Settings::get('analytics.google_tag_manager_id', $defaults['google_tag_manager_id']);
            if ($singleGtmId) {
                $gtmIdsArray = [$singleGtmId];
            }
        }

        $settings = [
            'meta_pixel_id' => Settings::get('analytics.meta_pixel_id', $defaults['meta_pixel_id']),
            'tiktok_pixel_id' => Settings::get('analytics.tiktok_pixel_id', $defaults['tiktok_pixel_id']),
            'tiktok_test_event_code' => Settings::get('analytics.tiktok_test_event_code', $defaults['tiktok_test_event_code']),
            'google_tag_manager_id' => Settings::get('analytics.google_tag_manager_id', $defaults['google_tag_manager_id']),
            'google_tag_manager_ids' => implode("\n", $gtmIdsArray), // Display as newline-separated
            'google_ads_id' => Settings::get('analytics.google_ads_id', $defaults['google_ads_id']),
        ];

        return view('admin.settings.analytics', compact('settings', 'defaults'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'meta_pixel_id' => ['nullable', 'string', 'max:64'],
            'tiktok_pixel_id' => ['nullable', 'string', 'max:64'],
            'tiktok_test_event_code' => ['nullable', 'string', 'max:64'],
            'google_tag_manager_id' => ['nullable', 'string', 'max:64'],
            'google_tag_manager_ids' => ['nullable', 'string'],
            'google_ads_id' => ['nullable', 'string', 'max:64'],
        ]);

        foreach ($validated as $key => $value) {
            if ($key === 'google_tag_manager_ids') {
                // Process multiple GTM IDs
                if ($value !== null && trim($value) !== '') {
                    // Split by newline or comma, filter empty values, trim each
                    $ids = array_filter(array_map('trim', preg_split('/[,\n\r]+/', $value)));
                    if (!empty($ids)) {
                        // Store as JSON array
                        Settings::set("analytics.google_tag_manager_ids", json_encode($ids));
                    } else {
                        Settings::set("analytics.google_tag_manager_ids", null);
                    }
                } else {
                    Settings::set("analytics.google_tag_manager_ids", null);
                }
            } else {
                Settings::set("analytics.$key", $value !== null ? trim($value) : null);
            }
        }

        return redirect()
            ->route('admin.settings.analytics.edit')
            ->with('success', 'Đã cập nhật cấu hình tracking.');
    }
}
